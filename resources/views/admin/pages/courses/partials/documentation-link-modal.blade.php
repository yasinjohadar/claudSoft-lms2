@include('admin.partials.documentation-link-modal', [
    'modalMode' => 'course',
    'course' => $course,
    'allCourses' => $allCourses ?? collect(),
])
