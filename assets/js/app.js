document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!window.confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
  });
  document.querySelectorAll('[data-dismiss-alert]').forEach(el => {
    window.setTimeout(() => { el.classList.add('fade'); window.setTimeout(() => el.remove(), 300); }, 5500);
  });
  document.querySelectorAll('[data-search-target]').forEach(input => {
    input.addEventListener('input', () => {
      const target = document.querySelector(input.dataset.searchTarget);
      if (!target) return;
      const q = input.value.toLowerCase();
      target.querySelectorAll('[data-searchable]').forEach(row => {
        row.hidden = !row.textContent.toLowerCase().includes(q);
      });
    });
  });
});
