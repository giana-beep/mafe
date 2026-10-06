document.addEventListener('DOMContentLoaded',()=>{
 const video=document.getElementById('video'), start=document.getElementById('startScan'), status=document.getElementById('scanStatus');
 if(start) start.onclick=async()=>{
   try{
    const stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:'environment'}});
    video.srcObject=stream; status.textContent='Camera aktif. Arahkan ke barcode.';
    if('BarcodeDetector' in window){
      const detector=new BarcodeDetector();
      const scan=async()=>{ if(video.readyState>=2){try{const codes=await detector.detect(video);if(codes.length){document.getElementById('barcode').value=codes[0].rawValue;status.textContent='Barcode terdeteksi: '+codes[0].rawValue;return;}}catch(e){}} requestAnimationFrame(scan);};scan();
    } else status.textContent='Browser ini belum mendukung BarcodeDetector. Masukkan barcode secara manual.';
   }catch(e){status.textContent='Kamera tidak bisa dibuka. Coba izinkan akses kamera atau gunakan input manual.';}
 };
 const analyze=document.getElementById('analyze'), inci=document.getElementById('inci'), out=document.getElementById('analysis');
 if(analyze) analyze.onclick=()=>{
   const text=inci.value.trim(); if(!text){out.innerHTML='<div class="error">Masukkan ingredient list dulu.</div>';return;}
   fetch('analyze.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'inci='+encodeURIComponent(text)})
   .then(r=>r.text()).then(html=>out.innerHTML=html);
 };
 const cs=document.getElementById('compareSearch'), cr=document.getElementById('compareResults');
 if(cs){let timer;cs.oninput=()=>{clearTimeout(timer);timer=setTimeout(()=>fetch('search.php?q='+encodeURIComponent(cs.value)).then(r=>r.text()).then(x=>cr.innerHTML=x),250);};}
});