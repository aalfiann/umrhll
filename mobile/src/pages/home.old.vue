<template>
  <f7-page>
    <f7-navbar>
      <f7-nav-left>
        <f7-link icon-if-ios="f7:menu" icon-if-md="material:menu" panel-open="left" data-reload="true"></f7-link>
      </f7-nav-left>
      <f7-nav-title>Umrahalal</f7-nav-title>
    </f7-navbar>
    <p class="text-center"><img width="300" src="../images/umrahalal-logo-app.jpg" /></p>
    <hr>
    <f7-block>
      <f7-row>
        <f7-col width="33" class="text-center">
          <a href="/agent/">
            <img width="64" height="64" src="../images/arab.png" />
            <br><span>Agent</span>
          </a>
        </f7-col>
        <f7-col width="33" class="text-center">
          <a href="/saldo/">
            <img width="64" height="64" src="../images/wallet.png" />
            <br><span>Cek Saldo</span>
          </a>
        </f7-col>
        <f7-col width="33" class="text-center">
          <a href="/schedule/">
            <img width="64" height="64" src="../images/101-time.png" />
            <br><span>Cek Jadwal</span>
          </a>
        </f7-col>
      </f7-row>
    </f7-block>
    <f7-block>
      <f7-row>
        <f7-col width="33" class="text-center">
          <a href="/about/">
            <img width="64" height="64" src="../images/101-skyscraper.png" />
            <br><span>Tentang Kami</span>
          </a>
        </f7-col>
        <f7-col width="33" class="text-center">
          <a href="/tos/">
            <img width="64" height="64" src="../images/101-list.png" />
            <br><span>Syarat dan Ketentuan</span>
          </a>
        </f7-col>
        <f7-col v-if="waslogin" width="33" class="text-center">
          <a href="#" @click="goLogout">
            <img width="64" height="64" src="../images/101-security.png" />
            <br><span>Logout</span>
          </a>
        </f7-col>
      </f7-row>
    </f7-block>
    <f7-block-title>Navigation</f7-block-title>
    <f7-list>
      <f7-list-item link="/about/" title="About"></f7-list-item>
      <f7-list-item link="/form/" title="Form"></f7-list-item>
    </f7-list>
    <f7-block-title>Modals</f7-block-title>
    <f7-block strong>
      <f7-row>
        <f7-col width="50">
          <f7-button fill raised popup-open="#popup">Popup</f7-button>
        </f7-col>
        <f7-col width="50">
          <f7-button fill raised login-screen-open="#login-screen">Login Screen</f7-button>
        </f7-col>
      </f7-row>
    </f7-block>
    <f7-block-title>Panels</f7-block-title>
    <f7-block strong>
      <f7-row>
        <f7-col width="50">
          <f7-button fill raised panel-open="left">Left Panel</f7-button>
        </f7-col>
        <f7-col width="50">
          <f7-button fill raised panel-open="right">Right Panel</f7-button>
        </f7-col>
      </f7-row>
    </f7-block>
    <f7-list>
      <f7-list-item link="/dynamic-route/blog/45/post/125/?foo=bar#about" title="Dynamic Route"></f7-list-item>
      <f7-list-item link="/load-something-that-doesnt-exist/" title="Default Route (404)"></f7-list-item>
    </f7-list>
  </f7-page>
</template>
<script>
export default {
  data() {
    return {
      waslogin: false
    }
  },
  mounted() {
    this.waslogin = this.isLogged();
  },
  methods: {
    isLogged(){
      const self = this;
      const app = self.$f7;
      if(self.$auth.isLogin(self)){
        return true;
      }
      return false;
    },
    goLogin() {
      const self = this;
      const app = self.$f7;
      self.$f7ready(($f7) => {$f7.views.main.router.navigate('/login/');});
    },
    goLogout() {
      const self = this;
      const app = self.$f7;
      self.$auth.clearLogin(self);
      self.$f7ready(($f7) => {$f7.views.main.router.navigate('/');});
      this.islogin = false;
    }
  }
}
</script>
