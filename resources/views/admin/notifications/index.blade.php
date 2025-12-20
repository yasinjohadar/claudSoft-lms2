@extends('admin.layouts.master')

@section('page-title', 'إرسال الإشعارات')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h4 class="page-title fw-semibold fs-18 mb-0">إرسال الإشعارات</h4>
            <p class="fw-normal text-muted fs-14 mb-0">إرسال إشعارات مخصصة للطلاب</p>
        </div>
        <div class="ms-md-auto d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('admin.notifications.history') }}" class="btn btn-secondary btn-wave">
                <i class="ri-history-line me-1"></i> سجل الإشعارات
            </a>
            <a href="{{ route('admin.notifications.statistics') }}" class="btn btn-info btn-wave">
                <i class="ri-bar-chart-line me-1"></i> الإحصائيات
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">إنشاء إشعار جديد</div>
                </div>
                <div class="card-body">
                    <form id="notificationForm">
                        @csrf

                        <!-- Recipient Type -->
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label">نوع المستلم <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="recipient_type" id="type_student" value="student" checked>
                                    <label class="btn btn-outline-primary" for="type_student">طالب واحد</label>

                                    <input type="radio" class="btn-check" name="recipient_type" id="type_course" value="course">
                                    <label class="btn btn-outline-primary" for="type_course">طلاب كورس</label>

                                    <input type="radio" class="btn-check" name="recipient_type" id="type_group" value="group">
                                    <label class="btn btn-outline-primary" for="type_group">طلاب مجموعة</label>

                                    <input type="radio" class="btn-check" name="recipient_type" id="type_broadcast" value="broadcast">
                                    <label class="btn btn-outline-primary" for="type_broadcast">جميع الطلاب</label>
                                </div>
                            </div>
                        </div>

                        <!-- Student Selection -->
                        <div class="row mb-4" id="student_selection">
                            <label class="col-sm-2 col-form-label">اختر الطالب <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select class="form-select" id="student_id" name="student_id">
                                    <option value="">-- اختر طالب --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Course Selection -->
                        <div class="row mb-4 d-none" id="course_selection">
                            <label class="col-sm-2 col-form-label">اختر الكورس <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select class="form-select" id="course_id" name="course_id">
                                    <option value="">-- اختر كورس --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Group Selection -->
                        <div class="row mb-4 d-none" id="group_selection">
                            <label class="col-sm-2 col-form-label">اختر المجموعة <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select class="form-select" id="group_id" name="group_id">
                                    <option value="">-- اختر مجموعة --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Notification Type -->
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label">نوع الإشعار <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select class="form-select" name="type" id="notification_type" required>
                                    <option value="admin_announcement">إعلان من الإدارة</option>
                                    <option value="custom_message">رسالة مخصصة</option>
                                    <option value="system_maintenance">صيانة النظام</option>
                                    <option value="course_update">تحديث في كورس</option>
                                    <option value="important_notice">إشعار مهم</option>
                                </select>
                            </div>
                        </div>

                        <!-- Title -->
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label">العنوان <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="title" id="title" placeholder="عنوان الإشعار" required>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label">الرسالة <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <textarea class="form-control" name="message" id="message" rows="5" placeholder="محتوى الإشعار" required></textarea>
                                <small class="text-muted">يمكنك كتابة رسالة طويلة ومفصلة</small>
                            </div>
                        </div>

                        <!-- Icon -->
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label">الأيقونة</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="icon" id="icon" placeholder="🔔" maxlength="4">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#iconModal">
                                        اختر أيقونة
                                    </button>
                                </div>
                                <small class="text-muted">اختر emoji أو اتركه فارغاً</small>
                            </div>
                        </div>

                        <!-- Action URL -->
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label">رابط الإجراء</label>
                            <div class="col-sm-10">
                                <input type="url" class="form-control" name="action_url" id="action_url" placeholder="https://example.com">
                                <small class="text-muted">رابط يتم الانتقال إليه عند النقر على الإشعار (اختياري)</small>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label">معاينة</label>
                            <div class="col-sm-10">
                                <div class="alert alert-light border">
                                    <div class="d-flex align-items-start">
                                        <div class="fs-20 me-3" id="preview_icon">🔔</div>
                                        <div>
                                            <h6 class="mb-1 fw-semibold" id="preview_title">عنوان الإشعار</h6>
                                            <p class="mb-0 text-muted" id="preview_message">محتوى الإشعار سيظهر هنا</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" class="btn btn-primary btn-wave" id="sendBtn">
                                    <i class="ri-send-plane-line me-1"></i> إرسال الإشعار
                                </button>
                                <button type="reset" class="btn btn-secondary btn-wave">
                                    <i class="ri-refresh-line me-1"></i> إعادة تعيين
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Icon Selector Modal -->
<div class="modal fade" id="iconModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">اختر أيقونة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2" style="grid-template-columns: repeat(8, 1fr);">
                    @foreach(['🔔', '📢', '📣', '⚠️', '✅', '❌', '🎉', '🎓', '📚', '💡', '🔥', '⭐', '🏆', '🎯', '💰', '📊', '📈', '📉', '🔔', '📝', '✏️', '📖', '🎨', '🎬'] as $emoji)
                        <button type="button" class="btn btn-light fs-20" onclick="selectIcon('{{ $emoji }}')" data-bs-dismiss="modal">
                            {{ $emoji }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
// Toggle recipient selection
document.querySelectorAll('[name="recipient_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        console.log('Recipient type changed:', this.value);
        const studentSelection = document.getElementById('student_selection');
        const courseSelection = document.getElementById('course_selection');
        const groupSelection = document.getElementById('group_selection');

        if (this.value === 'student') {
            studentSelection.classList.remove('d-none');
            courseSelection.classList.add('d-none');
            groupSelection.classList.add('d-none');
        } else if (this.value === 'course') {
            studentSelection.classList.add('d-none');
            courseSelection.classList.remove('d-none');
            groupSelection.classList.add('d-none');
        } else if (this.value === 'group') {
            studentSelection.classList.add('d-none');
            courseSelection.classList.add('d-none');
            groupSelection.classList.remove('d-none');
        } else {
            studentSelection.classList.add('d-none');
            courseSelection.classList.add('d-none');
            groupSelection.classList.add('d-none');
        }
    });
});

// Icon selector
function selectIcon(emoji) {
    document.getElementById('icon').value = emoji;
    updatePreview();
}

// Real-time preview
['title', 'message', 'icon'].forEach(field => {
    document.getElementById(field).addEventListener('input', updatePreview);
});

function updatePreview() {
    const title = document.getElementById('title').value || 'عنوان الإشعار';
    const message = document.getElementById('message').value || 'محتوى الإشعار سيظهر هنا';
    const icon = document.getElementById('icon').value || '🔔';

    document.getElementById('preview_title').textContent = title;
    document.getElementById('preview_message').textContent = message;
    document.getElementById('preview_icon').textContent = icon;
}

// Load students via AJAX
async function loadStudents(search = '') {
    try {
        const response = await fetch(`/admin/notifications/api/students?search=${search}`);
        const students = await response.json();
        console.log('Students loaded:', students.length);

        const select = document.getElementById('student_id');
        select.innerHTML = '<option value="">-- اختر طالب --</option>';

        students.forEach(student => {
            const option = document.createElement('option');
            option.value = student.id;
            option.textContent = `${student.name} (${student.email})`;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading students:', error);
    }
}

// Load courses via AJAX
async function loadCourses(search = '') {
    try {
        const response = await fetch(`/admin/notifications/api/courses?search=${search}`);
        const courses = await response.json();
        console.log('Courses loaded:', courses.length);

        const select = document.getElementById('course_id');
        select.innerHTML = '<option value="">-- اختر كورس --</option>';

        courses.forEach(course => {
            const option = document.createElement('option');
            option.value = course.id;
            option.textContent = course.title;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading courses:', error);
    }
}

// Load groups via AJAX
async function loadGroups(search = '') {
    try {
        const response = await fetch(`/admin/notifications/api/groups?search=${search}`);
        const groups = await response.json();
        console.log('Groups loaded:', groups.length);

        const select = document.getElementById('group_id');
        select.innerHTML = '<option value="">-- اختر مجموعة --</option>';

        groups.forEach(group => {
            const option = document.createElement('option');
            option.value = group.id;
            option.textContent = group.name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading groups:', error);
    }
}

// Load data on page load
loadStudents();
loadCourses();
loadGroups();

// Form submission
document.getElementById('notificationForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const recipientType = formData.get('recipient_type');

    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري الإرسال...';

    try {
        let endpoint = '';
        let data = {
            type: formData.get('type'),
            title: formData.get('title'),
            message: formData.get('message'),
            icon: formData.get('icon'),
            action_url: formData.get('action_url'),
        };

        if (recipientType === 'student') {
            endpoint = '/admin/notifications/send-to-student';
            data.student_id = formData.get('student_id');
        } else if (recipientType === 'course') {
            endpoint = '/admin/notifications/send-to-course';
            data.course_id = formData.get('course_id');
        } else if (recipientType === 'group') {
            endpoint = '/admin/notifications/send-to-group';
            data.group_id = formData.get('group_id');
        } else if (recipientType === 'broadcast') {
            endpoint = '/admin/notifications/send-broadcast';
        }

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            alert('✅ ' + result.message);
            this.reset();
            updatePreview();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ حدث خطأ أثناء إرسال الإشعار');
    } finally {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="ri-send-plane-line me-1"></i> إرسال الإشعار';
    }
});
</script>
</div>
</div>
<!-- End::app-content -->
@endsection
