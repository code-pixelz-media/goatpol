<?php

 ?>
 <?php get_header(); ?>

<style>
  * {
    margin: 0;
    padding: 0;
  }
  body {
      overflow-y: hidden;
  }
  .site-logo {
    max-width: 980px;
    margin: 0 auto;
    padding-top: 30px;
  }
  .container {
    max-width: 980px;
    margin: 0 auto;
    height: 100vh;
    padding-top: 180px;
    text-align: center;
  }
  a {
    color: #0f0f0f;
    text-decoration: none;
  }
  .container h1 {
    font-size: 30px;
    font-weight: normal;
  }
  .logo-logotype {
    margin-top: 100px;
  }
  .header-navigation{
    display:none !important;
  }
</style>
<a href="<?php //echo  home_url(); ?>" id="landing-page-2">
  <body>
  
<div class="container">
    
  <div class="section-inner">
  <h1>In a polity we all have agency and equality. We give what we wish to
take and commit ourselves to common purpose, even amidst conflict. Conflict is welcome
because none are foreign or can ever be kicked out. Please come write and read with us
in a polity of literature.</h1>
    
    
    <img class="logo-logotype" src="<?php echo site_url(); ?>wp-content/themes/goatpol/assets/img/FullTitle_transparent.png"  style="display:inline;"  width="678" height="39" alt="The GOAT PoL">
  </div>
</div>
</body>
</a>


<script>
  //   setTimeout(function(){
  //  window.location.href = '<?php echo home_url('/map'); ?>';
  //  }, 15000);
</script>
<?php wp_footer(); ?>