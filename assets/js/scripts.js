/**
 * File scripts.js
 */

/* ------------------------------------------------------------------------------ /*
/*  HELPERS
/* ------------------------------------------------------------------------------ */

/**
 * Output AJAX errors
 */
function polAjaxErrors(jqXHR, exception) {
  var message = "";
  if (jqXHR.status === 0) {
    message = "Not connect.n Verify Network.";
  } else if (jqXHR.status == 404) {
    message = "Requested page not found. [404]";
  } else if (jqXHR.status == 500) {
    message = "Internal Server Error [500].";
  } else if (exception === "parsererror") {
    message = "Requested JSON parse failed.";
  } else if (exception === "timeout") {
    message = "Time out error.";
  } else if (exception === "abort") {
    message = "Ajax request aborted.";
  } else {
    message = "Uncaught Error.n" + jqXHR.responseText;
  }
  console.log("AJAX ERROR:" + message);
}

/**
 * Toggle an attribute
 */
function polToggleAttribute($element, attribute, trueVal, falseVal) {
  if (typeof trueVal === "undefined") {
    trueVal = true;
  }
  if (typeof falseVal === "undefined") {
    falseVal = false;
  }

  if ($element.attr(attribute) !== trueVal) {
    $element.attr(attribute, trueVal);
  } else {
    $element.attr(attribute, falseVal);
  }
}

/**
 * Retrive Url Parameter by name
 */
function getUrlParameter(name) {
  var results = new RegExp("[?&]" + name + "=([^&#]*)").exec(
    window.location.href
  );
  if (results == null) {
    return null;
  } else {
    return results[1] || 0;
  }
}

(function ($) {
  /* ------------------------------------------------------------------------------ /*
	/*  NAMESPACE
	/* ------------------------------------------------------------------------------ */

  var pol = pol || {};

  /* ------------------------------------------------------------------------------ /*
	/*  GLOBALS
	/* ------------------------------------------------------------------------------ */

  var $polDoc = $(document),
    $polWin = $(window);
  var placeid = (window.sidebarClickedcontent = null);

  jQuery(document).on("hover", ".pol-ajelms>li", function () {
    //pol.map.setLocalStorage('mapsNonloggedprops' , [map.getCenter().lat(), map.getCenter().lng() ,map.getZoom() ]);
  });

  /* ------------------------------------------------------------------------------ /*
	/*  Auto Pop up  map page sidebar click and from url param received from mail
	/* ------------------------------------------------------------------------------ */

  function pop(map, marker) {
    jQuery(document).ready(function () {
      jQuery(".pol-ajelms>li").hover(function () {
        var placeID = $(this).data("id");
      });
    });
    jQuery(document).on("click", ".pol-ajelms>li", function (ev) {
      ev.preventDefault();
      var placeID = $(this).data("id");
      var markerid = marker.get("placeid");

      if (parseInt(placeID) === markerid) {
        google.maps.event.trigger(marker, "click");
      }
    });

    // jQuery(document).on( 'click','.pol-search-list', function(ev){
    // 	ev.preventDefault();
    // 	var placeid = jQuery(this).data('marker');
    // 	var markerid= marker.get("placeid");
    // 	if(parseInt(placeid) === markerid){
    // 		google.maps.event.trigger(marker, 'click');
    // 	}
    // } );
    jQuery(document).ready(function () {
      //checks url parameter and pops up the infowwindow marker
      var place = getUrlParameter("place");
      var markerid = marker.get("placeid");

      if (parseInt(place) === markerid) {
        google.maps.event.trigger(marker, "click");
      } else {
        /* Checking if the user is logged-in. */
        if (jQuery("body").hasClass("logged-in")) {
          /* Getting the last marker from local storage and parsing it into a JSON object. */
          var prev_local = JSON.parse(localStorage.getItem("lastMarker"));
          /* Checking to see if the array is not empty */
          if (prev_local) {
            var currentUser = jQuery("body").attr("data-uid");
            var prevUser = prev_local[0];
            var prevMarker = prev_local[1];
            /* Checking if the previous user is the same as the current user. */
            if (prevUser === currentUser) {
              /* Checking if the previous marker is the same as the current marker. */
              if (parseInt(prevMarker) == markerid) {
                google.maps.event.trigger(marker, "click");
              }
            }
          }
        }
      }
    });
  }

  /**
   * Extend jQuery easing
   */
  $.extend($.easing, {
    easeInOutQuint: function (x) {
      return x < 0.5 ? 16 * x * x * x * x * x : 1 - Math.pow(-2 * x + 2, 5) / 2;
    },
  });

  /* ------------------------------------------------------------------------------ /*
	/*  INTERVAL SCROLL
	/* ------------------------------------------------------------------------------ */

  pol.intervalScroll = {
    init: function () {
      didScroll = false;

      // Check for the scroll event.
      $polWin.on("scroll load", function () {
        didScroll = true;
      });

      // Once every 250ms, check if we have scrolled, and if we have, do the intensive stuff.
      setInterval(function () {
        if (didScroll) {
          didScroll = false;

          // When this triggers, we know that we have scrolled.
          $polWin.trigger("did-interval-scroll");
        }
      }, 250);
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  TOGGLES
	/* ------------------------------------------------------------------------------ */

  pol.toggles = {
    init: function () {
      // Do the toggle.
      pol.toggles.toggle();

      // Check for toggle/untoggle on resize.
      pol.toggles.resizeCheck();

      // Check for untoggle on escape key press.
      pol.toggles.untoggleOnEscapeKeyPress();
    },

    // Do the toggle.
    toggle: function () {
      $("*[data-toggle-target]").on("click", function (e) {
        // Get our targets
        var $toggle = $(this),
          targetString = $(this).data("toggle-target"),
          $target = $(targetString);

        // Trigger events on the toggle targets before they are toggled.
        if ($target.is(".active")) {
          $target.trigger("toggle-target-before-active");
        } else {
          $target.trigger("toggle-target-before-inactive");
        }

        // For cover modals, set a short timeout duration so the class animations have time to play out.
        var timeOutTime = $target.hasClass("cover-modal") ? 5 : 0;

        setTimeout(function () {
          // Toggle the target of the clicked toggle.
          if ($toggle.data("toggle-type") == "slidetoggle") {
            var duration = $toggle.data("toggle-duration")
              ? $toggle.data("toggle-duration")
              : 250;
            if ($("body").hasClass("has-anim")) {
              $target.slideToggle(duration);
            } else {
              $target.toggle();
            }
          } else {
            $target.toggleClass("active");
          }

          // Toggle all toggles with this toggle target.
          $('*[data-toggle-target="' + targetString + '"]').toggleClass(
            "active"
          );

          // Toggle aria-expanded on the target.
          polToggleAttribute($target, "aria-expanded", "true", "false");

          // Toggle aria-pressed on the toggle.
          polToggleAttribute($toggle, "aria-pressed", "true", "false");

          // Toggle body class.
          if ($toggle.data("toggle-body-class")) {
            $("body").toggleClass($toggle.data("toggle-body-class"));
          }

          // Check whether to lock the screen.
          if ($toggle.data("lock-screen")) {
            pol.scrollLock.setTo(true);
          } else if ($toggle.data("unlock-screen")) {
            pol.scrollLock.setTo(false);
          } else if ($toggle.data("toggle-screen-lock")) {
            pol.scrollLock.setTo();
          }

          // Check whether to set focus.
          if ($toggle.data("set-focus")) {
            var $focusElement = $($toggle.data("set-focus"));
            if ($focusElement.length) {
              if ($toggle.is(".active")) {
                $focusElement.focus();
              } else {
                $focusElement.blur();
              }
            }
          }

          // Trigger the toggled event on the toggle target.
          $target.trigger("toggled");

          // Trigger events on the toggle targets after they are toggled.
          if ($target.is(".active")) {
            $target.trigger("toggle-target-after-active");
          } else {
            $target.trigger("toggle-target-after-inactive");
          }
        }, timeOutTime);

        return false;
      });
    },

    // Check for toggle/untoggle on screen resize.
    resizeCheck: function () {
      if (
        $(
          "*[data-untoggle-above], *[data-untoggle-below], *[data-toggle-above], *[data-toggle-below]"
        ).length
      ) {
        $polWin.on("resize", function () {
          var winWidth = $polWin.width(),
            $toggles = $(".toggle");

          $toggles.each(function () {
            $toggle = $(this);

            var unToggleAbove = $toggle.data("untoggle-above"),
              unToggleBelow = $toggle.data("untoggle-below"),
              toggleAbove = $toggle.data("toggle-above"),
              toggleBelow = $toggle.data("toggle-below");

            // If no width comparison is set, continue
            if (
              !unToggleAbove &&
              !unToggleBelow &&
              !toggleAbove &&
              !toggleBelow
            ) {
              return;
            }

            // If the toggle width comparison is true, toggle the toggle
            if (
              (((unToggleAbove && winWidth > unToggleAbove) ||
                (unToggleBelow && winWidth < unToggleBelow)) &&
                $toggle.hasClass("active")) ||
              (((toggleAbove && winWidth > toggleAbove) ||
                (toggleBelow && winWidth < toggleBelow)) &&
                !$toggle.hasClass("active"))
            ) {
              $toggle.trigger("click");
            }
          });
        });
      }
    },

    // Close toggle on escape key press.
    untoggleOnEscapeKeyPress: function () {
      $polDoc.on("keyup", function (e) {
        if (e.key === "Escape") {
          $("*[data-untoggle-on-escape].active").each(function () {
            if ($(this).hasClass("active")) {
              $(this).trigger("click");
            }
          });
        }
      });
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  COVER MODALS
	/* ------------------------------------------------------------------------------ */

  pol.coverModals = {
    init: function () {
      if ($(".cover-modal").length) {
        // Handle cover modals when they're toggled.
        pol.coverModals.onToggle();

        // When toggled, untoggle if visitor clicks on the wrapping element of the modal.
        pol.coverModals.outsideUntoggle();

        // Close on escape key press.
        pol.coverModals.closeOnEscape();

        // Hide and show modals before and after their animations have played out.
        pol.coverModals.hideAndShowModals();
      }
    },

    // Handle cover modals when they're toggled.
    onToggle: function () {
      $(".cover-modal").on("toggled", function () {
        var $modal = $(this),
          $body = $("body");

        if ($modal.hasClass("active")) {
          $body.addClass("showing-modal");
        } else {
          $body.removeClass("showing-modal").addClass("hiding-modal");

          // Remove the hiding class after a delay, when animations have been run
          setTimeout(function () {
            $body.removeClass("hiding-modal");
          }, 500);
        }
      });
    },

    // Close modal on outside click.
    outsideUntoggle: function () {
      $polDoc.on("click", function (e) {
        var $target = $(e.target),
          modal = ".cover-modal.active";

        if ($target.is(modal)) {
          pol.coverModals.untoggleModal($target);
        }
      });
    },

    // Close modal on escape key press.
    closeOnEscape: function () {
      $polDoc.on("keyup", function (e) {
        if (e.key === "Escape") {
          $(".cover-modal.active").each(function () {
            pol.coverModals.untoggleModal($(this));
          });
        }
      });
    },

    // Hide and show modals before and after their animations have played out.
    hideAndShowModals: function () {
      var $modals = $(".cover-modal");

      // Show the modal.
      $modals.on("toggle-target-before-inactive", function (e) {
        if (e.target != this) {
          return;
        }
        $(this).addClass("show-modal");
      });

      // Hide the modal after a delay, so animations have time to play out.
      $modals.on("toggle-target-after-inactive", function (e) {
        if (e.target != this) {
          return;
        }

        var $modal = $(this);
        setTimeout(function () {
          $modal.removeClass("show-modal");
        }, 250);
      });
    },

    // Untoggle a modal.
    untoggleModal: function ($modal) {
      $modalToggle = false;

      // If the modal has specified the string (ID or class) used by toggles to target it, untoggle the toggles with that target string.
      // The modal-target-string must match the string toggles use to target the modal.
      if ($modal.data("modal-target-string")) {
        var modalTargetClass = $modal.data("modal-target-string"),
          $modalToggle = $(
            '*[data-toggle-target="' + modalTargetClass + '"]'
          ).first();
      }

      // If a modal toggle exists, trigger it so all of the toggle options are included.
      if ($modalToggle && $modalToggle.length) {
        $modalToggle.trigger("click");

        // If one doesn't exist, just hide the modal.
      } else {
        $modal.removeClass("active");
      }
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  STICKY HEADER
	/* ------------------------------------------------------------------------------ */

  pol.stickyHeader = {
    init: function () {
      var $stickyElement = $("#site-header.stick-me");

      if ($stickyElement.length) {
        // Add our stand-in element for the sticky header.
        if (!$(".header-sticky-adjuster").length) {
          $stickyElement.before('<div class="header-sticky-adjuster"></div>');
        }

        // Stick the header.
        $stickyElement.addClass("is-sticky");

        // Update the dimensions of our stand-in element on load and screen size change.
        pol.stickyHeader.updateStandIn($stickyElement);

        $polWin.on("resize orientationchange", function () {
          pol.stickyHeader.updateStandIn($stickyElement);
        });
      }
    },

    updateStandIn: function ($stickyElement) {
      $(".header-sticky-adjuster")
        .height($stickyElement.outerHeight())
        .css("margin-bottom", parseInt($stickyElement.css("marginBottom")));
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  RESPONSIVE EMBEDS
	/* ------------------------------------------------------------------------------ */

  pol.responsiveEmbeds = {
    init: function () {
      pol.responsiveEmbeds.makeFit();

      $polWin.on("resize fit-videos", function () {
        pol.responsiveEmbeds.makeFit();
      });
    },

    makeFit: function () {
      var vidSelector = "iframe, object, video";

      $(vidSelector).each(function () {
        var $video = $(this),
          $container = $video.parent(),
          iTargetWidth = $container.width();

        // Skip videos we want to ignore.
        if (
          $video.hasClass("intrinsic-ignore") ||
          $video.parent().hasClass("intrinsic-ignore")
        ) {
          return true;
        }

        if (!$video.attr("data-origwidth")) {
          // Get the video element proportions.
          $video.attr("data-origwidth", $video.attr("width"));
          $video.attr("data-origheight", $video.attr("height"));
        }

        // Get ratio from proportions.
        var ratio = iTargetWidth / $video.attr("data-origwidth");

        // Scale based on ratio, thus retaining proportions.
        $video.css("width", iTargetWidth + "px");
        $video.css("height", $video.attr("data-origheight") * ratio + "px");
      });
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  SCROLL LOCK
	/* ------------------------------------------------------------------------------ */

  pol.scrollLock = {
    init: function () {
      // Initialize variables.
      (window.scrollLocked = false),
        (window.prevScroll = {
          scrollLeft: $polWin.scrollLeft(),
          scrollTop: $polWin.scrollTop(),
        }),
        (window.prevLockStyles = {}),
        (window.lockStyles = {
          "overflow-y": "scroll",
          position: "fixed",
          width: "100%",
        });

      // Instantiate cache in case someone tries to unlock before locking.
      pol.scrollLock.saveStyles();
    },

    // Save context's inline styles in cache.
    saveStyles: function () {
      var styleAttr = $("html").attr("style"),
        styleStrs = [],
        styleHash = {};

      if (!styleAttr) {
        return;
      }

      styleStrs = styleAttr.split(/;\s/);

      $.each(styleStrs, function serializeStyleProp(styleString) {
        if (!styleString) {
          return;
        }

        var keyValue = styleString.split(/\s:\s/);

        if (keyValue.length < 2) {
          return;
        }

        styleHash[keyValue[0]] = keyValue[1];
      });

      $.extend(prevLockStyles, styleHash);
    },

    // Lock the scroll
    lock: function () {
      var appliedLock = {};

      if (scrollLocked) {
        return;
      }

      // Save scroll state and styles
      prevScroll = {
        scrollLeft: $polWin.scrollLeft(),
        scrollTop: $polWin.scrollTop(),
      };

      pol.scrollLock.saveStyles();

      // Compose our applied CSS, with scroll state as styles.
      $.extend(appliedLock, lockStyles, {
        left: -prevScroll.scrollLeft + "px",
        top: -prevScroll.scrollTop + "px",
      });

      // Then lock styles and state.
      $("html").css(appliedLock);
      $("html").addClass("scroll-locked");
      $("html").attr("scroll-lock-top", prevScroll.scrollTop);
      $polWin.scrollLeft(0).scrollTop(0);

      window.scrollLocked = true;
    },

    // Unlock the scroll.
    unlock: function () {
      if (!window.scrollLocked) {
        return;
      }

      // Revert styles and state.
      $("html").attr("style", $("<x>").css(prevLockStyles).attr("style") || "");
      $("html").removeClass("scroll-locked");
      $("html").attr("scroll-lock-top", "");
      $polWin.scrollLeft(prevScroll.scrollLeft).scrollTop(prevScroll.scrollTop);

      window.scrollLocked = false;
    },

    // Call this to lock or unlock the scroll.
    setTo: function (on) {
      // If an argument is passed, lock or unlock accordingly.
      if (arguments.length) {
        if (on) {
          pol.scrollLock.lock();
        } else {
          pol.scrollLock.unlock();
        }
        // If not, toggle to the inverse state.
      } else {
        if (window.scrollLocked) {
          pol.scrollLock.unlock();
        } else {
          pol.scrollLock.lock();
        }
      }
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  FOCUS MANAGEMENT
	/* ------------------------------------------------------------------------------ */

  pol.focusManagement = {
    init: function () {
      // Focus loops.
      pol.focusManagement.focusLoops();

      // Add and remove a class from dropdown menu items on focus
      pol.focusManagement.dropdownFocus();
    },

    focusLoops: function () {
      // Add focus loops for the menu modal (which includes the #site-aside navigation toggle on desktop) and search modal.
      $polDoc.on("keydown", function (e) {
        var $focusElement = $(":focus");

        if (e.keyCode == 9) {
          var $destination = false;

          // Get the first and last visible focusable elements in the menu modal, for comparison against the focused element.
          var $menuModalFocusable = $(".menu-modal")
              .find(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
              )
              .filter(":visible"),
            $menuModalFirst = $menuModalFocusable.first(),
            $menuModalLast = $menuModalFocusable.last();

          // Tabbing backwards.
          if (e.shiftKey) {
            if ($focusElement.is("#site-aside .nav-toggle.active")) {
              $destination = $(".menu-modal a:visible:last");
            } else if ($focusElement.is($menuModalFirst)) {
              $destination = $("#site-aside .nav-toggle").is(":visible")
                ? $("#site-aside .nav-toggle")
                : $menuModalLast;
            } else if ($focusElement.is(".search-modal .search-field")) {
              $destination = $(".search-untoggle");
            }
          }

          // Tabbing forwards.
          else {
            if ($focusElement.is($menuModalLast)) {
              $destination = $("#site-aside .nav-toggle").is(":visible")
                ? $("#site-aside .nav-toggle")
                : $menuModalFirst;
            } else if ($focusElement.is("#site-aside .nav-toggle.active")) {
              $destination = $menuModalFirst;
            } else if ($focusElement.is(".search-untoggle")) {
              $destination = $(".search-modal .search-field");
            }
          }

          // If a destination is set, change focus.
          if ($destination) {
            $destination.focus();
            return false;
          }
        }
      });
    },

    dropdownFocus: function () {
      $(".dropdown-menu a").on("blur focus", function (e) {
        $(this).parents("li.menu-item-has-children").toggleClass("focus");

        if (e.type == "focus") {
          $(this).trigger("focus-applied");
        }
      });
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  NAV MENUS
	/* ------------------------------------------------------------------------------ */

  pol.navMenus = {
    init: function () {
      // If the current menu item is in a sub level, expand all the levels higher up on load.
      pol.navMenus.expandLevel();

      // Determine the direction of sub menus in the dropdown menu
      pol.navMenus.dropdownMenu();
    },

    // If the current menu item is in a sub level, expand all the levels higher up on load.
    expandLevel: function () {
      var $activeMenuItem = $(".modal-menu .current-menu-item");

      if ($activeMenuItem.length !== false) {
        $activeMenuItem.parents("li").each(function () {
          $subMenuToggle = $(this).find(".sub-menu-toggle").first();

          if ($subMenuToggle.length) {
            $subMenuToggle.trigger("click");
          }
        });
      }
    },

    // Determine the direction of sub menus in the dropdown menu
    dropdownMenu: function () {
      // Note: the focus-applied event is triggered
      // by pol.focusManagement.dropdownFocus when
      // the sub has been given the .focus class.

      $(".dropdown-menu a").on("mouseover focus-applied", function () {
        var $sub = $(this).closest("li").find("ul").first();

        if ($sub.length) {
          var $descendantSubs = $sub.find("ul"),
            subOffsetLeft = $sub.offset().left,
            subOffsetRight = subOffsetLeft + $sub.outerWidth(),
            winWidth = $polWin.width();

          if (subOffsetRight > winWidth) {
            $sub
              .add($descendantSubs)
              .removeClass("expand-right")
              .addClass("expand-left");
          } else if (subOffsetLeft < 0) {
            $sub
              .add($descendantSubs)
              .removeClass("expand-left")
              .addClass("expand-right");
          }
        }
      });
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  LOAD MORE
	/* ------------------------------------------------------------------------------ */

  pol.loadMore = {
    init: function () {
      var $pagination = $("#pagination");

      // First, check that there's a pagination.
      if ($pagination.length) {
        // Default values for variables.
        window.polIsLoading = false;
        window.polIsLastPage = $(".pagination-wrapper").hasClass("last-page");

        pol.loadMore.prepare($pagination);
      }

      // When the pagination query args are updated, reset the posts to reflect the new pagination
      $polWin.on("reset-posts", function () {
        // Fade out the pagination and existing posts.
        $pagination
          .add($($pagination.data("load-more-target")).find(".article-wrapper"))
          .animate({ opacity: 0 }, 300, "linear");

        // Reset posts.
        pol.loadMore.prepare($pagination, (resetPosts = true));
      });
    },

    prepare: function ($pagination, resetPosts) {
      // Default resetPosts to false.
      if (typeof resetPosts === "undefined" || !resetPosts) {
        resetPosts = false;
      }

      // Get the query arguments from the pagination element.
      var queryArgs = JSON.parse($pagination.attr("data-query-args"));

      // If we're resetting posts, reset them.
      if (resetPosts) {
        pol.loadMore.loadPosts($pagination, resetPosts);
      }

      // If not, check the paged value against the max_num_pages.
      else {
        if (queryArgs.paged == queryArgs.max_num_pages) {
          $(".pagination-wrapper").addClass("last-page");
        }

        // Get the load more type (button or scroll).
        var loadMoreType = $pagination.data("pagination-type")
          ? $pagination.data("pagination-type")
          : "button";

        // Do the appropriate load more detection, depending on the type.
        if (loadMoreType == "scroll") {
          pol.loadMore.detectScroll($pagination);
        } else if (loadMoreType == "button") {
          pol.loadMore.detectButtonClick($pagination);
        }
      }
    },

    // Load more on scroll
    detectScroll: function ($pagination, query_args) {
      $polWin.on("did-interval-scroll", function () {
        // If it's the last page, or we're already loading, we're done here.
        if (polIsLastPage || polIsLoading) {
          return;
        }

        var paginationOffset = $pagination.offset().top,
          winOffset = $polWin.scrollTop() + $polWin.outerHeight();

        // If the bottom of the window is below the top of the pagination, start loading.
        if (winOffset > paginationOffset) {
          pol.loadMore.loadPosts($pagination, query_args);
        }
      });
    },

    // Load more on click.
    detectButtonClick: function ($pagination, query_args) {
      // Load on click.
      $("#load-more").on("click", function () {
        // Make sure we aren't already loading.
        if (polIsLoading) {
          return;
        }

        pol.loadMore.loadPosts($pagination, query_args);
        return false;
      });
    },

    // Load the posts
    loadPosts: function ($pagination, resetPosts) {
      // Default resetPosts to false.
      if (typeof resetPosts === "undefined" || !resetPosts) {
        resetPosts = false;
      }

      // Get the query arguments.
      var queryArgs = $pagination.attr("data-query-args"),
        queryArgsParsed = JSON.parse(queryArgs),
        $paginationWrapper = $(".pagination-wrapper"),
        $articleWrapper = $($pagination.data("load-more-target"));

      // We're now loading.
      polIsLoading = true;
      if (!resetPosts) {
        $paginationWrapper.addClass("loading");
      }

      // If we're not resetting posts, increment paged (reset = initial paged is correct).
      if (!resetPosts) {
        queryArgsParsed.paged++;
      } else {
        queryArgsParsed.paged = 1;
      }

      // Prepare the query args for submission.
      var jsonQueryArgs = JSON.stringify(queryArgsParsed);

      $.ajax({
        url: pol_ajax_load_more.ajaxurl,
        type: "post",
        data: {
          action: "pol_ajax_load_more",
          json_data: jsonQueryArgs,
        },
        success: function (result) {
          // Get the results.
          var $result = $(result);

          // If we're resetting posts, remove the existing posts.
          if (resetPosts) {
            $articleWrapper.find("*:not(.grid-sizer)").remove();
          }

          // If there are no results, we're at the last page.
          if (!$result.length) {
            polIsLoading = false;
            $articleWrapper.addClass("no-results");
            $paginationWrapper.addClass("last-page").removeClass("loading");
          }

          if ($result.length) {
            $articleWrapper.removeClass("no-results");

            // Add the paged attribute to the articles, used by updateHistoryOnScroll().
            $result.find("article").each(function () {
              $(this).attr("data-post-paged", queryArgsParsed.paged);
            });

            // Wait for the images to load.
            $result.imagesLoaded(function () {
              // Append the results.
              $articleWrapper
                .append($result)
                .isotope("appended", $result)
                .isotope();

              $polWin.trigger("ajax-content-loaded");
              $polWin.trigger("did-interval-scroll");

              // We're now finished with the loading.
              polIsLoading = false;
              $paginationWrapper.removeClass("loading");

              // Update the pagination query args.
              $pagination.attr("data-query-args", jsonQueryArgs);

              // Reset the resetting of posts.
              if (resetPosts) {
                setTimeout(function () {
                  $pagination.animate({ opacity: 1 }, 600, "linear");
                }, 400);
                $("body").removeClass("filtering-posts");
              }

              // If that was the last page, make sure we don't check for more.
              if (queryArgsParsed.paged == queryArgsParsed.max_num_pages) {
                $paginationWrapper.addClass("last-page");
                polIsLastPage = true;
                return;

                // If not, make sure the pagination is visible again.
              } else {
                $paginationWrapper.removeClass("last-page");
                polIsLastPage = false;
              }
            });
          }
        },

        error: function (jqXHR, exception) {
          polAjaxErrors(jqXHR, exception);
        },
      });
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  FILTERS
	/* ------------------------------------------------------------------------------ */

  pol.filters = {
    init: function () {
      $polDoc.on("click", ".filter-link", function () {
        if ($(this).hasClass("active")) {
          return false;
        }

        $("body").addClass("filtering-posts");

        var $link = $(this),
          termId = $link.data("filter-term-id")
            ? $link.data("filter-term-id")
            : null,
          taxonomy = $link.data("filter-taxonomy")
            ? $link.data("filter-taxonomy")
            : null,
          postType = $link.data("filter-post-type")
            ? $link.data("filter-post-type")
            : "";

        $link.addClass("pre-active");

        $.ajax({
          url: pol_ajax_filters.ajaxurl,
          type: "post",
          data: {
            action: "pol_ajax_filters",
            post_type: postType,
            term_id: termId,
            taxonomy: taxonomy,
          },
          success: function (result) {
            // Add them to the pagination.
            $("#pagination").attr("data-query-args", result);

            // Reset the posts.
            $polWin.trigger("reset-posts");

            // Update active class.
            $(".filter-link").removeClass("pre-active active");
            $link.addClass("active");
          },

          error: function (jqXHR, exception) {
            polAJAXErrors(jqXHR, exception);
          },
        });

        return false;
      });
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  ELEMENT IN VIEW
	/* ------------------------------------------------------------------------------ */

  pol.elementInView = {
    init: function () {
      $targets = $("body.has-anim .do-spot");
      pol.elementInView.run($targets);

      // Rerun on AJAX content loaded.
      $polWin.on("ajax-content-loaded", function () {
        $targets = $("body.has-anim .do-spot");
        pol.elementInView.run($targets);
      });
    },

    run: function ($targets) {
      if ($targets.length) {
        // Add class indicating the elements will be spotted.
        $targets.each(function () {
          $(this).addClass("will-be-spotted");
        });

        pol.elementInView.handleFocus($targets);

        $polWin.on(
          "load resize orientationchange did-interval-scroll",
          function () {
            pol.elementInView.handleFocus($targets);
          }
        );
      }
    },

    handleFocus: function ($targets) {
      // Check for our targets.
      $targets.each(function () {
        var $this = $(this);

        if (pol.elementInView.isVisible($this, (checkAbove = true))) {
          $this.addClass("spotted").trigger("spotted");
        }
      });
    },

    // Determine whether the element is in view.
    isVisible: function ($elem, checkAbove) {
      if (typeof checkAbove === "undefined") {
        checkAbove = false;
      }

      var winHeight = $polWin.height(),
        docViewTop = $polWin.scrollTop(),
        docViewBottom = docViewTop + winHeight,
        docViewLimit = docViewBottom,
        elemTop = $elem.offset().top;

      // If checkAbove is set to true, which is default, return true if the browser has already scrolled past the element.
      if (checkAbove && elemTop <= docViewBottom) {
        return true;
      }

      // If not, check whether the scroll limit exceeds the element top.
      return docViewLimit >= elemTop;
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  GRID
	/* ------------------------------------------------------------------------------ */

  pol.grid = {
    init: function () {
      var $wrapper = $(".posts-grid");

      if ($wrapper.length) {
        $wrapper.imagesLoaded(function () {
          $grid = $wrapper.isotope({
            columnWidth: ".grid-sizer",
            itemSelector: ".article-wrapper",
            percentPosition: true,
            stagger: 0,
            transitionDuration: 0,
            hiddenStyle: { opacity: 0 },
            visibleStyle: { opacity: 1 },
            layoutMode:
              $wrapper.data("layout") == "grid" ? "fitRows" : "masonry",
            masonry: { columnWidth: ".grid-sizer" },
          });

          // Trigger will-be-spotted elements.
          $grid.on("layoutComplete", function () {
            $polWin.trigger("scroll");
          });

          // Check for Masonry layout changes on an interval. Accounts for DOM changes caused by lazyloading plugins.
          // The interval is cleared when all previews have been spotted.
          pol.grid.intervalUpdate($grid);

          // Reinstate the interval when new content is loaded.
          $polWin.on("ajax-content-loaded", function () {
            pol.grid.intervalUpdate($grid);
          });
        });
      }
    },

    intervalUpdate: function ($grid) {
      var gridLayoutInterval = setInterval(function () {
        $grid.isotope();

        // Clear the interval when all previews have been spotted.
        if (!$(".preview.do-spot:not(.spotted)").length) {
          clearInterval(gridLayoutInterval);
        }
      }, 1000);
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  DYNAMIC HEIGHTS
	/* ------------------------------------------------------------------------------ */

  pol.dynamicHeights = {
    init: function () {
      pol.dynamicHeights.resize();

      $polWin.on("resize orientationchange", function () {
        pol.dynamicHeights.resize();
      });
    },

    resize: function () {
      var $header = $("#site-header"),
        $footer = $("#site-footer"),
        $content = $("#site-content"),
        $entry = $(".entry-content.has-featured-image"),
        headerHeight = $header.outerHeight(),
        contentHeight =
          $polWin.outerHeight() -
          headerHeight -
          parseInt($header.css("marginBottom")) -
          $footer.outerHeight() -
          parseInt($footer.css("marginTop"));

      document.body.style.setProperty(
        "--pol-screen-height",
        $polWin.innerHeight() + "px"
      );
      document.body.style.setProperty(
        "--pol-content-height",
        contentHeight + "px"
      );

      // Set the desktop navigation toggle and search modal field to match the header height, including line-height of pseudo (thanks, Firefox).
      $("#site-aside .nav-toggle-inner").css("height", headerHeight);
      $(".search-modal .search-field").css("height", headerHeight);
      $(
        "<style>.modal-search-form .search-field::-moz-placeholder { line-height: " +
          headerHeight +
          "px }</style>"
      ).appendTo("head");

      // Sets the min-height for entry content with images.
      if ($entry.length) {
        var $featuredImg = $entry.find(".story-media img"),
          $footerGoat = $(".footer-goat");

        if ($featuredImg.length && $footerGoat.length) {
          var imgBottom = $featuredImg.offset().top + $featuredImg.height(),
            goatTop = $footerGoat.offset().top,
            dist = goatTop - imgBottom,
            offset;

          if (dist < 25) {
            (offset = 25 - dist), (minHeight = $entry.height() + offset);

            $entry.css("min-height", minHeight + "px");
          }
        }
      }
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  FADE ON SCROLL
	/* ------------------------------------------------------------------------------ */

  pol.fadeOnScroll = {
    init: function () {
      var scroll =
        window.requestAnimationFrame ||
        window.webkitRequestAnimationFrame ||
        window.mozRequestAnimationFrame ||
        window.msRequestAnimationFrame ||
        window.oRequestAnimationFrame ||
        // IE Fallback, you can even fallback to onscroll
        function (callback) {
          window.setTimeout(callback, 1000 / 60);
        };

      function loop() {
        var windowOffset = window.pageYOffset;

        if (windowOffset < $polWin.outerHeight()) {
          $(".fade-on-scroll").css({
            opacity: 1 - windowOffset * 0.00175,
          });
        }

        scroll(loop);
      }
      loop();
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  SMOOTH SCROLL
	/* ------------------------------------------------------------------------------ */

  pol.smoothScroll = {
    init: function () {
      // Scroll to on-page elements by hash
      $('body.has-anim:not(.disable-smooth-scroll) a[href*="#"]')
        .not('[href="#"]')
        .not('[href="#0"]')
        .not(".disable-smooth-scroll")
        .on("click", function (e) {
          if (
            location.pathname.replace(/^\//, "") ==
              this.pathname.replace(/^\//, "") &&
            location.hostname == this.hostname
          ) {
            var $target = $(this.hash).length
              ? $(this.hash)
              : $("[name=" + this.hash.slice(1) + "]");
            pol.smoothScroll.scrollToTarget($target, $(this));
            e.preventDefault();
          }
        });

      // Scroll to elements specified with a data attribute
      $("body.has-anim *[data-scroll-to]").on("click", function (e) {
        var $target = $($(this).data("scroll-to"));
        pol.smoothScroll.scrollToTarget($target, $(this));
        e.preventDefault();
      });
    },

    // Scroll to target
    scrollToTarget: function ($target, $clickElem) {
      if ($target.length) {
        var additionalOffset = -50,
          scrollSpeed = 1400;

        // Get options
        if ($clickElem && $clickElem.length) {
          (additionalOffset = $clickElem.data("scroll-offset")
            ? $clickElem.data("scroll-offset")
            : additionalOffset),
            (scrollSpeed = $clickElem.data("scroll-speed")
              ? $clickElem.data("scroll-speed")
              : scrollSpeed);
        }

        // Determine offset
        var originalOffset = $target.offset().top;

        // Special handling of scroll offset when scroll locked
        if ($("html").attr("scroll-lock-top")) {
          var originalOffset =
            parseInt($("html").attr("scroll-lock-top")) + $target.offset().top;
        }

        // If the header is sticky, subtract its height from the offset
        if ($("#site-header.stick-me").length) {
          var originalOffset = originalOffset - $("#site-header").outerHeight();
        }

        // If the header is sticky, subtract its height from the offset
        if ($(".header-inner.stick-me").length) {
          var originalOffset =
            originalOffset - $(".header-inner.stick-me").outerHeight();
        }

        // Close any parent modal before scrolling
        if ($clickElem.closest(".cover-modal").length) {
          pol.coverModals.untoggleModal($clickElem.closest(".cover-modal"));
        }

        // Add the additional offset
        var scrollOffset = originalOffset + additionalOffset;

        pol.smoothScroll.scrollToPosition(scrollOffset, scrollSpeed);
      }
    },

    scrollToPosition: function (position, speed) {
      $("html, body").animate(
        {
          scrollTop: position,
        },
        speed,
        "easeInOutQuint",
        function () {
          $polWin.trigger("did-interval-scroll");
        }
      );
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  MAP
	/* ------------------------------------------------------------------------------ */

  pol.map = {
    init: function () {
      pol.map.mapForm();

      $(".acf-map").each(function () {
        pol.map.googleMaps($(this));
      });
    },

    // Renders a Google Map onto the selected jQuery element.
    googleMaps: function ($target) {
      // Find marker elements within map.
      var $markers = $target.find(".marker");
      var maxLat = (Math.atan(Math.sinh(Math.PI)) * 180) / Math.PI;
      var zoom = 1;
      if (
        /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
          navigator.userAgent
        )
      ) {
        zoom = 3;
      }

      var mapArgs = {
        zoom: zoom,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        minZoom: 0,
        mapTypeId: $target.data("map-style") || "hybrid",
        center: {
          lat: $target.data("map-lat") || 52.8814742,
          lng: $target.data("map-lng") || 6.84929598,
        },
        restriction: {
          latLngBounds: {
            north: maxLat,
            south: -maxLat,
            west: -180,
            east: 180,
          },
          strictBounds: true,
        },
      };
      // if(pol.map.checkLoggedIn){
      // 	var localData = pol.map.getLocalStorage('lastMapProps') , currentuser =pol.map.getLoggedInUserId();
      // 	if(localData){
      // 		if(localData[3] === currentuser){
      // 			mapArgs.zoom =  localData[2];
      // 			mapArgs.center = {
      // 				lat					: localData[0],
      // 				lng					: localData[1]
      // 			}
      // 		}
      // 	}

      // }

      if ($target.data("map-id")) {
        mapArgs["mapId"] = $target.data("map-id");
      }

      var map = new google.maps.Map($target[0], mapArgs);

      if ($markers.length) {
        pol.map.getPlaces(map, $markers);
      }

      pol.map.mapDrag(map);
      return map;
    },

    /* Saving the map properties to local storage. */
    mapDrag: function (map) {
      map.addListener("bounds_changed", () => {
        if (pol.map.checkLoggedIn()) {
          var lastMapProps = [
            map.getCenter().lat(),
            (lng = map.getCenter().lng()),
            map.getZoom(),
            pol.map.getLoggedInUserId(),
          ];
          pol.map.setLocalStorage("lastMapProps", lastMapProps);
        }
      });
    },

    /* Setting the local storage. */
    setLocalStorage: function (name, data) {
      if (name) localStorage.setItem(name, JSON.stringify(data));
    },

    /* Getting the local storage data and returning it. */
    getLocalStorage: function (key) {
      var data = JSON.parse(localStorage.getItem(key));
      if (data) {
        return data;
      } else {
        return false;
      }
    },

    /* Checking if the body has a class of logged-in. If it does, it returns true, if not, it returns
		false. */
    checkLoggedIn: function () {
      var checkLoged = $("body").hasClass("logged-in") ? true : false;
      return checkLoged;
    },

    /* Checking if the user is logged in and if so, it returns the user id. */
    getLoggedInUserId: function () {
      if (pol.map.checkLoggedIn()) {
        return $("body").attr("data-uid");
      } else {
        return false;
      }
    },

    /* The above code is creating a search box on the map. */
    intializeAutocomplete: function (map, marker) {
      var count = 0;
      const input = document.getElementById("pol-map-search-input");
      map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
      var inputSearch = $("#pol-map-search-input");
      var uiAutomplete = $(inputSearch)
        .autocomplete({
          delay: 100,
          autoFocus: true,
          minLength: 1,
          source: function (request, response) {
            // Fetch data
            $.ajax({
              url: pol_ajax_load_more.ajaxurl,
              type: "post",
              dataType: "json",
              data: {
                action: "pol_search_data_fetch",
                search: request.term,
              },
              beforeSend: function () {
                $(".inputcontainer").css("display", "block");
              },
              success: function (data) {
                response(data);
                $(".inputcontainer").css("display", "none");
              },
            });
          },
          response: function (event, ui) {
            var responseMarkers = [];

            ui.content.forEach(function (item) {
              responseMarkers.push(parseInt(item.id));
            });

            if (pol_ajax_map_parameters.show_stories_on_search)
              pol.map.hideMarkerPopups(map);

            map.markers.forEach(function (marker) {
              var markerid = marker.get("placeid");

              marker.setVisible(
                responseMarkers.indexOf(parseInt(markerid)) > -1
              );

              if (
                responseMarkers.length < 20 &&
                marker.getVisible() &&
                pol_ajax_map_parameters.show_stories_on_search
              ) {
                pol.map.showMarkerPopups(marker, marker.get("element"), {
                  type: "story",
                  post_id: 0,
                });
              }
            });
          },
          select: function (event, ui) {
            event.preventDefault();

            var marker = pol.map.getMarker(map, parseInt(ui.item.id));
            if (marker != null) {
              google.maps.event.trigger(marker, "click", {
                type: ui.item.pt,
                post_id: ui.item.sid,
              });
            }
          },
          open: function (event, ui) {
            var menu = $(this).autocomplete("instance").menu.element;
            if (menu.find(".pol-search-container").length == 0)
              menu
                .children()
                .wrapAll($('<div class="pol-search-container"></div>'));
          },
          focus: function (event, ui) {
            if (
              !ui.item ||
              !ui.item.data ||
              typeof ui.item.data("ui-autocomplete-item") === "undefined"
            ) {
              event.preventDefault();
              return false;
            }
          },
        })
        .data("ui-autocomplete");

      uiAutomplete._renderItem = function (ul, item) {
        var listItem = $("<li></li>").data("ui-autocomplete-item", item);
        var re = new RegExp("^" + this.term, "i"); //i makes the regex case insensitive
        var t = item.label.replace(
          re,
          "<span class=required-drop>" + this.term + "</span>"
        );
        var count = item.count;

        if (eval(count) == 0) {
          listItem.html(
            '<span class="menu-item-header-type">' +
              item.pt +
              '</span><div data-posttype="' +
              item.pt +
              '" data-storyid="' +
              item.sid +
              '" data-marker= "' +
              item.id +
              '" tabindex="-1" class="ui-menu-item-wrapper pol-search-list">' +
              t +
              "</div>"
          );
        } else {
          listItem.html(
            '<div data-posttype="' +
              item.pt +
              '" data-storyid="' +
              item.sid +
              '" data-marker= "' +
              item.id +
              '" tabindex="-1" class="ui-menu-item-wrapper pol-search-list">' +
              t +
              "</div>"
          );
        }

        return listItem.appendTo(ul);
      };

      $(inputSearch).on("keyup", function () {
        if ($(this).val() == "") {
          if (pol_ajax_map_parameters.show_stories_on_search)
            pol.map.hideMarkerPopups(map);

          map.markers.forEach(function (marker) {
            marker.setVisible(true);
          });
        }
      });

      pop(map, marker);
    },

    createPopups: function (map, count) {
      if (typeof map.popups === "undefined") {
        map.popups = [];
        map.popupsCurrent = 0;
      }

      var popupIndex = map.popups.length;

      while (map.popups.length < count + map.popupsCurrent) {
        // Create a generic info window.
        var infoWindow = new google.maps.InfoWindow({
          enableEventPropagation: true,
          pixelOffset: new google.maps.Size(0, -10),
        });

        // Remove body class when infoWindow is closed.
        infoWindow.addListener("closeclick", () => {
          pol.map.closePopup(map, true);
        });

        google.maps.event.addListener(infoWindow, "domready", function () {
          var parents = $(".story-popup").parents("[role=dialog]");
          parents.addClass("dialog-story");
          parents.removeClass("selected-story");
          parents.has(".selected-story").addClass("selected-story");
        });

        var popup = {
          infoWindow: infoWindow,
          visible: false,
        };

        map.popups.push(popup);

        popupIndex++;
      }
    },

    showMarkerPopups: function (marker, markerNode, args = {}) {
      if (!args) args = { type: "place", post_id: 0 };

      var map = marker.getMap();
      var storyTemplate = $(".story-popup-template").find(".story-popup");
      var selectedStory = args.post_id;
      var stories = $(
        markerNode
          .find(".place-stories .place-story")
          .clone()
          .toArray()
          .sort(function (lhs, rhs) {
            if ($(lhs).data("id") == selectedStory) return -1;
            else if ($(rhs).data("id") == selectedStory) return 1;
            return 0;
          })
      );
      var storyCount = stories.length;

      if (args["type"] != "story") storyCount = 1;

      this.createPopups(map, storyCount);

      if (selectedStory) {
        for (var storyIndex = 0; storyIndex < storyCount; storyIndex++) {
          var popupIndex = map.popupsCurrent + storyIndex;
          var popup = map.popups[popupIndex];

          var storyContent = null;

          if (args["type"] == "story") {
            var story = stories.eq(storyIndex).clone();

            if (story.data("id") == selectedStory) {
              var storyUrl = story.data("url");
              story.find(".place-story-pills").remove();
              storyContent = storyTemplate.clone();
              storyContent
                .find(".story-content-placeholder")
                .replaceWith(story);
              // storyContent.addClass('selected-story');

              // else
              // storyContent.removeClass('selected-story');

              if (pol_ajax_map_parameters.show_large_story_popup) {
                storyContent
                  .find(".story-actions .read-more")
                  .on("click", function (e) {
                    e.preventDefault();

                    var story = $(this)
                      .parents(".story-popup")
                      .find(".place-story");

                    //var cookieValue = document.cookie.split('; ').find((row) => row.startsWith('testtt=')).split('=')[1];

                    //if (cookieValue == 'yes')
                    {
                      var storyUrl = story.data("url");
                      var storyFull = $(
                        '.story-iframe[data-storyid="' + story.data("id") + '"]'
                      );
                      $(".story-iframe").hide();

                      if (storyFull.length == 0) {
                        var spinner = $(this).find(".fa-spinner");
                        if (spinner.length == 0) {
                          spinner = $(
                            '<i class="fa fa-spinner fa-spin" style="padding-left:2pt;"></i>'
                          );
                          $(this).append(spinner);
                        }
                        spinner.show();

                        var storyIframe = $("<iframe>");
                        storyIframe.attr("src", storyUrl);
                        storyFull = $(
                          '<div class="story-iframe"><i class="close-button fa fa-times-circle" aria-hidden="true"></i></div>'
                        );
                        storyFull.attr("data-storyid", story.data("id"));
                        storyFull.append(storyIframe);
                        storyFull.appendTo(document.body);
                        storyFull.hide();

                        storyFull
                          .find(".close-button")
                          .on("click", function () {
                            storyFull.hide();
                          });

                        storyIframe.on("load", function () {
                          spinner.hide();
                          var jFrame = $(this);
                          var jFrameContent = jFrame
                            .contents()
                            .find("#site-content");
                          jFrameContent.siblings().hide();
                          jFrameContent.find("article.story").css({
                            "padding-top": "1.5em",
                            "padding-bottom": "1.5em",
                          });
                          storyFull.show();
                        });
                      } else {
                        storyFull.show();
                      }
                    }

                    return false;
                  });
              } else
                storyContent
                  .find(".story-actions .read-more")
                  .attr("href", storyUrl);

              popup.infoWindow.setContent(storyContent.get(0));
              setTimeout(
                function (popup) {
                  popup.infoWindow.open({
                    anchor: marker,
                    shouldFocus: false,
                  });
                  popup.visible = true;
                },
                250,
                popup
              );
            }
          } else {
            storyContent = markerNode.children().clone();
          }

          var pixelOffset = new google.maps.Size(0, -10 - 100 * storyIndex);
          if (popup.infoWindow.get("pixelOffset") != pixelOffset) {
            popup.infoWindow.set("pixelOffset", pixelOffset);
            popup.infoWindow.close();
          }
        }
      } else {
        for (var storyIndex = 0; storyIndex < storyCount; storyIndex++) {
          var popupIndex = map.popupsCurrent + storyIndex;
          var popup = map.popups[popupIndex];

          var storyContent = null;

          if (args["type"] == "story") {
            var story = stories.eq(storyIndex).clone();

            if (story.data("id") == selectedStory) {
              var storyUrl = story.data("url");
              story.find(".place-story-pills").remove();
              storyContent = storyTemplate.clone();
              storyContent
                .find(".story-content-placeholder")
                .replaceWith(story);
              // storyContent.addClass('selected-story');

              // else
              // storyContent.removeClass('selected-story');

              if (pol_ajax_map_parameters.show_large_story_popup) {
                storyContent
                  .find(".story-actions .read-more")
                  .on("click", function (e) {
                    e.preventDefault();

                    var story = $(this)
                      .parents(".story-popup")
                      .find(".place-story");

                    //var cookieValue = document.cookie.split('; ').find((row) => row.startsWith('testtt=')).split('=')[1];

                    //if (cookieValue == 'yes')
                    {
                      var storyUrl = story.data("url");
                      var storyFull = $(
                        '.story-iframe[data-storyid="' + story.data("id") + '"]'
                      );
                      $(".story-iframe").hide();

                      if (storyFull.length == 0) {
                        var spinner = $(this).find(".fa-spinner");
                        if (spinner.length == 0) {
                          spinner = $(
                            '<i class="fa fa-spinner fa-spin" style="padding-left:2pt;"></i>'
                          );
                          $(this).append(spinner);
                        }
                        spinner.show();

                        var storyIframe = $("<iframe>");
                        storyIframe.attr("src", storyUrl);
                        storyFull = $(
                          '<div class="story-iframe"><i class="close-button fa fa-times-circle" aria-hidden="true"></i></div>'
                        );
                        storyFull.attr("data-storyid", story.data("id"));
                        storyFull.append(storyIframe);
                        storyFull.appendTo(document.body);
                        storyFull.hide();

                        storyFull
                          .find(".close-button")
                          .on("click", function () {
                            storyFull.hide();
                          });

                        storyIframe.on("load", function () {
                          spinner.hide();
                          var jFrame = $(this);
                          var jFrameContent = jFrame
                            .contents()
                            .find("#site-content");
                          jFrameContent.siblings().hide();
                          jFrameContent.find("article.story").css({
                            "padding-top": "1.5em",
                            "padding-bottom": "1.5em",
                          });
                          storyFull.show();
                        });
                      } else {
                        storyFull.show();
                      }
                    }

                    return false;
                  });
              } else
                storyContent
                  .find(".story-actions .read-more")
                  .attr("href", storyUrl);
            }
          } else {
            storyContent = markerNode.children().clone();
          }

          var pixelOffset = new google.maps.Size(0, -10 - 100 * storyIndex);
          if (popup.infoWindow.get("pixelOffset") != pixelOffset) {
            popup.infoWindow.set("pixelOffset", pixelOffset);
            popup.infoWindow.close();
          }

          popup.infoWindow.setContent(storyContent.get(0));
          setTimeout(
            function (popup) {
              popup.infoWindow.open({
                anchor: marker,
                shouldFocus: false,
              });
              popup.visible = true;
            },
            250,
            popup
          );
        }
      }
      map.popupsCurrent += storyCount;
    },

    hideMarkerPopups: function (map) {
      if (map.popups) {
        for (var popupIndex = 0; popupIndex < map.popups.length; popupIndex++) {
          var popup = map.popups[popupIndex];
          popup.infoWindow.close();
          popup.visible = false;
        }

        map.popupsCurrent = 0;
      }
    },

    // Gathers all map places and creates a marker for each.
    getPlaces: function (map, $markers) {
      // Add markers.
      map.markers = [];
      $markers.each(function () {
        pol.map.createMarker($(this), map);
      });

      // Close the popups if clicking on the map.
      google.maps.event.addListener(map, "click", function () {
        pol.map.hideMarkerPopups(map);
        pol.map.closePopup(map);
      });
    },

    closePopup: function (map, resetZoom = false) {
      $("body").removeClass("popup-open");

      var lastLat = $("body").attr("data-lat"),
        lastLng = $("body").attr("data-lng"),
        lastZoom = $("body").attr("data-zoom");

      if (lastLat && lastLng) {
        var lastPos = new google.maps.LatLng(lastLat, lastLng);
        map.panTo(lastPos);
      }

      if (lastZoom && resetZoom) {
        map.setZoom(parseFloat(lastZoom));
      }
      // map.setZoom(1);
      $("body").attr("data-lat", "");
      $("body").attr("data-lng", "");
      $("body").attr("data-zoom", "");
      //pol.map.centerMap(map);
      if ($("body").hasClass("logged-in"))
        localStorage.removeItem("lastMarker");
      var previousPos = pol.map.getLocalStorage("mapsNonloggedprops");
      if (previousPos) {
        map.setCenter(new google.maps.LatLng(previousPos[0], previousPos[1]));
        map.setZoom(previousPos[2]);
      }
    },

    //Open Pop on Sidebar item click
    sidebarTriggerAutoPopUp: function (marker, map, urlParam) {
      pop(map, marker);
    },
    // Creates a marker for the given jQuery element and map.
    createMarker: function ($marker, map) {
      // Get position from marker.
      var lat = $marker.data("lat") || 52.8814742;
      var lng = $marker.data("lng") || 6.84929598;
      var mrkplaceid = $marker.data("markid");

      var latLng = {
        lat: parseFloat(lat),
        lng: parseFloat(lng),
      };

      var marker = $marker.data("color") || "green",
        markerColor =
          marker === "red"
            ? "#cc4916"
            : marker === "yellow"
            ? "#deb817"
            : "#1f990c";

      var markerUrl = $marker.data("mrkurl");

      //Previous used vector path
      // // if(iconOrImage){
      // var marker = new google.maps.Marker( {
      // 	position: latLng,
      // 	map: map,
      // 	icon: {
      // 		path: "M11,-0.018c-4.264,0 -7.712,3.449 -7.712,7.713c-0,5.784 7.712,14.323 7.712,14.323c0,-0 7.712,-8.539 7.712,-14.323c0,-4.264 -3.448,-7.713 -7.712,-7.713Zm0,10.467c-1.52,0 -2.754,-1.234 -2.754,-2.754c-0,-1.521 1.234,-2.755 2.754,-2.755c1.52,0 2.754,1.234 2.754,2.755c0,1.52 -1.234,2.754 -2.754,2.754Z",
      // 		fillColor: markerColor,
      // 		fillOpacity: 1,
      // 		strokeWeight: .75,
      // 		strokeColor: "white",
      // 		strokeOpacity: 1,
      // 		rotation: 0,
      // 		scale: 1.8,
      // 		anchor: new google.maps.Point(11, 22),
      // 	}
      // } );
      const iconObj = {
        url: markerUrl, // url
        scaledSize: new google.maps.Size(30, 30), // scaled size
        origin: new google.maps.Point(0, 0), // origin
        anchor: new google.maps.Point(11, 22), // anchor
      };
      var marker = new google.maps.Marker({
        position: latLng,
        map: map,
        icon: iconObj,
      });

      //add id to the marker
      marker.set("placeid", mrkplaceid);
      marker.set("element", $marker);
      // Append to reference for later use.
      map.markers.push(marker);
      // If marker contains HTML, add it to the infoWindow object.
      if ($marker.html()) {
        //open marker popups on sidebar item click
        pol.map.sidebarTriggerAutoPopUp(marker);
        pol.map.intializeAutocomplete(map, marker);

        // Show info window when marker is clicked.
        google.maps.event.addListener(marker, "click", function (args) {
          google.maps.event.clearListeners(marker, "mouseover");
          if ($("body").hasClass("logged-in")) {
            var historydata = [
              $("body").attr("data-uid"),
              marker.get("placeid"),
            ];
            localStorage.setItem("lastMarker", JSON.stringify(historydata));
          }
          var markerPos = this.getPosition(),
            mapZoom = map.getZoom();

          $("body").addClass("popup-open");
          $("body").attr("data-lat", markerPos.lat());
          $("body").attr("data-lng", markerPos.lng());
          $("body").attr("data-zoom", mapZoom);

          map.panTo(markerPos);
          if (mapZoom < 5) {
            map.setZoom(mapZoom + 2);
          }

          pol.map.hideMarkerPopups(map);
          pol.map.showMarkerPopups(marker, $marker, args);
        });

        /* Setting the local storage of the map to the current center and zoom level of the map. */
        google.maps.event.addListener(marker, "mouseover", function () {
          pol.map.setLocalStorage("mapsNonloggedprops", [
            map.getCenter().lat(),
            map.getCenter().lng(),
            map.getZoom(),
          ]);
        });
      }
    },
    getMarker: function (map, id) {
      var result = null;

      map.markers.forEach(function (marker) {
        var markerid = marker.get("placeid");

        if (id === markerid) {
          result = marker;
          return;
        }
      });

      return result;
    },

    // Centers the map showing all markers in view.
    centerMap: function (map) {
      // Create map boundaries from all map markers.
      var bounds = new google.maps.LatLngBounds();
      map.markers.forEach(function (marker) {
        bounds.extend({
          lat: marker.position.lat(),
          lng: marker.position.lng(),
        });
      });

      // Case: Single marker.
      if (map.markers.length == 1) {
        map.setCenter(bounds.getCenter());

        // Case: Multiple markers.
      } else {
        map.setCenter(bounds.getCenter());
        map.setZoom(3); // Change the zoom value as required
      }
    },

    //

    // Handles all ACF map functions.
    mapForm: function () {
      if (!window.acf || typeof acf.getFields !== "function") {
        return;
      }

      var fields = acf.getFields(),
        mapField,
        latField,
        lngField;

      if (!fields.length) {
        return;
      }

      // Our location fields.
      fields.forEach((field) => {
        if ("place_map" == field.data.name) {
          mapField = field;
        }
        if ("place_lat" == field.data.name) {
          latField = field;
        }
        if ("place_lng" == field.data.name) {
          lngField = field;
        }
      });

      if (!mapField && !latField && !lngField) {
        return;
      }

      // Functions to run when map is initialized.
      acf.addAction("google_map_init", function (map, marker, field) {
        // Set zoom if there's a value.
        if (field.val()) {
          pol.map.zoomMap(map);
        }

        // Updates the map on manual Lat field change.
        latField.on("change", 'input[type="number"]', function (e) {
          var newLat = parseFloat($(this).val()),
            currentLng = parseFloat(lngField.val());

          if (!currentLng) {
            return;
          }

          var newPos = {
            lat: newLat,
            lng: currentLng,
          };

          pol.map.updateMapMarkerPos(newPos, marker, map);
        });

        // Updates the map on manual Lng field change.
        lngField.on("change", 'input[type="number"]', function (e) {
          var newLng = parseFloat($(this).val()),
            currentLat = parseFloat(latField.val());

          if (!currentLat) {
            return;
          }

          var newPos = {
            lat: currentLat,
            lng: newLng,
          };

          pol.map.updateMapMarkerPos(newPos, marker, map);
        });
      });

      // Updates the Lat and Lng values when a map search result is accepted.
      acf.add_filter(
        "google_map_result",
        function (result, geocoderResult, map, field) {
          if (field.data("name") == "place_map") {
            if (!latField || !lngField) {
              return;
            }

            var lat = result.lat,
              lng = result.lng;

            if (lat && latField.val() !== lat) {
              latField.val(lat);
            }

            if (lng && lngField.val() !== lng) {
              lngField.val(lng);
            }
          }

          return result;
        }
      );

      // Set initial Lat / Lng values if field is empty (but we have a map marker)
      var mapVal = mapField.val();

      if (mapVal) {
        if (!latField.val() && mapVal.lat) {
          latField.val(mapVal.lat);
        }
        if (!lngField.val() && mapVal.lng) {
          lngField.val(mapVal.lng);
        }
      }
    },

    // Update map marker position.
    updateMapMarkerPos: function (newPos, marker, map) {
      marker.setPosition(newPos);
      map.setCenter(newPos);
      pol.map.zoomMap(map);
    },

    // Update map zoom level.
    zoomMap: function (map, level = 15) {
      map.setZoom(level);
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  FORMS
	/* ------------------------------------------------------------------------------ */

  pol.forms = {
    init: function () {
      if (!window.acf || typeof window.acf == "undefined") {
        return;
      }

      pol.forms.imageUploader();
      pol.forms.multiseclectorOPtions();
      pol.forms.mapSettings();
    },

    nextGeoCode: function (lat, lng) {
      var googlemapurl =
        "https://maps.googleapis.com/maps/api/geocode/json?latlng=" +
        lat +
        "," +
        lng +
        "&key=AIzaSyASjYF9QSfmERIuCuLv1X9PSglIo7QRVkM";
      $.ajax({
        url: googlemapurl,
        type: "GET",
        success: function (res) {
          if (res.status == "OK") {
            var address;
            if (res.results.length > 1) {
              address = res.results[2].formatted_address;
            } else {
              address = res.results[0].formatted_address;
            }

            $(".pac-target-input").val(address);
            var updating_val = $(".acf-google-map>input").val();
            var jsonObj = JSON.parse(updating_val);
            jsonObj.address = $(".pac-target-input").val();
            $(".acf-google-map>input").val(JSON.stringify(jsonObj));
          } else {
            alert("Some Error Occured.Please try reloading the page!!");
          }
        },
        error: function (jqXHR, exception) {
          polAjaxErrors(jqXHR, exception);
        },
      });
    },

    mapSettings: function () {
      acf.add_filter(
        "google_map_result",
        function (result, geocoderResult, map, field) {
          if (result.address.indexOf("+") !== -1) {
            pol.forms.nextGeoCode(result.lat, result.lng);
            var val = $(".pac-target-input").val();
            result.address = val;
          }
          return result;
        }
      );
    },

    // Handle the image uploader button.
    imageUploader: function () {
      // acf.addAction('ready_field/name=story_featured_image', function( field ){

      // });

      acf.addAction("ready_field/name=story_featured_image", function (field) {
        var $actions = field.$el.find(".show-if-value .acf-actions"),
          $uploader = field.$el.find(".acf-basic-uploader");

        if (!$actions.length || !$uploader.length) {
          return;
        }

        var $fileInput = $uploader.find('input[type="file"]'),
          cancelBtn = $actions.html();

        // Add the actions button to the uploader.
        $uploader.append(cancelBtn);

        field.on("click", ".acf-basic-uploader a", function (e) {
          e.preventDefault();
          field.$el.removeClass("has-image");
          $fileInput.val("");
        });

        // Change class when a file has been uploaded.
        field.on("change", 'input[type="file"]', function (e) {
          const imageFile = e.target.files;

          if ($(this).val() && $(this).val() !== "") {
            field.$el.addClass("has-image");
          } else {
            field.$el.removeClass("has-image");
          }
        });
        //   acf.add_filter('select2_args', function( args, $select, settings, field, instance ){
        // 	if(field.prop('id')== 'finish-story-select'){
        // 		args.tags = true
        // 	}
        // 	return args;
        // });
      });
    },

    multiseclectorOPtions: function () {
      acf.add_filter(
        "select2_args",
        function (args, $select, settings, field, instance) {
          if (field.prop("id") == "finish-story-select") {
            args.tags = true;
          }
          return args;
        }
      );
    },
  };

  /* ------------------------------------------------------------------------------ /*
	/*  INIT
	/* ------------------------------------------------------------------------------ */
  $polDoc.ready(function () {
    pol.intervalScroll.init();
    pol.toggles.init();
    pol.coverModals.init();
    pol.elementInView.init();
    pol.responsiveEmbeds.init();
    pol.stickyHeader.init();
    pol.scrollLock.init();
    pol.navMenus.init();
    pol.focusManagement.init();
    pol.loadMore.init();
    pol.filters.init();
    pol.grid.init();
    pol.dynamicHeights.init();
    pol.fadeOnScroll.init();
    pol.smoothScroll.init();
    pol.map.init();
    pol.forms.init();
    //pol.setCustomCookie.init();

    cssVars(); // css-vars-ponyfill
  });

  /* ------------------------------------------------------------------------------ /*
	/*  nav-togg
	/* ------------------------------------------------------------------------------ */

  // const hamburgerr = document.querySelector(".hamburgerr")

  // hamburgerr.addEventListener("click", () =>{
  // 	hamburgerr.classList.toggle("active");
  // })

  jQuery(document).ready(function () {
    //jQuery("#pol-map-search-input").insertBefore(jQuery(".main-menu .ancestor-wrapper"));
    // MOSTRANDO Y OCULTANDO MENU
    jQuery("#button-menu").click(function () {
      jQuery(".navegacion #infinite-list").toggleClass("main-menu-opened");
      if (!jQuery(".navegacion #infinite-list").hasClass("main-menu-opened")) {
        jQuery(".navegacion #infinite-list")
          .find("li")
          .removeClass("sub-menu-opened");
        // jQuery('.navegacion .menu > .item-submenu a').parent().parent().removeClass('sub-menu-opened');
      }
      if ($("#button-menu").attr("class") == "hamburgerr") {
        $(".navegacion #infinite-list").css({ right: "0px" }); // Mostramos el menu
        //$('.navegacion #infinite-list').addClass('main-menu-opened');
      } else {
        jQuery(".navegacion .submenu").css({ right: "-320px" }); // Ocultamos los submenus
        jQuery(".navegacion .menu").css({ right: "-320px" }); // Ocultamos el Menu
      }
    });

    // MOSTRANDO SUBMENU
    jQuery(".navegacion .menu > .item-submenu a").click(function () {
      //jQuery('.navegacion .menu > .item-submenu').addClass('sub-menu-opened');
      var positionMenu = jQuery(this).parent().attr("menu"); // Buscamos el valor del atributo menu y lo guardamos en una variable
      jQuery(this).parent().toggleClass("sub-menu-opened");
      jQuery(".item-submenu[menu=" + positionMenu + "] .submenu").css({
        right: "0px",
      }); // Mostramos El submenu correspondiente
    });

    // OCULTANDO SUBMENU
    jQuery(".navegacion .submenu li.go-back").click(function () {
      //jQuery('.navegacion .menu > .item-submenu').removeClass('sub-menu-opened');
      jQuery(this).parent().parent().removeClass("sub-menu-opened");
      jQuery(this).parent().css({ right: "-320px" }); // Ocultamos el submenu
      //jQuery(this).parent()
    });
  });

  //========================lists page js start vile goat===========================
  jQuery(document).ready(function () {
    //check the correct toggle button on page load
    if (document.getElementsByClassName("gp-toggle-wrapper").length > 0) {
      var currPageUrl = window.location.href;
      if (currPageUrl.toLocaleLowerCase().includes("/map")) {
        document.getElementById("gp-toggle-right").checked = true;
      } else if (currPageUrl.toLocaleLowerCase().includes("/list")) {
        document.getElementById("gp-toggle-left").checked = true;
      }

      document.getElementById("gp-toggle-right").onclick = function () {
        if (
          this.checked == true &&
          !currPageUrl.toLocaleLowerCase().includes("/map")
        ) {
          window.location = "/map/";
        }
      };

      document.getElementById("gp-toggle-left").onclick = function () {
        if (
          this.checked == true &&
          !currPageUrl.toLocaleLowerCase().includes("/list")
        ) {
          window.location = "/list/";
        }
      };
      document.getElementById("gp-toggle-middle").onclick = function () {
        if (
          this.checked == true &&
          !currPageUrl.toLocaleLowerCase().includes("/list")
        ) {
          window.location = "/list";
        }
      };
    }

    //for load more
    for (var i = 1000; i < 1008; i++) {
      //hide the end of story message
      jQuery("#load-more-end-" + i).css("display", "none");

      if (jQuery("#load-more-stories-" + i).length) {
        jQuery("#load-more-stories-" + i).click(
          { curr_index: i },
          function (event) {
            var listViewIndividualContainer =
              "#gp-infinite-story-" + event.data.curr_index;

            ajaxLoadMorePostsForListView(
              event.data.curr_index,
              "#load-more-stories-" + event.data.curr_index,
              listViewIndividualContainer
            );

            jQuery(this).html(
              'Loading <i class="fa fa-spinner fa-spin gp-list-view-page-spinner"></i>'
            );
            jQuery(this).prop("disabled", true);
          }
        );
      }
    }

    function ajaxLoadMorePostsForListView(
      currIndex,
      theloadmorebtn,
      listViewIndividualContainer
    ) {
      var value = jQuery(theloadmorebtn).attr("data-selected");
      var paged = jQuery(theloadmorebtn).attr("data-paged");
      var maxpage = jQuery(theloadmorebtn).attr("data-maxpage");
      var actionFunction =
        currIndex == 1006 || currIndex == 1007
          ? "pol_get_internet_and_book_stories"
          : "pol_lists_view_individual_story_generator";

      if (currIndex == 1006 || currIndex == 1007) {
        //for internet and book
        jQuery.ajax({
          type: "post",
          url: pol_ajax_load_more.ajaxurl,
          data: {
            action: actionFunction,
            page: parseInt(paged) + 1,
            value: value,
            section_id: currIndex,
          },
          success: function (response) {
            jQuery(listViewIndividualContainer).append(response);
            jQuery(theloadmorebtn).attr("data-paged", parseInt(paged) + 1);
            paged = parseInt(jQuery(theloadmorebtn).attr("data-paged")) + 1;

            if (parseInt(paged) === parseInt(maxpage)) {
              console.log("bitra chiryo:::" + currIndex);
              jQuery("#load-more-stories-" + currIndex).hide();
            }

            jQuery(theloadmorebtn).html("Load More");
            jQuery(theloadmorebtn).prop("disabled", false);
          },
        });
      } else {
        // if (parseInt(paged) != parseInt(maxpage)) {
        jQuery.ajax({
          type: "post",
          url: pol_ajax_load_more.ajaxurl,
          data: {
            action: actionFunction,
            page: parseInt(paged) + 1,
            value: value,
            section_id: currIndex,
          },
          success: function (response) {
            jQuery(listViewIndividualContainer).append(response);
            jQuery(theloadmorebtn).attr("data-paged", parseInt(paged) + 1);

            if (parseInt(paged) + 1 == parseInt(maxpage)) {
              console.log("bitra chiryo:::" + currIndex);
              jQuery("#load-more-stories-" + currIndex).hide();
            }

            paged = parseInt(jQuery(theloadmorebtn).attr("data-paged")) + 1;

            jQuery(theloadmorebtn).html("Load More");
            jQuery(theloadmorebtn).prop("disabled", false);
          },
        });
        // } else {
        // 	//remove load more button
        // 	document.querySelector('#load-more-stories-' + currIndex).remove();

        // 	//show the end of story message
        // 	// jQuery('#load-more-end-' + currIndex).css('display', 'block');
        // }
      }
    }

    //refresh the random stories
    jQuery("#random-story-refresh-btn").on("click", function () {
      jQuery.ajax({
        type: "post",
        url: pol_ajax_load_more.ajaxurl,
        data: {
          action: "pol_refresh_random_stories",
        },
        beforeSend: function (response) {
          //clear the stories
          jQuery("#gp-infinite-story-1005 .gp-story-list-row").remove();

          //add spinner
          jQuery("#gp-infinite-story-1005").html(
            '<i class="fa fa-spinner fa-spin gp-list-view-page-spinner" style="font-size: 3rem;"></i>'
          );

          //spin the refresh button
          jQuery("#random-story-refresh-btn").css(
            "animation",
            "spin 1s linear infinite"
          );
        },
        success: function (response) {
          //stop spinning the refresh button
          jQuery("#random-story-refresh-btn").css("animation", "");

          if (response != "") {
            //add stories
            jQuery("#gp-infinite-story-1005").html(response);
          } else {
            //display
            jQuery("#gp-infinite-story-1005").html(
              '<p class="gp-end-of-story">Something went wrong while loading random stories, please try again !</p>'
            );
          }
        },
      });
    });

    // setup before functions
    var authorTypingTimer,
      locationTypingTimer,
      internetTypingTimer,
      bookTypingTimer;
    var authorDoneTypingInterval =
      (locationDoneTypingInterval =
      internetDoneTypingInterval =
      bookDoneTypingInterval =
        500);
    var authorSearchInput = $("#search-author"),
      locationSearchInput = $("#search-location"),
      internetSearchInput = $("#search-internet"),
      bookSearchInput = $("#search-book");

    //on keyup, start the countdown
    authorSearchInput.on("keyup", function (e) {
      clearTimeout(authorTypingTimer);
      authorTypingTimer = setTimeout(
        authorSearchAjax,
        authorDoneTypingInterval
      );

      if (e.keyCode == 8 || e.keyCode == 46) {
        clearTimeout(authorTypingTimer);
        authorTypingTimer = setTimeout(
          authorSearchAjax,
          authorDoneTypingInterval
        );
      }
    });

    //on keydown, clear the countdown
    authorSearchInput.on("keydown", function () {
      clearTimeout(authorTypingTimer);
    });

    //user is "finished typing," do something
    function authorSearchAjax() {
      updateAuthorListOnSearch(
        authorSearchInput.val(),
        $("#curr-section-auth").attr("value")
      );
    }

    function updateAuthorListOnSearch(input, section) {
      jQuery.ajax({
        type: "post",
        url: pol_ajax_load_more.ajaxurl,
        data: {
          action: "pol_live_search_author_and_location",
          search_query: input,
          search_type: "author",
          section_name: section,
        },
        beforeSend: function (response) {
          //if no spinner present, add a spinner
          if (jQuery(".gp-author-lists-1001 ul i").length == 0) {
            jQuery(".gp-author-lists-1001 ul").append(
              '<i class="fa fa-spinner fa-spin gp-list-view-page-spinner"></i>'
            );
          }

          //remove result
          jQuery(".gp-author-lists-1001 ul li").remove();
        },
        success: function (response) {
          if (response != "") {
            //add result
            jQuery(".gp-author-lists-1001 ul").html(response);
          } else {
            jQuery(".gp-author-lists-1001 ul").html(
              '<p class="gp-end-of-story">No author found</p>'
            );
          }
        },
      });
    }

    //on keyup, start the countdown
    locationSearchInput.on("keyup", function (e) {
      clearTimeout(locationTypingTimer);
      locationTypingTimer = setTimeout(
        locationSearchAjax,
        locationDoneTypingInterval
      );

      //for delte and backspace
      if (e.keyCode == 8 || e.keyCode == 46) {
        clearTimeout(locationTypingTimer);
        locationTypingTimer = setTimeout(
          locationSearchAjax,
          locationDoneTypingInterval
        );
      }
    });

    //on keydown, clear the countdown
    locationSearchInput.on("keydown", function () {
      clearTimeout(locationTypingTimer);
    });

    //user is "finished typing," do something
    function locationSearchAjax() {
      updateLocationListOnSearch(
        locationSearchInput.val(),
        $("#curr-section-loc").attr("value")
      );
    }

    function updateLocationListOnSearch(input, section) {
      jQuery.ajax({
        type: "post",
        url: pol_ajax_load_more.ajaxurl,
        data: {
          action: "pol_live_search_author_and_location",
          search_query: input,
          search_type: "location",
          section_name: section,
        },
        beforeSend: function (response) {
          //if no spinner present, add a spinner
          if (jQuery(".gp-author-lists-1002 ul i").length == 0) {
            jQuery(".gp-author-lists-1002 ul").append(
              '<i class="fa fa-spinner fa-spin gp-list-view-page-spinner"></i>'
            );
          }

          //remove result
          jQuery(".gp-author-lists-1002 ul li").remove();
        },
        success: function (response) {
          // console.log('location ajax');
          if (response != "") {
            jQuery(".gp-author-lists-1002 ul").html(response);
          } else {
            jQuery(".gp-author-lists-1002 ul").html(
              '<p class="gp-end-of-story">No location found</p>'
            );
          }
        },
      });
    }

    //on keyup, start the countdown
    internetSearchInput.on("keyup", function (e) {
      clearTimeout(internetTypingTimer);
      internetTypingTimer = setTimeout(
        internetSearchAjax,
        internetDoneTypingInterval
      );

      //for delte and backspace
      if (e.keyCode == 8 || e.keyCode == 46) {
        clearTimeout(internetTypingTimer);
        internetTypingTimer = setTimeout(
          internetSearchAjax,
          internetDoneTypingInterval
        );
      }
    });

    //on keydown, clear the countdown
    internetSearchInput.on("keydown", function () {
      clearTimeout(internetTypingTimer);
    });

    function internetSearchAjax() {
      if (internetSearchInput.val().length === 0) {
        jQuery.ajax({
          type: "post",
          url: pol_ajax_load_more.ajaxurl,
          data: {
            action: "pol_get_internet_and_book_stories",
            value: "internet",
            section_id: "1006",
          },
          success: function (response) {
            //remove the no story found
            jQuery("#gp-infinite-story-1006 .gp-end-of-story").remove();

            jQuery("#gp-infinite-story-1006 .gp-story-list-row").remove();
            jQuery("#gp-infinite-story-1006").append(response);
            jQuery("#load-more-stories-1006").show();
            jQuery("#load-more-stories-1006").attr("data-paged", 0);
          },
          error: function (response) {
            console.log(response);
          },
        });
      } else {
        updateInternetListOnSearch(
          internetSearchInput.val(),
          $("#curr-section-internet").attr("value")
        );

        //add the loading animation
        jQuery("#gp-infinite-story-1006").append(
          '<i class="fa fa-spinner fa-spin gp-list-view-page-spinner"></i>'
        );
      }
    }

    function updateInternetListOnSearch(input, section) {
      jQuery.ajax({
        type: "post",
        url: pol_ajax_load_more.ajaxurl,
        data: {
          action: "pol_live_search_internet_and_book",
          search_query: input,
          search_type: "internet",
          section_name: section,
        },
        beforeSend: function (response) {
          //remove the no story found
          jQuery("#gp-infinite-story-1006 .gp-end-of-story").remove();
        },
        success: function (response) {
          //remove the stories
          jQuery("#gp-infinite-story-1006 .gp-story-list-row").remove();

          //hide load more button
          jQuery("#load-more-stories-1006").hide();

          //remove the spinner
          jQuery("#gp-infinite-story-1006 i").remove();

          //append response
          jQuery("#gp-infinite-story-1006").append(response);

          if (response == "") {
            //show no stories found
            jQuery("#gp-infinite-story-1006").append(
              '<p class="gp-end-of-story">No story found</p>'
            );
          }

          if (input == "") {
            jQuery("#gp-infinite-story-1006 .gp-end-of-story").remove();
          }
        },
      });
    }

    //on keyup, start the countdown
    bookSearchInput.on("keyup", function (e) {
      clearTimeout(bookTypingTimer);
      bookTypingTimer = setTimeout(bookSearchAjax, bookDoneTypingInterval);

      //for delte and backspace
      if (e.keyCode == 8 || e.keyCode == 46) {
        clearTimeout(bookTypingTimer);
        bookTypingTimer = setTimeout(bookSearchAjax, bookDoneTypingInterval);
      }
    });

    //on keydown, clear the countdown
    bookSearchInput.on("keydown", function () {
      clearTimeout(bookTypingTimer);
    });

    function bookSearchAjax() {
      if (bookSearchInput.val().length === 0) {
        jQuery.ajax({
          type: "post",
          url: pol_ajax_load_more.ajaxurl,
          data: {
            action: "pol_get_internet_and_book_stories",
            value: "book",
            section_id: "1007",
          },
          success: function (response) {
            //remove the no story found
            jQuery("#gp-infinite-story-1007 .gp-end-of-story").remove();

            jQuery("#gp-infinite-story-1007 .gp-story-list-row").remove();
            jQuery("#gp-infinite-story-1007").append(response);
            jQuery("#load-more-stories-1007").show();
            jQuery("#load-more-stories-1007").attr("data-paged", 0);
          },
          error: function (response) {
            console.log(response);
          },
        });
      } else {
        updateBookListOnSearch(
          bookSearchInput.val(),
          $("#curr-section-book").attr("value")
        );

        //add the loading animation
        jQuery("#gp-infinite-story-1007").append(
          '<i class="fa fa-spinner fa-spin gp-list-view-page-spinner"></i>'
        );
      }
    }

    function updateBookListOnSearch(input, section) {
      jQuery.ajax({
        type: "post",
        url: pol_ajax_load_more.ajaxurl,
        data: {
          action: "pol_live_search_internet_and_book",
          search_query: input,
          search_type: "book",
          section_name: section,
        },
        beforeSend: function (response) {
          //remove the no story found
          jQuery("#gp-infinite-story-1007 .gp-end-of-story").remove();
        },
        success: function (response) {
          //remove the stories
          jQuery("#gp-infinite-story-1007 .gp-story-list-row").remove();

          //hide load more button
          jQuery("#load-more-stories-1007").hide();

          //remove the spinner
          jQuery("#gp-infinite-story-1007 i").remove();

          //append response
          jQuery("#gp-infinite-story-1007").append(response);

          if (response == "") {
            //show no stories found
            jQuery("#gp-infinite-story-1007").append(
              '<p class="gp-end-of-story">No story found</p>'
            );
          }

          if (input == "") {
            jQuery("#gp-infinite-story-1007 .gp-end-of-story").remove();
          }
        },
      });
    }

    //search featrure in list view
    if (document.getElementsByClassName("page-id-15629").length > 0) {
      document
        .getElementsByClassName("gp-story-listings")[0]
        .addEventListener("click", function () {
          document.getElementById("ui-id-1").style.display = "none";
        });

      document
        .getElementById("site-header")
        .addEventListener("click", function () {
          document.getElementById("ui-id-1").style.display = "none";
        });

      $("#pol-map-search-input").on("input", function () {
        console.log('+++++++');
        $.ajax({
          url: pol_ajax_load_more.ajaxurl,
          type: "post",
          dataType: "json",
          data: {
            action: "pol_search_data_fetch",
            search: this.value,
          },
          beforeSend: function () {
            $(".inputcontainer").css("display", "");
          },
          success: function (response) {
            // console.log(response);
            $(".inputcontainer").css("display", "none");
            $("#ui-id-1").css("display", "");

            var response_elements = "";
            response.forEach((element, index) => {
              response_elements += `
								<li class="ui-menu-item">
									<div data-posttype="story" data-storyid="${element.post_id}" data-marker="${
                element.id
              }" tabindex="-1" class="ui-menu-item-wrapper pol-search-list" id="ui-id-${
                index + 2
              }">
										<a href="${element.perma_link}">
											${element.label}
										</a>
									</div>
								</li>
							`;
            });

            $(".pol-search-container").html(response_elements);
          },
        });
      });
    }
  });
  //========================lists page js end=============================
})(jQuery);

// like unlike jquery
// jQuery(document).ready(function ($) {
//   $(".like-uploaded-story").on("click", function () {
//     var $button = $(this);
//     var storyId = $button.data("story-id");

//     // Toggle between classes
//     if ($button.hasClass("like-uploaded-story")) {
//       // If it has the 'like-uploaded-story' class, toggle to 'liked-uploaded-story'
//       $button
//         .removeClass("like-uploaded-story")
//         .addClass("liked-uploaded-story");
//       // You can perform additional actions here, such as sending an AJAX request to update the server.
//       // Example: updateServer(storyId, 'like');
//     } else {
//       // If it has the 'liked-uploaded-story' class, toggle to 'like-uploaded-story'
//       $button
//         .removeClass("liked-uploaded-story")
//         .addClass("like-uploaded-story");
//       // You can perform additional actions here, such as sending an AJAX request to update the server.
//       // Example: updateServer(storyId, 'unlike');
//     }
//   });
// });
