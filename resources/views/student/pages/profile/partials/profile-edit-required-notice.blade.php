@if($profileLocked ?? false)
<div class="student-profile-required-notice mb-4" role="alert">
    <div class="student-profile-required-notice__badge">
        <i class="fe fe-alert-circle me-1"></i>
        خطوة إلزامية قبل استخدام المنصة
    </div>
    <h5 class="student-profile-required-notice__title mb-2">
        هذه هي الصفحة المخصّصة لإكمال ملفك الشخصي
    </h5>
    <p class="student-profile-required-notice__lead mb-3">
        لا يمكنك فتح الكورسات أو أي قسم آخر في المنصة حتى تُكمل بياناتك بالكامل (100%).
        ابقَ في هذه الصفحة واتبع الخطوات التالية:
    </p>
    <ol class="student-profile-required-notice__steps mb-0">
        <li>املأ جميع الحقول الناقصة في النموذج أدناه (مُشار إليها باللون الأحمر).</li>
        <li>اضغط زر <strong>«حفظ وإكمال الملف»</strong> في أسفل النموذج.</li>
        <li>عند الوصول إلى 100% سيتم فتح المنصة لك تلقائياً.</li>
    </ol>
</div>
@endif
