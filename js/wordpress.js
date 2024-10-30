/*jslint strict: true, indent: 2, plusplus: true, browser: true, nomen: true */
function updateProduction() {
  jQuery('#productions').off("change.productions");
  jQuery('#productions').on("change.productions", updateProduction);
  
  var selected = jQuery('#productions option:selected');
  
  var output = "<p>";
  var short_codes = ["tickets", "location", "additional_info", "minical", "tickets", "people"];
  for(var i = 0; i < short_codes.length; i++) {
    output += "[bpt_" + short_codes[i] + " url=" + selected.val() + "]<br />";
  }
  output += "</p>";
  jQuery('#production-shortcode').html(output);
  jQuery('#production-name').html(selected.html());
}
