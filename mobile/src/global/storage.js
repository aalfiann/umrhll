import Vue from 'vue';
const storage = {
	has(key) {
  	return !!localStorage.getItem(key);
  },
  get(key) {
  	return JSON.parse(localStorage.getItem(key));
  },
  set(key, value) {
  	localStorage.setItem(key, JSON.stringify(value));
  }
};

Vue.$storage = storage;
Object.defineProperty(Vue.prototype, '$storage', {
  get() {
    return storage;
  },
});