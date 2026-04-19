/**
 * Dorpshuis theme – main JS entry point.
 * Component-specific behaviour lives in patterns/<component>/<component>.js
 * and is loaded via dorpshuis.libraries.yml.
 */

((Drupal, once) => {
  'use strict';

  // Smooth-scroll to anchor links within the page.
  Drupal.behaviors.dorphuisSmoothScroll = {
    attach(context) {
      once('dorpshuis-smooth-scroll', 'a[href^="#"]', context).forEach((link) => {
        link.addEventListener('click', (e) => {
          const id = link.getAttribute('href').slice(1);
          const target = document.getElementById(id);
          if (!target) return;
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          target.focus({ preventScroll: true });
        });
      });
    },
  };

  // Lazy-load images that are below the fold.
  Drupal.behaviors.dorphuisLazyLoad = {
    attach(context) {
      if (!('IntersectionObserver' in window)) return;

      once('dorpshuis-lazy', 'img[loading="lazy"]', context).forEach((img) => {
        if (img.complete) return;
        img.addEventListener('load', () => img.classList.add('is-loaded'));
      });
    },
  };

})(Drupal, once);
