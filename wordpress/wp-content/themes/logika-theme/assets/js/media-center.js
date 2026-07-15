document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('main');
  const featured = root?.querySelector('.archive-section__main');
  const list = root?.querySelector('.news-section__items');
  const context = window.logikaCityContext;
  const config = window.logikaMediaCenter || {};
  let requestId = 0;

  if (!root || !featured || !list || !context || !config.endpoint) return;

  const link = (className, card) => {
    const element = document.createElement('a');
    element.className = className;
    element.href = card.url;
    return element;
  };
  const thumbnail = (card) => {
    const element = link('news-card__thumbnail', card);
    if (card.image) {
      const image = document.createElement('img');
      image.src = card.image;
      image.alt = '';
      element.append(image);
    }
    return element;
  };
  const title = (card) => {
    const element = link('news-card__title', card);
    element.textContent = card.title;
    return element;
  };
  const date = (card) => {
    const element = document.createElement('p');
    element.textContent = card.date;
    return element;
  };
  const render = (cards) => {
    featured.replaceChildren();
    list.replaceChildren();
    if (!cards.length) {
      const empty = document.createElement('p');
      empty.textContent = 'Для цього міста поки немає матеріалів.';
      featured.append(empty);
      return;
    }
    const card = cards[0];
    const article = document.createElement('article');
    const info = document.createElement('div');
    const details = document.createElement('ul');
    const description = document.createElement('p');
    article.className = 'news-card';
    info.className = 'news-card__info';
    details.className = 'news-card__details';
    description.className = 'news-card__descr';
    const detail = document.createElement('li');
    detail.append(date(card));
    details.append(detail);
    description.textContent = card.excerpt;
    info.append(details, title(card), description);
    article.append(thumbnail(card), info);
    featured.append(article);

    cards.slice(1).forEach((item) => {
      const row = document.createElement('li');
      const news = document.createElement('article');
      const info = document.createElement('div');
      row.className = 'news-section__item';
      news.className = 'news-card';
      info.className = 'news-card__info';
      info.append(title(item), date(item));
      news.append(thumbnail(item), info);
      row.append(news);
      list.append(row);
    });
  };
  const refresh = (city) => {
    const id = ++requestId;
    const url = new URL(config.endpoint, window.location.origin);
    if (city) url.searchParams.set('city', city.id);
    root.setAttribute('aria-busy', 'true');
    fetch(url)
      .then((response) => response.ok ? response.json() : [])
      .then((cards) => { if (id === requestId) render(Array.isArray(cards) ? cards : []); })
      .catch(() => { if (id === requestId) render([]); })
      .finally(() => { if (id === requestId) root.removeAttribute('aria-busy'); });
  };

  context.load().then(() => refresh(context.get()));
  window.addEventListener('logika:city-change', ({ detail }) => refresh(detail.city));
});
