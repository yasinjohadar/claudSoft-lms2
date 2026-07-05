<!-- Start Switcher -->
<div class="offcanvas offcanvas-end student-theme-switcher" tabindex="-1" id="switcher-canvas" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header student-theme-switcher__header border-0">
        <div>
            <h5 class="offcanvas-title mb-1" id="offcanvasRightLabel">إعدادات العرض</h5>
            <p class="student-theme-switcher__subtitle mb-0">خصّص مظهر لوحتك بسرعة</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="إغلاق"></button>
    </div>

    <div class="offcanvas-body student-theme-switcher__body">
        {{-- وضع العرض --}}
        <section class="student-theme-switcher__section">
            <h6 class="student-theme-switcher__label">
                <i class="fe fe-sun"></i>
                وضع العرض
            </h6>
            <div class="student-theme-switcher__grid student-theme-switcher__grid--2">
                <label class="student-theme-option">
                    <input class="visually-hidden" type="radio" name="theme-style" id="switcher-light-theme" checked>
                    <span class="student-theme-option__icon"><i class="fe fe-sun"></i></span>
                    <span class="student-theme-option__text">فاتح</span>
                </label>
                <label class="student-theme-option">
                    <input class="visually-hidden" type="radio" name="theme-style" id="switcher-dark-theme">
                    <span class="student-theme-option__icon"><i class="fe fe-moon"></i></span>
                    <span class="student-theme-option__text">داكن</span>
                </label>
            </div>
        </section>

        {{-- اللون الرئيسي --}}
        <section class="student-theme-switcher__section">
            <h6 class="student-theme-switcher__label">
                <i class="fe fe-droplet"></i>
                اللون الرئيسي
            </h6>
            <div class="student-theme-switcher__swatches">
                <label class="student-theme-swatch" title="أزرق">
                    <input class="form-check-input color-input color-primary-1" type="radio" name="theme-primary" id="switcher-primary">
                </label>
                <label class="student-theme-swatch" title="تركواز">
                    <input class="form-check-input color-input color-primary-2" type="radio" name="theme-primary" id="switcher-primary1">
                </label>
                <label class="student-theme-swatch" title="بنفسجي">
                    <input class="form-check-input color-input color-primary-3" type="radio" name="theme-primary" id="switcher-primary2">
                </label>
                <label class="student-theme-swatch" title="أخضر">
                    <input class="form-check-input color-input color-primary-4" type="radio" name="theme-primary" id="switcher-primary3">
                </label>
                <label class="student-theme-swatch" title="أحمر">
                    <input class="form-check-input color-input color-primary-5" type="radio" name="theme-primary" id="switcher-primary4">
                </label>
                <div class="student-theme-swatch student-theme-swatch--picker">
                    <div class="theme-container-primary"></div>
                    <div class="pickr-container-primary"></div>
                </div>
            </div>
        </section>

        {{-- شكل القائمة --}}
        <section class="student-theme-switcher__section">
            <h6 class="student-theme-switcher__label">
                <i class="fe fe-sidebar"></i>
                شكل القائمة
            </h6>
            <div class="student-theme-switcher__grid student-theme-switcher__grid--2">
                <label class="student-theme-option">
                    <input class="visually-hidden" type="radio" name="sidemenu-layout-styles" id="switcher-default-menu" checked>
                    <span class="student-theme-option__icon"><i class="fe fe-menu"></i></span>
                    <span class="student-theme-option__text">كاملة</span>
                </label>
                <label class="student-theme-option">
                    <input class="visually-hidden" type="radio" name="sidemenu-layout-styles" id="switcher-closed-menu">
                    <span class="student-theme-option__icon"><i class="fe fe-disc"></i></span>
                    <span class="student-theme-option__text">مصغّرة</span>
                </label>
            </div>
        </section>

        {{-- لون القائمة --}}
        <section class="student-theme-switcher__section">
            <h6 class="student-theme-switcher__label">
                <i class="fe fe-layout"></i>
                لون القائمة
            </h6>
            <div class="student-theme-switcher__grid student-theme-switcher__grid--2">
                <label class="student-theme-option student-theme-option--compact">
                    <input class="visually-hidden" type="radio" name="menu-colors" id="switcher-menu-light" checked>
                    <span class="student-theme-option__icon"><i class="fe fe-sun"></i></span>
                    <span class="student-theme-option__text">فاتحة</span>
                </label>
                <label class="student-theme-option student-theme-option--compact">
                    <input class="visually-hidden" type="radio" name="menu-colors" id="switcher-menu-dark">
                    <span class="student-theme-option__icon"><i class="fe fe-moon"></i></span>
                    <span class="student-theme-option__text">داكنة</span>
                </label>
            </div>
        </section>

        {{-- خيارات مخفية (مطلوبة لسكربت الثيم) --}}
        <div class="visually-hidden" aria-hidden="true">
            <input type="radio" name="direction" id="switcher-ltr">
            <input type="radio" name="direction" id="switcher-rtl" checked>

            <input type="radio" name="navigation-style" id="switcher-vertical" checked>
            <input type="radio" name="navigation-style" id="switcher-horizontal">

            <input type="radio" name="navigation-menu-styles" id="switcher-menu-click" checked>
            <input type="radio" name="navigation-menu-styles" id="switcher-menu-hover">
            <input type="radio" name="navigation-menu-styles" id="switcher-icon-click">
            <input type="radio" name="navigation-menu-styles" id="switcher-icon-hover">

            <input type="radio" name="sidemenu-layout-styles" id="switcher-icontext-menu">
            <input type="radio" name="sidemenu-layout-styles" id="switcher-icon-overlay">
            <input type="radio" name="sidemenu-layout-styles" id="switcher-detached">
            <input type="radio" name="sidemenu-layout-styles" id="switcher-double-menu">

            <input type="radio" name="page-styles" id="switcher-regular" checked>
            <input type="radio" name="page-styles" id="switcher-classic">
            <input type="radio" name="page-styles" id="switcher-modern">

            <input type="radio" name="layout-width" id="switcher-full-width" checked>
            <input type="radio" name="layout-width" id="switcher-boxed">

            <input type="radio" name="menu-positions" id="switcher-menu-fixed" checked>
            <input type="radio" name="menu-positions" id="switcher-menu-scroll">

            <input type="radio" name="header-positions" id="switcher-header-fixed" checked>
            <input type="radio" name="header-positions" id="switcher-header-scroll">

            <input type="radio" name="page-loader" id="switcher-loader-enable">
            <input type="radio" name="page-loader" id="switcher-loader-disable" checked>

            <input type="radio" name="menu-colors" id="switcher-menu-primary">
            <input type="radio" name="menu-colors" id="switcher-menu-gradient">
            <input type="radio" name="menu-colors" id="switcher-menu-transparent">

            <input type="radio" name="header-colors" id="switcher-header-light" checked>
            <input type="radio" name="header-colors" id="switcher-header-dark">
            <input type="radio" name="header-colors" id="switcher-header-primary">
            <input type="radio" name="header-colors" id="switcher-header-gradient">
            <input type="radio" name="header-colors" id="switcher-header-transparent">

            <input type="radio" name="theme-background" id="switcher-background">
            <input type="radio" name="theme-background" id="switcher-background1">
            <input type="radio" name="theme-background" id="switcher-background2">
            <input type="radio" name="theme-background" id="switcher-background3">
            <input type="radio" name="theme-background" id="switcher-background4">
            <input type="radio" name="theme-background" id="switcher-bg-img">
            <input type="radio" name="theme-background" id="switcher-bg-img1">
            <input type="radio" name="theme-background" id="switcher-bg-img2">
            <input type="radio" name="theme-background" id="switcher-bg-img3">
            <input type="radio" name="theme-background" id="switcher-bg-img4">

            <div class="theme-container-background"></div>
            <div class="pickr-container-background"></div>
        </div>
    </div>

    <div class="student-theme-switcher__footer">
        <a href="javascript:void(0);" id="reset-all" class="btn btn-outline-danger w-100 rounded-pill">
            <i class="fe fe-rotate-ccw me-2"></i>إعادة الضبط الافتراضي
        </a>
    </div>
</div>
<!-- End Switcher -->
