const cityRoot = document.querySelector('.header__location');
const cityTrigger = cityRoot ? cityRoot.querySelector('.header__location-trigger') : null;

if (cityRoot && cityTrigger) {
  const cityLabel = cityTrigger.querySelector('p');
  const dropdown = document.createElement('div');
  const search = document.createElement('input');
  const list = document.createElement('ul');
  const empty = document.createElement('p');

  dropdown.className = 'header__city-dropdown';
  dropdown.setAttribute('aria-hidden', 'true');
  search.className = 'header__city-search';
  search.type = 'search';
  search.placeholder = 'Пошук міста';
  search.setAttribute('aria-label', 'Пошук міста');
  list.className = 'header__city-list';
  list.setAttribute('role', 'listbox');
  empty.className = 'header__city-empty';
  empty.textContent = 'Місто не знайдено';
  empty.hidden = true;
  dropdown.append(search, list, empty);
  cityRoot.append(dropdown);
  cityTrigger.tabIndex = 0;
  cityTrigger.setAttribute('role', 'button');
  cityTrigger.setAttribute('aria-expanded', 'false');

  const close = () => {
    cityRoot.classList.remove('header__location--open');
    cityTrigger.setAttribute('aria-expanded', 'false');
    dropdown.setAttribute('aria-hidden', 'true');
  };

  const open = () => {
    cityRoot.classList.add('header__location--open');
    cityTrigger.setAttribute('aria-expanded', 'true');
    dropdown.setAttribute('aria-hidden', 'false');
    search.focus();
  };

  const toggle = () => cityRoot.classList.contains('header__location--open') ? close() : open();
  const regionLabel = (label) => String(label || 'Інші міста').replace(' область', ' обл.');
  const cityOptionLabel = (label) => label === 'Онлайн' || /^м\.\s/.test(label) ? label : `м. ${label}`;
  const clearList = () => {
    while (list.firstChild) list.firstChild.remove();
  };
  const setRegionState = (button, isOpen) => {
    const item = button.closest('.header__city-region');
    item.classList.toggle('is-open', isOpen);
    button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  };
  const renderCities = (cities, current) => {
    clearList();
    const groups = cities.reduce((regions, city) => {
      const region = city.region || {};
      const key = region.slug || 'other';
      if (!regions[key]) regions[key] = { region, cities: [] };
      regions[key].cities.push(city);
      return regions;
    }, {});

    Object.values(groups).forEach((group) => {
      const item = document.createElement('li');
      const button = document.createElement('button');
      const citiesList = document.createElement('ul');
      const isCurrentRegion = current && group.cities.some((city) => current.id === city.id);

      item.className = 'header__city-region';
      button.className = 'header__city-region-toggle';
      button.type = 'button';
      button.textContent = regionLabel(group.region.label);
      button.setAttribute('aria-expanded', 'false');
      citiesList.className = 'header__city-region-cities';
      citiesList.hidden = true;

      button.addEventListener('click', () => {
        const nextState = !item.classList.contains('is-open');
        list.querySelectorAll('.header__city-region-toggle').forEach((region) => setRegionState(region, false));
        list.querySelectorAll('.header__city-region-cities').forEach((regionCities) => { regionCities.hidden = true; });
        setRegionState(button, nextState);
        citiesList.hidden = !nextState;
      });

      group.cities.forEach((city) => {
        const cityItem = document.createElement('li');
        const link = document.createElement('a');
        link.className = 'header__city-option';
        link.href = city.url;
        link.dataset.id = city.id;
        link.textContent = cityOptionLabel(city.label);
        link.setAttribute('role', 'option');

        if (current && current.id === city.id) {
          link.classList.add('is-active');
          link.setAttribute('aria-selected', 'true');
        }

        link.addEventListener('click', () => {
          localStorage.setItem('logika-city-id', city.id);
          close();
        });

        cityItem.append(link);
        citiesList.append(cityItem);
      });

      item.append(button, citiesList);
      list.append(item);
      if (isCurrentRegion) {
        setRegionState(button, true);
        citiesList.hidden = false;
      }
    });
  };

  fetch(logikaCitySelector.endpoint)
    .then((response) => response.ok ? response.json() : [])
    .then((cities) => {
      const current = cities.find((city) => {
        try {
          return new URL(city.url).pathname === window.location.pathname;
        } catch (error) {
          return false;
        }
      }) || cities.find((city) => String(city.id) === localStorage.getItem('logika-city-id'));

      renderCities(cities, current);
      if (current && cityLabel) cityLabel.textContent = current.label;
    });

  search.addEventListener('input', () => {
    const query = search.value.trim().toLowerCase();
    let visible = 0;
    list.querySelectorAll('.header__city-region').forEach((region) => {
      const regionName = region.querySelector('.header__city-region-toggle').textContent.toLowerCase();
      const cities = [...region.querySelectorAll('.header__city-option')];
      const regionMatch = regionName.includes(query);
      let cityMatches = 0;
      cities.forEach((option) => {
        const match = regionMatch || option.textContent.toLowerCase().includes(query);
        option.parentElement.hidden = !match;
        if (match) cityMatches += 1;
      });
      region.hidden = query ? cityMatches === 0 : false;
      if (query) {
        region.classList.toggle('is-open', cityMatches > 0);
        region.querySelector('.header__city-region-toggle').setAttribute('aria-expanded', cityMatches > 0 ? 'true' : 'false');
        region.querySelector('.header__city-region-cities').hidden = cityMatches === 0;
      }
      visible += cityMatches;
    });
    empty.hidden = visible > 0;
  });

  cityTrigger.addEventListener('click', toggle);
  cityTrigger.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      toggle();
    }
  });
  document.addEventListener('click', (event) => {
    if (!cityRoot.contains(event.target)) close();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') close();
  });
}
