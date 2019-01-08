<template>
  <!-- App -->
  <f7-app :params="f7params">

    <!-- Statusbar -->
    <f7-statusbar></f7-statusbar>

    <!-- Left Panel -->
    <f7-panel left reveal theme-light>
      <f7-view url="/panel-left/"></f7-view>
    </f7-panel>


    <!-- Main View -->
    <f7-view id="main-view" url="/" main></f7-view>

    <!-- Popup -->
    <f7-popup id="popup">
      <f7-view>
        <f7-page>
          <f7-navbar title="Popup">
            <f7-nav-right>
              <f7-link popup-close>Close</f7-link>
            </f7-nav-right>
          </f7-navbar>
          <f7-block>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Neque, architecto. Cupiditate laudantium rem nesciunt numquam, ipsam. Voluptates omnis, a inventore atque ratione aliquam. Omnis iusto nemo quos ullam obcaecati, quod.</f7-block>
        </f7-page>
      </f7-view>
    </f7-popup>

    <!-- Login Screen -->
    <f7-login-screen id="login-screen">
      <f7-view>
        <f7-page login-screen>
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
              <p>Click Sign In to close Login Screen</p>
            </f7-block-footer>
          </f7-list>
        </f7-page>
      </f7-view>
    </f7-login-screen>

  </f7-app>
</template>

<script>
// Import Routes
import routes from './routes.js';
export default {
  data() {
    return {
      // Framework7 parameters here
      f7params: {
        id: 'com.csw.umrahalal', // App bundle ID
        name: 'umrahalal', // App name
        theme: 'auto', // Automatic theme detection
        // App routes
        routes: routes
      },
      username: '',
      password: '',
      token: ''
    }
  },
  methods: {
      signIn() {
        const self = this;
        const app = self.$f7;
        app.dialog.alert(`Username: ${self.username}<br>Password: ${self.password}`, () => {
          app.loginScreen.close();
          self.$f7ready(($f7) => {$f7.views.main.router.navigate('/about/');}); 
        });
        var tk = {};
        tk["lifetime"] = Date.now();
        tk["token"] = 'cth001239';
        this.$storage.set('token', tk);
        console.log(self.$apikey);
      },
    },
}
</script>
