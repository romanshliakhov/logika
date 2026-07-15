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

  const cityFromPath = () => {
    const match = window.location.pathname.match(/^\/cities\/([^/]+)(?:\/|$)/);
    return match ? cities.find((city) => new URL(city.url, window.location.origin).pathname === `/cities/${match[1]}/`) || null : null;
  };

  const get = () => cityFromPath() || cities.find((city) => String(city.id) === storedId()) || null;

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
        const city = cityFromPath();
        if (city) remember(city.id);
        return cities;
      })
      .catch(() => []);

    return loading;
  };

  const set = (city, updateUrl = false) => {
    if (!city || !city.id) return;
    remember(city.id);
    const url = updateUrl && cityUrl(city);
    if (url && window.history?.pushState) window.history.pushState({ cityId: city.id }, '', url);
    window.dispatchEvent(new CustomEvent('logika:city-change', { detail: { city } }));
  };

  window.addEventListener('popstate', () => {
    const city = get();
    if (city) window.dispatchEvent(new CustomEvent('logika:city-change', { detail: { city } }));
  });

  window.logikaCityContext = { get, load, set, url: cityUrl };
})();
