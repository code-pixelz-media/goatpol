jQuery(document).ready(function () {
  jQuery("#org_rae").select2({
    placeholder: "Select an option",
    allowClear: true,
    minimumResultsForSearch: 10, // Allows search when there are more than 10 options
  });
  jQuery("#current_owner").select2({
    placeholder: "Select an option",
    allowClear: true,
    minimumResultsForSearch: 10, // Allows search when there are more than 10 options
  });

  jQuery("td.author.column-author").each(function () {
    var commentDate = jQuery(this).find("p.commentaire-date");
    commentDate.appendTo(jQuery(this).find("strong"));
  });

  // Open the popup
  jQuery("#add-commission").click(function () {
    jQuery("#popup").show();
    jQuery(".popup-overlay").show(); // show the overlay
    jQuery("body").addClass("popupshown");
  });
  // Close the popup
  jQuery("#popup-close-button").click(function () {
    jQuery("#popup").hide();
    jQuery("body").removeClass("popupshown");
    jQuery(".popup-overlay").hide(); // hide the overlay
  });
});
