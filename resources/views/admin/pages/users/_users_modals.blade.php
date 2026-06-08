@foreach ($users as $user)
    @include('admin.pages.users.delete')
    @include('admin.pages.users.toggle_status')
@endforeach
