(function () {
  var dataNode = document.getElementById('world-map-data');
  var mapNode = document.getElementById('world-map');

  if (!dataNode || !mapNode || typeof L === 'undefined') {
    return;
  }

  var raw = dataNode.textContent || '[]';
  var markers = [];

  try {
    markers = JSON.parse(raw);
  } catch (error) {
    markers = [];
  }

  var map = L.map('world-map', {
    center: [20, 0],
    zoom: 2,
    minZoom: 2,
    worldCopyJump: true
  });

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  markers.forEach(function (item) {
    if (!Number.isFinite(item.lat) || !Number.isFinite(item.lng)) {
      return;
    }

    var marker = L.circleMarker([item.lat, item.lng], {
      radius: 6,
      color: '#e05a2a',
      weight: 1,
      fillColor: '#c8922a',
      fillOpacity: 0.85
    }).addTo(map);

    var title = item.title || 'Article';
    var safeTitle = title.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    var link = item.url || '#';
    marker.bindPopup('<a href="' + link + '">' + safeTitle + '</a>');
  });
})();
