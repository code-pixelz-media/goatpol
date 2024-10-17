(function ($) {
  var ground_rules_2_popup = `
    <div id="ground-rules-2" title="Ground Rules" style="display: none;">
      <div class="has-text-align-center wp-block-image is-style-no-vertical-margin" style="font-size: 26px;text-align:center">
          <h2>Ground Rules</h2>
      </div>
      <div class="wp-block-image is-style-no-vertical-margin">
          <?php echo pol_get_random_goat(); ?>
      </div>
      <div>
          <p class="p1"><span class="s1"><strong>A polity is a group of people who respect and support one another as equals</strong>. Difference is celebrated and engaged. We relinquish power over others to find the potentials of collectivity. The GOAT PoL is a polity comprised of reading and writing. <strong>It is a polity</strong> of literature and <strong>not a market</strong> for literature.</span></p>
          <p class="p1"><span class="s1">To maintain good relationships in the polity, <strong>The GOAT PoL has ground rules</strong>. Please read them and take them seriously. Without agreement to these ground rules, we cannot be a polity. The GOAT PoL ground rules are:</span></p>
          <p class="p1"><span class="s1">(1) Everyone is honest. No lying. No hiding the actions you take. No false claims about yourself or your work.</span></p>
          <p class="p1"><span class="s1">(2) One-commission-at-a-time. Every writer is limited to working on one-commission-at-a-time. If you have several pseudonyms (made-up names you write under) don't pursue commissions for more than one-at-a-time.</span></p>
          <p class="p1"><span class="s1">(3) One account only: do not open multiple accounts at The GOAT PoL. Use one account only for all your writing and reading.</span></p>
          <p class="p1"><span class="s1">(4) In the work place we cultivate and maintain mutual respect: ask, don't demand; disagree with respect, don't belittle or dismiss; listen and try to understand, even if you disagree.</span></p>
          <p class="p1"><span class="s1">(5) No stealing—if you submit work written by someone else and claim it is your own work, we can't work with you.</span></p>

          <strong>If you’re still interested, please click “close” below</strong>, hello one, confirming that you understand and want to continue working in The GOAT PoL.
          By clicking “close” I confirm that I understand and I want to continue participating in The GOAT PoL.
      </div>
      <div style="text-align:center;">
          <button type="button" class="close-modal ground-rules-2-close cbtn-ground-rules">Close</button>
      </div>
    </div>
  `;
  jQuery(document).ready(function () {
    // jQuery(window).mousewheel(function (turn, delta) {
    //   if (delta == 1) {
    //     console.log("up");
    //   } // going up
    //   else {
    //     console.log("down");
    //   } // going down
    //   // all kinds of code

    //   // return false;
    // });
    if ($("body").hasClass("page-template-template-user-profile-details")) {
      var lastScrollTop = 0;
      $(window).scroll(function (event) {
        if ($(window).width() < 900) {
          var st = $(this).scrollTop();
          if (st > lastScrollTop) {
            $(".registration-floating-menu").css("top", "0");
          } else {
            $(".registration-floating-menu").css("top", "20vh");
          }
          lastScrollTop = st;
        }
      });
    }
    if (window.location.href.indexOf("add-place") > -1) {
      $("#menu-popup-content").show();
      $(".cpm-popup-overlay").show();
      $("body").addClass("ground-rules-popup-open");
    }

    $(".menu-pop-up").on("click", function () {
      $("#menu-popup-content").toggle();
      $(".cpm-popup-overlay").show();
      $("body").addClass("ground-rules-popup-open");
    });

    $(".menu-popup-close").on("click", function () {
      $("#menu-popup-content").hide();
      $(".cpm-popup-overlay").hide();
      $("body").removeClass("ground-rules-popup-open");
    });

    // for story author popup
    $(".cpm-story-autor").on("click", function () {
      $("#story-popup-content").toggle();
    });

    $(".logo-popup-close").click(function () {
      $("#story-popup-content").hide();
    });

    $(".cpm-autor-list-wrapper").on("click", function () {
      $(".cpm-autor-option-wrapper").toggle();
    });

    // scroll make on click close button
    $("#button-menu").click(function () {
      $(".sidebar-content").animate(
        {
          scrollTop: 0,
        },
        1
      );
    });

    var scrollLimit = 600;

    //var values;
    $("select.story-ajax-filter").on("change", function (ev) {
      var value = jQuery(this).val();
      var scrollLimit_val = jQuery("#infinite-list").attr("data-scrollLimit");
      scrollLimit = parseInt(scrollLimit_val);

      jQuery("#infinite-list").attr("data-selected", value);

      $.ajax({
        method: "POST",
        url: pol_ajax_filters.ajaxurl,
        data: {
          action: "wp_select_ajax_filter",
          value: value,
        },
        success: function (response) {
          jQuery(".pol-ajelms").html(response);
          var run_ajax = jQuery("#select-infinite-list").attr("data-runajax");
          var maxpage = jQuery("#select-infinite-list").attr("data-maxpage");
          jQuery("#infinite-list").attr("data-runajax", run_ajax);
          jQuery("#infinite-list").attr("data-maxpage", maxpage);
          jQuery("#infinite-list").attr("data-paged", 1);
          jQuery("#infinite-list").attr("data-scrollLimit", 600);
          jQuery("#element-lazyload").html("Loading...");
        },
      });
    });

    var i = 1;
    $("#ajaxlazyload").on("scroll", function () {
      scrollPosition = $(this).scrollTop();

      var scrolHeigt = $("#infinite-list")[0].scrollHeight;

      if (scrollPosition >= scrollLimit) {
        TopScroll = window.pageYOffset || document.documentElement.scrollTop;
        (LeftScroll =
          window.pageXOffset || document.documentElement.scrollLeft),
          // loadContent(limit, offset); // loadContent method in which ajax() call is defined
          ajaxLoadPosts();
        // Update values on each scroll
        var scrolHeigt = $("#infinite-list")[0].scrollHeight;

        if (i == 1) {
          scrollLimit = scrollLimit + 700;
        } else {
          scrollLimit = scrolHeigt - 250;
        }
        i++;
        // offset = offset + 3;
      }
    });

    function ajaxLoadPosts() {
      var value = jQuery("#infinite-list").attr("data-selected");
      var post_id = jQuery(".infinite-post-id").attr("data-id");
      var paged = jQuery("#infinite-list").attr("data-paged");
      var maxpage = jQuery("#infinite-list").attr("data-maxpage");
      if (parseInt(maxpage) >= parseInt(paged)) {
        $.ajax({
          method: "POST",
          url: pol_ajax_filters.ajaxurl,
          data: {
            action: "wp_infinitepaginate",
            page: parseInt(paged) + 1,
            value: value,
          },
          success: function (response) {
            jQuery(".pol-ajelms").append(response);
            jQuery("#infinite-list").attr("data-paged", parseInt(paged) + 1);
          },
        });
      }
      if (parseInt(maxpage) == parseInt(paged)) {
        jQuery("#element-lazyload").html("no more posts");
      }
    }
  });

  jQuery(document).ready(function () {
    jQuery(".four-col li:nth-child(4n)").after(
      "<div style='clear:both;'></div>"
    );
  });

  if (window.location.href.indexOf("new_place=true") > -1) {
    var val = jQuery("body .af-field--payment-status").attr("data-name");
    if (val === "_payment_status") {
      jQuery("body .af-field--payment-status").addClass("acf-hidden");
    }
  }

  //================submenu modal / arpan===========

  // jQuery(function () {
  //   jQuery(".getPassport-modal").on("click", function () {
  //     jQuery("#getPassport-options-2").modal({
  //       fadeDuration: 200,
  //     });
  //     return false;
  //   });
  // });
  // jQuery(function () {
  //   jQuery(".getPassport-modal-2").on("click", function () {
  //     jQuery("#getPassport-options-2").modal({
  //       fadeDuration: 200,
  //     });
  //     return false;
  //   });
  // });

  // register show password
  jQuery(document).ready(function () {
    jQuery(".toggle-password").on("click", function () {
      var passwordInput = jQuery(".password");
      var icon = jQuery(this);

      // Toggle password visibility
      var type =
        passwordInput.attr("type") === "password" ? "text" : "password";
      passwordInput.attr("type", type);

      // Toggle icon based on password visibility
      if (type === "password") {
        icon.removeClass("fa-eye").addClass("fa-eye-slash");
      } else {
        icon.removeClass("fa-eye-slash").addClass("fa-eye");
      }
    });
  });

  //==============user profile / vileroze==============
  $(document).ready(function () {
    if ($("body").hasClass("page-template-template-user-profile-details")) {
      //to add a placeholder in the search area of select2js
      var Defaults = $.fn.select2.amd.require("select2/defaults");

      $.extend(Defaults.defaults, {
        searchInputPlaceholder: "",
      });

      var SearchDropdown = $.fn.select2.amd.require("select2/dropdown/search");
      var _renderSearchDropdown = SearchDropdown.prototype.render;

      SearchDropdown.prototype.render = function (decorated) {
        // invoke parent method
        var $rendered = _renderSearchDropdown.apply(
          this,
          Array.prototype.slice.apply(arguments)
        );
        this.$search.attr(
          "placeholder",
          this.options.get("searchInputPlaceholder")
        );
        return $rendered;
      };

      //adds search functionality to user dropdown
      $("#user_list").select2({
        multiple: false,
        searchInputPlaceholder: "Search user",
      });

      $("#seeking_commission_list").select2({
        multiple: false,
        searchInputPlaceholder: "Search user",
      });

      $("#assign_commission").on("click", function () {
        //get curr_user_role
        let currUserRole = $("#curr-user-role").val();

        // Get selected values from checkboxes
        var selectedCommissions = $('input[type="checkbox"]:checked');
        var selectedCommissionIds = [];
        selectedCommissions.each(function () {
          selectedCommissionIds.push($(this).data("id"));
        });

        // Get selected value from Select2 dropdown
        var selectedUser = $("#user_list").val();
        console.log("🚀 ~ selectedUser:", selectedUser);
        var userSeekingCommission = $("#seeking_commission_list").val();
        console.log("🚀 ~ userSeekingCommission:", userSeekingCommission);

        var finalSelectedUser = "";

        if (selectedUser != "default") {
          finalSelectedUser = selectedUser;
        }

        if (userSeekingCommission != "default") {
          finalSelectedUser = userSeekingCommission;
        }

        var assignMessageContainer = document.getElementById(
          "assignMessage-container"
        );

        //show a message for 5 seconds if no commission is chosen
        if (selectedCommissionIds.length == 0) {
          assignMessageContainer.innerText = "Select at least one commission";
          assignMessageContainer.style.display = "block";
          setTimeout(function () {
            assignMessageContainer.style.display = "none";
          }, 5000);
          return;
        }

        //show a message for 5 seconds if no user is chosen
        // if(typeof userSeekingCommission != 'undefined'){
        if (selectedUser == "default" && userSeekingCommission == "default") {
          assignMessageContainer.innerText = "Select a user";
          assignMessageContainer.style.display = "block";
          setTimeout(function () {
            // Hide the message
            assignMessageContainer.style.display = "none";
          }, 5000);
          return;
        }
        // }else{
        //   if (selectedUser == "default") {
        //     assignMessageContainer.innerText = "Select a user";
        //     assignMessageContainer.style.display = "block";
        //     setTimeout(function () {
        //       // Hide the message
        //       assignMessageContainer.style.display = "none";
        //     }, 5000);
        //     return;
        //   }
        // }

        //show message if user selects user from both options
        // if(typeof userSeekingCommission != 'undefined'){
        if (selectedUser != "default" && userSeekingCommission != "default") {
          assignMessageContainer.innerText = "Please select only one user";
          assignMessageContainer.style.display = "block";
          setTimeout(function () {
            // Hide the message
            assignMessageContainer.style.display = "none";
          }, 5000);
          return;
        }
        // }else{
        //   if (selectedUser != "default") {
        //     assignMessageContainer.innerText = "Please select only one user";
        //     assignMessageContainer.style.display = "block";
        //     setTimeout(function () {
        //       // Hide the message
        //       assignMessageContainer.style.display = "none";
        //     }, 5000);
        //     return;
        //   }
        // }

        //show commission successfully transferred message
        var assignMessageContainerSuccess = document.getElementById(
          "assignMessage-container-success"
        );
        assignMessageContainerSuccess.innerText =
          "Commision transferred successfully";
        assignMessageContainerSuccess.style.display = "block";
        setTimeout(function () {
          // Hide the message
          assignMessageContainerSuccess.style.display = "none";
        }, 5000);

        var selectedUser = $("#user_list").val();

        //remove the <tr> element of the transferred commission from the table of the current user.F
        $.each(selectedCommissionIds, function (index, id) {
          var $row = $("tr").has(
            'input[type="checkbox"][data-id="' + id + '"]'
          );

          $row.remove();
        });

        // let commission_ajax_action = currUserRole === "admin" ? "pol_transfer_commission_from_admin_to_rae" : "pol_transfer_commission_from_user_to_user";
        let commission_ajax_action = "pol_transfer_commission";
        let transfer_action =
          currUserRole === "admin" ? "admin_to_rae" : "user_to_user";

        let finalUser =
          selectedUser != "default" ? selectedUser : userSeekingCommission;

        if (userSeekingCommission != "default") {
          jQuery('#seeking_commission_list option[value="627"]').remove();
        }

        console.log("final user:::" + finalUser);

        //transfer commission
        $.ajax({
          method: "POST",
          url: pol_ajax_filters.ajaxurl,
          data: {
            action: commission_ajax_action,
            commissions: selectedCommissionIds,
            receiver: finalUser,
            currUserRole: currUserRole,
            transferAction: transfer_action,
          },
          success: function (response) { },
        });

        //remove user from the 'seeking commissions list'
        $.ajax({
          method: "POST",
          url: pol_ajax_filters.ajaxurl,
          data: {
            action: "pol_remove_user_from_seeking_commissions_list",
            user_id: finalUser,
          },
          success: function (response) { },
        });
      });
    }
    if ($(".write_genres").length > 0) {
      $(".write_genres").select2({
        multiple: true,
        searchInputPlaceholder: "---Select---",
      });
      $(".write_for").select2({
        multiple: true,
        searchInputPlaceholder: "---Select---",
      });
    }

    if ($(".write_read_languages").length > 0) {
      $(document).ready(function () {
        let tagsArr = [
          ".write_read_languages",
          ".grew_up_languages",
          ".fav_authors",
          ".fav_subject_to_write_about",
        ];
        tagsArr.forEach((el) => {
          $(el).select2({
            tags: true,
            multiple: true,
            language: {
              noResults: function () {
                return "";
              },
            },
          });
          let allOptions = [];
          $(el + " option").each(function () {
            allOptions.push($(this).val());
          });
          $(el).val(allOptions).trigger("change");
        });

        //for favorite goat pol stories
        $(".fav_goatpol_stories").select2({
          multiple: true,
        });
      });
    }
  });

  // contributors page single author profile tab
  // jQuery(document).ready(function () {
  //   jQuery(".author-tab").on("click", function () {
  //     // Remove the "active" class from all tabs
  //     jQuery(".author-tab").removeClass("active");

  //     // Add the "active" class to the clicked tab
  //     jQuery(this).addClass("active");

  //     // Hide all tab content
  //     jQuery(".author-tab-content").removeClass("active");

  //     // Show the clicked tab content
  //     jQuery("#" + jQuery(this).data("tab")).addClass("active");
  //   });
  // });

  //open the new ground rules popup when the user inserts commission.
  jQuery(document).ready(function ($) {

    $(".open-ground-rules-2").css('pointer-events', 'none');
    $(document).on('keyup', '#popup-commission', function () {
      // Remove any white spaces in the input value
      var inputVal = $(this).val().replace(/\s/g, '');
      $(this).val(inputVal);

      if (inputVal.length > 0) {
        $(".open-ground-rules-2").css('pointer-events', 'auto');
      } else {
        $(".open-ground-rules-2").css('pointer-events', 'none');
      }
    });

    $(document).on('keypress','#popup-commission', function(e) {
      if (e.which === 13) {
        e.preventDefault();
        e.stopPropagation();
        $('.open-ground-rules-2').click();
      }
    });

    if ($("#cpm-publish-user-information").length > 0) {
      return;
    }

    //click the 'save' button to open popup
    $(".open-ground-rules-2").on("click", function (e) {
      jQuery("body").append(ground_rules_2_popup);
      $("#ground-rules-2").modal({
        fadeDuration: 200,
        closeOnEscape: false, // Prevents closing on pressing escape
        clickClose: false
      });
    });

    // close the modal ground-rules-2
    $(".ground-rules-2-close").on("click", function () {
      $("#ground-rules-2").modal("close");
      $(".jquery-modal").css("display", "none");
      $("body").css("overflow", "auto");
      $("#ground-rules-2").remove();
    });

    //submit the form when clicked on 'close' of popup
    jQuery(document).on("click", "#ground-rules-2 .cbtn-ground-rules", function (e) {
      $(".use-commission").click(); //submit the form
    });
  });

  //open the new ground rules popup on 'save' in the '/register' page
  jQuery(document).ready(function ($) {
    if ($("#cpm-publish-user-information").length == 0) {
      return;
    }

    //click the 'save' button to open popup
    $(".cpm_create_user_open_ground_rules").on("click", function (e) {
      jQuery("body").append(ground_rules_2_popup);
      $("#ground-rules-2").modal({
        fadeDuration: 200,
      });
    });

    // close the modal ground-rules-2
    $(".ground-rules-2-close").on("click", function () {
      $("#ground-rules-2").modal("close");
      $(".jquery-modal").css("display", "none");
      $("body").css("overflow", "auto");
      $("#ground-rules-2").remove();
    });

    //submit the form when clicked on 'close' of popup
    jQuery(document).on("click","#ground-rules-2 .cbtn-ground-rules", function (e) {
      e.stopPropagation();
      // // $(".cpm_create_user").css("pointer-events", "none");
      // // $(".cpm_create_user").html("Saving...");

      // var fd = new FormData();
      // var file = jQuery(document).find(".profile_picture");
      // var profile_picture = file[0].files[0];

      // if (typeof profile_picture !== "undefined") {
      //   //if user uploaded a profile picture

      //   fd.append("file", profile_picture);
      //   fd.append("action", "pol_upload_profile_picture");

      //   jQuery.ajax({
      //     type: "POST",
      //     url: pol_ajax_filters.ajaxurl,
      //     data: fd,
      //     contentType: false,
      //     processData: false,
      //     success: function (response) {
      //       // console.log("🚀 ~ response:", response);
      //       $(".uploaded_picture_id").val(response.data);
      //       $(".cpm_create_user").click();
      //     },
      //   });
      // } else {
      //   // $(".cpm_create_user").click();
      // }
      $(".cpm_create_user").click();
    });
  });

  //open the new ground rules popup on 'save' in the '/registration' page
  jQuery(document).ready(function ($) {
    if ($("#cpm-update-user-information").length == 0) {
      return;
    }

    //no popup
    if ($(".cpm_update_user_open_ground_rules").length == 0) {
      // jQuery(".cpm_update_user_info").on("click", function (e) {
      //   $(".cpm_update_user_info").css("pointer-events", "none");
      //   $(".cpm_update_user_info").html("Saving...");
      //   let firstName = $(".first_name").val();
      //   let lastName = $(".last_name").val();
      //   let formErr = $("#form-err");
      //   if (firstName == "" || lastName == "") {
      //     formErr.html(
      //       'Please fill out all the fields marked as "<span class="required-field">*</span>"'
      //     );
      //     window.location.href = "#form-err";
      //     e.preventDefault();
      //   } else {
      //     // var fd = new FormData();
      //     // var file = jQuery(document).find(".profile_picture");
      //     // var profile_picture = file[0].files[0];
      //     // if (typeof profile_picture !== "undefined") {
      //     //   //if user uploaded a profile picture
      //     //   fd.append("file", profile_picture);
      //     //   //fd.append("action", "pol_update_profile_picture");
      //     //   jQuery.ajax({
      //     //     type: "POST",
      //     //     url: pol_ajax_filters.ajaxurl,
      //     //     data: fd,
      //     //     contentType: false,
      //     //     processData: false,
      //     //     success: function (response) {
      //     //       $(".cpm_update_user_info").css("pointer-events", "auto");
      //     //       $(".cpm_update_user_info").html("Save");
      //     //       $("#cpm-update-user-information").submit();
      //     //     },
      //     //   });
      //     // } else {
      //     //   $("#cpm-update-user-information").submit();
      //     // }
      //   }
      // });
    } else {
      //click the 'save' button to open popup
      $(".cpm_update_user_open_ground_rules").on("click", function (e) {
        jQuery("body").append(ground_rules_2_popup);
        $("#ground-rules-2").modal({
          fadeDuration: 200,
          closeOnEscape: false,
          clickClose: false
        });
      });

      // close the modal ground-rules-2
      $(".ground-rules-2-close").on("click", function (e) {
        $("#ground-rules-2").modal("close");
        $(".jquery-modal").css("display", "none");
        $("body").css("overflow", "auto");
        $("#ground-rules-2").remove();
      });

      //submit the form when clicked on 'close' of popup
      jQuery("#ground-rules-2 .cbtn-ground-rules").on("click", function (e) {
        // e.stopPropagation();

        // var fd = new FormData();
        // var file = jQuery(document).find(".profile_picture");
        // var profile_picture = file[0].files[0];

        // if (typeof profile_picture !== "undefined") {
        //if user uploaded a profile picture

        // fd.append("file", profile_picture);
        // fd.append("action", "pol_update_profile_picture");

        // jQuery.ajax({
        //   type: "POST",
        //   url: pol_ajax_filters.ajaxurl,
        //   data: fd,
        //   contentType: false,
        //   processData: false,
        //   success: function (response) {
        //     // console.log("🚀 ~ response:", response);
        //     $(".profile_picture").val(response.data);

        //     let firstName = $('.first_name').val();
        //     let lastName = $('.last_name').val();
        //     let formErr = $('#form-err');

        //     if (firstName == "" || lastName == "") {
        //       formErr.html('Please fill out all the fields marked as "<span class="required-field">*</span>"');
        //       window.location.href = "#form-err"
        //     } else {
        //       $(".cpm_update_user_info").click();
        //     }
        //   },
        // });
        // } else {
        let firstName = $(".first_name").val();
        let lastName = $(".last_name").val();
        let formErr = $("#form-err");

        if (firstName == "" || lastName == "") {
          formErr.html(
            'Please fill out all the fields marked as "<span class="required-field">*</span>"'
          );
          window.location.href = "#form-err";
        } else {
          $(".cpm_update_user_info").click();
        }
        $(".cpm_update_user_info").click();
        // }
      });
    }
  });

  // // update profile picture --- profile page
  // jQuery(document).ready(function ($) {
  //   jQuery(".cpm_update_user_info").on("click", function (e) {
  //     $(".cpm_update_user_info").css("pointer-events", "none");
  //     $(".cpm_update_user_info").html("Saving...");

  //     var fd = new FormData();
  //     var file = jQuery(document).find(".profile_picture");
  //     var profile_picture = file[0].files[0];

  //     if (typeof profile_picture !== "undefined") {
  //       //if user uploaded a profile picture

  //       fd.append("file", profile_picture);
  //       fd.append("action", "pol_update_profile_picture");

  //       jQuery.ajax({
  //         type: "POST",
  //         url: pol_ajax_filters.ajaxurl,
  //         data: fd,
  //         contentType: false,
  //         processData: false,
  //         success: function (response) {
  //           $(".cpm_update_user_info").css("pointer-events", "auto");
  //           $(".cpm_update_user_info").html("Save");

  //           $("#cpm-update-user-information").submit();
  //         },
  //       });
  //     } else {
  //       $("#cpm-update-user-information").submit();
  //     }
  //   });
  // });

  //custom direct story upload
  jQuery(document).on("submit", "#direct-story-upload", function (e) {
    e.preventDefault();

    $("#story-upload").css("pointer-events", "none");
    $("#story-upload").val("Uploading...");

    //story file upload
    var fd = new FormData();
    var file = jQuery(document).find("#story-file");
    var story_title = jQuery(document).find("#story-title").val();
    var story_desc = jQuery(document).find("#story-desc").val();
    console.log(file);
    var individual_file = file[0].files[0];
    fd.append("file", individual_file);
    fd.append("action", "pol_upload_story_file");
    fd.append("story_title", story_title);
    fd.append("story_desc", story_desc);

    jQuery.ajax({
      type: "POST",
      url: pol_ajax_filters.ajaxurl,
      data: fd,
      contentType: false,
      processData: false,
      success: function (response) {
        jQuery("#story-upload").prop("disabled", true);
        jQuery("#story-upload").addClass("hover-not-allowed");
        console.log(response);

        //story thumbnail upload
        if ($("#story-thumbnail").get(0).files.length !== 0) {
          let newStoryFileID = response.data;

          let fd = new FormData();
          let file = jQuery(document).find("#story-thumbnail");
          console.log(file);
          let individual_file = file[0].files[0];
          fd.append("file", individual_file);
          fd.append("action", "pol_upload_story_thumbnail");
          fd.append("story_file_id", newStoryFileID);

          jQuery.ajax({
            type: "POST",
            url: pol_ajax_filters.ajaxurl,
            data: fd,
            contentType: false,
            processData: false,
            success: function (response) {
              $("#story-upload").css("pointer-events", "auto");
              $("#story-upload").val("Upload");
              location.reload();
              // let url = window.location.href;
              // if (url.indexOf('?') > -1){
              //   url += '&direct=1'
              // } else {
              //   url += '?direct=1'
              // }
              // window.location.href = url;
            },
          });
        } else {
          $("#story-upload").css("pointer-events", "auto");
          $("#story-upload").val("Upload");
          location.reload();
          // let url = window.location.href;
          // if (url.indexOf('?') > -1){
          //   url += '&direct=1'
          // } else {
          //   url += '?direct=1'
          // }
          // window.location.href = url;
        }
      },
    });
  });

  //==============author.php page / vileroze==============

  //show / hide workshop form
  jQuery(document).ready(function ($) {
    $(document).on("click", ".show-hide-workshop-form", function (e) {
      if ($(this).attr("data-visibility") == "hidden") {
        $("#add-new-workshop").show();
        $(this).attr("data-visibility", "visible");
        $(this).html("Hide");
      } else {
        $("#add-new-workshop").hide();
        $(this).attr("data-visibility", "hidden");
        $(this).html("Add new workshop");
      }
    });
  });
  // show hide story add form
  jQuery(document).ready(function ($) {
    $(document).on("click", ".show-hide-add-story-form", function (e) {
      if ($(this).attr("data-visibility") == "hidden") {
        $("#direct-story-upload").show();
        $(this).attr("data-visibility", "visible");
        $(this).html("Hide");
      } else {
        $("#direct-story-upload").hide();
        $(this).attr("data-visibility", "hidden");
        $(this).html("Add new story");
      }
    });
  });

  //add workshops to authors
  jQuery(document).ready(function ($) {
    $("#workshop-form").on("click", function (e) {
      e.preventDefault();

      var title = $("#workshop-title").val();
      var details = $("#workshop-details").val();
      var link = $("#workshop-link").val();
      var currAuthorID = $("#author-page-curr-author").val();

      if (title.length == 0 || link.length == 0) {
        return;
      }

      $.ajax({
        method: "POST",
        url: pol_ajax_filters.ajaxurl,
        data: {
          action: "pol_add_user_workshop",
          title: title,
          details: details,
          link: link,
          author_id: currAuthorID,
        },
        success: function (response) {
          $(".workshop-msg").addClass("workshop-msg-add");
          $(".workshop-msg").removeClass("workshop-msg-del");

          $(".workshop-msg").html("New workshop added !!");

          setTimeout(function () {
            $(".workshop-msg").html("");
          }, 5000);

          var newRow =
            `
              <div class="workshop-table">
                <div class="workshop-card-title">` +
            title +
            `</div>
                <div class="workshop-card-detail-content">` +
            details +
            `</div>
                <div><a href="` +
            link +
            `" target="_blank">View Workshop</a></div>
                <div><button class="delete-workshop" data-title="` +
            title +
            `">Delete</button></div>
              </div>`;
          $(".all-workshops").append(newRow);
        },
      });
    });
  });

  //remove workshops from authors
  jQuery(document).ready(function ($) {
    $(document).on("click", ".delete-workshop", function () {
      var workshopTitle = $(this).data("title");
      var currAuthorID = $("#author-page-curr-author").val();

      $(this).closest(".workshop-table").remove();
      $(".workshop-msg").removeClass("workshop-msg-add");
      $(".workshop-msg").addClass("workshop-msg-del");
      $(".workshop-msg").html("Workshop removed !!");

      setTimeout(function () {
        $(".workshop-msg").html("");
      }, 5000);

      $.ajax({
        url: pol_ajax_filters.ajaxurl,
        type: "POST",
        data: {
          action: "pol_remove_user_workshop",
          author_id: currAuthorID,
          workshop_title: workshopTitle,
        },
        success: function (response) {
          // Remove the row from the table
          $(this).closest(".workshop-table").remove();
        },
      });
    });
  });

  //open the commissino popup to try again
  jQuery(document).ready(function ($) {
    $(document).on("click", "#commission-try-again", function () {
      jQuery(".gp-menu-suyw").click();
    });
  });

  //check if email exists
  jQuery(document).ready(function ($) {
    $(document).on("submit", "#check-email-form", function (e) {
      e.preventDefault();

      let email = $("#popup-email").val();

      if (!email) {
        return;
      }

      $.ajax({
        url: pol_ajax_filters.ajaxurl,
        type: "POST",
        data: {
          action: "pol_check_email_exists",
          email: email,
        },
        success: function (response) {
          if (response.data === "not_exists") {
            window.location.href = "/register?email=" + email;
          } else {
            window.location.href = "/wp-login.php?email=" + email;
          }
        },
      });
    });
  });

  //request for commission
  jQuery(document).ready(function ($) {
    $(document).on("click", ".request-commission", function () {
      $(".request-commission-msg").html("Checking...");

      $.ajax({
        url: pol_ajax_filters.ajaxurl,
        type: "POST",
        data: {
          action: "pol_request_commission",
        },
        success: function (response) {
          let msg = response.data;

          if (msg == "rae_notified" || msg == "rae_notification_sent") {
            $('#commission-request-confirmation-popup').show();
            $(".request-commission-msg").html(
              `The RAEs have been notified. Thank you for your patience. 
                The RAEs will all look at your Contributor’s Page to see what 
                kinds of writing and reading interest you. While waiting, 
                please be active as a writer and reader, sharing new work on your CP, 
                reading the work of others, and (if you can and would enjoy it) taking 
                part in the free workshops. Thank you again for participating and for 
                your patience. The GOAT PoL RAEs`
            );

            if (msg == "rae_notified") {
              //send email to user stating the successfull request of commission
              $.ajax({
                url: pol_ajax_filters.ajaxurl,
                type: "POST",
                data: {
                  action: "cpm_send_email_to_user_seeking_commission",
                },
                success: function (response) { },
              });
            }
          } else if (msg == "has_commission") {
            $(".request-commission-msg").html(
              "You already have commission, please check your profile !!"
            );
          }

          // setTimeout(function () {
          //   $(".request-commission-msg").html("");
          // }, 5000);
        },
      });
    });

    $(document).on("click", "#commission-request-confirmation-popup .close-modal", function () {
      $('#commission-request-confirmation-popup').hide();
    });
  });

  //like uploaded stories
  jQuery(document).ready(function ($) {
    $(document).on("click", ".like-uploaded-story", function () {
      var $this = $(this); // Store $(this) in a variable
      let action = "";

      if ($this.find("i").hasClass("fa-solid")) {
        $this.find("i").removeClass("fa-solid");
        $this.find("i").addClass("fa-regular");
        $this
          .closest("button")
          .find(".tooltiptext")
          .html("Choose this story as one of your favorites");
        action = "unlike";
      } else {
        $this.find("i").addClass("fa-solid");
        $this.find("i").removeClass("fa-regular");
        $this
          .closest("button")
          .find(".tooltiptext")
          .html("Remove this story from your favorites");
        action = "like";
      }

      $this.css("pointer-events", "none");

      let storyID = $this.attr("data-story-id");
      console.log("🚀 ~ storyID:", storyID);
      let authorID = $this.attr("data-author-id");
      let storyType = $this.attr("data-type");
      console.log("🚀 ~ authorID:", authorID);

      $.ajax({
        url: pol_ajax_filters.ajaxurl,
        type: "POST",
        data: {
          action: "pol_like_uploaded_stories",
          storyID: storyID,
        },
        success: function (response) {
          $this.css("pointer-events", "auto"); // Use the stored variable here

          if (action == "like") {
            //send email to author about the liked story
            $.ajax({
              url: pol_ajax_filters.ajaxurl,
              type: "POST",
              data: {
                action: "cpm_send_story_liked_email",
                authorID: authorID,
                storyID: storyID,
                storyType: storyType,
              },
            });
          }
        },
      });
    });

    // //for adding tooltip to star icon to favorite users
    // if($('.like-uploaded-story').length > 0){
    //   $('.like-uploaded-story').tooltip();
    // }
  });

  //show and hide contributors in the profile page under "available commissions"
  jQuery(document).ready(function ($) {
    if ($("#user_list").length == 0) {
      return;
    }

    $(".all-contributors").show();
    $(".all-contributors-seeking-commmission").hide();

    $(document).on("click", ".show-all-contributors", function () {
      $(".show-all-contributors").addClass("active");
      $(".show-contributors-seeking-commmission").removeClass("active");
      $(".show-contributors-transfer-commmission").removeClass("active");

      $(".all-contributors-seeking-commmission").hide();
      $(".all-contributors-transfer-commmission").hide();
      $("#assign_commission").show();

      $(".all-contributors").show();
    });

    $(document).on(
      "click",
      ".show-contributors-seeking-commmission",
      function () {
        $(".show-contributors-seeking-commmission").addClass("active");
        $(".show-all-contributors").removeClass("active");
        $(".show-contributors-transfer-commmission").removeClass("active");

        $(".all-contributors").hide();
        $(".all-contributors-transfer-commmission").hide();
        $("#assign_commission").show();

        $(".all-contributors-seeking-commmission").show();
      }
    );

    $(document).on(
      "click",
      ".show-contributors-transfer-commmission",
      function () {
        $(".show-all-contributors").removeClass("active");
        $(".show-contributors-seeking-commmission").removeClass("active");
        $(".show-contributors-transfer-commmission").addClass("active");

        $(".all-contributors").hide();
        $(".all-contributors-seeking-commmission").hide();
        $("#assign_commission").hide();

        $(".all-contributors-transfer-commmission").show();
      }
    );
  });

  //make dropdowns muliselect in "/contributors" page filters
  jQuery(document).ready(function ($) {
    var url = new URL(window.location.href);
    var checkParams = false;

    if ($("#filter-contributors").length == 0) {
      return;
    }

    let select2Arr = ["#genre", "#write_for"];
    select2Arr.forEach((el) => {
      let placeholder = "select";
      if (el == "#genre") {
        placeholder = checkParams ? '' : "fiction, novel ";
      } else if (el == "#write_for") {
        placeholder = checkParams ? '' : "for children, for school";
      }
      $(el).select2({
        multiple: true,
        placeholder: placeholder,
        // searchInputPlaceholder: "--select as many as you wish--"
      });
    });

    $(".select-nation-search").select2({
      multiple: true,
      placeholder: checkParams ? '' : "Search countries",
      searchInputPlaceholder: "Search countries",
    });
    // let curr_loc = [];
    // $(".select-nation-search" + " option").each(function () {
    //   grewUpLangs.push($(this).val());
    // });
    // $(".select-nation-search").val(curr_loc).trigger("change");

    //create select2js for search fields
    let multipleSearchFields = {};
    multipleSearchFields["#grew_up_languages"] = checkParams ? '' : "english, german";
    multipleSearchFields["#fav_author"] = checkParams ? '' : "John Green, Rowling";
    multipleSearchFields["#subject"] = checkParams ? '' : "crime, community";
    // multipleSearchFields['#curr_loc'] = 'netherlands, queens';

    for (var field in multipleSearchFields) {
      $(field).select2({
        tags: true,
        multiple: true,
        placeholder: multipleSearchFields[field],
        language: {
          noResults: function () {
            return "";
          },
        },
      });
      let grewUpLangs = [];
      $(field + " option").each(function () {
        grewUpLangs.push($(this).val());
      });
      $(field).val(grewUpLangs).trigger("change");
    }

    //hide the palce holder in the contributors filter page when in focus
    jQuery(
      "#filter-contributors input.select2-search__field, #filter-contributors input"
    ).each(function () {
      var $this = jQuery(this);

      $this.data("holder", $this.attr("placeholder"));

      jQuery(document).on("focusin", $this, function () {
        $this.attr("placeholder", "");
      });

      jQuery(document).on("focusout", $this, function () {
        $this.attr("placeholder", $this.data("holder"));
      });
    });
  });

  //choose city nation in profile edit and register pages
  jQuery(document).ready(function ($) {
    if ($(".fill-nation-now").length == 0) {
      return;
    }

    $(".fill-nation-now").select2({
      multiple: false,
      searchInputPlaceholder: checkParams ? '' : "Search countries",
      theme: "bootstrap fill-nation-now-select2 writer-nation",
    });

    $(".fill-nation-grewup").select2({
      multiple: false,
      searchInputPlaceholder: "Search countries",
      theme: "bootstrap fill-nation-grewup-select2 writer-nation",
    });

    // $(".fill-nation-now-select2").hide();
    // $(".fill-nation-grewup-select2").hide();

    // $(document).on("change", "#fill-city-nation-now", function () {
    //   let val = $(this).val();

    //   if (val == "city") {
    //     $(".fill-nation-now-select2").hide();
    //     $(".fill-city-now").show();
    //   } else if (val == "nation") {
    //     $(".fill-nation-now-select2").show();
    //     $(".fill-city-now").hide();
    //   }
    // });

    // $(document).on("change", "#fill-city-nation-grewup", function () {
    //   let val = $(this).val();

    //   if (val == "city") {
    //     $(".fill-nation-grewup-select2").hide();
    //     $(".fill-city-grewup").show();
    //   } else if (val == "nation") {
    //     $(".fill-nation-grewup-select2").show();
    //     $(".fill-city-grewup").hide();
    //   }
    // });
  });

  // //nations select dropdown in '/contributors' page
  // jQuery(document).ready(function ($) {
  //   if ($(".select-nation-search").length == 0) {
  //     return;
  //   }

  // });

  //crud on uploaded story
  jQuery(document).ready(function ($) {
    $(document).on("click", ".edit-modal-close", function () {
      $(this).closest(".modal").hide();
    });

    //edit uploaded story
    $(document).on("click", ".edit-uploaded-story-btn", function () {
      let modal = $("#edit-uploaded-story-modal");
      modal.toggle();

      let $this = $(this);
      let storyId = $this.attr("data-story-id");
      let storyTitle = $this.attr("data-story-title");
      let storyDesc = $this.attr("data-story-desc");
      let storyThumbnailId = $this.attr("data-thumbnail-id");
      let storyThumbnailSrc = $this.attr("data-thumbnail-src");

      modal.find(".story-id").val(storyId);
      modal.find(".story-title").val(storyTitle);
      modal.find(".story-desc").val(storyDesc);
      modal.find(".story-thumbnail-id").val(storyThumbnailId);
      modal.find(".story-thumbnail-src").attr("src", storyThumbnailSrc);
    });

    //upload the edited story
    // $(document).on("click", ".edit-uploaded-story-btn", function () {
    //   let modal = $('#edit-uploaded-story-modal');
    // });

    //delete uploaded story
    $(document).on("click", ".del-uploaded-story-btn", function () {
      let $this = $(this);
      let storyId = $this.attr("data-story-id");

      $this.css("pointer-events", "none");
      $this.html("DELETING...");

      $.ajax({
        url: pol_ajax_filters.ajaxurl,
        type: "POST",
        data: {
          action: "pol_delete_uploaded_story",
          storyID: storyId,
        },
        success: function (response) {
          $this.closest("li").remove();
        },
      });
    });
  });
  // close modal edit story
  $(".edit-modal-close").on("click", function () {
    // console.log("chalena");
    $("#edit-uploaded-story-modal").hide();
    $("body").css("overflow", "auto");
  });

  $(".open-ground-rules-2").on("click", function (e) {
    jQuery("body").append(ground_rules_2_popup);
    $("#ground-rules-2").modal({
      fadeDuration: 200,
      closeOnEscape: false,
      clickClose: false
    });
  });

  //story popup in the author page
  jQuery(document).ready(function ($) {
    $(".open-story-popup-author-page").on("click", function (e) {
      if ($(this).hasClass('dont-show-popup')) {
        return;
      }
      e.preventDefault();
      let popup = $("#author-page-story-popup");

      let story_heading = $(this).attr("data-title");
      let story_id = $(this).attr("data-story-id");
      let story_image = $(this).closest("li").find("img").attr("src");

      popup.show();
      popup.find("h2").html(story_heading); //populate story heading
      popup.find("img").attr("src", story_image); //populate image
      popup
        .find(".story-content")
        .html(
          '<i class="fa fa-spinner fa-spin gp-list-view-page-spinner"></i>'
        );

      //ajax to get story content
      $.ajax({
        url: pol_ajax_filters.ajaxurl,
        type: "POST",
        data: {
          action: "pol_get_story_content",
          storyID: story_id,
        },
        success: function (response) {
          // console.log("🚀 ~ response:", response.data);
          popup.find(".story-content").html(response.data);
        },
      });
    });

    $("#author-page-story-popup .close-modal").on("click", function (e) {
      e.preventDefault();
      $("#author-page-story-popup").hide();
    });
  });

  //open cofirm story delete modal
  jQuery(document).ready(function ($) {
    $(".remove-uploaded-story").on("click", function (e) {
      let storyID = $(this).attr("data-story-id");
      $("#confirm-delete-story-modal")
        .find(".confirm")
        .attr("data-story-id", storyID);
      $("#confirm-delete-story-modal").show();
    });

    //confirm removal of story
    $("#confirm-delete-story-modal .confirm").on("click", function (e) {
      $("#confirm-delete-story-modal").hide();
      let storyID = $(this).attr("data-story-id");

      //remove the story card
      jQuery('li .remove-uploaded-story[data-story-id="' + storyID + '"]')
        .parent("li")
        .remove();

      $.ajax({
        url: pol_ajax_filters.ajaxurl,
        type: "POST",
        data: {
          action: "pol_remove_story_from_user_list",
          storyID: storyID,
        },
        success: function (response) { },
      });
    });

    //cancel removal of story
    $("#confirm-delete-story-modal .cancel").on("click", function (e) {
      $("#confirm-delete-story-modal").hide();
    });
  });

  //single page php
  $(document).on("click", ".signup_for_workshop", function (e) {
    $(".site-msg").html(
      "<i class='fa-solid fa-check'></i>Congratulations, you are now signed up for this workshop !!"
    );
    $("#add-to-signup").hide();
  });

  $(document).ready(function () {
    // Move the .workshop-page-header before the ul element inside the .header-navigation
    $(".workshop-page-header").insertAfter(
      ".page-template-template-workshops #site-header"
    );
    $(".single-workshop-new-header").insertBefore(
      ".single-workshop .header-inner"
    );
  });

  //remove participant from workshop
  $(document).ready(function () {
    $(".remove-participant").on("click", function (e) {
      let userId = $(this).attr("data-uid");
      let workshopId = $(this).attr("data-workshop-id");
      let $this = $(this);

      $.ajax({
        url: pol_ajax_filters.ajaxurl,
        type: "POST",
        data: {
          action: "pol_remove_participants",
          userId: userId,
          workshopId: workshopId,
        },
        success: function (response) {
          $this.closest("tr").remove();
          window.location.reload();
        },
      });
    });
  });

  // $(document).ready(function () {
  //   // Check if the URL contains the fragment identifier for the target section
  //   if (window.location.pathname === "/workshops") {
  //     // Get the target element by ID
  //     var targetElement = $("#workshop-navigate");

  //     // Check if the target element exists
  //     if (targetElement.length) {
  //       // Scroll 20 pixels below the target section with the ID workshop-navigate
  //       $("html, body").animate(
  //         {
  //           scrollTop: targetElement.offset().top + 10,
  //         },
  //         1000
  //       );
  //     } else {
  //       console.error("Element with ID 'workshop-navigate' not found.");
  //     }
  //   }
  // });

  //scroll to the list of workshop participants in single workshop page
  $(document).ready(function () {

    if ($('.single-workshop-participants').length == 1) {

      // Parse the query string of the current URL
      var urlParams = new URLSearchParams(window.location.search);

      // Check if the "view" parameter exists and its value is "user_list"
      if (urlParams.has('view') && urlParams.get('view') === 'user_list') {
        // Calculate the position of the element
        var targetElement = $('.single-workshop-participants');
        var targetPosition = targetElement.offset().top - $(window).height() + targetElement.outerHeight();

        // Animate the scroll position
        $('html, body').animate({
          scrollTop: targetPosition
        }, 1000); // 1000 is the duration of the animation in milliseconds
      }

    }
  });

  $(document).ready(function () {
    //   $(this).addClass("active");
    $(".registration-floating-menu a li").click(function (e) {
      // Remove 'active' class from all anchors
      $(".registration-floating-menu a li").removeClass("active");

      // Add 'active' class to the clicked anchor
      $(this).addClass("active");

      var targetId = $(this).find("a").attr("href");

      if (targetId) {
        $("html, body").animate(
          {
            scrollTop: $(targetId).offset().top,
          },
          1000
        );
        e.preventDefault();
      }
    });
    // });/worksho
  });

  $(document).ready(function () {
    // Add click event listener to the button with class 'update-user-info'
    $(".update-user-info").on("click", function () {
      // Trigger a click on the button with class 'cpm_update_user_info'
      $(".cpm_update_user_info").click();
    });
    $(".save-user-info").on("click", function () {
      // Trigger a click on the button with class 'cpm_update_user_info'
      $(".acf-form-submit .acf-button").click();
    });
  });

  $(document).ready(function () {
    $(".transfer-single-commission").on("click", function (e) {
      let text =
        "Do you really want to transfer a new commission to this writer?";
      if (confirm(text) == true) {
        let $this = $(this);
        $this.css("pointer-events", "none");
        $this.html("Transferring...");
        let raeID = $this.attr("data-rae-id");
        let authorID = $this.attr("data-author-id");

        $.ajax({
          url: pol_ajax_filters.ajaxurl,
          type: "POST",
          data: {
            action: "pol_transfer_single_commission",
            raeID: raeID,
            authorID: authorID,
          },
          success: function (response) {
            $this.css("pointer-events", "auto");
            $this.html("Transfer a commission to this writer");
            $(".transfer-single-commission").hide();
            if (response.data == "transfered") {
              $(".transfer-single-commission-msg").html(
                "Commission transferred successfully!!"
              );
            } else {
              $(".transfer-single-commission-msg").html(
                "You dont have any available commissions!!"
              );
            }
            $(".transfer-single-commission-msg").show();
            setTimeout(() => {
              $(".transfer-single-commission-msg").hide();
            }, 3000);
            window.location.reload();
          },
        });
      }
    });

    // $(document).on("click", "#revoke-button", function () {
    //   let revoke_confirm_text = "Are you sure you want to revoke this commission? Writer will no longer have access to this commission and it will be returned to your RAE account as “available.”";
    //   if (confirm(revoke_confirm_text) == true) {
    //     var rae_id = $(this).data("org_rae");
    //     var owner_id = $(this).data("current_owner");
    //     var comission_id = $(this).data("comission_id");
    //     $.ajax({
    //       url: pol_ajax_filters.ajaxurl,
    //       type: "POST",
    //       data: {
    //         action: "pol_revoke_commission",
    //         rae_id: rae_id,
    //         owner_id: owner_id,
    //         comission_id: comission_id,
    //       },
    //       success: function (response) {
    //         window.location.reload();
    //       },
    //     });
    //   }
    // });

    $(document).on("click", "#revoke-button", function () {
      // Create the confirmation text
      let revoke_confirm_text = "Are you sure you want to revoke this commission? Writer will no longer have access to this commission and it will be returned to your RAE account as “available.”";

      // Store the necessary data
      var rae_id = $(this).data("org_rae");
      var owner_id = $(this).data("current_owner");
      var commission_id = $(this).data("comission_id");

      // Create a jQuery UI dialog box
      $("<div>" + revoke_confirm_text + "</div>").dialog({
        title: "Revoke Commission",
        modal: true,
        width: 800,
        buttons: {
          "YES, revoke commission": function () {
            // If confirmed, make the AJAX request
            $.ajax({
              url: pol_ajax_filters.ajaxurl,
              type: "POST",
              data: {
                action: "pol_revoke_commission",
                rae_id: rae_id,
                owner_id: owner_id,
                comission_id: commission_id,
              },
              success: function (response) {
                window.location.reload();
              },
            });
            $(this).dialog("close");
          },
          "NO, please leave commission as it is": function () {
            // Close the dialog if user cancels
            $(this).dialog("close");
          },
        }
      });
    });

    let sortOrder = 'asc';
    $(document).on("click", ".sort_comission", function () {
      let rows = $(".profile-table tbody tr").not(":first"); // Exclude the header row

      rows.sort(function (a, b) {
        let A = $(a).find("td:nth-child(5)").text().toUpperCase(); // Get the text from the Status column (4th column)
        let B = $(b).find("td:nth-child(5)").text().toUpperCase();

        if (sortOrder === 'asc') {
          return A > B ? 1 : (A < B ? -1 : 0);
        } else {
          return A < B ? 1 : (A > B ? -1 : 0);
        }
      });

      // Append sorted rows back into the table body
      $(".profile-table tbody").append(rows);

      // Toggle the sort order for next click
      sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';

      // $("#comission_table").toggleClass("asc");

      // if ($("#comission_table").hasClass("asc")) {
      //   // Corrected part
      //   let sort_by = "DESC";
      //   let current_author_id = $(this).data('current_author_id');
      //   console.log("sort by", sort_by);
      //   $.ajax({
      //     url: pol_ajax_filters.ajaxurl,
      //     type: "POST",
      //     data: {
      //       action: "list_user_commisions",
      //       sort_by: sort_by,
      //       current_author_id: current_author_id,
      //     },
      //     success: function (response) {
      //       $('#comission_table').html(response.data);
      //       console.log(response);
      //     },
      //   });
      // } else {
      //   let sort_by = "ASC";
      //   let current_author_id = $(this).data('current_author_id');
      //   console.log("sort by", sort_by);
      //   $.ajax({
      //     url: pol_ajax_filters.ajaxurl,
      //     type: "POST",
      //     data: {
      //       action: "list_user_commisions",
      //       sort_by: sort_by,
      //       current_author_id: current_author_id,
      //     },
      //     success: function (response) {
      //       $('#comission_table').html(response.data);
      //       console.log(response);
      //     },
      //   });
      // }
    });


    $(document).on('click', '[class^="log-"]', function () {
      // console.log('Log link clicked!');  // Check if click event works

      // Get the specific class that starts with 'log-' and doesn't include 'commission-log-open'
      var classes = $(this).attr('class').split(' ');  // Split classes into an array
      var logClass = '';

      // Find the class that starts with 'log-' but not 'commission-log-open'
      $.each(classes, function (index, value) {
        if (value.startsWith('log-') && value !== 'commission-log-open') {
          logClass = value.replace('log-', 'log-popup-');
        }
      });

      // console.log('Popup class: ', logClass);  // Check if class replacement works

      // Select the corresponding popup content based on the generated class
      var logContent = $('.' + logClass).html();
      // console.log('Log content: ', logContent);  // Check if the content exists

      // Ensure logContent is not undefined before creating a dialog
      if (logContent !== undefined && logContent.trim() !== '') {
        $('<div class="log-contents"></div>').html('<ul>' + logContent + '</ul>').dialog({
          title: 'Log History',
          modal: true,
          width: 600,
          height: 400,
          close: function () {
            $(this).dialog('destroy');
          }
        });
      } else {
        console.log('No log content found.');
      }
    });



  });

})(jQuery);

// workshop page scroll

// jQuery(document).ready(function () {
//   // Check if the URL contains a fragment identifier
//   if (window.location.hash) {
//     // Get the target element by its ID
//     var targetElement = jQuery(window.location.hash);

//     // Check if the target element exists
//     if (targetElement.length) {
//       // Calculate the offset for scrolling
//       var offset = targetElement.offset().top - 500;

//       // Scroll to the target element with the offset
//       jQuery("html, body").animate(
//         {
//           scrollTop: offset,
//         },
//         "slow"
//       ); // You can adjust the speed as needed
//     }
//   }
// });

jQuery(document).ready(function () {
  // Check if the URL contains '/workshops'
  if (window.location.href.includes("/workshops")) {
    // Define the target element
    var targetElement = jQuery("#workshop-navigate");

    // Check if the target element exists
    if (targetElement.length) {
      // Calculate the offset for scrolling (500 pixels above the target element)
      var offset = targetElement.offset().top - 500;

      // Scroll to the target element with the offset
      jQuery("html, body").animate(
        {
          scrollTop: offset,
        },
        "slow"
      );
    }
  }
});

// Save scroll position before page reload
jQuery(window).on('beforeunload', function () {
  localStorage.setItem('scrollPosition', jQuery(window).scrollTop());
});

// Scroll to the saved position after page reload
jQuery(document).ready(function () {
  var scrollPosition = localStorage.getItem('scrollPosition');
  if (scrollPosition) {
    jQuery(window).scrollTop(scrollPosition);
    localStorage.removeItem('scrollPosition'); // Clean up after using
  }
});

