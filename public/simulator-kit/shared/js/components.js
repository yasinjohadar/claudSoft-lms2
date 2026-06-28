/**
 * 🧩 Components - مكونات JavaScript قابلة لإعادة الاستخدام
 */

/**
 * فئة لإنشاء بطاقة (Card)
 */
class Card {
  constructor(options = {}) {
    this.title = options.title || '';
    this.content = options.content || '';
    this.footer = options.footer || '';
    this.className = options.className || '';
    this.onClick = options.onClick || null;
  }

  render() {
    const card = document.createElement('div');
    card.className = `card ${this.className}`;

    if (this.title) {
      const header = document.createElement('div');
      header.className = 'card-header';
      header.innerHTML = `<h3>${this.title}</h3>`;
      card.appendChild(header);
    }

    const body = document.createElement('div');
    body.className = 'card-body';
    body.innerHTML = this.content;
    card.appendChild(body);

    if (this.footer) {
      const footer = document.createElement('div');
      footer.className = 'card-footer';
      footer.innerHTML = this.footer;
      card.appendChild(footer);
    }

    if (this.onClick) {
      card.addEventListener('click', this.onClick);
      card.style.cursor = 'pointer';
    }

    return card;
  }
}

/**
 * فئة لإنشاء زر
 */
class Button {
  constructor(options = {}) {
    this.text = options.text || 'زر';
    this.type = options.type || 'button';
    this.variant = options.variant || 'primary';
    this.size = options.size || 'md';
    this.icon = options.icon || '';
    this.disabled = options.disabled || false;
    this.onClick = options.onClick || null;
    this.title = options.title || '';
  }

  render() {
    const button = document.createElement('button');
    button.type = this.type;
    button.className = `btn btn-${this.variant} btn-${this.size}`;
    button.textContent = this.text;
    button.disabled = this.disabled;
    if (this.title) button.title = this.title;

    if (this.icon) {
      const icon = document.createElement('span');
      icon.textContent = this.icon;
      button.prepend(icon);
    }

    if (this.onClick) {
      button.addEventListener('click', this.onClick);
    }

    return button;
  }
}

/**
 * فئة لإنشاء Input Field
 */
class InputField {
  constructor(options = {}) {
    this.label = options.label || '';
    this.name = options.name || '';
    this.type = options.type || 'text';
    this.placeholder = options.placeholder || '';
    this.value = options.value || '';
    this.required = options.required || false;
    this.error = options.error || '';
    this.helperText = options.helperText || '';
    this.onChange = options.onChange || null;
    this.onBlur = options.onBlur || null;
  }

  render() {
    const group = document.createElement('div');
    group.className = 'input-group';

    if (this.label) {
      const label = document.createElement('label');
      label.textContent = this.label;
      label.htmlFor = this.name;
      group.appendChild(label);
    }

    const input = document.createElement('input');
    input.className = 'input';
    input.type = this.type;
    input.name = this.name;
    input.placeholder = this.placeholder;
    input.value = this.value;
    input.required = this.required;

    if (this.error) {
      input.classList.add('input-error');
    }

    if (this.onChange) {
      input.addEventListener('change', this.onChange);
    }
    if (this.onBlur) {
      input.addEventListener('blur', this.onBlur);
    }

    group.appendChild(input);

    if (this.error) {
      const errorMsg = document.createElement('small');
      errorMsg.className = 'input-small-text error';
      errorMsg.textContent = this.error;
      group.appendChild(errorMsg);
    } else if (this.helperText) {
      const helperMsg = document.createElement('small');
      helperMsg.className = 'input-small-text';
      helperMsg.textContent = this.helperText;
      group.appendChild(helperMsg);
    }

    return group;
  }
}

/**
 * فئة لإنشاء Alert
 */
class Alert {
  constructor(options = {}) {
    this.title = options.title || '';
    this.message = options.message || '';
    this.type = options.type || 'info'; // success, danger, warning, primary
    this.dismissible = options.dismissible !== false;
    this.icon = options.icon || '';
  }

  render() {
    const alert = document.createElement('div');
    alert.className = `alert alert-${this.type}`;

    const content = document.createElement('div');
    content.className = 'alert-content';

    if (this.title) {
      const title = document.createElement('div');
      title.className = 'alert-title';
      if (this.icon) {
        title.innerHTML = `<span>${this.icon}</span> ${this.title}`;
      } else {
        title.textContent = this.title;
      }
      content.appendChild(title);
    }

    const message = document.createElement('div');
    message.textContent = this.message;
    content.appendChild(message);

    alert.appendChild(content);

    if (this.dismissible) {
      const closeBtn = document.createElement('button');
      closeBtn.type = 'button';
      closeBtn.textContent = '✕';
      closeBtn.style.cssText = 'background: none; border: none; cursor: pointer; font-size: 20px;';
      closeBtn.addEventListener('click', () => alert.remove());
      alert.appendChild(closeBtn);
    }

    return alert;
  }
}

/**
 * فئة لإنشاء Modal / Dialog
 */
class Modal {
  constructor(options = {}) {
    this.title = options.title || 'العنوان';
    this.content = options.content || '';
    this.footer = options.footer || '';
    this.size = options.size || 'md'; // sm, md, lg
    this.backdrop = options.backdrop !== false;
    this.onClose = options.onClose || null;
  }

  render() {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    `;

    if (this.backdrop) {
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          this.close();
        }
      });
    }

    const dialogContent = document.createElement('div');
    dialogContent.className = 'modal-content';
    dialogContent.style.cssText = `
      background: white;
      border-radius: 12px;
      box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
      max-width: ${this.size === 'sm' ? '400px' : this.size === 'lg' ? '800px' : '600px'};
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
    `;

    const header = document.createElement('div');
    header.className = 'modal-header';
    header.style.cssText = 'padding: 24px; border-bottom: 1px solid #e5e7eb;';
    header.innerHTML = `
      <h2 style="margin: 0;">${this.title}</h2>
      <button class="modal-close" style="position: absolute; right: 16px; top: 16px; background: none; border: none; cursor: pointer; font-size: 24px;">✕</button>
    `;

    const body = document.createElement('div');
    body.className = 'modal-body';
    body.style.cssText = 'padding: 24px;';
    body.innerHTML = this.content;

    dialogContent.appendChild(header);
    dialogContent.appendChild(body);

    if (this.footer) {
      const footer = document.createElement('div');
      footer.className = 'modal-footer';
      footer.style.cssText = 'padding: 16px 24px; border-top: 1px solid #e5e7eb; display: flex; gap: 12px; justify-content: flex-end;';
      footer.innerHTML = this.footer;
      dialogContent.appendChild(footer);
    }

    modal.appendChild(dialogContent);

    // وظائف
    this.modal = modal;
    this.closeBtn = header.querySelector('.modal-close');

    this.closeBtn.addEventListener('click', () => this.close());

    return modal;
  }

  open() {
    if (this.modal) {
      this.modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }
  }

  close() {
    if (this.modal) {
      this.modal.style.display = 'none';
      document.body.style.overflow = 'auto';
      if (this.onClose) this.onClose();
    }
  }

  destroy() {
    if (this.modal) {
      this.modal.remove();
    }
  }
}

/**
 * فئة لإنشاء Spinner/Loading
 */
class Spinner {
  constructor(options = {}) {
    this.size = options.size || 'md'; // sm, md, lg
    this.text = options.text || '';
  }

  render() {
    const container = document.createElement('div');
    container.style.cssText = `
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 16px;
    `;

    const spinner = document.createElement('div');
    spinner.className = `spinner spinner-${this.size}`;

    container.appendChild(spinner);

    if (this.text) {
      const text = document.createElement('p');
      text.textContent = this.text;
      container.appendChild(text);
    }

    return container;
  }
}

/**
 * فئة لإنشاء Tabs
 */
class Tabs {
  constructor(options = {}) {
    this.tabs = options.tabs || []; // [{label, content}, ...]
    this.activeIndex = options.activeIndex || 0;
    this.onChange = options.onChange || null;
  }

  render() {
    const container = document.createElement('div');

    const tabButtons = document.createElement('div');
    tabButtons.className = 'tabs-header';
    tabButtons.style.cssText = 'display: flex; gap: 12px; border-bottom: 1px solid #e5e7eb;';

    this.tabs.forEach((tab, index) => {
      const btn = document.createElement('button');
      btn.className = `tab-btn ${index === this.activeIndex ? 'active' : ''}`;
      btn.textContent = tab.label;
      btn.style.cssText = `
        padding: 12px 16px;
        border: none;
        background: none;
        cursor: pointer;
        font-weight: 500;
        border-bottom: 2px solid ${index === this.activeIndex ? '#3b82f6' : 'transparent'};
        transition: all 0.3s;
      `;

      btn.addEventListener('click', () => {
        this.setActive(index);
        if (this.onChange) this.onChange(index);
      });

      tabButtons.appendChild(btn);
    });

    const tabContent = document.createElement('div');
    tabContent.className = 'tabs-content';
    tabContent.style.cssText = 'padding: 16px 0;';

    this.tabs.forEach((tab, index) => {
      const content = document.createElement('div');
      content.className = 'tab-content';
      content.style.cssText = `display: ${index === this.activeIndex ? 'block' : 'none'};`;
      content.innerHTML = tab.content;
      tabContent.appendChild(content);
    });

    container.appendChild(tabButtons);
    container.appendChild(tabContent);

    this.container = container;
    this.tabButtons = tabButtons;
    this.tabContent = tabContent;

    return container;
  }

  setActive(index) {
    const buttons = this.tabButtons.querySelectorAll('.tab-btn');
    const contents = this.tabContent.querySelectorAll('.tab-content');

    buttons.forEach((btn, i) => {
      if (i === index) {
        btn.classList.add('active');
        btn.style.borderBottomColor = '#3b82f6';
      } else {
        btn.classList.remove('active');
        btn.style.borderBottomColor = 'transparent';
      }
    });

    contents.forEach((content, i) => {
      content.style.display = i === index ? 'block' : 'none';
    });

    this.activeIndex = index;
  }
}

// تصدير جميع المكونات
window.Components = {
  Card,
  Button,
  InputField,
  Alert,
  Modal,
  Spinner,
  Tabs
};

console.log('✅ Components loaded');
