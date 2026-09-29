/* FAW Gallery Page — filter, lazy grid, load-more. Lightbox via main.js API. */
(function () {
  'use strict';
  var GP = window.FAW_GP;
  var grid = document.getElementById('gpageGrid');
  if (!GP || !grid || !window.FAW_LIGHTBOX) return;

  var state = { event: 'all', date: 'all', sort: 'newest', visible: GP.perPage };

  // photos are indexed chronologically per event in FAW_GALLERY order;
  // build the display order per sort (events by date, in-event stays chronological)
  var order = (function () {
    var keys = Object.keys(GP.meta).sort(function (a, b) {
      return GP.meta[a].dateKey < GP.meta[b].dateKey ? -1 : 1; // oldest event first
    });
    var newestFirst = keys.slice().reverse();
    var seq = (state.sort === 'newest') ? newestFirst : keys;
    var out = [];
    seq.forEach(function (ev) {
      for (var i = GP.meta[ev].from; i <= GP.meta[ev].to; i++) out.push(i);
    });
    return out;
  })();

  function rebuildOrder() {
    var keys = Object.keys(GP.meta).sort(function (a, b) {
      return GP.meta[a].dateKey < GP.meta[b].dateKey ? -1 : 1;
    });
    var seq = (state.sort === 'newest') ? keys.slice().reverse() : keys;
    order.length = 0;
    seq.forEach(function (ev) {
      for (var i = GP.meta[ev].from; i <= GP.meta[ev].to; i++) order.push(i);
    });
  }

  function passes(p) {
    if (state.event !== 'all' && p.event !== state.event) return false;
    if (state.date !== 'all') {
      var m = GP.meta[p.event];
      if (!m || m.dateKey !== state.date) return false;
    }
    return true;
  }

  function render() {
    grid.innerHTML = '';
    var shown = 0, total = 0;
    for (var o = 0; o < order.length; o++) {
      var i = order[o];
      var p = GP.photos[i];
      if (!passes(p)) continue;
      total++;
      if (shown >= state.visible) continue;
      var d = document.createElement('div');
      d.className = 'gallery-thumb';
      d.setAttribute('data-lightbox', i);
      var img = document.createElement('img');
      img.src = p.thumb;
      img.alt = 'FAW event photo';
      img.loading = 'lazy';
      img.decoding = 'async';
      img.width = 480;
      img.height = 320;
      d.appendChild(img);
      grid.appendChild(d);
      shown++;
    }
    document.getElementById('gpageEmpty').hidden = total > 0;
    document.getElementById('gpageMoreWrap').style.display = shown < total ? '' : 'none';
    document.getElementById('gpageCount').textContent = 'Showing ' + shown + ' of ' + total + ' photos';
  }

  // delegation for lightbox opens (dynamic thumbs)
  grid.addEventListener('click', function (e) {
    var t = e.target.closest('.gallery-thumb[data-lightbox]');
    if (t) window.FAW_LIGHTBOX.open(parseInt(t.getAttribute('data-lightbox'), 10));
  });

  // filter chips
  function wire(group, key) {
    var el = document.getElementById(group);
    el.addEventListener('click', function (e) {
      var b = e.target.closest('.chip');
      if (!b) return;
      el.querySelectorAll('.chip').forEach(function (c) { c.classList.remove('is-active'); });
      b.classList.add('is-active');
      state[key] = b.getAttribute('data-' + key);
      if (key === 'sort') rebuildOrder();
      state.visible = GP.perPage;
      render();
    });
  }
  wire('gfEvent', 'event');
  wire('gfDate', 'date');
  wire('gfSort', 'sort');

  // load more
  document.getElementById('gpageMore').addEventListener('click', function () {
    state.visible += GP.perPage;
    render();
  });

  // deep link: /gallery/?event=rivor or crucible
  var q = new URLSearchParams(location.search).get('event');
  if (q === 'rivor' || q === 'crucible') {
    state.event = q;
    var evBtn = document.querySelector('#gfEvent .chip[data-event="' + q + '"]');
    if (evBtn) {
      document.querySelectorAll('#gfEvent .chip').forEach(function (c) { c.classList.remove('is-active'); });
      evBtn.classList.add('is-active');
    }
  }

  render();
})();
