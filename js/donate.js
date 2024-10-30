/*jslint strict: true, indent: 2, plusplus: true, browser: true, nomen: true */
function submitDonation(formId) {
  var form = document.getElementById(formId), donation_amount;
  setAction(jQuery('#' + formId), "savePending");
  donation_amount = jQuery('#donation_amount').val();
  if (isNaN(parseInt(donation_amount, 10))) {
    alertMessage("You must specify a numeric donation amount.");
    return false;
  }
  if (donation_amount.length < 1 || parseInt(donation_amount, 10) <= 0) {
    alertMessage("You must specify a donation amount.");
    return false;
  }
  
  data = jQuery('#' + formId).serializeArray();
  jsonpRequest('donate.service', data, function(response) {
    _savePending(response, alertMessage);
  }, logit, false, 'POST', false);
  return false;
}
