/**
 * 🎯 Constants - الثوابت العامة
 */

const CONSTANTS = {
  /* ========== ENVIRONMENT ========== */
  ENV: {
    PRODUCTION: 'production',
    DEVELOPMENT: 'development',
    TESTING: 'testing',
    CURRENT: process.env.NODE_ENV || 'development'
  },

  /* ========== STORAGE KEYS ========== */
  STORAGE: {
    PREFIX: 'simulation-langs',
    THEME: 'theme-preference',
    USER_SETTINGS: 'user-settings',
    BOOKMARKS: 'bookmarks',
    PROGRESS: 'learning-progress',
    TUTORIALS: 'completed-tutorials',
    ANALYTICS: 'user-analytics'
  },

  /* ========== SECTIONS ========== */
  SECTIONS: {
    CSS: {
      id: 'css',
      name: 'CSS',
      color: '#06b6d4',
      emoji: '🎨'
    },
    JAVASCRIPT: {
      id: 'javascript',
      name: 'JavaScript',
      color: '#f59e0b',
      emoji: '⚡'
    },
    DART: {
      id: 'dart',
      name: 'Dart Basics',
      color: '#8b5cf6',
      emoji: '🎯'
    },
    FLUTTER: {
      id: 'flutter',
      name: 'Flutter Widgets',
      color: '#ec4899',
      emoji: '📱'
    },
    JS_BASICS: {
      id: 'js-basics',
      name: 'JavaScript Basics',
      color: '#6366f1',
      emoji: '📚'
    },
    PHP: {
      id: 'php',
      name: 'PHP',
      color: '#777bb4',
      emoji: '🐘'
    }
  },

  /* ========== COLORS ========== */
  COLORS: {
    PRIMARY: '#3b82f6',
    ACCENT: '#ec4899',
    SUCCESS: '#10b981',
    WARNING: '#f59e0b',
    DANGER: '#ef4444',
    INFO: '#3b82f6'
  },

  /* ========== BREAKPOINTS ========== */
  BREAKPOINTS: {
    SM: 640,
    MD: 1024,
    LG: 1280,
    XL: 1536
  },

  /* ========== ANIMATIONS ========== */
  ANIMATIONS: {
    DURATION_FAST: 150,      // ms
    DURATION_BASE: 300,      // ms
    DURATION_SLOW: 500,      // ms
    EASING_EASE_OUT: 'ease-out',
    EASING_EASE_IN_OUT: 'ease-in-out'
  },

  /* ========== TOAST MESSAGES ========== */
  TOAST: {
    COPY_SUCCESS: 'تم النسخ إلى الحافظة! ✓',
    COPY_ERROR: 'فشل في النسخ',
    SAVE_SUCCESS: 'تم الحفظ بنجاح! ✓',
    SAVE_ERROR: 'فشل في الحفظ',
    LOAD_ERROR: 'فشل في تحميل البيانات',
    NETWORK_ERROR: 'خطأ في الاتصال'
  },

  /* ========== API ENDPOINTS (مثال) ========== */
  API: {
    BASE_URL: process.env.API_BASE_URL || 'https://api.example.com',
    ENDPOINTS: {
      TUTORIALS: '/tutorials',
      PROGRESS: '/progress',
      SUBMISSIONS: '/submissions',
      FEEDBACK: '/feedback'
    }
  },

  /* ========== KEYBOARD CODES ========== */
  KEYS: {
    ENTER: 'Enter',
    ESCAPE: 'Escape',
    SPACE: ' ',
    TAB: 'Tab',
    ARROW_UP: 'ArrowUp',
    ARROW_DOWN: 'ArrowDown',
    ARROW_LEFT: 'ArrowLeft',
    ARROW_RIGHT: 'ArrowRight'
  },

  /* ========== LOCAL STORAGE EXPIRY ========== */
  EXPIRY: {
    SESSION: 0,           // لا تنتهي
    ONE_DAY: 24 * 60 * 60 * 1000,
    ONE_WEEK: 7 * 24 * 60 * 60 * 1000,
    ONE_MONTH: 30 * 24 * 60 * 60 * 1000
  },

  /* ========== DIFFICULTY LEVELS ========== */
  DIFFICULTY: {
    BEGINNER: 'beginner',
    INTERMEDIATE: 'intermediate',
    ADVANCED: 'advanced'
  },

  /* ========== LEARNING PATHS ========== */
  LEARNING_PATHS: {
    CSS_MASTERY: {
      id: 'css-mastery',
      name: 'إتقان CSS',
      lessons: 13
    },
    JS_FUNDAMENTALS: {
      id: 'js-fundamentals',
      name: 'أساسيات JavaScript',
      lessons: 13
    },
    DART_STARTER: {
      id: 'dart-starter',
      name: 'البدء مع Dart',
      lessons: 13
    },
    FLUTTER_ESSENTIALS: {
      id: 'flutter-essentials',
      name: 'أساسيات Flutter',
      lessons: 35
    }
  },

  /* ========== REGEX PATTERNS ========== */
  REGEX: {
    EMAIL: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    URL: /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/,
    HEX_COLOR: /^#?([a-f\d]{6}|[a-f\d]{3})$/i,
    ARABIC: /[\u0600-\u06FF]/
  },

  /* ========== FEATURE FLAGS ========== */
  FEATURES: {
    DARK_MODE: true,
    ANALYTICS: true,
    OFFLINE_MODE: false,
    PWA: false,
    SOCIAL_SHARING: false
  },

  /* ========== PERFORMANCE ========== */
  PERFORMANCE: {
    DEBOUNCE_DELAY: 300,
    THROTTLE_DELAY: 500,
    API_TIMEOUT: 10000,
    CACHE_DURATION: 60000
  },

  /* ========== PAGINATION ========== */
  PAGINATION: {
    DEFAULT_PAGE_SIZE: 10,
    MAX_PAGE_SIZE: 100,
    LIMIT_MULTIPLIER: 1.5
  }
};

// تصدير الثوابت
window.CONSTANTS = CONSTANTS;

console.log('✅ Constants loaded');
