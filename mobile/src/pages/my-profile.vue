<template>
  <f7-page>
    <f7-navbar title="Profil Saya" back-link="Back"></f7-navbar>
    <f7-list form>
      <f7-list-item>
        <f7-label>Nama Lengkap</f7-label>
        <f7-input 
          type="text"
          placeholder="Nama lengkap Anda..."
          name="fullname"
          :error-message="messagefullname"
          :error-message-force="validatefullname"
          :value="fullname"
          @input="fullname = $event.target.value"></f7-input>
      </f7-list-item>
      <f7-list-item>
        <f7-label>Alamat E-mail</f7-label>
        <f7-input 
          type="email" 
          placeholder="Alamat E-mail Anda..."
          name="email"
          error-message="Format alamat email salah!"
          :error-message-force="validateemail"
          :value="email"
          @input="email = $event.target.value"></f7-input>
      </f7-list-item>
      <f7-list-item>
        <f7-label>Password</f7-label>
        <f7-input 
          type="password" 
          placeholder="Password Anda..."
          name="password1"
          error-message="Password tidak cocok!"
          :error-message-force="validatepassword1"
          :value="password1"
          @input="password1 = $event.target.value"></f7-input>
      </f7-list-item>
      <f7-list-item>
        <f7-label>Konfirmasi Password</f7-label>
        <f7-input 
          type="password" 
          placeholder="Konfirmasi Password Anda..."
          name="password2"
          error-message="Password tidak cocok!"
          :error-message-force="validatepassword2"
          :value="password2"
          @input="password2 = $event.target.value"></f7-input>
      </f7-list-item>
    </f7-list>

    <f7-block><p>Dengan klik <b>Submit</b> maka Anda telah menyetujui semua Syarat dan Ketentuan dari Umrahalal.</p></f7-block>
    <f7-block strong>
      <f7-row tag="p">
        <f7-button class="col" big fill raised color="red" @click="goBack">Batal</f7-button>
        <f7-button class="col" big fill raised color="green" @click="processRegisterAgent">Submit</f7-button>
      </f7-row>
    </f7-block>
  </f7-page>
</template>

<script>
import _ from 'lodash';
export default {
  beforeCreate() {
    const self = this;
    const app = self.$f7;
    if(!self.$auth.isLogin(self)){
      self.$f7ready(($f7) => {$f7.views.main.router.navigate('/login/');});
    }
  },
  data() {
    return {
      postResults:[],
      verifyResults:[],
      fullname: '',
      email: '',
      password1: '',
      password2: '',
      fullnameexists: false,
      messagefullname: 'Username 3-20 (angka dan huruf)',
      validatefullname: false,
      validateemail: false,
      validatepassword1: false,
      validatepassword2: false
    }
  },
  watch: {
    username: _.debounce(function() {
      if(this.$validate.isUsername(this.username)){      
        if(this.isUsernameExists()){
          this.validateusername = true;
          this.messageusername = 'Username telah terpakai!'; 
        } else {
          this.validateusername = false;
        }
      } else {
        this.validateusername = true;
      }
    },3000),
    email: function() {
      if(this.$validate.isEmail(this.email)){
        this.validateemail = false;
      } else {
        this.validateemail = true;
      }
    },
    password1: function() {
      if(this.password2.length <= 0){
        this.validatepassword2 = false;
      } else {
        if(this.password1 == this.password2){
          this.validatepassword2 = false;
        } else {
          this.validatepassword2 = true;
        }
      }
    },
    password2: function() {
      if(this.password2.length <= 0){
        this.validatepassword2 = false;
      } else {
        if(this.password2 == this.password1){
          this.validatepassword2 = false;
        } else {
          this.validatepassword2 = true;
        }
      }
    },
  },
  methods: {
    goBack() {
      this.$f7ready(($f7) => {$f7.views.main.router.navigate('/');});
    },
    isUsernameExists(){
      const self = this;
      const app = self.$f7;
      var results = false;
      app.request({
        url: this.$apiurl+'/user/verify/register/'+this.username,
        method: 'GET',
        dataType: 'JSON',
        async: false,
        beforeCreate: function() {
          app.preloader.show();
        },
        success: function (data, status, xhr) {
          var obj = JSON.parse(data);
          if (obj.status == 'success') {
            this.verifyResults = obj;
            results = true;
          } else {
            results = false;
          }
        },
        error: function (xhr, status) {
          var obj = JSON.parse(xhr.responseText);
          app.dialog.alert(`Failed!<br>`+obj.message);
          results = false;
        },
        complete: function (xhr, status) {
          app.preloader.hide();
        }
      });
      return results;
    },
    processRegisterAgent() {
        const self = this;
        const app = self.$f7;
        app.request({
          url: this.$apiurl+'/user/register/public',
          method: 'POST',
          data: {
            Username: self.username,
            Password: self.password2,
            Email: self.email,
            Fullname: self.username,
            Address: "",
            Phone: "",
            Aboutme: "",
            Avatar: "",
            Role: '3'
          },
          dataType: 'JSON',
          beforeCreate: function() {
            app.preloader.show();
          },
          success: function (data, status, xhr) {
            var obj = JSON.parse(data);
            if (obj.status == 'success') {
              app.dialog.confirm('Berhasil, coba login sekarang!', function () {
                self.$f7ready(($f7) => {$f7.views.main.router.navigate('/login/');});
              });
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
