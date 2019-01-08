<template>
        <f7-page login-screen>
          <f7-navbar><f7-link href="/"><f7-icon icon="icon-back"></f7-icon></f7-link> <div class="title">Kembali</div></f7-navbar>
          <f7-login-screen-title>Login</f7-login-screen-title>
          <f7-list form>
            <f7-list-item>
              <f7-label>Username</f7-label>
              <f7-input name="username" placeholder="Username" type="text"
               :value="username"
               @input="username = $event.target.value"></f7-input>
            </f7-list-item>
            <f7-list-item>
              <f7-label>Password</f7-label>
              <f7-input name="password" type="password" placeholder="Password"
               :value="password"
               @input="password = $event.target.value"></f7-input>
            </f7-list-item>
          </f7-list>
          <f7-list>
            <f7-list-button title="Sign In" @click="signIn" login-screen-close></f7-list-button>
            <f7-block-footer>
              <p><f7-link href="/register/">Belum punya akun ?</f7-link></p>
            </f7-block-footer>
          </f7-list>
        </f7-page>
</template>

<script>
export default {
  data() {
    return {
      username: '',
      password: '',
      token: '',
      postResults:[]
    }
  },
  methods: {
    signIn() {
        const self = this;
        const app = self.$f7;
        app.request({
          url: this.$apiurl+'/user/login',
          method: 'POST',
          data: {
            Username: self.username,
            Password: self.password
          },
          dataType: 'JSON',
          beforeCreate: function() {
            app.preloader.show();
          },
          success: function (data, status, xhr) {
            var obj = JSON.parse(data);
            if (obj.status == 'success') {
              this.postResults = obj;
              self.$auth.setLogin(self,obj.token);
              self.$f7ready(($f7) => {$f7.views.main.router.navigate('/');});
            } else {
              app.dialog.alert(`Failed!<br>`+obj.message);
            }
          },
          error: function (xhr, status) {
            var obj = JSON.parse(xhr.responseText);
            app.dialog.alert(`Failed!<br>`+obj.message);
          },
          complete: function (xhr, status) {
            app.preloader.hide();
          }
        });
      }
  },
}
</script>
