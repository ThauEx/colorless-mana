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
        } else if (data.flagIcon) {
          return `<div><img class="fi-custom me-2" src="${data.flagIcon}" alt="">${data.text}</div>`;
        } else if (data.lang) {
          return `<div><span class="fi fi-${data.lang} me-2"></span>${data.text}</div>`;
        }

        return `<div>${data.text}</div>`;
      },
      item: function (item) {
        if (item.icon) {
          return `<div><img class="me-2" src="${item.icon}" alt="">${item.text}</div>`;
        } else if (item.flagIcon) {
          return `<div><img class="fi-custom me-2" src="${item.flagIcon}" alt="">${item.text}</div>`;
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

const STORAGE_KEY = 'collection-batch-delete-ids';

function loadSelection() {
  try {
    return new Set(JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]'));
  } catch (e) {
    return new Set();
  }
}

function saveSelection(selection) {
  sessionStorage.setItem(STORAGE_KEY, JSON.stringify([...selection]));
}

function updateSelectedCount(selection) {
  const countEl = document.querySelector('.js-selected-count');
  const deleteBtn = document.querySelector('.js-delete-selected-btn');
  if (countEl) {
    countEl.textContent = selection.size;
  }
  if (deleteBtn) {
    deleteBtn.disabled = selection.size === 0;
  }
}

function updateSelectAllState() {
  const selectAll = document.querySelector('.js-select-all');
  const pageCheckboxes = document.querySelectorAll('.js-batch-select');
  if (!selectAll || pageCheckboxes.length === 0) {
    return;
  }
  const checkedOnPage = [...pageCheckboxes].filter(cb => cb.checked).length;
  selectAll.checked = checkedOnPage === pageCheckboxes.length;
  selectAll.indeterminate = checkedOnPage > 0 && checkedOnPage < pageCheckboxes.length;
}

let selection = loadSelection();

for (let checkbox of document.querySelectorAll('.js-batch-select')) {
  // Checkbox clicks must not toggle the row's collapse
  checkbox.addEventListener('click', event => event.stopPropagation());

  if (selection.has(checkbox.value)) {
    checkbox.checked = true;
  }

  checkbox.addEventListener('change', () => {
    if (checkbox.checked) {
      selection.add(checkbox.value);
    } else {
      selection.delete(checkbox.value);
    }
    saveSelection(selection);
    updateSelectedCount(selection);
    updateSelectAllState();
  });
}

updateSelectedCount(selection);
updateSelectAllState();

const selectAll = document.querySelector('.js-select-all');
if (selectAll) {
  selectAll.addEventListener('click', event => event.stopPropagation());
  selectAll.addEventListener('change', () => {
    for (let checkbox of document.querySelectorAll('.js-batch-select')) {
      checkbox.checked = selectAll.checked;
      if (selectAll.checked) {
        selection.add(checkbox.value);
      } else {
        selection.delete(checkbox.value);
      }
    }
    saveSelection(selection);
    updateSelectedCount(selection);
  });
}

const clearSelectionBtn = document.querySelector('.js-clear-selection');
if (clearSelectionBtn) {
  clearSelectionBtn.addEventListener('click', () => {
    selection.clear();
    saveSelection(selection);
    for (let checkbox of document.querySelectorAll('.js-batch-select')) {
      checkbox.checked = false;
    }
    updateSelectedCount(selection);
    updateSelectAllState();
  });
}

const batchDeleteForm = document.querySelector('.js-batch-delete-form');
if (batchDeleteForm) {
  batchDeleteForm.addEventListener('submit', event => {
    if (!confirm(batchDeleteForm.dataset.confirm)) {
      event.preventDefault();
      return;
    }

    // Add hidden inputs for selected ids from other pages, which have
    // no checkbox in the current DOM
    const idsOnPage = new Set([...document.querySelectorAll('.js-batch-select')].map(cb => cb.value));
    for (let id of selection) {
      if (!idsOnPage.has(id)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        batchDeleteForm.appendChild(input);
      }
    }
    sessionStorage.removeItem(STORAGE_KEY);
  });
}

const flipCardButtons = document.querySelectorAll('.js-flip-card');
for (let flipCardButton of flipCardButtons) {
  flipCardButton.addEventListener('click', event => {
    const inner = event.target.parentNode.parentNode.querySelector('.flip-card-inner');
    inner.style.transform = ['none', ''].includes(inner.style.transform) ? 'rotateY(-180deg)' : '';
  });
}
