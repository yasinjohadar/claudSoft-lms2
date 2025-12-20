@extends('admin.layouts.master')

@section('page-title')
    التوثيق - n8n Integration
@stop

@section('styles')
<style>
.documentation-section {
    margin-bottom: 2rem;
}
.code-block {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
    border-left: 3px solid #4e73df;
}
pre {
    margin: 0;
    white-space: pre-wrap;
}
</style>
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-book me-2"></i>دليل استخدام n8n Integration
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.index') }}">n8n Integration</a></li>
                        <li class="breadcrumb-item active">التوثيق</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.n8n.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <!-- Table of Contents -->
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <h6 class="mb-0">المحتويات</h6>
                    </div>
                    <div class="card-body">
                        <nav class="nav flex-column">
                            <a class="nav-link" href="#overview">نظرة عامة</a>
                            <a class="nav-link" href="#outgoing">Outgoing Webhooks</a>
                            <a class="nav-link" href="#incoming">Incoming Webhooks</a>
                            <a class="nav-link" href="#events">الأحداث المتاحة</a>
                            <a class="nav-link" href="#handlers">المعالجات المتاحة</a>
                            <a class="nav-link" href="#security">الأمان</a>
                            <a class="nav-link" href="#examples">أمثلة</a>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <!-- Overview -->
                <div class="card documentation-section" id="overview">
                    <div class="card-header">
                        <h5 class="mb-0">نظرة عامة</h5>
                    </div>
                    <div class="card-body">
                        <p>يوفر نظام n8n Integration تكاملاً ثنائي الاتجاه بين نظام الـ LMS وأداة n8n لأتمتة العمليات:</p>
                        <ul>
                            <li><strong>Outgoing Webhooks:</strong> إرسال البيانات من Laravel إلى n8n عند حدوث أحداث معينة</li>
                            <li><strong>Incoming Webhooks:</strong> استقبال البيانات من n8n لتنفيذ إجراءات في النظام</li>
                        </ul>
                    </div>
                </div>

                <!-- Outgoing Webhooks -->
                <div class="card documentation-section" id="outgoing">
                    <div class="card-header">
                        <h5 class="mb-0">Outgoing Webhooks (Laravel → n8n)</h5>
                    </div>
                    <div class="card-body">
                        <h6>كيفية الإعداد:</h6>
                        <ol>
                            <li>قم بإنشاء Workflow في n8n</li>
                            <li>أضف Webhook trigger وانسخ الـ URL</li>
                            <li>في لوحة التحكم، اذهب إلى نقاط النهاية → إضافة جديد</li>
                            <li>أدخل معلومات نقطة النهاية واختر نوع الحدث</li>
                            <li>احفظ وفعّل نقطة النهاية</li>
                        </ol>

                        <h6 class="mt-4">البيانات المُرسلة:</h6>
                        <div class="code-block">
<pre>{
    "event_type": "student.enrolled",
    "payload": {
        "student_id": 123,
        "course_id": 456,
        "enrollment_date": "2025-11-28 10:30:00"
    },
    "timestamp": 1732785000,
    "system": "LMS"
}</pre>
                        </div>

                        <h6 class="mt-4">Headers:</h6>
                        <div class="code-block">
<pre>X-Webhook-Signature: [HMAC SHA256 signature]
X-Webhook-Event: student.enrolled
Content-Type: application/json</pre>
                        </div>
                    </div>
                </div>

                <!-- Incoming Webhooks -->
                <div class="card documentation-section" id="incoming">
                    <div class="card-header">
                        <h5 class="mb-0">Incoming Webhooks (n8n → Laravel)</h5>
                    </div>
                    <div class="card-body">
                        <h6>Webhook URL:</h6>
                        <div class="code-block">
<pre>{{ url('/api/webhooks/n8n/incoming') }}</pre>
                        </div>

                        <h6 class="mt-4">كيفية الاستخدام:</h6>
                        <ol>
                            <li>في n8n، أضف HTTP Request node</li>
                            <li>اختر Method: POST</li>
                            <li>أدخل الـ URL أعلاه</li>
                            <li>أضف Headers المطلوبة</li>
                            <li>أرسل البيانات بصيغة JSON</li>
                        </ol>

                        <h6 class="mt-4">مثال على الطلب:</h6>
                        <div class="code-block">
<pre>POST /api/webhooks/n8n/incoming
Headers:
  X-N8N-Signature: [your-secret-key]
  Content-Type: application/json

Body:
{
    "handler_type": "user.create",
    "data": {
        "name": "أحمد محمد",
        "email": "ahmed@example.com",
        "password": "secure_password",
        "role": "student"
    }
}</pre>
                        </div>
                    </div>
                </div>

                <!-- Available Events -->
                <div class="card documentation-section" id="events">
                    <div class="card-header">
                        <h5 class="mb-0">الأحداث المتاحة (Outgoing)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Event Type</th>
                                        <th>الوصف</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(config('webhooks.n8n.available_events', []) as $key => $label)
                                    <tr>
                                        <td><code>{{ $key }}</code></td>
                                        <td>{{ $label }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Available Handlers -->
                <div class="card documentation-section" id="handlers">
                    <div class="card-header">
                        <h5 class="mb-0">المعالجات المتاحة (Incoming)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Handler Type</th>
                                        <th>الوصف</th>
                                        <th>الحقول المطلوبة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($handlers as $handler)
                                    <tr>
                                        <td><code>{{ $handler->handler_type }}</code></td>
                                        <td>{{ $handler->description }}</td>
                                        <td>
                                            @if($handler->required_fields)
                                                <ul class="mb-0">
                                                    @foreach($handler->required_fields as $field)
                                                    <li><code>{{ $field }}</code></li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Security -->
                <div class="card documentation-section" id="security">
                    <div class="card-header">
                        <h5 class="mb-0">الأمان والتوثيق</h5>
                    </div>
                    <div class="card-body">
                        <h6>Outgoing Webhooks:</h6>
                        <p>يتم توقيع كل طلب باستخدام HMAC SHA256:</p>
                        <div class="code-block">
<pre>signature = HMAC-SHA256(payload + timestamp, secret_key)</pre>
                        </div>
                        <p class="mt-3">للتحقق في n8n:</p>
                        <ol>
                            <li>اقرأ الـ signature من header: <code>X-Webhook-Signature</code></li>
                            <li>احسب التوقيع المتوقع باستخدام نفس الخوارزمية</li>
                            <li>قارن التوقيعين</li>
                        </ol>

                        <h6 class="mt-4">Incoming Webhooks:</h6>
                        <p>يجب إرسال Secret Key في الـ header:</p>
                        <div class="code-block">
<pre>X-N8N-Signature: your-secret-key-here</pre>
                        </div>
                        <p class="mt-3">يمكنك العثور على الـ Secret في ملف <code>.env</code>:</p>
                        <div class="code-block">
<pre>N8N_WEBHOOK_SECRET=your-secret-key</pre>
                        </div>
                    </div>
                </div>

                <!-- Examples -->
                <div class="card documentation-section" id="examples">
                    <div class="card-header">
                        <h5 class="mb-0">أمثلة عملية</h5>
                    </div>
                    <div class="card-body">
                        <h6>مثال 1: إرسال إشعار WhatsApp عند تسجيل طالب جديد</h6>
                        <ol>
                            <li>أنشئ endpoint جديد بنوع حدث: <code>student.enrolled</code></li>
                            <li>في n8n، أنشئ workflow يستقبل الـ webhook</li>
                            <li>أضف WhatsApp node وربطه بالبيانات المستلمة</li>
                            <li>فعّل الـ endpoint والـ workflow</li>
                        </ol>

                        <h6 class="mt-4">مثال 2: إنشاء مستخدم جديد من n8n</h6>
                        <div class="code-block">
<pre>// في n8n workflow
POST {{ url('/api/webhooks/n8n/incoming') }}

Headers:
{
    "X-N8N-Signature": "{{ env('N8N_WEBHOOK_SECRET') }}",
    "Content-Type": "application/json"
}

Body:
{
    "handler_type": "user.create",
    "data": {
        "name": "محمد أحمد",
        "email": "mohamed@example.com",
        "password": "SecurePass123!",
        "role": "student"
    }
}</pre>
                        </div>

                        <h6 class="mt-4">مثال 3: تسجيل طالب في كورس</h6>
                        <div class="code-block">
<pre>{
    "handler_type": "course.enroll",
    "data": {
        "student_id": 123,
        "course_id": 456
    }
}</pre>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
