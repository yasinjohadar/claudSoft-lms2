<div class="btn-group flex-wrap mb-4 gap-1" role="group">
    <a href="{{ route('admin.flaxxa-wapi.settings.index') }}" class="btn btn-sm {{ request()->routeIs('admin.flaxxa-wapi.settings.*') ? 'btn-success' : 'btn-outline-success' }}">إعدادات التوكن</a>
    <a href="{{ route('admin.flaxxa-wapi.messages.index') }}" class="btn btn-sm {{ request()->routeIs('admin.flaxxa-wapi.messages.*') ? 'btn-primary' : 'btn-outline-primary' }}">سجل الإرسال</a>
    <a href="{{ route('admin.flaxxa-wapi.send.message') }}" class="btn btn-sm {{ request()->routeIs('admin.flaxxa-wapi.send.message') ? 'btn-primary' : 'btn-outline-primary' }}">إرسال نص</a>
    <a href="{{ route('admin.flaxxa-wapi.send.template') }}" class="btn btn-sm {{ request()->routeIs('admin.flaxxa-wapi.send.template') ? 'btn-primary' : 'btn-outline-primary' }}">إرسال قالب</a>
    <a href="{{ route('admin.flaxxa-wapi.send.campaign') }}" class="btn btn-sm {{ request()->routeIs('admin.flaxxa-wapi.send.campaign') ? 'btn-primary' : 'btn-outline-primary' }}">حملة</a>
    <a href="{{ route('admin.flaxxa-wapi.templates.index') }}" class="btn btn-sm {{ request()->routeIs('admin.flaxxa-wapi.templates.*') ? 'btn-secondary' : 'btn-outline-secondary' }}">قوالب Flaxxa</a>
</div>
