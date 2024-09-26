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
    var post_id = jQuery(this).data("post_id");
    var actionType = jQuery(this).data("action");
      jQuery.ajax({
        url: ajaxurl, // WordPress AJAX URL
        type: "POST",
        data: {
          action: "get_commission_details", // WordPress action hook
          commission_id: commissionId,
          action_type: actionType,
          post_id: post_id,
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

  jQuery(document).on('click','.commission_delete_action',function(){
    var commissionId = jQuery(this).data("id");
    let text = "Do you want to delete this commission ?";
    if (confirm(text) == true) {
      jQuery.ajax({
        url: ajaxurl, // WordPress AJAX URL
        type: "POST",
        data: {
          action: "delete_commission_details", // WordPress action hook
          commission_id: commissionId,
        },
        success: function (response) {
          console.log(response);
          if (response.success) {
            jQuery(".after_action_message").html(response.data.message);
          } else {
            console.log("Error: " + response.data.message);
          }
          window.location.reload(); 
        },
        error: function () {
          console.log("Error deleting commission details.");
        },
      });
    }
  });

  // Close the popup
  jQuery("#commission-popup-close-button").click(function () {
    jQuery("#commission_popup").hide();
    jQuery("body").removeClass("commission-popupshown");
    jQuery(".popup-overlay").hide(); // hide the overlay
  });
});
