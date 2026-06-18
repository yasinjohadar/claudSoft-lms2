<?php

namespace App\Services;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class SmtpConnectionTestService
{
    /**
     * Test SMTP connection and authentication without sending an email.
     *
     * @param  array{host: string, port: int, encryption: string, username: string, password: string}  $config
     * @return array{success: bool, message: string, detail?: string}
     */
    public function test(array $config): array
    {
        $host = trim($config['host'] ?? '');
        $port = (int) ($config['port'] ?? 0);
        $encryption = strtolower($config['encryption'] ?? 'tls');
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        if ($host === '' || $port <= 0) {
            return [
                'success' => false,
                'message' => 'يرجى إدخال عنوان SMTP والمنفذ بشكل صحيح.',
            ];
        }

        if ($username === '' || $password === '') {
            return [
                'success' => false,
                'message' => 'يرجى إدخال اسم المستخدم وكلمة المرور.',
            ];
        }

        if (! extension_loaded('openssl')) {
            return [
                'success' => false,
                'message' => 'امتداد OpenSSL غير مفعّل على السيرفر. SMTP يتطلب OpenSSL للاتصال الآمن.',
            ];
        }

        $transport = $this->buildTransport($host, $port, $encryption, $username, $password);

        try {
            $transport->start();
            $transport->stop();

            return [
                'success' => true,
                'message' => 'تم الاتصال بخادم SMTP والمصادقة بنجاح دون إرسال بريد.',
            ];
        } catch (TransportExceptionInterface $e) {
            $detail = trim($e->getMessage());

            return [
                'success' => false,
                'message' => $this->translateError($detail, $encryption, $port),
                'detail' => $detail,
            ];
        } catch (\Throwable $e) {
            $detail = trim($e->getMessage());

            return [
                'success' => false,
                'message' => 'فشل اختبار الاتصال: '.$detail,
                'detail' => $detail,
            ];
        }
    }

    private function buildTransport(
        string $host,
        int $port,
        string $encryption,
        string $username,
        string $password
    ): EsmtpTransport {
        $scheme = $encryption === 'ssl' ? 'smtps' : 'smtp';

        $options = [];

        if ($encryption === 'tls') {
            $options['auto_tls'] = 'true';
        } elseif ($encryption === 'none') {
            $options['auto_tls'] = 'false';
        }

        if ($localDomain = $this->resolveLocalDomain()) {
            $options['local_domain'] = $localDomain;
        }

        if (! $this->shouldVerifyPeer()) {
            $options['verify_peer'] = 'false';
        }

        $dsn = new Dsn($scheme, $host, $username, $password, $port, $options);

        $factory = new EsmtpTransportFactory;
        /** @var EsmtpTransport $transport */
        $transport = $factory->create($dsn);

        /** @var SocketStream $stream */
        $stream = $transport->getStream();
        $stream->setTimeout((float) config('mail.smtp_timeout', env('MAIL_SMTP_TIMEOUT', 30)));

        return $transport;
    }

    private function shouldVerifyPeer(): bool
    {
        return filter_var(
            config('mail.smtp_verify_peer', env('MAIL_SMTP_VERIFY_PEER', true)),
            FILTER_VALIDATE_BOOL
        );
    }

    private function resolveLocalDomain(): ?string
    {
        $host = parse_url((string) config('app.url', ''), PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : null;
    }

    private function translateError(string $message, string $encryption, int $port): string
    {
        $lower = strtolower($message);

        if (str_contains($lower, 'authenticate') || str_contains($lower, 'authentication') || str_contains($lower, '535')) {
            return $this->withDetail(
                'فشلت المصادقة: تحقق من اسم المستخدم وكلمة المرور (استخدم App Password لـ Gmail).',
                $message
            );
        }

        if (str_contains($lower, 'connection refused') || str_contains($lower, 'could not connect') || str_contains($lower, 'connection could not be established')) {
            return $this->withDetail(
                'تعذر الاتصال بخادم SMTP. على السيرفر غالباً المنفذ '.$port.' محجوب من الجدار الناري — اطلب من الاستضافة فتح المنافذ الصادرة 587 و465.',
                $message
            );
        }

        if (str_contains($lower, 'starttls') || str_contains($lower, 'unable to connect with starttls')) {
            $hint = $encryption === 'tls' && $port === 587
                ? 'جرّب المنفذ 465 مع تشفير SSL، أو ثبّت حزمة ca-certificates على السيرفر. يمكنك مؤقتاً ضبط MAIL_SMTP_VERIFY_PEER=false في .env إذا كانت الشهادات ناقصة.'
                : 'تحقق من نوع التشفير والمنفذ (587 لـ TLS، 465 لـ SSL).';

            return $this->withDetail('فشل التشفير (STARTTLS/TLS): '.$hint, $message);
        }

        if (str_contains($lower, 'certificate') || str_contains($lower, 'ssl') || str_contains($lower, 'tls')) {
            return $this->withDetail(
                'فشل التحقق من شهادة SSL/TLS على السيرفر. ثبّت ca-certificates أو عطّل التحقق مؤقتاً عبر MAIL_SMTP_VERIFY_PEER=false في .env.',
                $message
            );
        }

        if (str_contains($lower, 'timed out') || str_contains($lower, 'timeout')) {
            return $this->withDetail(
                'انتهت مهلة الاتصال. تحقق من أن السيرفر يسمح بالاتصال الصادر إلى smtp.gmail.com على المنفذ '.$port.'.',
                $message
            );
        }

        return $this->withDetail('فشل اختبار الاتصال.', $message);
    }

    private function withDetail(string $message, string $technical): string
    {
        if ($technical === '') {
            return $message;
        }

        return $message.' — التفاصيل: '.$technical;
    }
}
