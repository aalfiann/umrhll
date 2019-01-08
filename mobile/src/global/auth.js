import Vue from 'vue';
const auth = {
	isLogin(self) {
    if(self.$storage.get('token')){
      if(self.$storage.get('token').lifetime <= Date.now() || self.$storage.get('token').token == ''){
        return false;
      }
      self.$auth.refreshLogin(self);
      return true;
    }
    return false;
  },
  setLogin(self,token,expire_at=300000) {
    var tk = {};
    tk["lifetime"] = Date.now()+expire_at;
    tk["token"] = token;
    self.$storage.set('token', tk);
  },
  refreshLogin(self,expire_at=300000) {  
    var tk = {};
    tk["lifetime"] = Date.now()+expire_at;
    tk["token"] = self.$storage.get('token').token;
    self.$storage.set('token', tk);
  },
  clearLogin(self) {
    var tk = {};
    tk["lifetime"] = Date.now()-300000;
    tk["token"] = '';
    self.$storage.set('token', tk);
  }
}

Vue.$auth = auth;
Object.defineProperty(Vue.prototype, '$auth', {
  get() {
    return auth;
  },
});