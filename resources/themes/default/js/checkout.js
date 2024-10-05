import {HSCollapse} from "preline/preline.js";

const toggle = document.querySelectorAll('#checkout-form .hs-collapse-toggle');
const gatewayInputs = document.querySelectorAll('.gateway-input');
toggle.forEach(function (el) {
  el.addEventListener('click', function (e) {
      Array.from(toggle).filter(function(el) {
          return el !== e.currentTarget;
      }).map(function(el) {
          HSCollapse.hide(el);
      });
  })
})
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function () {
        const index = (location.href.split('#')[1] ?? '') === 'login' ? 0 : 1 ;
        HSCollapse.show(toggle[index]);
    }, 100);
});
gatewayInputs.forEach(function (el) {
  el.addEventListener('change', function (e) {
      e.target.parentNode.classList.add('gateway-selected');
      Array.from(gatewayInputs).filter(function(el) {
          return el !== e.target;
      }).map(function(el) {
          el.parentNode.classList.remove('gateway-selected');
      });
  })
})
document.querySelector('#btnCheckout').addEventListener('click', function (e) {
    e.preventDefault();
    document.querySelector('#checkoutForm').submit();
})
