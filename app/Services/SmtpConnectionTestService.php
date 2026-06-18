<?php

namespace App\Services;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class SmtpConnectionTestService
{
    /**
     * Test SMTP connection and authentication without sending an email.
     *
     * @param  array{host: string, port: int, encryption: string, username: string, password: string}  $config
     * @return array{success: bool, message: string}
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

        $transport = $this->buildTransport($host, $port, $encryption, $username, $password);

        try {
            $transport->start();
            $transport->stop();

            return [
                'success' => true,
                'message' => 'تم الاتصال بخادم SMTP والمصادقة بنجاح دون إرسال بريد.',
            ];
        } catch (TransportExceptionInterface $e) {
            return [
                'success' => false,
                'message' => $this->translateError($e->getMessage()),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'فشل اختبار الاتصال: '.$e->getMessage(),
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
        $useImplicitTls = $encryption === 'ssl';

        $transport = new EsmtpTransport($host, $port, $useImplicitTls);
        $transport->setUsername($username);
        $transport->setPassword($password);

        if ($encryption === 'none') {
            $transport->setAutoTls(false);
        } elseif ($encryption === 'tls') {
            $transport->setAutoTls(true);
        }

        return $transport;
    }

    private function translateError(string $message): string
    {
        $lower = strtolower($message);

        if (str_contains($lower, 'authenticate') || str_contains($lower, 'authentication') || str_contains($lower, '535')) {
            return 'فشلت المصادقة: تحقق من اسم المستخدم وكلمة المرور (استخدم App Password لـ Gmail).';
        }

        if (str_contains($lower, 'connection') || str_contains($lower, 'could not connect') || str_contains($lower, 'connection refused')) {
            return 'تعذر الاتصال بخادم SMTP: تحقق من العنوان والمنفذ وإعدادات الجدار الناري.';
        }

        if (str_contains($lower, 'starttls') || str_contains($lower, 'tls') || str_contains($lower, 'ssl')) {
            return 'فشل التشفير (TLS/SSL): تحقق من نوع التشفير والمنفذ (587 لـ TLS، 465 لـ SSL).';
        }

        if (str_contains($lower, 'timed out') || str_contains($lower, 'timeout')) {
            return 'انتهت مهلة الاتصال بخادم SMTP. حاول مرة أخرى أو تحقق من الشبكة.';
        }

        return 'فشل اختبار الاتصال: '.$message;
    }
}
