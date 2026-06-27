@php
    $googleEvents = collect($googleDataLayerPageEvents ?? [])
        ->merge($googleDataLayerFlashEvents ?? [])
        ->values()
        ->all();
@endphp

@if(!empty($googleEvents))
<script>
(function () {
    window.dataLayer = window.dataLayer || [];
    var events = @json($googleEvents);

    events.forEach(function (item) {
        window.dataLayer.push(Object.assign({ event: item.event }, item.data || {}));
    });
})();
</script>
@endif
