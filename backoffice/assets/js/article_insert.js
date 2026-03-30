tinymce.init({
  selector: '#content',
  menubar: false,
  branding: false,
  height: 340,
  plugins: 'lists link table code',
  toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link table | code'
});

const map = L.map('map').setView([20, 0], 2);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

const latInput = document.getElementById('latitude');
const lngInput = document.getElementById('longitude');
const clearBtn = document.getElementById('clear-position');
let marker = null;

function setPosition(lat, lng, moveMap) {
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
    return;
  }

  if (!marker) {
    marker = L.marker([lat, lng]).addTo(map);
  } else {
    marker.setLatLng([lat, lng]);
  }

  latInput.value = lat.toFixed(6);
  lngInput.value = lng.toFixed(6);

  if (moveMap) {
    map.setView([lat, lng], Math.max(map.getZoom(), 6));
  }
}

map.on('click', function (event) {
  setPosition(event.latlng.lat, event.latlng.lng, false);
});

function syncFromInputs() {
  const lat = parseFloat(latInput.value.replace(',', '.'));
  const lng = parseFloat(lngInput.value.replace(',', '.'));
  if (Number.isFinite(lat) && Number.isFinite(lng)) {
    setPosition(lat, lng, true);
  }
}

latInput.addEventListener('change', syncFromInputs);
lngInput.addEventListener('change', syncFromInputs);

clearBtn.addEventListener('click', function () {
  latInput.value = '';
  lngInput.value = '';
  if (marker) {
    map.removeLayer(marker);
    marker = null;
  }
});

syncFromInputs();
