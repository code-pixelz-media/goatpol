jQuery(document).ready(function () {
  jQuery("#org_rae").select2({
    placeholder: "Select an option",
    allowClear: true,
    minimumResultsForSearch: 10, // Allows search when there are more than 10 options
  });
  jQuery("#edit_org_rae").select2({
    placeholder: "Select an option",
    allowClear: true,
    minimumResultsForSearch: 10, // Allows search when there are more than 10 options
  });
  jQuery("#edit_current_owner").select2({
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
    var commissionCode = jQuery(this).data("commission");
    var post_id = jQuery(this).data("post_id");
    var actionType = jQuery(this).data("action");

    jQuery(".edit_commission_key").val(commissionCode);
    jQuery(".edit_commission_id").val(commissionId);

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "get_commission_details",
        commission_id: commissionId,
        action_type: actionType,
        post_id: post_id,
      },
      success: function (response) {
        if (response.success) {
          var org_rae = response.data[0];
          var current_owner = response.data[1];

          //select the org_rae according to the drop down value in the #edit_org_rae section
          jQuery("#edit_org_rae").val(org_rae).trigger("change");
          jQuery("#edit_current_owner").val(current_owner).trigger("change");

          jQuery(".after_action_message").text(response.data[2]);
        } else {
          console.log("Error: " + response.data.message);
        }
      },
      error: function () {
        console.log("Error fetching commission details.");
      },
    });
  });

  jQuery(document).on("click", ".commission_delete_action", function () {
    var commissionId = jQuery(this).data("id");
    var post_id = jQuery(this).data("post_id");
    let text = "Do you want to delete this commission ?";
    Confirm(
      "Delete Commission",
      text,
      "Yes, I want to delete this commission and any story or content associated with it.",
      "No, please do not delete this commission and its associated content.",
      commissionId,
      post_id
    );
  });

  // Close the popup
  jQuery("#commission-popup-close-button").click(function () {
    jQuery("#commission_popup").hide();
    jQuery("body").removeClass("commission-popupshown");
    jQuery(".popup-overlay").hide(); // hide the overlay
  });
});

function Confirm(title, msg, $true, $false, commissionId,post_id) {
  var $content =
    "<div class='dialog-ovelay'>" +
    "<div class='dialog'><header>" +
    " <h3> " +
    title +
    " </h3> " +
    "<i class='fa fa-close'></i>" +
    "</header>" +
    "<div class='dialog-msg'>" +
    " <p> " +
    msg +
    " </p> " +
    "</div>" +
    "<footer>" +
    "<div class='controls'>" +
    " <button class='button button-primary doAction'>" +
    $true +
    "</button> " +
    " <button class='button button-danger cancelAction'>" +
    $false +
    "</button> " +
    "</div>" +
    "</footer>" +
    "</div>" +
    "</div>";
  $("body").prepend($content);
  $(".doAction").click(function () {
    jQuery.ajax({
      url: ajaxurl, // WordPress AJAX URL
      type: "POST",
      data: {
        action: "delete_commission_details", // WordPress action hook
        commission_id: commissionId,
        post_id: post_id,
      },
      success: function (response) {
        alert("Commission deleted.");
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
  });
  $(".cancelAction, .fa-close").click(function () {
    $(this)
      .parents(".dialog-ovelay")
      .fadeOut(500, function () {
        $(this).remove();
      });
  });
}
