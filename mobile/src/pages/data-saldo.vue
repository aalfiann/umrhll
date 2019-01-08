<template>
  <f7-page>
    <f7-navbar title="Data Saldo" back-link="Back"></f7-navbar>
    <f7-block strong>
      <p>Mohon Maaf</p>
      <p>Fitur ini masih dalam proses pembuatan.</p>
    </f7-block>
  </f7-page>
</template>
<script>
  export default {
    beforeCreate() {
      const self = this;
      const app = self.$f7;
      if(!self.$auth.isLogin(self)){
        self.$f7ready(($f7) => {$f7.views.main.router.navigate('/login/');});
      }
    }
  };
</script>