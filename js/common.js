/*jslint strict: true, indent: 2, plusplus: true, browser: true, nomen: true */
/*global $:true, alert: true, crosspath: true, basepath: true, smoke: true, Stripe: true, prompt: true, imagepath: true, jQuery: true*/
var ValidationResponse = function (id, success, message) {
  this.id = id;
  this.success = success;
  this.message = message;
};
function center(el) {
  (function($) {
    el = $(el);
    var height = el.innerHeight();
    el.css("position","relative");
    el.css("top", Math.max(0, (($(window).height() - $(el).outerHeight()) / 2) + 
        $(window).scrollTop()) + "px");
    el.css("left", Math.max(0, (($(window).width() - $(el).outerWidth()) / 2) + 
        $(window).scrollLeft()) + "px");    
    el.css("min-height", height + "px");
    
    el.scrollTop(el.height());    
  })(jQuery);
}
function fullheight(el) {
  el.css("height", ($(window).height()));
}

function setAction(form, action) {
  jQuery(form).find("input[name='action']").val(action);
}

function logit(msg) {
  if (window.console) {
    window.console.log(msg);
  }
}

function render(template, options) {
  if(window.Mustache) {
    return Mustache.render(template, options);
  }
}

function isNotEmpty(val) {
  if (val === undefined || val === null) {
    return false;
  }
  if (String(val).match(/^\s*$/)) {
    return false;
  }
  return true;
}

function isEmpty(val) {
  return !isNotEmpty(val);
}

function parseQueryString(queryString) {
  var i, params = {}, pairs, tuple;
  pairs = queryString.split("&");
  for (i = 0; i < pairs.length; i++) {
    tuple = pairs[i].split("=");
    params[tuple[0]] = tuple[1];
  }
  return params;
}

function disableButtons(disable) {
  if (!disable) {
    jQuery(document).find("input:button").removeAttr('disabled');
    jQuery(document).find("button").removeAttr('disabled');
    return;
  }
  jQuery(document).find("input:button").attr('disabled', 'disabled');
  jQuery(document).find("button").attr('disabled', 'disabled');
}

function showForcefield() {
  jQuery('#pleaseWait').show();
}

function clearForcefield(data, callback) {
  jQuery('#pleaseWait').hide();
  document.body.scrollTop = document.documentElement.scrollTop = 0;

  disableButtons(false);
  if (callback) {
    try {
      /*jshint validthis:true */
      callback.call(this, data);
    } catch (e) {
      alert(e);
    }
  }
}

function alertMessage(msg, callback) {
  var isMobile = /mobile|android/i.test (navigator.userAgent);
  if(window.smoke && !isMobile) {
    smoke.alert(msg);
  } else {
    alert(msg);
  }
  if (callback) {
    callback.call();
  }
}

function highlight(el){
  (function($) {
    try {
      var destination = $(el).offset().top;
      $('html, body').animate({
        scrollTop: destination
      }, 'slow');  
      $(el).effect('pulsate', {
        times: 3
      }, 1000);
    } catch(e) {
      logit(e);
    }
    return false;
  })(jQuery);
}

var dialog_tmpl = '<div class="dialog-overlay {{className}}" role="dialog"  aria-labelledby="dialogTitle">' +
  '<div class="dialog">' +
  '<header id="dialogTitle">{{title}}</header>' +
  '<section>' +
  '{{{msg}}}' +
  '<div class="error"></div>' +  
  '</section>' +
  '<footer class="buttonbar">' +
  '{{^hideOk}}' +
  '<button class="dialog-ok-button button">{{okLabel}}</button>' +
  '{{/hideOk}}' +
  '<button class="dialog-cancel-button button">{{cancelLabel}}</button>' +
  '</footer>' +
  '</div>' + 
'</div>';

var confirm_tmpl = '<div class="dialog-overlay {{className}}">' +
'<div class="dialog">' +
'<header>{{title}}</header>' +
'<section>' +
'{{{msg}}}' +
'<div class="error"></div>' +  
'</section>' +
'<footer class="buttonbar">' +
'<button class="dialog-ok-button button">Ok</button>' +
'</footer>' +
'</div>' + 
'</div>';


function closeDialog() {
  jQuery("div.dialog-overlay").detach();
  jQuery('.wrapper').show();
}

function addDialogError(msg) {
  jQuery("div.dialog-overlay").find(".error").html(msg).show();
}

function confirmMessage(title, msg, callback, className) {
  try {
    jQuery("div.dialog-overlay").detach();
    var output = render(confirm_tmpl, {
      "title": title,
      "msg": msg,
      "className": className
    });
    var dialog = jQuery('body').prepend(output);
    center(dialog.find('.dialog'));
    
    if(!callback) {
      callback = closeDialog;
    }
    
    dialog.find('.dialog-ok-button').click(function() {
      if(callback.call(this, dialog) === true) {
        closeDialog();
      }
    });
  } catch (e) {
    console.trace(e);
    alert(e);
  }  
}
function promptMessage(title, msg, callback, cancelCallback, params, hideOk) {
  try {
    if(!params) {
      params = {};
    }
    var className = params.className || '';
    var okLabel = params.okLabel || 'Ok';
    var cancelLabel = params.cancelLabel || 'Cancel';
    
    jQuery("div.dialog-overlay").detach();
    var output = render(dialog_tmpl, {
      "title": title,
      "msg": msg,
      "okLabel": okLabel,
      "cancelLabel": cancelLabel,
      "className": className,
      "hideOk": hideOk 
    });
    var dialog = jQuery('body').prepend(output);
    if(!className || className.match(/mobile-dialog/) === false) {
      center(dialog.find('.dialog'));
    } else if(className && className.match(/mobile-dialog/)) {
      $('.wrapper').hide();
      dialog.show();
    }
    
    if(!cancelCallback) {
      cancelCallback = closeDialog;
    }
    if(!callback) {
      callback = closeDialog;
    }
    
    
    dialog.find('.dialog-ok-button').click(function() {
      if(callback.call(this, dialog) === true) {
        closeDialog();
      }
    });
    dialog.find('.dialog-cancel-button').click(function() {
      if(cancelCallback.call(this, dialog) === true) {
        closeDialog();
      }
    });
  } catch (e) {
    console.trace(e);
    alert(e);
  }
}
function alertImage(msg, width, height) {
  var params = {
    autoOpen : true,
    modal : true,
    closeOnEscape : true
  };
  if (width) {
    params.width = width;
  }
  if (height) {
    params.height = height;
  }
  try {
    return jQuery('<div>' + msg + '</div>').dialog(params);
  } catch (e) {
    alert(e);
  }
}

function alertError(msg, callback) {
  try {
    if(!msg) {
      msg = "Unknown Error";
    }
    closeDialog();

    var isMobile = /mobile|android/i.test (navigator.userAgent);
    if(window.smoke && !isMobile) {
      smoke.alert(msg);
    } else {
      alert(msg);
    }
    if (callback) {
      callback.call();
    }
  } catch (e) {
    alert(e);
  }
}

function ajaxButtonRequest(service, data, callback, errorCallback,
    leaveForcefield, requestType, showForceField, cd) {
  if (showForceField) {
    showForcefield();
  }

  try {
    if (!requestType) {
      requestType = "POST";
    }
    /*jslint unparam: true*/
    var params = {
      data : data,
      type : requestType,
      url : basepath + service,
      success : function (data) {
        if (data && data.responseCode && data.responseCode === 401) {
          document.location = basepath + data.responseObj;
        }
        if (leaveForcefield) {
          callback.call(this, data);
          return;
        }
        clearForcefield(data, callback);
      },
      error : function (jqXHR, exception) {
        var message = "Unknown Connection Error";
        if (jqXHR.status === 0) {
          message = "Not connected. Please check your network connection.";
        } else if (jqXHR.status == 404) {
          message = "Page not found.";
        } else if (jqXHR.status == 500) {
          message = "Server Error. Please contact support.";        
        } else if (exception === 'parsererror') {
          message = "Unexpected Response. Please contact support.";
        } else if (exception === 'timeout') {
          message = "Time Out Error. Please check your network connection.";
        } else if (exception === 'abort') {
          message = "Request Aborted. Please check your network connection.";
        }
        clearForcefield(message, errorCallback);
      }
    };
    /*jslint unparam: false*/
    if (cd && crosspath) {
      params.url = crosspath + service;
      params.crossDomain = true;
      params.xhrFields = {
        withCredentials : true
      };
    }
    jQuery.ajax(params);
  } catch (e) {
    clearForcefield();
    alert(e);
  }
  return false;
}

function jsonpRequest(service, data, callback, errorCallback,
    leaveForcefield, requestType, showForceField) {
  if (showForceField) {
    showForcefield();
  }

  try {
    if (!requestType) {
      requestType = "POST";
    }
    data[data.length] = {
      name: 'method',
      value: requestType
    };
    /*jslint unparam: true*/
    var params = {
      data : data,
      dataType: 'jsonp',
      crossDomain: true,
      type : 'GET',
      url : basepath + service,
      success : function (data) {
        if (data && data.responseCode && data.responseCode === 401) {
          document.location = basepath + data.responseObj;
        }
        if (leaveForcefield) {
          callback.call(this, data);
          return;
        }
        clearForcefield(data, callback);
      },
      error : function (XMLHttpRequest, textStatus, errorThrown) {
        clearForcefield(errorThrown, errorCallback);
      }
    };
    jQuery.ajax(params);
  } catch (e) {
    alert(e);
  }
  return false;
}


function ajaxButtonClick(service, form, callback, errorCallback,
    leaveForcefield) {
  var data = null;
  if (form) {
    if (form.substring(0, 1) !== '#') {
      form = '#' + form;
    }
    data = jQuery(form).serialize();
  }
  return ajaxButtonRequest(service, data, callback, errorCallback,
      leaveForcefield);
}

function _ajaxSelectLoader(data) {
  if (!data) {
    console.log('no data returned');
    return;
  }
  if (data.responseCode && data.responseCode !== '200') {
    alertMessage(data.responseObj);
    return;
  }

  var html = "", select, key;
  select = jQuery('#' + data.select_id);
  for (key in data.responseObj) {
    if (data.responseObj.hasOwnProperty(key)) {
      html += "<option value='" + key + "'>" + data.responseObj[key] + "</option>";
    }
  }
  select.html(html);
}

function ajaxSelectLoader(service, action, el) {
  showForcefield();
  try {
    var data = {}, form, i;
    form = el.parents('form');
    if (form) {
      data = jQuery(form).serializeArray();
    }
    for (i = 0; i < data.length; i++) {
      if (data[i].name === 'action') {
        data[i].value = action;
      }
    }
    data[data.length] = {
      name : 'select_id',
      value : el.attr('id')
    };
    /*jslint unparam: true*/
    jQuery.ajax({
      data : data,
      type : "GET",
      url : basepath + service,
      success : function (data, textStatus, XMLHttpRequest) {
        clearForcefield(data, _ajaxSelectLoader);
      }
    });
    /*jslint unparam: false*/
  } catch (e) {
    alert(e);
  }
  return false;
}

function clearErrors(parent) {
  jQuery(parent).find('.errored div.fieldError').detach();
}

function camelCase(original) {
  return original.substring(0, 1).toUpperCase() + original.substring(1);
}

function validateFunction(name) {
  if (name.length > 0) {
    return "validate" + camelCase(name);
  }
}

function _validate(parent, params) {
  var response = [], v, vs, i, il, f;

  parent = jQuery(parent);
  v = parent.attr("v");
  if (v) {
    // we need to remove single-quotes from validators
    v = v.replace(/'/g, '"');
    vs = JSON.parse(v);
    for (i = 0, il = vs.length; i < il; i++) {
      f = validateFunction(vs[i]);
      if (window[f]) {
        response.push(window[f](params, parent.attr("id")));
      }
    }
  }
  parent.children().each(function () {
    response = response.concat(_validate(this, params));
  });
  return response;
}

function addError(id, message) {
  var el, errorDiv;
  el = jQuery('#' + id).parent();
  el.addClass("errored");

  errorDiv = document.createElement("div");
  errorDiv.className = "fieldError";
  errorDiv.setAttribute("title", message);
  errorDiv.innerHTML = message;
  el.append(errorDiv);
}

function switchTab() {
  var foundTab = false;
  jQuery('fieldset.tab').each(function (index, element) {
    if (foundTab) {
      return;
    }
    if (jQuery(element).find("div.errored").size() > 0) {
      jQuery('#editproduction_tabs').select(index);
      foundTab = true;
    }
  });
}

function validate(parent) {
  var isValid = true, params, i, elements, response, r;
  try {
    clearErrors(parent);
    elements = jQuery(parent).find("[v]");

    params = {};
    for (i = 0; i < elements.length; i++) {
      params[elements[i].id] = elements[i];
    }
    response = _validate(parent, params);

    for (i = 0; i < response.length; i++) {
      r = response[i];
      if (!r.success) {
        isValid = false;
        addError(r.id, r.message);
      }
    }
  } catch (e) {
    alert(e);
    logit(params);
    isValid = false;
  }
  switchTab();
  return isValid;
}

function validateRequired(params, id) {
  var el, fieldName;
  if (/_wrapper$/.test(id)) {
    // checkboxgroup or radiogroup make sure an input is checked
    if (jQuery(params[id]).find("input:checked").length > 0) {
      return new ValidationResponse(id, true);
    }
  } else if (params[id]) {
    el = jQuery(params[id]);
    if (el.is(":hidden") || isNotEmpty(el.val())) {
      return new ValidationResponse(id, true);
    }
  }
  fieldName = jQuery(document).find('label[for=' + id + ']');
  if (fieldName.length > 0) {
    return new ValidationResponse(id, false, jQuery(fieldName[0]).html() + " is required.");
  }
  return new ValidationResponse(id, false, "This field is required.");
}

function validatePositiveInteger(params, id) {
  if (params[id]) {
    var val = jQuery(params[id]).val();
    if (isNotEmpty(val) && val > 0) {
      return new ValidationResponse(id, true);
    }
  }
  return new ValidationResponse(id, false,
      "This field must be greater than 0.");
}

function validateInteger(params, id) {
  if (params[id]) {
    var val = jQuery(params[id]).val();
    if (String(val).match(/^\s*\d*\s*$/)) {
      return new ValidationResponse(id, true);
    }
  }
  return new ValidationResponse(id, false, "This field must be a number.");
}

function validateStripeCardNumber(params, id) {
  if (params[id]) {
    var val = jQuery(params[id]).val();
    if (Stripe.validateCardNumber(val)) {
      return new ValidationResponse(id, true);
    }
  }
  return new ValidationResponse(id, false, "Invalid credit card number.");
}

function highlightCardNumber(el) {
  var val = el.val();
  if (Stripe.validateCardNumber(val)) {
    el.removeClass('invalid-cc');
    el.addClass('valid-cc');
    logit('Valid');
    return;
  }
  el.removeClass('valid-cc');
  el.addClass('invalid-cc');
}

function validateStripeExpiry(params, id) {
  var exp_month, exp_year;
  if (params[id]) {
    exp_month = jQuery('#card-expiry-month').val();
    exp_year = jQuery('#card-expiry-year').val();
    if (Stripe.validateExpiry(exp_month, exp_year)) {
      return new ValidationResponse(id, true);
    }
  }
  return new ValidationResponse(id, false, "Card is expired.");
}

function validateStripeCvc(params, id) {
  var val;
  if (params[id]) {
    val = jQuery(params[id]).val();
    if (Stripe.validateCVC(val)) {
      return new ValidationResponse(id, true);
    }
  }
  return new ValidationResponse(id, false, "Invalid CVC.");
}

function validateStripeCardType(params, id) {
  var val;
  if (params[id]) {
    val = jQuery(params[id]).val();
    if (Stripe.cardType(val) !== 'Unknown') {
      return new ValidationResponse(id, true);
    }
  }
  return new ValidationResponse(id, false, "Unknown credit card type.");
}

/*jslint unparam: true*/
function validateOnlyOne(params, id) {

  var links, foundChecked, i;

  links = jQuery('div.onlyOne');

  foundChecked = 0;
  for (i = 0; i < links.length; i++) {

    if (jQuery(links[i]).find("input:checkbox:checked").length > 0) {
      foundChecked++;
    }
  }

  if (foundChecked === 0) {
    return new ValidationResponse(id, false, "You must select an option.");
  }

  if (foundChecked > 1) {
    return new ValidationResponse(id, false,
        "You must select only one option.");
  }

  return new ValidationResponse(id, true);
}
/*jslint unparam: false*/

/*jslint unparam: true*/
function validatePassword(params, id) {

  var password1, password2, passwordFields;
  passwordFields = jQuery(".password input:password");

  if (passwordFields.length !== 2) {
    return new ValidationResponse(id, false,
        "Unable to find two password fields");
  }

  password1 = passwordFields[0];
  password2 = passwordFields[1];

  if (password1.value !== password2.value) {
    return new ValidationResponse(id, false, "Passwords do not match");
  }

  return new ValidationResponse(id, true);
}
/*jslint unparam: false*/

/*jslint unparam: true*/
function validateCreditcard(params, id) {
  return new ValidationResponse(id, true);
}
/*jslint unparam: false*/

function hideHelp() {
  jQuery('.hidden').css('left', '-999em');
}

function displayHelp(el) {
  hideHelp();
  jQuery(el).find('.hidden').css('left', 'auto');
}

function s4() {
  return Math.floor((1 + Math.random()) * 0x10000)
             .toString(16)
             .substring(1);
}

function guid() {
  return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
         s4() + '-' + s4() + s4() + s4();
}

function addHashEditRow(hashedit) {
  var id, tbody, rows, index, template, tr;
  id = guid();

  tbody = jQuery(hashedit).find("table tbody");
  rows = tbody.find("tr");
  index = rows.size();

  template = jQuery(rows[0]).clone();

  tr = jQuery('<tr id="' + id + '"></tr>');
  template.find('td').each(
    function () {
      var cell;
      cell = jQuery(this);
      cell.find('input').each(
        function () {
          var input, name;
          input = jQuery(this);
          name = String(input.attr("name")).replace(/_\d+$/,
            "_" + index);
          input.attr("name", name);
          input.val('');
        }
      );
      cell.find('select').each(
        function () {
          var input, name;
          input = jQuery(this);
          name = String(input.attr("name")).replace(/_\d+$/,
            "_" + index);
          input.attr("name", name);
          input.val('');
        }
      );
      cell.find('a').each(
        function () {
          var a = jQuery(this);
          if (a.attr("title") === "Delete Row") {
            a.attr("onclick", "deleteHashEditRow('" + hashedit + "', '" + id + "'); return false;");
          }
        }
      );
      tr.append("<td>" + jQuery(this).html() + "</td>");
    }
  );
  tbody.append(tr);
}

function deleteHashEditRow(hashedit, uid) {
  var el;
  if (!uid) {
    el = jQuery(hashedit).find("table tbody tr:first-child");
  } else {
    el = jQuery('#' + uid);
  }

  if (jQuery(hashedit).find("table tbody tr").size() > 1) {
    el.detach();
  } else {
    el.find("td input").each(function () {
      jQuery(this).val("");
    });
  }
}

function overrideSelect(id, value, overrideLabel) {
  var select, i;
  select = document.getElementById(id);
  for (i = 0; i < select.options.length; i++) {
    if (select.options[i].value === value) {
      select.options[i].selected = true;
      return;
    }
  }
  if (isEmpty(value) && select.options.length > 0) {
    select.options[0].selected = true;
    return;
  }
  if (isEmpty(overrideLabel)) {
    overrideLabel = "Override";
  }
  select.options[select.options.length] = new Option(value + " (" + overrideLabel + ")", value);
  select.options[select.options.length - 1].selected = true;
}

function editSelect(id, label, promptStart, overrideLabel, validator) {
  if (isEmpty(promptStart)) {
    promptStart = "Override with new ";
  }
  if (isEmpty(validator)) {
    validator = function (id, val, overrideLabel) {
      if (isNotEmpty(val)) {
        overrideSelect(id, val, overrideLabel);
      }
      return false;
    };
  }
  var value = prompt(promptStart + label + ": ");
  /*jshint validthis:true */
  return validator.call(this, id, value, overrideLabel);
}

function hideFilterPrompt() {
  var div = jQuery('#__filter');
  if (div) {
    div.detach();
  }
  return false;
}

function filterPrompt(el, tableId, filterColumn) {
  hideFilterPrompt();

  var div = document.createElement("DIV");
  div.setAttribute("class", "filter");
  div.setAttribute("id", "__filter");
  div.innerHTML = "<div class=\"titlebar\"><a onclick=\"return hideFilterPrompt();\" href=\"#\"><img align=\"top\" alt=\"close window\" src=\"" + imagepath+ "close.gif\" /></a></div><div class=\"filterText\"><input type=\"text\" id=\"__filter_input\" /><a href=\"#\" onclick=\"return filterAjaxTable('" +
      tableId +
      "', '" +
      filterColumn +
      "',jQuery('#__filter_input').val())\"><img align=\"top\" alt=\"close window\" src=\"" +
      imagepath + 
      "find.gif\" /></a></div></div>";
  el.parentNode.appendChild(div);
  return false;
}

function clearTable(tableId) {
  var i, table, body, rows;

  table = jQuery(tableId);
  body = table.find("tbody").first();
  rows = body.find("tr");
  for (i = rows.length; i > 0; i--) {
    jQuery(rows[i - 1]).detach();
  }
}

function redrawPager(tableId) {
  var i, table, currentPage, totalPages, pagerCell, pages, start, max, output;
  table = jQuery(tableId);
  currentPage = Number(table.attr("currentPage"));
  totalPages = Number(table.attr("totalPages"));
  logit(table.attr("currentPage"));

  pagerCell = table.find(".pager").first();

  // first we determine the page numbers we need
  pages = [];
  start = currentPage - 2;
  if (totalPages <= 5) {
    start = 1;
  }
  for (i = start; i < currentPage; i++) {
    // so count down to zero, add all pages greater than 0 and greater than
    // currentPAge - 2
    if (i > 0) {
      pages[pages.length] = i;
    }
  }
  for (i = currentPage; i <= parseInt(currentPage, 10) + 2; i++) {
    if (i <= totalPages) {
      pages[pages.length] = i;
    }
  }

  if (totalPages <= 5) {
    for (i = pages.length + 1; i <= totalPages; i++) {
      pages[pages.length] = i;
    }
  } else if (pages.length !== 5 && (pages[0] === 1 || pages[0] === 2)) {
    for (i = pages.length + 1; i <= 5; i++) {
      if (i <= totalPages) {
        pages[pages.length] = i;
      }
    }
  } else if (pages.length !== 5 && 
      (pages[pages.length - 1] === totalPages || pages[pages.length - 1] === (totalPages - 1))) {
    max = pages[0];
    start = max - (5 - pages.length);
    for (i = start; i < max; i++) {
      pages[pages.length] = i;
    }
  }
  pages.sort(function (a, b) {
    return a - b;
  });
  output = "";
  if (table.attr("filter") && table.attr("filter").length > 0) {
    output += "<div class=\"clearFilter\"><a href=\"#\" onclick=\"jQuery('" +
        tableId + "').attr('filter', ''); return pageAjaxTable('" + tableId +
        "', '" + currentPage +
        "');\"><img align=\"top\" alt=\"start\" title=\"start\" src=\"" +
        imagepath + "/delete.gif\" />Clear Filter</a></div>";
  }
  if (currentPage > 0 && currentPage !== 1) {
    output += "<a href=\"#\" onclick=\"return pageAjaxTable('" + tableId +
        "', '1');\" class=\"pager_first\">&laquo; First</a>";
    output += "<a href=\"#\" onclick=\"return pageAjaxTable('" + tableId +
        "', '" + (parseInt(currentPage, 10) - 1) +
        "');\" class=\"pager_prev\">&laquo; Prev</a>";
  }
  for (i = 0; i < pages.length; i++) {
    output += "<a " +
        (pages[i] === currentPage ? "class=\"pager_selected\""
            : "class=\"pager_unselected\"") +
        " href=\"#\" onclick=\"return pageAjaxTable('" + tableId + "', '" +
        pages[i] + "');\">" + pages[i] + "</a>";
  }
  if (totalPages > 0 && currentPage !== totalPages) {
    output += "<a href=\"#\" onclick=\"return pageAjaxTable('" + tableId +
        "', '" + (parseInt(currentPage, 10) + 1) + 
        "');\" class=\"pager_next\">Next &raquo;</a>";
    output += "<a href=\"#\" onclick=\"return pageAjaxTable('" + tableId +
        "', '" + totalPages + "');\" class=\"pager_last\">Last &raquo;</a>";
  }
  pagerCell.html(output);
}

function _sortAjaxTable(response) {
  var i, j, data, table, body, row, tr, td;
  try {
    data = response.data;
    table = jQuery(response.tableId);
    clearTable(response.tableId);

    body = table.find("tbody").first();
    for (i = 0; i < data.length; i++) {
      row = data[i];
      tr = document.createElement("TR");
      tr.setAttribute("class", "transRow" + (i % 2 ? 1 : 2));
      for (j = 0; j < row.length; j++) {
        td = document.createElement("TD");
        td.setAttribute("class", 'bCol' + j +
            (row[j].match(/<img/) ? ' centeredImg' : ''));
        td.innerHTML = row[j];
        tr.appendChild(td);
      }
      body.append(tr);
    }
    table.attr("totalPages", response.totalPages);
    redrawPager(response.tableId);
    hideFilterPrompt();
  } catch (e) {
    logit(response);
    alertError(e);
  }
}

function filterAjaxTable(tableId, filterColumn, filter) {
  var table, service, sort, direction, form, paramString, params;
  hideFilterPrompt();

  table = jQuery(tableId);
  service = table.attr("service");
  sort = table.attr("sort");
  direction = table.attr("direction");

  form = '#' + table.attr("form");
  paramString = jQuery(form).serialize();
  params = parseQueryString(paramString);

  params.action = "populateTable";
  params.tableId = tableId;
  params.currentPage = 1;
  params.filterColumn = filterColumn;
  params.filter = filter;
  if (!sort) {
    sort = 1;
  }
  if (!direction) {
    direction = "asc";
  }
  params.sort = sort;
  params.direction = direction;

  table.attr("currentPage", 1);
  table.attr("sort", sort);
  table.attr("direction", direction);
  table.attr("filterColumn", filterColumn);
  table.attr("filter", filter);

  /*jslint unparam: true*/
  jQuery.ajax({
    data : params,
    type : "POST",
    url : basepath + service,
    success : function (data, textStatus, XMLHttpRequest) {
      logit(XMLHttpRequest);
      _sortAjaxTable(data);
    },
    error : function (XMLHttpRequest, textStatus, errorThrown) {
      alertError(errorThrown);
    }
  });
  /*jslint unparam: false*/
  return false;
}

function pageAjaxTable(tableId, page) {
  var table, service, sort, direction, filter, filterColumn, form, paramString, params;

  table = jQuery(tableId);
  service = table.attr("service");
  sort = table.attr("sort");
  direction = table.attr("direction");
  filter = table.attr("filter");
  filterColumn = table.attr("filterColumn");

  form = '#' + table.attr("form");
  paramString = jQuery(form).serialize();
  params = parseQueryString(paramString);
  params.action = "populateTable";
  params.tableId = tableId;
  params.currentPage = page;
  params.filter = filter;
  params.filterColumn = filterColumn;
  if (!sort) {
    sort = 1;
  }
  if (!direction) {
    direction = "asc";
  }
  params.sort = sort;
  params.direction = direction;

  table.attr("currentPage", page);
  table.attr("sort", sort);
  table.attr("direction", direction);

  /*jslint unparam: true*/
  jQuery.ajax({
    data : params,
    type : "POST",
    url : basepath + service,
    success : function (data, textStatus, XMLHttpRequest) {
      logit(XMLHttpRequest);
      _sortAjaxTable(data);
    },
    error : function (XMLHttpRequest, textStatus, errorThrown) {
      alertError(errorThrown);
    }
  });
  /*jslint unparam: false*/
  return false;
}

function sortAjaxTable(tableId, sort) {
  var table, service, oldSort, direction, filter, filterColumn, form, paramString, params;

  table = jQuery(tableId);
  service = table.attr("service");
  oldSort = table.attr("sort");
  direction = table.attr("direction");
  filter = table.attr("filter");
  filterColumn = table.attr("filterColumn");
  form = '#' + table.attr("form");
  paramString = jQuery(form).serialize();
  params = parseQueryString(paramString);

  params.action = "populateTable";
  params.tableId = tableId;
  params.currentPage = table.attr("currentPage");
  params.sort = sort;
  params.filter = filter;
  params.filterColumn = filterColumn;
  if (sort === oldSort) {
    direction = (direction === "asc" ? "desc" : "asc");
  } else {
    direction = "asc";
  }
  params.direction = direction;

  table.attr("sort", sort);
  table.attr("direction", direction);

  /*jslint unparam: true*/
  jQuery.ajax({
    data : params,
    type : "POST",
    url : basepath + service,
    success : function (data, textStatus, XMLHttpRequest) {
      logit(XMLHttpRequest);
      _sortAjaxTable(data);
    },
    error : function (XMLHttpRequest, textStatus, errorThrown) {
      alertError(errorThrown);
    }
  });
  /*jslint unparam: false*/
  return false;
}

/*jslint unparam: true*/
function toggleToggle(name, formField, value) {
  return true;
}
/*jslint unparam: false*/

function prefix(value, prefixChar) {
  if (!prefixChar) {
    prefixChar = "0";
  }
  value = String(value);
  if (value.length === 1) {
    return prefixChar + value;
  }
  return value;
}

function formatMoney(value) {
  return Number(value).toFixed(2);
}

function updateColor(elName) {
  var i, il, el, currentValue, newCurrentValue, red, green, blue, textColor;
  el = jQuery(elName);
  currentValue = jQuery.trim(el.val());
  if (currentValue.length === 4) {
    newCurrentValue = "#";
    for (i = 1, il = currentValue.length; i < il; i++) {
      newCurrentValue += prefix(currentValue.charAt(i), currentValue
          .charAt(i));
    }
    currentValue = newCurrentValue;
    el.val(currentValue);
  }
  if (currentValue.length === 7) {
    el.css("background-color", currentValue);
    red = parseInt(currentValue.substring(1, 3), 16);
    green = parseInt(currentValue.substring(3, 5), 16);
    blue = parseInt(currentValue.substring(5, 7), 16);

    textColor = "#" + prefix((255 - Number(red)).toString(16)) +
        prefix((255 - Number(green)).toString(16)) +
        prefix((255 - Number(blue)).toString(16));
    el.css("color", textColor);
  }
}

function openModal(modalId, title, callback, width, height) {
  if (!width) {
    width = 'auto';
  }
  if (!height) {
    height = 'auto';
  }
  jQuery('#' + modalId).dialog({
    modal: true,
    title: title,
    height: height,
    width: width,
    autoOpen: true,
    open: function () {
      if (callback) {
        callback(jQuery('#' + modalId).find('form').attr('id'));
      }
    }
  });
  return false;
}

function openSigninModal(modalId, title) {
  promptMessage("Sign In", $('#' + modalId + "-template").html(), null, null, {}, true);
  return false;
}

function showHelp(className, index) {
  jQuery('.' + className).hide();
  if (jQuery('#' + className + "-" + index).length) {
    jQuery('#' + className + "-" + index).show();
  }
}
