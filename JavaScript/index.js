document.addEventListener('DOMContentLoaded', function () {

  // Mobile menu
  const burger    = document.getElementById('burger');
  const mobileNav = document.getElementById('mobileNav');

  burger.addEventListener('click', function () {
    mobileNav.classList.toggle('open');
  });

  mobileNav.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () {
      mobileNav.classList.remove('open');
    });
  });

});
