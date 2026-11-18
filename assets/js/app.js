

// JavaScript dasar (tanpa library)
document.addEventListener('DOMContentLoaded', function () {

  // Cari spesies: Enter = submit
  var searchInput = document.querySelector('[data-live-search]');
  if (searchInput) {
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && searchInput.form) { searchInput.form.submit(); }
    });
  }

  // Menu mobile
  var toggle = document.querySelector('[data-nav-toggle]');
  if (toggle) {
    toggle.addEventListener('click', function () {
      var open = document.body.classList.toggle('nav-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // Tombol kembali ke atas
  var topBtn = document.querySelector('[data-scroll-top]');
  if (topBtn) {
    topBtn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // Carousel spesies pilihan: tombol panah menggeser satu kartu
  document.querySelectorAll('[data-carousel]').forEach(function (box) {
    var track = box.querySelector('[data-track]');
    var next = box.querySelector('[data-next]');
    var prev = box.querySelector('[data-prev]');
    if (!track) return;

    var step = function () {
      var card = track.firstElementChild;
      if (!card) return 240;
      var gap = parseFloat(getComputedStyle(track).columnGap) || 16;
      return card.offsetWidth + gap;
    };
    var atEnd = function () { return track.scrollLeft + track.clientWidth >= track.scrollWidth - 4; };

    if (next) next.addEventListener('click', function () {
      if (atEnd()) { track.scrollTo({ left: 0, behavior: 'smooth' }); }
      else { track.scrollBy({ left: step(), behavior: 'smooth' }); }
    });
    if (prev) prev.addEventListener('click', function () {
      track.scrollBy({ left: -step(), behavior: 'smooth' });
    });
  });

  // Peta Interaktif Biota Laut Endemik & Khas Nusantara
  var mapSection = document.getElementById('peta');
  if (mapSection) {
    var pins = mapSection.querySelectorAll('.map-pin-btn');
    var chips = mapSection.querySelectorAll('.map-chip');
    var chipsBar = mapSection.querySelector('.map-chips-bar');
    var spotlight = document.getElementById('mapSpotlight');
    var closeBtn = document.getElementById('spotlightClose');

    var titleEl = document.getElementById('spotlightTitle');
    var sciEl = document.getElementById('spotlightScientific');
    var sumEl = document.getElementById('spotlightSummary');
    var locEl = document.getElementById('spotlightLocText');
    var depthEl = document.getElementById('spotlightDepth');
    var sizeEl = document.getElementById('spotlightSize');
    var imgEl = document.getElementById('spotlightImg');
    var linkEl = document.getElementById('spotlightLink');
    var badgeEl = document.getElementById('spotlightStatusBadge');
    var habitatEl = document.getElementById('spotlightHabitat');

    function activatePin(pin, scrollSpotlight) {
      pins.forEach(function (p) { p.classList.remove('active'); });
      pin.classList.add('active');

      var id = pin.getAttribute('data-id');
      var title = pin.getAttribute('data-title') || '';
      var scientific = pin.getAttribute('data-scientific') || '';
      var slug = pin.getAttribute('data-slug') || '';
      var habitat = pin.getAttribute('data-habitat') || '';
      var location = pin.getAttribute('data-location') || '';
      var region = pin.getAttribute('data-region') || '';
      var status = pin.getAttribute('data-status') || '';
      var statusLabel = pin.getAttribute('data-status-label') || '';
      var depth = pin.getAttribute('data-depth') || '';
      var size = pin.getAttribute('data-size') || '';
      var summary = pin.getAttribute('data-summary') || '';
      var image = pin.getAttribute('data-image') || '';

      if (titleEl) titleEl.textContent = title;
      if (sciEl) sciEl.textContent = scientific;
      if (sumEl) sumEl.textContent = summary;
      if (locEl) locEl.textContent = location + (region ? ', ' + region : '');
      if (depthEl) depthEl.textContent = depth ? 'Kedalaman: ' + depth : '-';
      if (sizeEl) sizeEl.textContent = size ? 'Ukuran: ' + size : '-';
      if (imgEl) {
        imgEl.src = image;
        imgEl.alt = title;
      }
      if (linkEl) {
        linkEl.href = 'species-detail.php?slug=' + encodeURIComponent(slug);
      }
      if (badgeEl) {
        badgeEl.className = 'badge-status status-' + status.toLowerCase();
        badgeEl.textContent = status + (statusLabel ? ' (' + statusLabel + ')' : '');
      }
      if (habitatEl) habitatEl.textContent = habitat;

      // Sinkronkan tombol chip filter
      chips.forEach(function (c) {
        var isMatch = c.getAttribute('data-target') === id;
        c.classList.toggle('active', isMatch);
        if (isMatch && chipsBar) {
          c.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }
      });

      if (spotlight) {
        spotlight.classList.add('active');
        if (scrollSpotlight && window.innerWidth < 992) {
          spotlight.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
      }
    }

    pins.forEach(function (pin) {
      pin.addEventListener('click', function (e) {
        e.preventDefault();
        activatePin(pin, true);
      });
    });

    chips.forEach(function (chip) {
      chip.addEventListener('click', function (e) {
        e.preventDefault();
        var target = chip.getAttribute('data-target');
        if (target === 'all') {
          chips.forEach(function (c) { c.classList.remove('active'); });
          chip.classList.add('active');
          var firstPin = pins[0];
          if (firstPin) activatePin(firstPin, false);
        } else {
          var targetPin = mapSection.querySelector('.map-pin-btn[data-id="' + target + '"]');
          if (targetPin) {
            activatePin(targetPin, true);
          }
        }
      });
    });

    if (chipsBar) {
      chipsBar.addEventListener('wheel', function (e) {
        if (e.deltaY !== 0) {
          e.preventDefault();
          chipsBar.scrollLeft += e.deltaY;
        }
      }, { passive: false });
    }

    if (closeBtn && spotlight) {
      closeBtn.addEventListener('click', function () {
        spotlight.classList.remove('active');
      });
    }
  }
});
