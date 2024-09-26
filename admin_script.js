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

  //edit /delete seciton
  // Open the popup
  jQuery(document).on("click", ".commission_action", function (e) {
    e.preventDefault();
    jQuery("#commission_popup").show();
    jQuery(".popup-overlay").show(); // show the overlay
    jQuery("body").addClass("commission-popupshown");

    // Get the data attributes for the ID and action type
    var commissionId = jQuery(this).data("id");
    var actionType = jQuery(this).data("action");
console.log(commissionId,actionType);
    jQuery.ajax({
      url: ajaxurl, // WordPress AJAX URL
      type: "POST",
      data: {
        action: "get_commission_details", // WordPress action hook
        commission_id: commissionId,
        action_type: actionType,
      },
      success: function (response) {
        if (response.success) {
          jQuery(".commission-form-wrapper").html(response.data);
        } else {
          console.log("Error: " + response.data.message);
        }
      },
      error: function () {
        console.log("Error fetching commission details.");
      },
    });
  });
  // Close the popup
  jQuery("#commission-popup-close-button").click(function () {
    jQuery("#commission_popup").hide();
    jQuery("body").removeClass("commission-popupshown");
    jQuery(".popup-overlay").hide(); // hide the overlay
  });
});
