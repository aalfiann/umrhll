<template>
  <f7-page :page-content="false">
    <f7-navbar title="Syarat Ketentuan" back-link="Back"></f7-navbar>
    <f7-toolbar tabbar>
    <f7-link tab-link="#tab-1" tab-link-active>Keberangkatan</f7-link>
    <f7-link tab-link="#tab-2">Keagenan</f7-link>
  </f7-toolbar>
  <f7-tabs animated>
    <f7-tab id="tab-1" class="page-content" tab-active>
      <f7-block-title>Syarat dan Ketentuan Keberangkatan Umrah</f7-block-title>
    <f7-block strong>
      <p>1. Pendaftar telah melunasi Biaya Umrah 2 (Dua) bulan sebelum Jadwal yang disepakati.</p>
      <p>2. Umrahalal.com berhak menunjuk Travel Umrah dalam menyelenggrakan Ibadah Umrah sesuai harga Paket Umrah yang disepakati.</p>
      <p>3. Jika satu dan lain hal Pendaftar membatalkan keberangkatan secara sepihak, maka Pendaftar bersedia dikenakan biaya sebagai berikut :<br>
        a. 8 Minggu sebelum keberangkatan sebesar 0% dari harga Paket Umrah.<br>
        b. 7 Minggu sebelum keberangkatan sebesar 25% dari Biaya Paket Umrah.<br>
        c. 6 Minggu sebelum keberangkatan sebesar 50% dari Biaya Paket Umrah.<br>
        d. 5 Minggu sebelum keberangkatan sebesar 75% dari Biaya Paket Umrah.<br>
        e. 4 Minggu sebelum keberangkatan sebesar 100%.
      </p>
      <p>4. Pendaftar telah menyerahkan semua kelengkapan Dokumen yang ditentukan dengan mengirim Dokumen kepada umrahalal.com baik langsung maupun melalui Jasa pengiriman ke alamat umrahalal.com.</p>
      <p>Dengan membaca Syarat dan Ketentuan Keberangkatan maka Pendaftar dianggap telah menyatakan pernyataan setuju.</p>
    </f7-block>
    </f7-tab>
    <f7-tab id="tab-2" class="page-content">
      <f7-block-title>Syarat dan Ketentuan Keagenan Umrah</f7-block-title>
      <f7-block strong>
        <p>1. Agen bersedia dan sepakat menyetorkan Setoran awal Biaya Umrah sebesar Rp.200.000. sebagai saldo Biaya Umrah.</p>
        <p>2. Agen bersedia dan sepakat oleh karena satu dan lain hal membatalkan fiducia keagenan yang dilakukan secara sepihak akan dikenakan biaya administrasi sebesar 25% dari saldo setoran awal.</p>
        <p>3. Biaya Paket Umrah akan dikurangi setoran awal.</p>
        <p>4. Surat Rekomendasi untuk pembuatan pasport akan diterbitkan jika Jamaah Umrah telah membayar Booking Seat Keberangkatan dan Perlengkapan Umrah sebesar Rp.5.000.000</p>
        <p>5. Pihak umrahalal.com berhak men-suspend PIN AGEN apabila terdapat pelanggaran oleh Agen.</p>
        <p>Dengan membaca Syarat dan Ketentuan Keagenan maka Pendaftar / Agen dianggap telah menyatakan pernyataan setuju.</p>
      </f7-block>
    </f7-tab>
  </f7-tabs>
  </f7-page>
</template>

<script>
export default {}
</script>