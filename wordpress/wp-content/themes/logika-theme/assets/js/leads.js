const phoneInstances = new WeakMap();
const createLeadKey = () => {
  const bytes = new Uint8Array(16);
  window.crypto?.getRandomValues?.(bytes);
  return [...bytes].map((byte) => byte.toString(16).padStart(2, '0')).join('') || `${Date.now()}-${Math.random()}`;
};
const logikaLeadToast = (message, type = 'success') => {
  let toast = document.querySelector('[data-logika-lead-toast]');
  if (!toast) {
    toast = document.createElement('div');
    toast.className = 'logika-lead-toast';
    toast.dataset.logikaLeadToast = '';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    document.body.append(toast);
  }
  toast.textContent = message;
  toast.dataset.type = type;
  toast.classList.add('is-visible');
  window.clearTimeout(logikaLeadToast.timeout);
  logikaLeadToast.timeout = window.setTimeout(() => toast.classList.remove('is-visible'), 5000);
};
const normalizePhoneCountry = (country) => /^[a-z]{2}$/i.test(country || '') ? country.toLowerCase() : '';
const phoneCountryDefault = normalizePhoneCountry(logikaLead.phoneCountryDefault) || 'ua';
const phoneCountryEndpoint = logikaLead.phoneCountryEndpoint || '';

const resolvePhoneCountry = (success) => {
  if (!phoneCountryEndpoint) {
    success(phoneCountryDefault);
    return;
  }

  fetch(phoneCountryEndpoint, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
    .then((response) => response.ok ? response.json() : null)
    .then((data) => success(normalizePhoneCountry(data?.country) || phoneCountryDefault))
    .catch(() => success(phoneCountryDefault));
};

const updatePhoneState = (input) => {
  input.closest('.iti')?.classList.toggle('iti--has-value', Boolean(input.value.trim()));
};
const setPhoneError = (input, show) => {
  const wrap = input?.closest('.main-form__phone-wrap');
  const error = wrap?.querySelector('[data-logika-phone-error]');
  wrap?.classList.toggle('has-phone-error', show);
  input?.classList.toggle('main-form__input--error', show);
  input?.setAttribute('aria-invalid', show ? 'true' : 'false');
  if (error) error.hidden = !show;
};

document.querySelectorAll('[data-logika-phone-input], input[type="tel"][name="phone"], input[type="tel"][name="tel"]').forEach((input) => {
  if (!window.intlTelInput) return;

  const instance = window.intlTelInput(input, {
    initialCountry: 'auto',
    countrySearch: false,
    geoIpLookup: resolvePhoneCountry,
    i18n: window.logikaIntlTelInputUk || {},
    nationalMode: true,
    separateDialCode: true,
    showSelectedDialCode: true,
    strictMode: true,
    utilsScript: logikaLead.phoneUtilsUrl,
  });
  phoneInstances.set(input, instance);
  updatePhoneState(input);

  input.addEventListener('open:countrydropdown', () => input.closest('.iti')?.classList.add('iti--phone-dropdown-open'));
  input.addEventListener('close:countrydropdown', () => input.closest('.iti')?.classList.remove('iti--phone-dropdown-open'));

  input.addEventListener('input', () => {
    updatePhoneState(input);
    if (!input.value.trim() || phoneInstances.get(input)?.isValidNumber?.()) setPhoneError(input, false);
  });
  input.addEventListener('countrychange', () => { updatePhoneState(input); setPhoneError(input, false); });
  input.addEventListener('blur', () => {
    const instance = phoneInstances.get(input);
    if (input.value.trim()) setPhoneError(input, Boolean(instance && !instance.isValidNumber()));
  });
});

document.querySelectorAll('[data-logika-age-select]').forEach((root) => {
  const select = root.querySelector('select[name="child_age"]');
  const trigger = root.querySelector('.main-form__age-trigger');
  const label = root.querySelector('.main-form__age-label');
  const dropdown = root.querySelector('.main-form__age-dropdown');
  const options = [...root.querySelectorAll('.main-form__age-option')];
  const placeholder = select?.querySelector('option[value=""]')?.textContent || '';

  if (!select || !trigger || !label || !dropdown) return;

  const close = () => {
    root.classList.remove('is-open');
    trigger.setAttribute('aria-expanded', 'false');
    dropdown.hidden = true;
  };
  const open = () => {
    root.classList.add('is-open');
    trigger.setAttribute('aria-expanded', 'true');
    dropdown.hidden = false;
  };
  const setValue = (value) => {
    select.value = value;
    const selected = options.find((option) => option.dataset.value === value);
    label.textContent = selected ? selected.textContent : placeholder;
    root.classList.toggle('has-value', Boolean(selected));
    options.forEach((option) => {
      const active = option === selected;
      option.classList.toggle('is-active', active);
      option.setAttribute('aria-selected', active ? 'true' : 'false');
    });
  };

  setValue(select.value);
  trigger.addEventListener('click', () => root.classList.contains('is-open') ? close() : open());
  options.forEach((option) => {
    option.addEventListener('click', () => {
      setValue(option.dataset.value || '');
      select.dispatchEvent(new Event('change', { bubbles: true }));
      close();
      trigger.focus();
    });
  });
  root.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') close();
  });
  root.closest('form')?.addEventListener('reset', () => requestAnimationFrame(() => setValue(select.value)));
  document.addEventListener('click', (event) => {
    if (!root.contains(event.target)) close();
  });
});

const setStatus = (status, message) => {
  if (!status) return;
  status.hidden = false;
  status.textContent = message;
};
const submitButton = (form) => form.querySelector('[type="submit"], .main-form__btn');
const setSubmitError = (form, show) => {
  let alert = form.querySelector('.main-form__submit-error');
  if (!alert) {
    alert = document.createElement('div');
    alert.className = 'main-form__submit-error';
    alert.setAttribute('role', 'alert');
    alert.innerHTML = '<strong>Не вдалося надіслати заявку.</strong><span>Спробуйте ще раз або зателефонуйте нам.</span>';
    submitButton(form)?.before(alert);
  }
  alert.hidden = !show;
};

document.querySelectorAll('[data-logika-lead-form]').forEach((form) => {
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const status = form.querySelector('.main-form__status');
    const key = form.querySelector('[name="idempotency_key"]');
    const phoneInput = form.querySelector('[data-logika-phone-input], input[type="tel"][name="phone"], input[type="tel"][name="tel"]');
    const phone = phoneInput ? phoneInstances.get(phoneInput) : null;

    const data = Object.fromEntries(new FormData(form));
    if (phone?.promise) {
      await phone.promise.catch(() => {});
    }

    if (!phoneInput?.value.trim() || (phone && !phone.isValidNumber())) {
      setPhoneError(phoneInput, true);
      return;
    }

    data.phone = phone ? phone.getNumber() : (data.phone || data.tel);
    data.child_age ||= data.age;
    key.value ||= createLeadKey();
    setSubmitError(form, false);
    setStatus(status, 'Надсилаємо заявку…');

	const button = submitButton(form);
    try {
	  button?.setAttribute('disabled', 'disabled');
	  const tokenResponse = await fetch(`${logikaLead.tokenEndpoint}?form_id=${encodeURIComponent(data.form_id)}`, { credentials: 'same-origin' });
	  const tokenData = tokenResponse.ok ? await tokenResponse.json() : null;
	  if (!tokenData?.token) throw new Error('token');
      const response = await fetch(logikaLead.endpoint, {
        method: 'POST',
		headers: { 'Content-Type': 'application/json', 'X-Logika-Form-Token': tokenData.token },
        body: JSON.stringify({ ...data, source_url: window.location.href }),
      });
      if (response.ok) {
        form.reset();
        requestAnimationFrame(() => phoneInput && updatePhoneState(phoneInput));
        key.value = '';
        setPhoneError(phoneInput, false);
        setSubmitError(form, false);
        setStatus(status, 'Дякуємо! Ми скоро зателефонуємо вам.');
        logikaLeadToast('Дякуємо! Заявку прийнято, ми скоро зателефонуємо.', 'success');
        return;
      }
      setStatus(status, '');
      setSubmitError(form, true);
    } catch (error) {
      setStatus(status, '');
	  setSubmitError(form, true);
	} finally {
	  button?.removeAttribute('disabled');
    }
  });
});
