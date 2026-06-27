@php
    $metaEvents = collect($metaPixelPageEvents ?? [])
        ->merge($metaPixelFlashEvents ?? [])
        ->values()
        ->all();
@endphp

@if(!empty($metaEvents))
<script>
(function () {
    if (typeof fbq !== 'function') return;

    var events = @json($metaEvents);

    events.forEach(function (item) {
        var options = item.event_id ? { eventID: item.event_id } : {};
        var data = item.data || {};

        if (item.event === 'LeadStarted') {
            fbq('trackCustom', 'LeadStarted', data, options);
            return;
        }

        fbq('track', item.event, data, options);
    });
})();
</script>
@endif
