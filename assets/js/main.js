// main.js - menghubungkan scanner (Quagga) dan geolocation, lalu kirim POST ke checkin.php
document.addEventListener('DOMContentLoaded', function(){
  const status = document.getElementById('status');
  const btnLoc = document.getElementById('btn-get-location');
  const codeInput = document.getElementById('employee_code');
  const typeSelect = document.getElementById('type');
  const noteInput = document.getElementById('note');
  const photoInput = document.getElementById('photo');
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // Inisialisasi Quagga scanner (kamera)
  if (navigator.mediaDevices && typeof Quagga !== 'undefined') {
    Quagga.init({
      inputStream : {
        name : 'Live',
        type : 'LiveStream',
        target: document.querySelector('#scanner'),
        constraints: {
          facingMode: 'environment'
        },
      },
      decoder : {
        readers : ['code_128_reader','ean_reader','ean_8_reader','upc_reader','upc_e_reader','code_39_reader']
      }
    }, function(err) {
      if (err) {
        console.log(err);
        document.getElementById('scanner').innerText = 'Tidak dapat mengakses kamera (device mungkin tidak mendukung).';
        return;
      }
      Quagga.start();
      Quagga.onDetected(function(data){
        if (data && data.codeResult && data.codeResult.code) {
          codeInput.value = data.codeResult.code;
          status.innerText = 'Kode terdeteksi: ' + data.codeResult.code;
          // hentikan sementara agar tidak multipel
          Quagga.stop();
          setTimeout(()=>{ Quagga.start(); }, 1500);
        }
      });
    });
  } else {
    document.getElementById('scanner').innerText = 'Scanner tidak tersedia pada device ini.';
  }

  btnLoc.addEventListener('click', function(){
    const code = codeInput.value.trim();
    if (!code) {
      status.innerText = 'Masukkan kode pegawai atau scan terlebih dahulu.';
      return;
    }
    status.innerText = 'Mencari lokasi...';
    if (!navigator.geolocation) {
      status.innerText = 'Geolocation tidak didukung. Mengirim tanpa lokasi...';
      postCheckin(code, null, null);
      return;
    }
    navigator.geolocation.getCurrentPosition(function(pos){
      const lat = pos.coords.latitude;
      const lon = pos.coords.longitude;
      status.innerText = 'Lokasi ditemukan. Mengirim absensi...';
      postCheckin(code, lat, lon);
    }, function(err){
      status.innerText = 'Gagal mengambil lokasi: ' + err.message + '. Mengirim tanpa lokasi...';
      postCheckin(code, null, null);
    }, {timeout:10000});
  });

  function postCheckin(code, lat, lon){
    const fd = new FormData();
    fd.append('employee_code', code);
    if (lat) fd.append('lat', lat);
    if (lon) fd.append('lon', lon);
    fd.append('type', typeSelect.value);
    fd.append('note', noteInput.value.trim());
    fd.append('csrf_token', csrfToken);
    if (photoInput.files[0]) {
      fd.append('photo', photoInput.files[0]);
    }
    fetch('checkin.php', {method:'POST', body: fd})
      .then(r=>r.json()).then(j=>{
        if (j.success) {
          status.innerText = 'OK — ' + j.message + ' (' + (j.employee||'') + ', ' + (j.type||'') + ')';
          setTimeout(()=>{ status.innerText=''; }, 4000);
        } else {
          status.innerText = 'Gagal: ' + j.message;
        }
      }).catch(e=>{
        status.innerText = 'Error jaringan: ' + e.message;
      });
  }
});
