(() => {
  const storageKey = 'logika-city-id';
  const config = window.logikaCityContextConfig || {};
  let cities = [];
  let loading = null;

  const storedId = () => {
    try {
      return localStorage.getItem(storageKey);
    } catch (error) {
      return null;
    }
  };

  const remember = (id) => {
    try {
      localStorage.setItem(storageKey, String(id));
    } catch (error) {
      // The current page still works when browser storage is unavailable.
    }
  };

  const get = () => cities.find((city) => String(city.id) === storedId()) || null;

  const cityUrl = (city) => {
    if (!city?.url) return '';
    const url = new URL(city.url, window.location.origin);
    const path = window.location.pathname.replace(/^\/cities\/[^/]+(?:\/|$)/, '/').replace(/^\/+/, '');
    url.pathname = `${url.pathname.replace(/\/?$/, '/')}${path}`;
    url.search = window.location.search;
    url.hash = window.location.hash;
    return url.href;
  };

  const load = () => {
    if (loading) return loading;
    if (!config.endpoint) return Promise.resolve(cities);
    loading = fetch(config.endpoint)
      .then((response) => response.ok ? response.json() : [])
      .then((items) => {
        cities = Array.isArray(items) ? items : [];
        return cities;
      })
      .catch(() => []);

    return loading;
  };

  const set = (city) => {
    if (!city || !city.id) return;
    remember(city.id);
    window.dispatchEvent(new CustomEvent('logika:city-change', { detail: { city } }));
  };

  window.logikaCityContext = { get, load, set, url: cityUrl };
})();
