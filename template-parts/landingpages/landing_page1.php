
 <?php wp_head(); ?>
<style>
  * {
    margin: 0;
    padding: 0;
  }
  body {
      overflow-y: hidden;
  }
  .container {
    max-width: 980px;
    margin: 0 auto;
    height: 100vh;
    padding-top: 100px;
    text-align: center;
  }
  a {
    color: #0f0f0f;
    text-decoration: none;
  }
  .container h1 {
    font-size: 60px;
    font-weight: normal;
    margin-bottom: 30px;
  }
  .logo-logotype {
    margin-top: 40px;
  }
</style>

<a href="">
  <body>
<div class="container">
  <div class="section-inner">
    <h1>You are now entering a polity...</h1>
    <img src="<?php echo site_url(); ?>/wp-content/themes/goatpol/assets/img/passport_transparent.png" style="display:inline;" width="500" />
    <h2>Full citizenship here for all who write and read!</h2>
    <img class="logo-logotype" src="<?php echo site_url(); ?>/wp-content/themes/goatpol/assets/img/FullTitle_transparent.png"  style="display:inline;"  width="578" height="39" alt="The GOAT PoL">
  </div>
</div>
</body>
</a>
<script>
    // setTimeout(function(){
    // window.location.href = '<?php //echo home_url('/landing-page-2'); ?>';
    // }, 15000);
</script>
<?php wp_footer(); ?>