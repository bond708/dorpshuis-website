((Drupal, once) => {
  'use strict';

  Drupal.behaviors.dorphuisHeader = {
    attach(context) {
      once('dorpshuis-header', '[data-mobile-toggle]', context).forEach((toggle) => {
        const menuId = toggle.getAttribute('aria-controls');
        const menu = document.getElementById(menuId);
        if (!menu) return;

        toggle.addEventListener('click', () => {
          const isOpen = toggle.getAttribute('aria-expanded') === 'true';
          toggle.setAttribute('aria-expanded', String(!isOpen));
          menu.setAttribute('aria-hidden', String(isOpen));
          menu.classList.toggle('is-open', !isOpen);
          toggle.setAttribute('aria-label', !isOpen
            ? Drupal.t('Close menu')
            : Drupal.t('Open menu'));
        });
      });
    },
  };
})(Drupal, once);
