/**
 * overlay-client.js
 * Cliente SSE compartido por todos los overlays HTML.
 * Se conecta a /api/overlay/events y llama a window.onOverlayState(state)
 * en cada actualización de estado.
 *
 * Uso en cada overlay:
 *   <script src="/js/overlay-client.js"></script>
 *   <script>
 *     window.onOverlayState = function(state) { ... };
 *   </script>
 */
(function () {
  'use strict';

  // Permite override desde el HTML: window.OVERLAY_API = 'https://mi-dominio.com'
  var API = (window.OVERLAY_API || '') + '/api/overlay/events';
  var lastJson = '';

  function connect() {
    var es = new EventSource(API);

    es.onmessage = function (e) {
      if (!e.data || e.data === lastJson) return;
      lastJson = e.data;
      try {
        var state = JSON.parse(e.data);
        if (typeof window.onOverlayState === 'function') {
          window.onOverlayState(state);
        }
      } catch (err) {
        console.error('[overlay-client] Error parseando state:', err);
      }
    };

    es.onerror = function () {
      es.close();
      setTimeout(connect, 3000);
    };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', connect);
  } else {
    connect();
  }
})();
