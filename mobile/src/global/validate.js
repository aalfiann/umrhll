import Vue from 'vue';
const validate = {
	isString(value) {
    if (typeof value === 'string') {
      return true;
    }
    return false;
  },
  isInt(value) {  
    return Number.isInteger(value);
  },
  isEmpty(value){
    for(var key in value) {
      if(value.hasOwnProperty(key))
        return false;
    }
    return true;
  },
  isNotEmpty(value) {
    if (value !== '' && value !== null && typeof value !== 'undefined') {
      return true;
    }
    return false;
  },
  isRequired(value){
    var rgx = /.*\S.*/;
    if (rgx.test(value) == false) {
      return false;
    }
    return true;
  },
  isDate(value){
    var rgx = /([123456789]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))/;
    if (rgx.test(value) == false) {
      return false;
    }
    return true;
  },
  isTimestamp(value){
    var rgx = /^\d\d\d\d-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01]) (00|[0-9]|1[0-9]|2[0-3]):([0-9]|[0-5][0-9]):([0-9]|[0-5][0-9])$/;
    if (rgx.test(value) == false) {
      return false;
    }
    return true;
  },
  isAlphanumeric(value){
    var rgx = /^[a-zA-Z0-9]+$/;
    if (rgx.test(value) == false) {
      return false;
    }
    return true;
  },
  isAlphabet(value){
    var rgx = /^[a-zA-Z]+$/;
    if (rgx.test(value) == false) {
      return false;
    }
    return true;
  },
  isDecimal(value){
    var rgx = /^[+-]?[0-9]+(?:\.[0-9]+)?$/;
    if (rgx.test(value) == false) {
      return false;
    }
    return true;
  },
  isNotLeadingZero(value){
    var rgx = /^[1-9][0-9]*$/;
    if (rgx.test(value) == false) {
      return false;
    }
    return true;
  },
  isNumeric(value){
    var rgx = /^[0-9]+$/;
    if (rgx.test(value) == false) {
      return false;
    }
    return true;
  },
  isDouble(value){
    var rgx = /^[+-]?[0-9]+(?:,[0-9]+)*(?:\.[0-9]+)?$/;
    if (rgx.test(value) == false) {
      return false;
    }
    return true;
  },
  isUsername(value){
    var rgx = /^[a-zA-Z0-9]{3,20}$/;
    if (rgx.test(value) == false) {
      return false;
    }
    return true;
  },
  isDomain(value){
    var rgx = /^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/;
    if (rgx.test(value) == false) {
      return false;
    }
    return true;
  },
  isEmail(value){
    var rgx = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (rgx.test(value) == false) {
      return false;
    }
    return true;
  },
  withRegex(value,rgx) {
    if (rgx.test(value) == false) {
      return false;
    }
    return true;
  }
}

Vue.$validate = validate;
Object.defineProperty(Vue.prototype, '$validate', {
  get() {
    return validate;
  },
});