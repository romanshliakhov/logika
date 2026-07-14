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

const preferredPhoneCountries = ['ua', 'sk', 'pl', 'cz', 'de', 'ro', 'md', 'gb', 'ca'];
const phoneCountryAliases = {
  ua: 'ukr ukraine україна украина',
  sk: 'slovakia slovensko словакия словаччина',
  pl: 'pol poland polska польща польша',
  cz: 'czech czechia чехія чехия',
  de: 'germany deutschland німеччина германия',
  ro: 'romania румунія румыния',
  md: 'moldova молдова',
  gb: 'great britain united kingdom uk england велика британія великобритания',
  ca: 'canada канада',
};
const normalizePhoneCountrySearch = (value) => value
  .normalize('NFD')
  .replace(/[\u0300-\u036f]/g, '')
  .toLocaleLowerCase('uk-UA')
  .trim();

const setupPhoneCountrySearch = (input) => {
  const root = input.closest('.iti');
  const dropdown = root?.querySelector('.iti__dropdown-content');
  const countryList = root?.querySelector('.iti__country-list');
  if (!dropdown || !countryList || dropdown.querySelector('.iti__search-input')) return;

  const countries = [...countryList.querySelectorAll('.iti__country')];
  const countriesByCode = new Map(countries.map((country) => [country.dataset.countryCode, country]));
  const popularCountries = preferredPhoneCountries.map((code) => countriesByCode.get(code)).filter(Boolean);
  const divider = document.createElement('li');
  divider.className = 'iti__divider';
  divider.setAttribute('aria-hidden', 'true');

  countryList.prepend(...popularCountries, divider);

  const search = document.createElement('input');
  search.className = 'iti__search-input';
  search.type = 'search';
  search.placeholder = 'Пошук країни...';
  search.setAttribute('aria-label', search.placeholder);
  dropdown.prepend(search);

  const filterPhoneCountries = () => {
    const query = normalizePhoneCountrySearch(search.value);

    countries.forEach((country) => {
      const countryCode = country.dataset.countryCode || '';
      const countryName = country.querySelector('.iti__country-name')?.textContent || '';
      const dialCode = country.dataset.dialCode || '';
      const haystack = normalizePhoneCountrySearch([
        countryName,
        countryCode,
        dialCode,
        `+${dialCode}`,
        phoneCountryAliases[countryCode] || '',
      ].join(' '));
      country.hidden = Boolean(query) && !haystack.includes(query);
    });

    divider.hidden = Boolean(query);
    countryList.scrollTop = 0;
  };

  search.addEventListener('input', filterPhoneCountries);
  search.addEventListener('search', filterPhoneCountries);
  search.addEventListener('click', (event) => event.stopPropagation());
  search.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') event.stopPropagation();
  });
  input.addEventListener('close:countrydropdown', () => {
    search.value = '';
    filterPhoneCountries();
  });
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
  setupPhoneCountrySearch(input);
  updatePhoneState(input);

  input.addEventListener('open:countrydropdown', () => {
    const iti = input.closest('.iti');
    iti?.classList.add('iti--phone-dropdown-open');
    iti?.classList.toggle('iti--phone-dropdown-up', Boolean(iti?.querySelector('.iti__dropdown-content--dropup')));
  });
  input.addEventListener('close:countrydropdown', () => input.closest('.iti')?.classList.remove('iti--phone-dropdown-open', 'iti--phone-dropdown-up'));

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
const fieldLabels = {
  name: 'ім’я',
  phone: 'номер телефону',
  tel: 'номер телефону',
  child_age: 'вік дитини',
  age: 'вік дитини',
  consent_accepted: 'згоду з політикою конфіденційності',
};
const setFieldError = (field, show) => {
  const target = field?.closest('[data-logika-age-select]')?.querySelector('.main-form__age-trigger') || field;
  target?.classList.toggle('main-form__input--error', show);
  target?.setAttribute('aria-invalid', show ? 'true' : 'false');
};
const validateRequiredFields = (form, phoneInput, phone) => {
  const missing = [];
  const fields = new Set(form.querySelectorAll('[required]'));

  ['name', 'phone', 'tel', 'child_age'].forEach((name) => {
    const field = form.querySelector(`[name="${name}"]`);
    if (field) fields.add(field);
  });

  fields.forEach((field) => {
    const name = field.name || '';
    let invalid = field.type === 'checkbox' ? !field.checked : !field.value.trim();
    if (field === phoneInput) invalid = !field.value.trim() || Boolean(phone && !phone.isValidNumber());
    setFieldError(field, invalid);
    if (invalid) missing.push(fieldLabels[name] || field.getAttribute('aria-label') || field.placeholder || name);
  });

  return [...new Set(missing)];
};
const setSubmitError = (form, show, message = '') => {
  let alert = form.querySelector('.main-form__submit-error');
  if (!alert) {
    alert = document.createElement('div');
    alert.className = 'main-form__submit-error';
    alert.setAttribute('role', 'alert');
    submitButton(form)?.before(alert);
  }
  alert.textContent = message || 'Не вдалося надіслати заявку. Спробуйте ще раз або зателефонуйте нам.';
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

    const missingFields = validateRequiredFields(form, phoneInput, phone);
    setPhoneError(phoneInput, missingFields.includes(fieldLabels.phone));
    if (missingFields.length) {
      setStatus(status, '');
      setSubmitError(form, true, `Заповніть ${missingFields.length > 1 ? 'поля' : 'поле'}: ${missingFields.join(', ')}.`);
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
