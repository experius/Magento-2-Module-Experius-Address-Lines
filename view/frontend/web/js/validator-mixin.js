define([
  'jquery',
  'moment'
], function ($, moment) {
  'use strict';

  return function (validator) {

    validator.addRule(
      'po-box-validation',
      function (value) {
        var re = /^parcel.*|^locker.*|^box.*|.*po\s*box.*|.*p[\.\s]o\.?\sbox.*/i
        return !re.test(value);
      }, $.mage.__("We don't ship to PO Boxes, Parcel Lockers, or any postal service. Courier only.")
    );

    return validator;
  };
})
