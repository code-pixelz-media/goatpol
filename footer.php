<?php

/**
 * The footer.
 *
 * @package GOAT PoL
 */

$add_story_path = get_page_by_path('add-story');

// Footer content.
get_template_part('template-parts/global/site-footer');
generateToggleTabs();

// Popups


// Popups
get_template_part('template-parts/popup/popup');


get_template_part('template-parts/popup/menu');

get_template_part('template-parts/popup/draftcheck');

wp_footer();
?>

<div id="getPassport-options-2" class="modal">

    <div class="container">
        <div class="row">
            <div class="col-lg-12">

                <div class="wp-block-image is-style-no-vertical-margin">
                    <?php pol_the_random_goat('popup-goat'); ?>
                </div>
                <span>
                    <h4>Welcome to The GOAT PoL—The Geographical Open Atlas of The Polity of Literature.</h4> The
                    GOAT
                    PoL
                    is free and open to everyone who wants to read and write. You can post your own writing,
                    read work by others, take part in free group workshops, or work one-on-one with one of our
                    ten Reader/Advisor/Editors (RAEs) to develop and publish your writing. We pay writers to
                    work with us, $70 (Canadian) for each piece we publish. To learn more, read our <span> <a
                            href="/about">About Page. </a></span> <br>
                    <span class="getPassport-options-2-links">
                        <a href="" class="option2-close-modal">No thanks, I'm
                            just looking</a> <br>
                        <a href="" class="option2-open-nextPopup">Yes, I want to participate</a>
                    </span>
                </span>
            </div>
        </div>
    </div>
</div>
<div id="getPassport-options-commission-question" class="modal">

    <div class="container">
        <div class="row">
            <div class="col-lg-12">

                <div class="wp-block-image is-style-no-vertical-margin">
                    <?php pol_the_random_goat('popup-goat'); ?>
                </div>
                <span>
                    <!-- <h4>Have you made your contributor’s page yet? If not, <a href="/register">Register here</a>
                </h4> -->
                    <h6>You can find commission under <a href="/registration/#commissions-profile-page">here</a>
                    </h6>
                </span>

            </div>
        </div>
    </div>
</div>


<div id="menu-popup-content"
    class="adp-popup pop2 adp-popup-type-content adp-popup-location-center adp-preview-image-none adp-preview-image-no"
    data-limit-display="0" data-limit-lifetime="30" data-open-trigger="" data-open-delay-number="0"
    data-open-scroll-position="10" data-open-scroll-type="%" data-open-manual-selector="a[href^='#tellusyourstory']"
    data-close-trigger="none" data-close-delay-number="30" data-close-scroll-position="10"
    data-close-scroll-type="%" data-open-animation="popupOpenSlideFade" data-exit-animation="popupExitSlideFade"
    data-light-close="false" data-overlay="true" data-mobile-disable="false" data-body-scroll-disable="true"
    data-overlay-close="false" data-esc-close="" data-f4-close="false">
    <div class="adp-popup-wrap" style="height: 40%;">
        <div class="adp-popup-container">
            <div class="adp-popup-outer">
                <div class="adp-popup-content" style="padding: 50px 24px;">
                    <div class="adp-popup-inner">
                        <div class="has-text-align-center wp-block-image is-style-no-vertical-margin"
                            style="font-size: 26px;text-align:center">
                            <h2>
                                <?php
                                echo get_field('cpm_gr_popup_title', 'option');
                                ?>
                            </h2>
                        </div>
                        <div class="wp-block-image is-style-no-vertical-margin">
                            <?php pol_the_random_goat('popup-goat'); ?>
                        </div>
                        <div>
                            <p class="has-text-align-center has-h5-font-size has-h6-line-height">
                                <?php echo get_field('cpm_gr_main_content', 'option'); ?>
                            </p>
                        </div>
                        <div style="text-align:center;">
                            <p class="has-text-align-center has-h5-font-size has-h6-line-height">
                                <?php echo get_field('cpm_gr_before_close_btn', 'option'); ?>
                            </p>
                            <button type="button" class="menu-popup-close cbtn-ground-rules">
                                <?php echo get_field('cpm_gr_close_btn_label', 'option'); ?>
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div id="ground-rules-2" class="modal">
    <div class="has-text-align-center wp-block-image is-style-no-vertical-margin"
        style="font-size: 26px;text-align:center">
        <h2>
            Ground Rules
        </h2>
    </div>
    <div class="wp-block-image is-style-no-vertical-margin">
        <?php echo pol_get_random_goat(); ?>
    </div>
    <div>
        <p class="p1"><span class="s1"><strong>A polity is a group of people who respect and
                    support one another as equals</strong>. Difference is celebrated and
                engaged. We relinquish power over others to find the potentials of collectivity.
                The GOAT PoL is a polity comprised of reading and writing. <strong>It is a
                    polity</strong> of literature and <strong>not a market</strong> for
                literature.</span></p>
        <p class="p1"><span class="s1">To maintain good relationships in the polity, <strong>The
                    GOAT PoL has ground rules</strong>. Please read them and take them
                seriously. Without agreement to these ground rules, we cannot be a polity. The
                GOAT PoL ground rules are:</span></p>
        <p class="p1"><span class="s1">(1) Everyone is honest. No lying. No hiding the actions
                you take. No false claims about yourself or your work.</span></p>
        <p class="p1"><span class="s1">(2) One-commission-at-a-time. Every writer is limited to
                working on one-commission-at-a-time. If you have several pseudonyms (made-up
                names you write under) don't pursue commissions for more than
                one-at-a-time.</span></p>
        <p class="p1"><span class="s1">(3) One account only: do not open multiple accounts at
                The GOAT PoL. Use one account only for all your writing and reading.</span></p>
        <p class="p1"><span class="s1">(4) In the work place we cultivate and maintain mutual
                respect: ask, don't demand; disagree with respect, don't belittle or dismiss;
                listen and try to understand, even if you disagree.</span></p>
        <p class="p1"><span class="s1">(5) No stealing—if you submit work written by someone
                else and claim it is your own work, we can't work with you.</span></p>

        <strong>If you’re still interested, please click “close” below</strong>, confirming that you understand and
        want to continue working in The GOAT PoL.
        By clicking “close” I confirm that I understand and I want to continue participating in The GOAT PoL.
    </div>
    <div style="text-align:center;">
        <p class="has-text-align-center has-h5-font-size has-h6-line-height"></p>
        <button type="button" rel="modal:close" class="close-modal ground-rules-2-close cbtn-ground-rules">
            Close
        </button>
    </div>
</div>


<div id="ground-rules-3" class="modal">
    <div class="has-text-align-center wp-block-image is-style-no-vertical-margin"
        style="font-size: 26px;text-align:center">
        <h2>
            Ground Rules
        </h2>
    </div>
    <div class="wp-block-image is-style-no-vertical-margin">
        <?php echo pol_get_random_goat(); ?>
    </div>
    <div>
        <p class="p1"><span class="s1"><strong>A polity is a group of people who respect and
                    support one another as equals</strong>. Difference is celebrated and
                engaged. We relinquish power over others to find the potentials of collectivity.
                The GOAT PoL is a polity comprised of reading and writing. <strong>It is a
                    polity</strong> of literature and <strong>not a market</strong> for
                literature.</span></p>
        <p class="p1"><span class="s1">To maintain good relationships in the polity, <strong>The
                    GOAT PoL has ground rules</strong>. Please read them and take them
                seriously. Without agreement to these ground rules, we cannot be a polity. The
                GOAT PoL ground rules are:</span></p>
        <p class="p1"><span class="s1">(1) Everyone is honest. No lying. No hiding the actions
                you take. No false claims about yourself or your work.</span></p>
        <p class="p1"><span class="s1">(2) One-commission-at-a-time. Every writer is limited to
                working on one-commission-at-a-time. If you have several pseudonyms (made-up
                names you write under) don't pursue commissions for more than
                one-at-a-time.</span></p>
        <p class="p1"><span class="s1">(3) One account only: do not open multiple accounts at
                The GOAT PoL. Use one account only for all your writing and reading.</span></p>
        <p class="p1"><span class="s1">(4) In the work place we cultivate and maintain mutual
                respect: ask, don't demand; disagree with respect, don't belittle or dismiss;
                listen and try to understand, even if you disagree.</span></p>
        <p class="p1"><span class="s1">(5) No stealing—if you submit work written by someone
                else and claim it is your own work, we can't work with you.</span></p>

        <strong>If you’re still interested, please click “close” below</strong>, confirming that you understand and
        want to continue working in The GOAT PoL.
        By clicking “close” I confirm that I understand and I want to continue participating in The GOAT PoL.
    </div>
    <div style="text-align:center;">
        <p class="has-text-align-center has-h5-font-size has-h6-line-height"></p>
        <button type="button" rel="modal:close" class="close-modal ground-rules-3-close cbtn-ground-rules">
            Close
        </button>
    </div>
</div>
</body>

</html>