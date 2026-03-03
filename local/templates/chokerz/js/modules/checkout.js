/**
 * /local/templates/chokerz/js/modules/checkout.js
 * ES6 модуль оформления заказа CHOKERZ
 * Vanilla JS — без jQuery, без глобальных переменных
 */

// DEBUG-флаг: активируется наличием data-debug на <html>
const DEBUG = document.documentElement.dataset.debug !== undefined;

// ─── Конфигурация валидации ───────────────────────────────────────────────────

const VALIDATORS = {
  required: (value) => value.trim().length > 0,
  email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim()),
  phone: (value) => /^(\+7|8)?[\s\-]?\(?\d{3}\)?[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}$/.test(value.trim()),
};

// ─── Утилиты ─────────────────────────────────────────────────────────────────

const showFieldError = (input, message) => {
  const field = input.closest('.order-form__field');
  if (!field) return;
  const errorEl = field.querySelector('.checkout__error');
  if (!errorEl) return;
  input.classList.add('order-form__input--error');
  errorEl.textContent = message;
  errorEl.removeAttribute('hidden');
};

const clearFieldError = (input) => {
  const field = input.closest('.order-form__field');
  if (!field) return;
  const errorEl = field.querySelector('.checkout__error');
  if (!errorEl) return;
  input.classList.remove('order-form__input--error');
  errorEl.textContent = '';
  errorEl.setAttribute('hidden', '');
};

const validateField = (input) => {
  const rules = (input.dataset.validate || '').split('|').filter(Boolean);
  if (!rules.length) return true;
  for (const rule of rules) {
    if (!VALIDATORS[rule]?.(input.value)) {
      showFieldError(input, input.dataset.validateMsg || 'Заполните поле корректно');
      return false;
    }
  }
  clearFieldError(input);
  return true;
};

const validateConditionalField = (input) => {
  const isVisible = !input.closest('[hidden]') && !input.closest('[aria-hidden="true"]');
  if (!isVisible) return true;
  const rules = (input.dataset.validateConditional || '').split('|').filter(Boolean);
  for (const rule of rules) {
    if (!VALIDATORS[rule]?.(input.value)) {
      showFieldError(input, input.dataset.validateMsg || 'Заполните поле корректно');
      return false;
    }
  }
  clearFieldError(input);
  return true;
};

// ─── Маска телефона ───────────────────────────────────────────────────────────

const applyPhoneMask = (input) => {
  let digits = input.value.replace(/\D/g, '');
  if (digits.startsWith('8') || digits.startsWith('7')) digits = digits.slice(1);
  digits = digits.slice(0, 10);
  let masked = '+7';
  if (digits.length > 0) masked += ' (' + digits.slice(0, 3);
  if (digits.length >= 3) masked += ') ' + digits.slice(3, 6);
  if (digits.length >= 6) masked += '-' + digits.slice(6, 8);
  if (digits.length >= 8) masked += '-' + digits.slice(8, 10);
  input.value = masked;
};

// ─── Доставка ────────────────────────────────────────────────────────────────

const initDeliveryOptions = (container) => {
  const radios = container.querySelectorAll('[data-delivery-radio]');
  if (!radios.length) return;

  const updateDelivery = (radio) => {
    container.querySelectorAll('[data-delivery-option]').forEach((l) => l.classList.remove('delivery-option--checked'));
    radio.closest('[data-delivery-option]')?.classList.add('delivery-option--checked');
    container.querySelectorAll('[data-delivery-extra]').forEach((e) => e.setAttribute('hidden', ''));
    container.querySelector(`[data-delivery-extra="${radio.value}"]`)?.removeAttribute('hidden');
  };

  radios.forEach((radio) => {
    if (radio.checked) updateDelivery(radio);
    radio.addEventListener('change', () => updateDelivery(radio));
  });
};

// ─── Оплата ──────────────────────────────────────────────────────────────────

const initPaymentOptions = (container) => {
  const radios = container.querySelectorAll('[data-payment-radio]');
  if (!radios.length) return;

  const updatePayment = (radio) => {
    container.querySelectorAll('[data-payment-option]').forEach((l) => l.classList.remove('payment-option--checked'));
    radio.closest('[data-payment-option]')?.classList.add('payment-option--checked');
  };

  radios.forEach((radio) => {
    if (radio.checked) updatePayment(radio);
    radio.addEventListener('change', () => updatePayment(radio));
  });
};

// ─── Адресная форма ──────────────────────────────────────────────────────────
/*
 * address-form вынесена за пределы #js-checkout-form в template.php
 * (role="dialog" внутри form — некорректная ARIA-семантика).
 * Ищем по id в document, а не в checkoutContainer.
 */
const initAddressForm = () => {
  const addressForm = document.getElementById('js-address-form');
  if (!addressForm) return;

  const openForm = () => {
    addressForm.removeAttribute('hidden');
    addressForm.setAttribute('aria-hidden', 'false');
    addressForm.querySelector('input')?.focus();
  };

  const closeForm = () => {
    addressForm.setAttribute('hidden', '');
    addressForm.setAttribute('aria-hidden', 'true');
  };

  document.querySelectorAll('[data-address-new-toggle], [data-address-add-new]').forEach((btn) => {
    btn.addEventListener('click', openForm);
  });
  document.querySelectorAll('[data-address-cancel]').forEach((btn) => {
    btn.addEventListener('click', closeForm);
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !addressForm.hasAttribute('hidden')) closeForm();
  });
};

// ─── Сохранённые адреса ──────────────────────────────────────────────────────

const initSavedAddresses = (container) => {
  const savedBlock = container.querySelector('[data-saved-addresses]');
  if (!savedBlock) return;
  const items = savedBlock.querySelectorAll('[data-address-id]');
  items.forEach((item) => {
    item.addEventListener('click', (e) => {
      if (e.target.closest('button')) return;
      items.forEach((i) => i.classList.remove('delivery-address__item--selected'));
      item.classList.add('delivery-address__item--selected');
    });
  });
};

// ─── Валидация формы ─────────────────────────────────────────────────────────

const validateForm = (form) => {
  let isValid = true;

  form.querySelectorAll('[data-validate]').forEach((input) => {
    if (!validateField(input)) isValid = false;
  });

  form.querySelectorAll('[data-validate-conditional]').forEach((input) => {
    if (!validateConditionalField(input)) isValid = false;
  });

  // Чекбокс согласия рендерится вне form через form="id", ищем в document
  const agreementCheckbox = document.getElementById('js-agreement-policy');
  if (agreementCheckbox && !agreementCheckbox.checked) {
    const errorEl = agreementCheckbox.closest('.checkout__checkbox')?.nextElementSibling;
    if (errorEl?.classList.contains('checkout__error')) {
      errorEl.textContent = 'Необходимо принять условия пользовательского соглашения';
      errorEl.removeAttribute('hidden');
    }
    isValid = false;
  }

  return isValid;
};

// ─── AJAX-отправка ───────────────────────────────────────────────────────────

const submitOrderForm = async (form, submitBtn) => {
  submitBtn.disabled = true;
  submitBtn.classList.add('btn--loading');

  try {
    const response = await fetch(form.action, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(form),
    });

    const text = await response.text();
    let json;

    try {
      json = JSON.parse(text);
    } catch {
      /*
       * Ответ не является валидным JSON.
       * document.write() запрещён: при вызове после загрузки страницы
       * уничтожает DOM и несовместим с ES6-модулями.
       * window.location.reload() — страница перезагрузится,
       * Битрикс выполнит серверный редирект штатным образом.
       */
      window.location.reload();
      return;
    }

    if (json.STATUS === 'OK' && json.REDIRECT) {
      window.location.href = json.REDIRECT;
      return;
    }

    if (json.STATUS === 'ERROR' && json.ERRORS) {
      const banner = document.querySelector('.checkout__error-banner');
      if (banner) {
        banner.textContent = Object.values(json.ERRORS).join(' ');
        banner.removeAttribute('hidden');
      }
    }
  } catch (err) {
    // console.* только под DEBUG-guard — п. стандартов проекта
    if (DEBUG) {
      console.error('[checkout] Ошибка при отправке:', err);
    }
  } finally {
    submitBtn.disabled = false;
    submitBtn.classList.remove('btn--loading');
  }
};

// ─── Инлайн-валидация ────────────────────────────────────────────────────────

const initInlineValidation = (form) => {
  form.querySelectorAll('[data-validate]').forEach((input) => {
    input.addEventListener('blur', () => validateField(input));
    input.addEventListener('input', () => {
      if (input.classList.contains('order-form__input--error')) validateField(input);
    });
  });
};

// ─── Маски ───────────────────────────────────────────────────────────────────

const initPhoneMasks = (root) => {
  root.querySelectorAll('input[type="tel"]').forEach((input) => {
    input.addEventListener('input', () => applyPhoneMask(input));
    input.addEventListener('focus', () => { if (!input.value) input.value = '+7 '; });
  });
};

// ─── Точка входа ─────────────────────────────────────────────────────────────

const initCheckout = () => {
  const checkoutContainer = document.getElementById('js-checkout-container');
  if (!checkoutContainer) return;

  const form      = document.getElementById('js-checkout-form');
  const submitBtn = document.getElementById('js-checkout-submit');

  initDeliveryOptions(checkoutContainer);
  initPaymentOptions(checkoutContainer);
  initAddressForm();
  initSavedAddresses(checkoutContainer);
  // Маски — по всему document: телефоны есть и в address-form вне checkoutContainer
  initPhoneMasks(document);

  if (!form) return;

  initInlineValidation(form);

  /*
   * Слушаем submit на form, а не click на кнопке.
   * Это гарантирует срабатывание при:
   *   - нажатии Enter в поле ввода;
   *   - кнопке type="submit" с form="js-checkout-form" из aside;
   *   - любом другом стандартном способе отправки формы.
   */
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (validateForm(form)) {
      const btn = submitBtn ?? form.querySelector('[type="submit"]');
      if (btn) submitOrderForm(form, btn);
    } else {
      form.querySelector('.order-form__input--error')?.focus();
    }
  });
};

document.addEventListener('DOMContentLoaded', initCheckout);

export { initCheckout };
