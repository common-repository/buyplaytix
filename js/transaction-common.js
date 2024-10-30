/*jslint strict: true, indent: 2, plusplus: true, browser: true, nomen: true */
function _savePending(response, errorFunction) {
  try {
    if (!errorFunction) {
      errorFunction = alertError;
    }
    showForcefield();
    if (response.not_enough_tickets == "True") {
      clearForcefield();
      errorFunction("There are not enough tickets left to fulfill your order.");
      return false;
    }
    if (response.__error) {
      clearForcefield();
      errorFunction(response.__error);
      return false;
    }
    if (response.__inline_shopping_cart == "True" && response.__inline_shopping_cart_callback) {
      clearForcefield();
      var callback = window[response.__inline_shopping_cart_callback];
      callback(response);
      return false;
    }
    if (response.__shopping_cart == "True") {
      if (response.changed_quantity == "True") {
        clearForcefield();
        alertMessage("You have added a duplicate item to your cart. Please verify the quantity is what you intended.", function() {
          document.location.href = basepath + 'editcart.html?PHPSESSID=' + response.PHPSESSID;
        });
        return false;
      }
      document.location.href = basepath + 'editcart.html?PHPSESSID=' + response.PHPSESSID;
      return false;
    }
    if (response.__checkout_form == "True") {
      // should only get here if we use authorize and don't have a shopping cart
      document.location.href = basepath + 'checkout-signin.html.html?PHPSESSID=' + response.PHPSESSID;
      return false;
    }

    disableButtons(true);
    updatePaymentForm(response);

    var form = jQuery('#payment_form');
    form.submit();
  } 
  catch (e) {
    logit(e);
    alertMessage(e);
  }
}
