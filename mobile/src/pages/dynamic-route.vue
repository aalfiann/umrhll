<template>
  <f7-page>
    <f7-navbar title="Dynamic Route" back-link="Back"></f7-navbar>
    <f7-block strong>
      <ul>
        <li><b>Url:</b> {{$f7route.url}}</li>
        <li><b>Path:</b> {{$f7route.path}}</li>
        <li><b>Hash:</b> {{$f7route.hash}}</li>
        <li><b>Token:</b> {{token | capitalize }}</li>
        <li><b>Token-Expired:</b> {{tokentime}}</li>
        <li><b>Params:</b>
          <ul>
            <li v-for="(value, key) in $f7route.params" :key="key"><b>{{key}}:</b> {{value}}</li>
          </ul>
        </li>
        <li><b>Query:</b>
          <ul>
            <li v-for="(value, key) in $f7route.query" :key="key"><b>{{key}}:</b> {{value}}</li>
          </ul>
        </li>
        <li><b>Route:</b> {{$f7route.route}}</li>
      </ul>
    </f7-block>
    <f7-block strong>
      <f7-link @click="$f7router.back()">Go back via Router API</f7-link>
    </f7-block>
  </f7-page>
</template>

<script>
export default {
  beforeCreate() {
    const self = this;
    const app = self.$f7;
    if(self.$auth.isLogin(self)){
      console.log(this.$storage.get('token').lifetime);
      console.log(this.$storage.get('token').token);
      console.log(Date.now());
      console.log(self.$apikey);
    } else {
      self.$f7ready(($f7) => {$f7.views.main.router.navigate('/login/');});
    }
  },
  data() {
    return {
      token: '',
      tokentime:''
    }
  }
}
</script>
