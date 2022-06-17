import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

function getConfig(element) {
  const config = {
    plugins: {
      remove_button: {
        title: 'Entfernen',
      },
    },
    render: {
      option: function (data) {
        if (data.icon) {
          return `<div><img class="me-2" src="${data.icon}" alt="">${data.text}</div>`;
        } else if (data.lang) {
          return `<div><span class="fi fi-${data.lang} me-2"></span>${data.text}</div>`;
        }

        return `<div>${data.text}</div>`;
      },
      item: function (item) {
        if (item.icon) {
          return `<div><img class="me-2" src="${item.icon}" alt="">${item.text}</div>`;
        } else if (item.lang) {
          return `<div><span class="fi fi-${item.lang} me-2"></span>${item.text}</div>`;
        }

        return `<div>${item.text}</div>`;
      },
    },
  };

  if (element.dataset.maxItems) {
    config.maxItems = element.dataset.maxItems;
  }

  return config;
}

for (let element of document.querySelectorAll('.js-select')) {
  const selector = '.' + [...element.classList].filter(e => e.startsWith('js-select-'));

  new TomSelect(selector, getConfig(element));
}
