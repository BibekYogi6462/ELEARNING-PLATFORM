document.addEventListener('DOMContentLoaded', () => {
  // Profile, Search Form, Navbar Toggles
  const profile = document.querySelector('.header .flex .profile');
  const searchForm = document.querySelector('.header .flex .search-form');
  const navbar = document.querySelector('.header .flex .navbar');

  const toggleActive = (element) => {
    element.classList.toggle('active');
    [profile, searchForm, navbar].forEach((el) => {
      if (el !== element) el.classList.remove('active');
    });
  };

  document.querySelector('#user-btn')?.addEventListener('click', () => toggleActive(profile));
  document.querySelector('#search-btn')?.addEventListener('click', () => toggleActive(searchForm));
  document.querySelector('#menu-btn')?.addEventListener('click', () => toggleActive(navbar));

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.header .flex')) {
      [profile, searchForm, navbar].forEach((el) => el.classList.remove('active'));
    }
  });
});
