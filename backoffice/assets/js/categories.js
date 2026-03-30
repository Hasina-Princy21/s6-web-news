document.querySelectorAll('.js-delete-category-form').forEach(function (form) {
  form.addEventListener('submit', function (event) {
    if (!window.confirm('Delete this category?')) {
      event.preventDefault();
    }
  });
});
