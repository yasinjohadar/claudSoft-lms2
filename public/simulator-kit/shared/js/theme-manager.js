/**
 * 🌙 Theme Manager - نظام إدارة المواضيع
 * يدير تبديل Light/Dark Mode وحفظ التفضيلات
 */

class ThemeManager {
  constructor(options = {}) {
    this.storageKey = options.storageKey || 'claudsoft-theme';
    this.themes = options.themes || ['light', 'dark'];
    this.defaultTheme = options.defaultTheme || 'light';
    this.html = document.documentElement;
    this.toggleSelector = options.toggleSelector || '.theme-toggle';
    this.prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');

    this.init();
  }

  /**
   * تهيئة نظام المواضيع
   */
  init() {
    // تطبيق الموضوع المحفوظ أو الافتراضي
    const savedTheme = this.getSavedTheme();
    const attrTheme = this.html.getAttribute('data-theme');
    const attrOk = attrTheme && this.themes.includes(attrTheme);
    const themeToApply =
      savedTheme || (attrOk ? attrTheme : null) || this.getSystemTheme();
    this.setTheme(themeToApply);

    // إضافة مستمع لزر التبديل
    this.attachToggleButton();

    // مراقبة تغييرات تفضيلات النظام
    this.prefersDarkScheme.addEventListener('change', () => {
      if (!this.getSavedTheme()) {
        this.setTheme(this.getSystemTheme());
      }
    });

    // تطبيق التغييرات عند تغيير التبويب
    window.addEventListener('focus', () => {
      const theme = this.getSavedTheme();
      if (theme) {
        this.setTheme(theme);
      }
    });

    console.log('✅ Theme Manager initialized');
  }

  /**
   * الحصول على الموضوع المحفوظ من localStorage
   */
  getSavedTheme() {
    return localStorage.getItem(this.storageKey);
  }

  /**
   * الحصول على موضوع النظام
   */
  getSystemTheme() {
    return this.prefersDarkScheme.matches ? 'dark' : 'light';
  }

  /**
   * تعيين موضوع معين
   * @param {string} theme - اسم الموضوع (light/dark)
   */
  setTheme(theme) {
    if (!this.themes.includes(theme)) {
      console.warn(`Theme "${theme}" not found. Using default theme.`);
      theme = this.defaultTheme;
    }

    // تطبيق الموضوع على HTML element
    this.html.setAttribute('data-theme', theme);

    // تحديث icon الزر
    this.updateToggleButtonIcon(theme);

    // تحديث رمز favicons (إذا أردت)
    this.updateFavicon(theme);

    // حفظ الموضوع
    this.persistTheme(theme);

    // إطلاق حدث مخصص
    this.dispatchThemeChangeEvent(theme);
  }

  /**
   * حفظ الموضوع في localStorage
   */
  persistTheme(theme) {
    localStorage.setItem(this.storageKey, theme);
  }

  /**
   * تبديل الموضوع بين light و dark
   */
  toggle() {
    const currentTheme = this.html.getAttribute('data-theme') || this.defaultTheme;
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    this.setTheme(newTheme);
  }

  /**
   * الحصول على الموضوع الحالي
   */
  getCurrentTheme() {
    return this.html.getAttribute('data-theme') || this.defaultTheme;
  }

  /**
   * تحديث icon الزر
   */
  updateToggleButtonIcon(theme) {
    const toggleButtons = document.querySelectorAll(this.toggleSelector);
    toggleButtons.forEach(btn => {
      if (theme === 'dark') {
        btn.textContent = '☀️';
        btn.setAttribute('aria-label', 'التبديل للوضع الفاتح');
        btn.title = 'وضع فاتح';
      } else {
        btn.textContent = '🌙';
        btn.setAttribute('aria-label', 'التبديل للوضع الداكن');
        btn.title = 'وضع داكن';
      }
    });
  }

  /**
   * تحديث favicon
   */
  updateFavicon(theme) {
    const favicon = document.querySelector('link[rel="icon"]');
    if (favicon) {
      // يمكنك تحديث favicon هنا إذا أردت
      if (theme === 'dark') {
        favicon.setAttribute('data-theme', 'dark');
      } else {
        favicon.setAttribute('data-theme', 'light');
      }
    }
  }

  /**
   * إطلاق حدث مخصص عند تغيير الموضوع
   */
  dispatchThemeChangeEvent(theme) {
    const event = new CustomEvent('themechange', {
      detail: { theme, timestamp: Date.now() },
      bubbles: true,
      cancelable: true
    });
    document.dispatchEvent(event);
  }

  /**
   * إضافة مستمع لزر التبديل
   */
  attachToggleButton() {
    const toggleButtons = document.querySelectorAll(this.toggleSelector);
    
    toggleButtons.forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        this.toggle();
      });

      // دعم لوحة المفاتيح
      btn.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          this.toggle();
        }
      });
    });

    // تحديث الأيقونة الأولية
    this.updateToggleButtonIcon(this.getCurrentTheme());
  }

  /**
   * حذف الموضوع المحفوظ والعودة للافتراضي
   */
  reset() {
    localStorage.removeItem(this.storageKey);
    const systemTheme = this.getSystemTheme();
    this.setTheme(systemTheme);
  }

  /**
   * تعطيل المواضيع
   */
  destroy() {
    const toggleButtons = document.querySelectorAll(this.toggleSelector);
    toggleButtons.forEach(btn => {
      btn.removeEventListener('click', this.toggle);
    });
    this.prefersDarkScheme.removeEventListener('change', () => {});
  }
}

/**
 * تهيئة مدير المواضيع عند تحميل الصفحة
 */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
  });
} else {
  window.themeManager = new ThemeManager();
}

/**
 * مثال للاستخدام المخصص:
 * 
 * const customThemeManager = new ThemeManager({
 *   storageKey: 'my-app-theme',
 *   themes: ['light', 'dark', 'auto'],
 *   defaultTheme: 'light',
 *   toggleSelector: '#theme-toggle'
 * });
 * 
 * // الاستماع لتغييرات الموضوع
 * document.addEventListener('themechange', (e) => {
 *   console.log('Theme changed to:', e.detail.theme);
 * });
 */
