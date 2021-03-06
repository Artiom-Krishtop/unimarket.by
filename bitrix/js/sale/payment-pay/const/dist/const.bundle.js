this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.PaymentPay = this.BX.Sale.PaymentPay || {};
(function (exports) {
	'use strict';

	var EventType = Object.freeze({
	  payment: {
	    start: 'Sale:PaymentPay:Payment:Start',
	    error: 'Sale:PaymentPay:Payment:Error',
	    success: 'Sale:PaymentPay:Payment:Success'
	  },
	  consent: {
	    accepted: 'Sale:PaymentPay:Consent:Accepted',
	    refused: 'Sale:PaymentPay:Consent:Refused'
	  },
	  global: {
	    paySystemAjaxError: 'onPaySystemAjaxError',
	    paySystemUpdateTemplate: 'onPaySystemUpdateTemplate'
	  }
	});

	exports.EventType = EventType;

}((this.BX.Sale.PaymentPay.Const = this.BX.Sale.PaymentPay.Const || {})));
//# sourceMappingURL=const.bundle.js.map
