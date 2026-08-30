@props([
    // مصفوفة البطاقات. مفاتيح كل عنصر:
    //   theme     مطلوب  blue|green|orange|purple|gold|silver
    //                    (gold يحمل لمعة متحركة — خُصّص لبطاقة «نوع الحساب»)
    //   icon      مطلوب  صنف أيقونة Remix مثل ri-book-open-line
    //   title     مطلوب  العنوان الصغير أعلى الرقم
    //   value     مطلوب  الرقم (أو نص إن كان value_text)
    //   subtext   اختياري  السطر الوصفي أسفل الرقم
    //   suffix    اختياري  لاحقة الرقم مثل %
    //   decimals  اختياري  true لعرض منزلة عشرية واحدة
    //   value_text اختياري true إن كانت القيمة نصاً لا رقماً (بلا عدّ تصاعدي)
    //   route     اختياري  اسم راوت — تصبح البطاقة رابطاً
    'items' => [],
    // أصناف الأعمدة لكل بطاقة
    'cols' => 'col-xl-3 col-lg-6 col-md-6 col-sm-12',
    // أصناف إضافية على صف البطاقات
    'rowClass' => 'row g-3 mb-4',
])

{{--
    ودجات الإحصاء بنمط Hr-System — مكوّن موحّد.

    الغلاف .hr-stat-widgets إلزامي: تنسيقات portal-kpi.css معزولة تحته لأن
    الصنفين stat-value و stat-label مستخدمان بمعانٍ أخرى في المشروع.

    الاستخدام:
      <x-stat-widgets :items="$statWidgets" />
      <x-stat-widgets :items="$items" cols="col-xl-2 col-lg-4 col-md-6" />

    العدّ التصاعدي يعتمد [data-countup] الذي تعالجه سكربتات الصفحات
    (assets/js/admin-dashboard.js أو السكربت المدمج) — لا يحتاج المكوّن سكربتاً خاصاً.
--}}
<div class="{{ $rowClass }} hr-stat-widgets">
    @foreach ($items as $index => $item)
        @php
            // راوت غير موجود يُعرض بلا رابط بدل أن يُسقط الصفحة باستثناء
            $href = (!empty($item['route']) && Route::has($item['route']))
                ? route($item['route'], $item['route_params'] ?? [])
                : null;
            $tag = $href ? 'a' : 'div';
        @endphp
        <div class="{{ $cols }}">
            <{{ $tag }} @if($href) href="{{ $href }}" @endif
               class="dashboard-stat-link"
               style="--card-delay: {{ $index * 0.1 }}s">
                <div class="dashboard-stat-card dashboard-stat-{{ $item['theme'] ?? 'blue' }}">
                    <div class="stat-card-shine"></div>
                    <div class="stat-card-mesh"></div>
                    <div class="stat-card-bubble stat-card-bubble-1"></div>
                    <div class="stat-card-bubble stat-card-bubble-2"></div>
                    <div class="stat-card-bubble stat-card-bubble-3"></div>
                    <div class="stat-card-glow"></div>
                    <div class="stat-card-body">
                        <div class="stat-card-content">
                            <span class="stat-label">{{ $item['title'] }}</span>
                            @if (!empty($item['value_text']))
                                <span class="stat-value">{{ $item['value'] }}</span>
                            @else
                                <span class="stat-value"
                                      data-countup="{{ $item['value'] }}"
                                      @if(!empty($item['suffix'])) data-countup-suffix="{{ $item['suffix'] }}" @endif
                                      @if(!empty($item['decimals'])) data-countup-decimals="1" @endif>0</span>
                            @endif
                            @if (!empty($item['subtext']))
                                <span class="stat-subtext">{{ $item['subtext'] }}</span>
                            @endif
                        </div>
                        <div class="stat-icon-wrap">
                            <span class="stat-icon-ring"></span>
                            <span class="stat-icon-circle">
                                <i class="{{ $item['icon'] }}"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </{{ $tag }}>
        </div>
    @endforeach
</div>
