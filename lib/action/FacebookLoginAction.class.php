<?php
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/system/session/UserSession.class.php');
require_once(WCF_DIR.'lib/system/auth/UserAuth.class.php');
require_once(WCF_DIR.'lib/data/facebook/facebook-client/facebook.php');
/**
 * Facebook-API
 * 
 * @author	Tim Wittenberg
 * @copyright	GneX
 * @license	GneX*Lizenz
 */
class FacebookLoginAction extends AbstractAction {
	public $useCookies = 1;
	public $api_key = FACEBOOK_KEY_PUBLIC;
  public $secret = FACEBOOK_KEY_PRIVATE;
  public $user;
  public $facebook;
  public $facebookID = '';
	
	/**
	 * Creates a new LoginForm object.
	 */
	public function __construct() {
		if (!MODULE_FACEBOOK) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('org.gnex.facebook.disabled'));
		}
		
		if (WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		parent::__construct();
	}
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
    $this->facebook = new Facebook($this->api_key, $this->secret);
    $this->facebookID = $this->facebook->get_loggedin_user();
    
    #$this->url = WCF::getSession()->requestURI;
    $this->url = $_SERVER['HTTP_REFERER'];
	}
	
	/**
	 * @see Action::save()
	 */
	public function execute() {
		parent::execute();
		
		if(!isset($this->facebookID) || empty($this->facebookID)) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('org.gnex.facebook.noEx'));
    }
    
    $this->user = UserAuth::getInstance()->loginManuallyFacebook($this->facebookID);
		
		// set cookies
		if ($this->useCookies == 1) {
			UserAuth::getInstance()->storeAccessDataFacebook($this->user);
		}
		
		// change user
		WCF::getSession()->changeUser($this->user);
		
		// delete captcha
		WCF::getSession()->unregister('captchaDone');
		
		// get redirect url
		$this->checkURL();
		
		// redirect to url
		WCF::getTPL()->assign(array(
			'url' => $this->url,
			'message' => WCF::getLanguage()->get('wcf.user.login.redirect'),
			'wait' => 5
		));
		WCF::getTPL()->display('redirect');
		exit;
	}
	
	/**
	 * Gets the redirect url.
	 */
	protected function checkURL() {
		if (empty($this->url) || StringUtil::indexOf($this->url, 'index.php?form=UserLogin') !== false || StringUtil::indexOf($this->url, 'index.php?action=FacebookLogin') !== false || StringUtil::indexOf($this->url, 'index.php?page=Register') !== false) {
			$this->url = 'index.php'.SID_ARG_1ST;
		}
		// append missing session id
		else if (SID_ARG_1ST != '' && !preg_match('/(?:&|\?)s=[a-z0-9]{40}/', $this->url)) {
			if (StringUtil::indexOf($this->url, '?') !== false) $this->url .= SID_ARG_2ND_NOT_ENCODED;
			else $this->url .= SID_ARG_1ST;
		}
	}
}
?>