{if !$this->user->userID && !LOGIN_USE_CAPTCHA && MODULE_FACEBOOK && FACEBOOK_KEY_PUBLIC && FACEBOOK_KEY_PRIVATE}
<script src="http://static.new.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/de_DE" type="text/javascript"></script>
  <fb:login-button v="2" size="medium" onlogin="facebook_onlogin_ready()">{lang}org.gnex.facebook.button.login{/lang}</fb:login-button>
<script type="text/javascript">FB.init("{FACEBOOK_KEY_PUBLIC}", "xd_receiver.htm");</script>
<script src="{@RELATIVE_WCF_DIR}lib/data/facebook/javascript/fbconnect.js" type="text/javascript"></script>
{/if}