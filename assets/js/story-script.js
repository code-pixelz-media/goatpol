(function ($) {



    //check
    $(document).ready(function ($) {
        if (!($('body').hasClass('post-new-php') && $('body').hasClass('post-type-story'))) {
            return;
        }

        // Intercept the form submission
        $('#post').on('keypress', function(event) {
            if (event.which === 13) {
                event.preventDefault();
            }
        });

        function disable_publish_and_draft_btns() {
            $('#publish').prop('disabled', true);
            $('#save-post').prop('disabled', true);
            $('#post-preview').attr('disabled', 'disabled');
            $('#post-preview').css('pointer-events', 'none');
        }

        function enable_publish_and_draft_btns() {
            $('#publish').prop('disabled', false);
            $('#save-post').prop('disabled', false);
            $('#post-preview').removeAttr('disabled');
            $('#post-preview').css('pointer-events', '');
        }

        function check_if_commission_is_valid() {
            let checkBtn = $('#check-commission-availability');
            let errMsg = $('.invalid-commission');
            let author_id = checkBtn.attr('data-author');
            let commission = checkBtn.attr('data-commission');

            // console.log(author_id);
            // console.log(commission);

            checkBtn.html('Checking...');
            checkBtn.prop('disabled', true);

            $.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: "pol_check_if_commission_is_valid",
                    author_id: author_id,
                    commission: commission
                },
                success: function (response) {
                    checkBtn.html('Check');
                    checkBtn.prop('disabled', false);

                    let is_valid_commission = response.data;
                    console.log(is_valid_commission);
                    if(is_valid_commission){
                        errMsg.html('The commission you entered is valid !!');
                        enable_publish_and_draft_btns();
                    }else{
                        errMsg.html('The commission you entered is invalid !!');
                    }
                },
            });
        }


        //===========================
        //===========================
        //===========================


        disable_publish_and_draft_btns();

        //disable check btn
        let checkBtn = $('#check-commission-availability');
        checkBtn.prop('disabled', true);

        //get the value of current author selected and put it as attribute in thhe check button
        let currentAuthorId = $('#post_author_override').val();
        checkBtn.attr('data-author',currentAuthorId);


        $('#commission_field').on('input', function () {
            
            disable_publish_and_draft_btns();

            if ($(this).val().length == 12) {
                checkBtn.attr('data-commission',$(this).val());
                checkBtn.prop('disabled', false);
            } else {
                checkBtn.attr('data-commission','');
                checkBtn.prop('disabled', true);
            }
        });

        $('#check-commission-availability').on('click', function () {
            check_if_commission_is_valid();
        });

        $('#post_author_override').on('change', function() {
            let currentAuthorId = $('#post_author_override').val();
            checkBtn.attr('data-author',currentAuthorId);
        });

    });

})(jQuery);