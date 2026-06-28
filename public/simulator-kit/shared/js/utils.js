/**
 * 🔧 Utilities - دوال مساعدة عامة
 */

/**
 * نسخ نص إلى الحافظة
 */
async function copyToClipboard(text) {
  try {
    await navigator.clipboard.writeText(text);
    return true;
  } catch (err) {
    console.error('Failed to copy:', err);
    return false;
  }
}

/**
 * تنسيق الأرقام بفواصل الآلاف
 */
function formatNumber(num) {
  return new Intl.NumberFormat('ar-SA').format(num);
}

/**
 * تنسيق الوقت بصيغة صديقة للمستخدم
 */
function formatTime(seconds) {
  if (seconds < 60) return `${seconds}ث`;
  if (seconds < 3600) return `${Math.floor(seconds / 60)}د`;
  return `${Math.floor(seconds / 3600)}س`;
}

/**
 * تأخير العملية (Promise-based)
 */
function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * حفظ بيانات في localStorage
 */
function saveToStorage(key, value, prefix = 'app') {
  const storageKey = `${prefix}:${key}`;
  try {
    localStorage.setItem(storageKey, JSON.stringify(value));
    return true;
  } catch (err) {
    console.error('Storage error:', err);
    return false;
  }
}

/**
 * استرجاع بيانات من localStorage
 */
function getFromStorage(key, defaultValue = null, prefix = 'app') {
  const storageKey = `${prefix}:${key}`;
  try {
    const value = localStorage.getItem(storageKey);
    return value ? JSON.parse(value) : defaultValue;
  } catch (err) {
    console.error('Storage error:', err);
    return defaultValue;
  }
}

/**
 * حذف بيانات من localStorage
 */
function removeFromStorage(key, prefix = 'app') {
  const storageKey = `${prefix}:${key}`;
  try {
    localStorage.removeItem(storageKey);
    return true;
  } catch (err) {
    console.error('Storage error:', err);
    return false;
  }
}

/**
 * مسح كل localStorage
 */
function clearStorage(prefix = 'app') {
  try {
    const keys = Object.keys(localStorage);
    keys.forEach(key => {
      if (key.startsWith(`${prefix}:`)) {
        localStorage.removeItem(key);
      }
    });
    return true;
  } catch (err) {
    console.error('Storage error:', err);
    return false;
  }
}

/**
 * إظهار رسالة Toast بسيطة
 */
function showToast(message, type = 'info', duration = 3000) {
  const toastContainer = document.getElementById('toast-container') || createToastContainer();
  
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.textContent = message;
  toast.style.cssText = `
    padding: 16px 24px;
    margin-bottom: 12px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    animation: slideIn 0.3s ease-out;
  `;

  if (type === 'success') toast.style.backgroundColor = '#10b981';
  if (type === 'error') toast.style.backgroundColor = '#ef4444';
  if (type === 'warning') toast.style.backgroundColor = '#f59e0b';
  if (type === 'info') toast.style.backgroundColor = '#3b82f6';

  toastContainer.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = 'slideOut 0.3s ease-out';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

/**
 * إنشاء حاوية Toast
 */
function createToastContainer() {
  const container = document.createElement('div');
  container.id = 'toast-container';
  container.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    max-width: 400px;
  `;
  document.body.appendChild(container);
  return container;
}

/**
 * إخفاء تحميل
 */
function showLoading(element, show = true) {
  if (show) {
    element.setAttribute('data-loading', 'true');
    element.style.opacity = '0.6';
    element.style.pointerEvents = 'none';
  } else {
    element.removeAttribute('data-loading');
    element.style.opacity = '1';
    element.style.pointerEvents = 'auto';
  }
}

/**
 * التحقق من صحة البريد الإلكتروني
 */
function isValidEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}

/**
 * الحصول على معاملات URL
 */
function getQueryParam(param) {
  const params = new URLSearchParams(window.location.search);
  return params.get(param);
}

/**
 * إنشاء معرف فريد
 */
function generateUUID() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    const r = Math.random() * 16 | 0;
    const v = c === 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}

/**
 * إعادة محاولة دالة عدة مرات
 */
async function retry(fn, maxAttempts = 3, delay = 1000) {
  for (let i = 0; i < maxAttempts; i++) {
    try {
      return await fn();
    } catch (err) {
      if (i === maxAttempts - 1) throw err;
      await delay(delay * (i + 1));
    }
  }
}

/**
 * تجميع الدوال
 */
function throttle(fn, limit) {
  let inThrottle;
  return function(...args) {
    if (!inThrottle) {
      fn.apply(this, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
}

/**
 * تأجيل تنفيذ الدالة
 */
function debounce(fn, limit) {
  let timeout;
  return function(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn.apply(this, args), limit);
  };
}

/**
 * قياس أداء عملية
 */
function measurePerformance(name, fn) {
  const start = performance.now();
  const result = fn();
  const end = performance.now();
  console.log(`⏱️ ${name}: ${(end - start).toFixed(2)}ms`);
  return result;
}

/**
 * تسجيل بيانات للتطوير
 */
const log = {
  info: (msg, data) => console.log(`ℹ️ ${msg}`, data || ''),
  success: (msg, data) => console.log(`✅ ${msg}`, data || ''),
  warn: (msg, data) => console.warn(`⚠️ ${msg}`, data || ''),
  error: (msg, data) => console.error(`❌ ${msg}`, data || ''),
  debug: (msg, data) => console.log(`🐛 ${msg}`, data || '')
};

// تصدير جميع الدوال للاستخدام العام
window.Utils = {
  copyToClipboard,
  formatNumber,
  formatTime,
  delay,
  saveToStorage,
  getFromStorage,
  removeFromStorage,
  clearStorage,
  showToast,
  showLoading,
  isValidEmail,
  getQueryParam,
  generateUUID,
  retry,
  throttle,
  debounce,
  measurePerformance,
  log
};

console.log('✅ Utilities loaded');
