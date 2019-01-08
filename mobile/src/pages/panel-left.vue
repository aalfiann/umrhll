<template>
  <f7-page>
    <f7-navbar title="Tentang Kami"></f7-navbar>
    <f7-block-title>Umrahalal - v.1.0.0</f7-block-title>
    <f7-block strong>
      <p>Umrahalal.com adalah Aplikasi Profesional Keagenan yang melindungi Reputasi dan Nama Baik untuk Agen yang sudah berpengalaman atau pihak pihak yang baru memulai usaha di bisnis Travel Umrah.</p>

      <p>Umrahalal.com menggaransi 100% Jamaah berangkat sesuai Jadwal setelah melunasi Biaya Umrah yang telah ditetapkan pihak Travel.</p>

      <p>Umrahalal.com hanya menjalin kerjasama dengan Travel yang telah memiliki Izin resmi dari Depag/ PPIU.</p>

      <p>Semua transaksi berbasis online, sehingga umrahalal.com tidak bertanggungjawab terhadap pihak-pihak yang mengatasnamakan umrahalal.com bila bertransaksi secara langsung.</p>

      <p><b>Alamat</b> :<br>
      Jl. Cempaka Baru Tengah No.20 <br>
      Rt.010 / Rw.006 <br>
      Kel.Cempaka Baru<br>
      Kemayoran - Jakarta Pusat<br>
      10640<br><br>
      <b>CS</b>      : 021-22423792<br>
      <b>Hotline</b> : 0822 6137 4944<br>
      <b>Email</b>   : cs@umrahalal.com<br>
      <b>Website</b> : umrahalal.com</p>
    </f7-block>
    <f7-block-title>Belum punya akun ?</f7-block-title>
    <f7-list>
      <f7-list-item link="/register/" title="Pendaftaran" view="#main-view" panel-close></f7-list-item>
    </f7-list>
  </f7-page>
</template>
<script>
export default {
  methods: {
    gologin() {
      const self = this;
      const app = self.$f7;
      self.$f7ready(($f7) => {$f7.views.main.router.navigate('/login/');});
    },
    gologout() {
      const self = this;
      const app = self.$f7;
      self.$auth.clearLogin(self);
      self.$f7ready(($f7) => {$f7.views.main.router.navigate('/');});
      this.islogin = false;
    }
  }
}
</script>