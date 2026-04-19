((Drupal, once) => {
  'use strict';

  Drupal.behaviors.dorphuisNavigation = {
    attach(context) {
      // Keyboard-accessible dropdowns
      once('dorpshuis-nav', '.o-header__nav .menu__item--expanded', context).forEach((item) => {
        const link = item.querySelector(':scope > .menu__link');
        const submenu = item.querySelector(':scope > .menu');
        if (!link || !submenu) return;

        link.setAttribute('aria-haspopup', 'true');
        link.setAttribute('aria-expanded', 'false');

        const open  = () => { link.setAttribute('aria-expanded', 'true');  submenu.removeAttribute('hidden'); };
        const close = () => { link.setAttribute('aria-expanded', 'false'); submenu.setAttribute('hidden', ''); };

        item.addEventListener('mouseenter', open);
        item.addEventListener('mouseleave', close);

        link.addEventListener('keydown', (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            link.getAttribute('aria-expanded') === 'true' ? close() : open();
          }
          if (e.key === 'Escape') close();
        });
      });
    },
  };
})(Drupal, once);
