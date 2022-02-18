function CivicDropdownFilter(el) {
  if (!el || el.hasAttribute('data-dropdown-filter-searchable')) {
    return;
  }

  this.el = el;

  // Settings.
  this.itemSelector = this.el.getAttribute('data-dropdown-filter-item-selector') ? this.el.getAttribute('data-dropdown-filter-item-selector') : '.civic-form-element--checkbox,.civic-form-element--radio';
  this.itemThreshold = this.el.getAttribute('data-dropdown-filter-item-threshold') ? parseInt(this.el.getAttribute('data-dropdown-filter-item-threshold'), 10) : 10;
  this.placeholderText = this.el.getAttribute('data-dropdown-filter-placeholder-text') ? this.el.getAttribute('data-dropdown-filter-placeholder-text') : 'Filter by keyword';

  this.dropdownFilterItems = this.el.querySelectorAll(this.itemSelector);

  // Add a search box to the dropdown filter if there are more options than the threshold.
  if (this.dropdownFilterItems.length >= this.itemThreshold) {
    this.init();
  }
}

// eslint-disable-next-line func-names
CivicDropdownFilter.prototype.init = function () {
  // Create the search box container.
  const search = document.createElement('div');
  search.classList.add('civic-dropdown-filter__search', 'civic-input', 'civic-theme-light');

  // Create the search box element and add it to the container.
  const searchInput = document.createElement('input');
  searchInput.classList.add('civic-dropdown-filter__search__input', 'civic-input__element', 'civic-input--default', 'civic-input--text');
  searchInput.setAttribute('placeholder', this.placeholderText);
  searchInput.setAttribute('value', '');
  searchInput.setAttribute('type', 'text');
  search.append(searchInput);
  this.searchInput = searchInput;

  // Add the search box container to the dropdown filter.
  this.el.prepend(search);

  // Add the search box key listener.
  this.keyupListener = this.keyupEvent.bind(this);
  this.searchInput.addEventListener('keyup', this.keyupListener, false);
  this.el.setAttribute('data-dropdown-filter-searchable', '');
};

// eslint-disable-next-line func-names
CivicDropdownFilter.prototype.filterBasedOnInput = function () {
  const query = this.searchInput.value.toLowerCase();
  const _this = this;

  this.dropdownFilterItems.forEach(function (item) {
    if (item.querySelector('label').innerHTML.toLowerCase().includes(query)) {
      _this.showItem(item);
    } else {
      _this.hideItem(item);
    }
  });
};

// eslint-disable-next-line func-names
CivicDropdownFilter.prototype.showItem = function (item) {
  item.setAttribute('data-dropdown-filter-item-visible', '');
  item.removeAttribute('data-dropdown-filter-item-hidden');
};

// eslint-disable-next-line func-names
CivicDropdownFilter.prototype.hideItem = function (item) {
  item.setAttribute('data-dropdown-filter-item-hidden', '');
  item.removeAttribute('data-dropdown-filter-item-visible');
};

// eslint-disable-next-line func-names
CivicDropdownFilter.prototype.keyupEvent = function () {
  this.filterBasedOnInput();
};

document.querySelectorAll('.civic-dropdown-filter fieldset.civic-form-element--radio, .civic-dropdown-filter fieldset.civic-form-element--checkbox').forEach((dropdownFilter) => {
  // eslint-disable-next-line no-new
  new CivicDropdownFilter(dropdownFilter);
});
