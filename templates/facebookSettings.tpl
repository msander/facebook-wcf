{include file="documentHeader"}
<head>
	<title>{lang}org.gnex.facebook.header{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body>
<script src="http://static.new.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/de_DE" type="text/javascript"></script>
{include file='header' sandbox=false}

<div id="main">

	{capture append=userMessages}
		{if $errorField}
			<p class="error">{lang}wcf.global.form.error{/lang}</p>
		{/if}
		
		{if $success|isset}
			<p class="success">{lang}{@$success}{/lang}</p>
		{/if}
	{/capture}
	
	<p class="error" style="display: none" id="showFacebookError">{lang}org.gnex.facebook.noSettings{/lang}</p>
	
	{include file="userCPHeader"}
	
	<form method="post" name="facebookForm" action="index.php?form=Facebook">
		<div class="border tabMenuContent">
			<div class="container-1">
				<h3 class="subHeadline">{lang}org.gnex.facebook.headline{/lang}</h3>
				
				<fieldset>
					<legend><label for="facebookID">{lang}org.gnex.facebook.headline{/lang}</label></legend>
					  {if $identityFacebook}
						<div class="formElement">
							<div class="formFieldLabel">
								<label>{lang}org.gnex.facebook.aktverbdesc{/lang}</label>
							</div>
							<div class="formField">{$identityFacebook}</div>
						</div>
						{else}
						<div class="formElement">
							<div class="formFieldLabel">
								<label>{lang}org.gnex.facebook.aktverbdesc{/lang}</label>
							</div>
							<div class="formField">{lang}org.gnex.facebook.aktverbdesc.no{/lang}</div>
						</div>
						{/if}
						
						{if $facebookID && $identityFacebook != $facebookID}
						<div class="formElement">
							<div class="formFieldLabel">
								<label>{lang}org.gnex.facebook.newverbdesc{/lang}</label>
							</div>
							<div class="formField">{$facebookID}</div>
						</div>
						{else}
						<div class="formElement">
							<div class="formFieldLabel">
								<label>{lang}org.gnex.facebook.newverbdesc{/lang}</label>
							</div>
							<div class="formField">{lang}org.gnex.facebook.newverbdesc.no{/lang}{if !$facebookID}{lang}org.gnex.facebook.newverbdesc.no.login{/lang}{/if}</div>
						</div>
						{/if}
				</fieldset>
				
				<div class="formElement{if $errorField == 'passwordFacebook'} formError{/if}">
					<div class="formFieldLabel">
						<label for="password">{lang}org.gnex.facebook.passwd{/lang}</label>
					</div>
					<div class="formField">
						<input type="password" class="inputText" name="password" value="{$password}" id="password" />
						{if $errorField == 'passwordFacebook'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'false'}{lang}wcf.user.login.error.password.false{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}org.gnex.facebook.passwd.description{/lang}</p>
					</div>
				</div>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
					
				{@SID_INPUT_TAG}
			</div>
		</div>
		
		<input type="hidden" name="action" value="facebook" />
		<div class="formSubmit">
			<fb:login-button v="2" size="medium" onlogin="checkFacebook()">{lang}org.gnex.facebook.button.desc{/lang}</fb:login-button>
		</div>
    <script type="text/javascript">FB.init("{FACEBOOK_KEY_PUBLIC}", "xd_receiver.htm");</script>
    <script type="text/javascript">
      function checkFacebook() {
        document.facebookForm.submit();
      }
    </script>
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>