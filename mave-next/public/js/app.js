document.addEventListener('DOMContentLoaded', () => {
  const csrf = (document.querySelector('meta[name=csrf-token]') || {}).content || '';

  // Scanner kamera (BarcodeDetector)
  const video = document.getElementById('video'), start = document.getElementById('startScan'), status = document.getElementById('scanStatus');
  if (start) start.onclick = async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
      video.srcObject = stream; status.textContent = 'Camera aktif. Arahkan ke barcode.';
      if ('BarcodeDetector' in window) {
        const detector = new BarcodeDetector();
        const scan = async () => {
          if (video.readyState >= 2) {
            try {
              const codes = await detector.detect(video);
              if (codes.length) { document.getElementById('barcode').value = codes[0].rawValue; status.textContent = 'Barcode terdeteksi: ' + codes[0].rawValue; return; }
            } catch (e) { /* abaikan */ }
          }
          requestAnimationFrame(scan);
        };
        scan();
      } else status.textContent = 'Browser ini belum mendukung BarcodeDetector. Masukkan barcode secara manual.';
    } catch (e) { status.textContent = 'Kamera tidak bisa dibuka. Coba izinkan akses kamera atau gunakan input manual.'; }
  };

  // Analisis daftar bahan (INCI)
  const analyze = document.getElementById('analyze'), inci = document.getElementById('inci'), out = document.getElementById('analysis');
  if (analyze) analyze.onclick = () => {
    const text = inci.value.trim();
    if (!text) { out.innerHTML = '<div class="error">Masukkan ingredient list dulu.</div>'; return; }
    fetch('/analyze', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'x-csrf-token': csrf },
      body: 'inci=' + encodeURIComponent(text),
    }).then((r) => r.text()).then((html) => { out.innerHTML = html; });
  };

  // Compare: cari dan tambah produk (maks. 4)
  const cs = document.getElementById('compareSearch'), cr = document.getElementById('compareResults'), hidden = document.getElementById('compareHidden');
  if (cs) {
    let timer;
    cs.oninput = () => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        if (!cs.value.trim()) { cr.innerHTML = ''; return; }
        fetch('/search?q=' + encodeURIComponent(cs.value)).then((r) => r.text()).then((x) => { cr.innerHTML = x; });
      }, 250);
    };
    cr.addEventListener('click', (e) => {
      const a = e.target.closest('[data-add-compare]');
      if (!a) return;
      e.preventDefault();
      if (hidden.querySelectorAll('input').length >= 4) { alert('Maksimal 4 produk.'); return; }
      const id = a.dataset.addCompare;
      if ([...hidden.querySelectorAll('input')].some((i) => i.value === id)) return;
      const i = document.createElement('input');
      i.type = 'hidden'; i.name = 'ids[]'; i.value = id; hidden.appendChild(i);
      cs.value = ''; cr.innerHTML = '';
      cs.form.submit();
    });
  }
});
