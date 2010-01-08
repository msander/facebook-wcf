<?php
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/system/session/UserSession.class.php');
require_once(WCF_DIR.'lib/system/auth/UserAuth.class.php');
require_once(WCF_DIR.'lib/data/facebook/facebook-client/facebook.php');

require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/data/mail/Mail.class.php');
require_once(WCF_DIR.'lib/system/language/Language.class.php');
/**
 * Facebook-API
 * 
 * @author	Tim Wittenberg
 * @copyright	GneX
 * @url GneX.org 
 * @license	GPL
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
		if (!MODULE_FACEBOOK || !FACEBOOK_KEY_PUBLIC || !FACEBOOK_KEY_PRIVATE) {
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
    $this->url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PAGE_URL;
	}
	
	/**
	 * @see Action::save()
	 */
	public function execute() {
		parent::execute();
		
		if(!isset($this->facebookID) || empty($this->facebookID)) {
      header('Location: index.php?page=Register'.SID_ARG_2ND_NOT_ENCODED);
      exit();
    }
    
    if(($this->user = UserAuth::getInstance()->loginManuallyFacebook($this->facebookID)) == false) {
      if($this->facebook->api_client->users_isAppUser($this->facebookID) == false) {
        $facebookFields = array(
          'uid',
          'username',
          'proxied_email',
          'email_hashes',
          'birthday_date',
          'sex'
        );
        $facebookUser = $this->facebook->api_client->users_getInfo($this->facebookID, $facebookFields);
        if($facebookUser[0]['uid'] && $facebookUser[0]['username'] && $facebookUser[0]['proxied_email'] && $facebookUser[0]['email_hashes'] && $facebookUser[0]['birthday_date'] && $facebookUser[0]['sex'] && UserUtil::isValidUsername($facebookUser[0]['username']) && UserUtil::isAvailableUsername($facebookUser[0]['username']) && UserUtil::isValidEmail($facebookUser[0]['proxied_email']) && UserUtil::isAvailableEmail($facebookUser[0]['proxied_email'])) {
          $this->createNewUser($facebookUser[0]['username'], $facebookUser[0]['proxied_email'], $facebookUser[0]['email_hashes'], $facebookUser[0]['birthday_date'], $facebookUser[0]['sex']);
        } else {
          header('Location: index.php?page=Register'.SID_ARG_2ND_NOT_ENCODED);
          exit();
        }
      } else {
			  require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			  throw new NamedUserException(WCF::getLanguage()->get('org.gnex.facebook.noVerb'));
      }
    }
		
		// set cookies
		if ($this->useCookies == 1) {
			UserAuth::getInstance()->storeAccessDataFacebook($this->user, $this->facebookID);
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
	
	protected function getAvailableLanguages() {
		$availableLanguages = array();
		foreach (Language::getAvailableLanguages(PACKAGE_ID) as $language) {
			$availableLanguages[$language['languageID']] = WCF::getLanguage()->get('wcf.global.language.'.$language['languageCode']);	
		}
		
		// sort languages
		StringUtil::sort($availableLanguages);
		
		return $availableLanguages;
	}
	
	public function createNewUser($username, $email, $emailHash, $birthday, $gender) {
    $password = UserRegistrationUtil::getNewPassword((REGISTER_PASSWORD_MIN_LENGTH > 9 ? REGISTER_PASSWORD_MIN_LENGTH : 9));
    
    $groups = Group::getAccessibleGroups(array(), array(Group::GUESTS, Group::EVERYONE, Group::USERS));
    
    $valuesBirthday = explode('/', $birthday);
    $activeOptions['birthday']['day'] = $valuesBirthday[1];
    $activeOptions['birthday']['month'] = $valuesBirthday[0];
    $activeOptions['birthday']['year'] = $valuesBirthday[2];
    
    if($this->facebookUser[0]['sex'] == 'männlich') {
      $activeOptions['gender'] = 1;
    } else if($this->facebookUser[0]['sex'] == 'weiblich') {
      $activeOptions['gender'] = 2;
    } else {
      $activeOptions['gender'] = 0;
    }
    
    $additionalFields['languageID'] = WCF::getLanguage()->getLanguageID();
    $additionalFields['registrationIpAddress'] = WCF::getSession()->ipAddress;
    
    $visibleLanguages = $this->getAvailableLanguages();
		
		// generate activation code
		$addDefaultGroups = true;
		if (REGISTER_ACTIVATION_METHOD == 1 || REGISTER_ACTIVATION_METHOD == 2) {
			$activationCode = UserRegistrationUtil::getActivationCode();
			$additionalFields['activationCode'] = $activationCode;
			$addDefaultGroups = false;
			$groups = Group::getGroupIdsByType(array(Group::EVERYONE, Group::GUESTS));
		}
    
    if(($user = UserEditor::create($username, $email, $password, $groups, $activeOptions, $additionalFields, $visibleLanguages, $addDefaultGroups))) {
      // update facebook
      $facebookIdentifierSalt = StringUtil::getRandomID();
      $facebookIdentifierHash = StringUtil::getDoubleSaltedHash($this->facebookID, $facebookIdentifierSalt);
		  $user->update('', '', '', null, null, array('facebookIdentifier' => $this->facebookID, 'facebookIdentifierHash' => $facebookIdentifierHash, 'facebookIdentifierSalt' => $facebookIdentifierSalt));
      #$this->facebook->api_client->connect_registerUsers(array('email_hash' => $emailHash, 'account_id' => $this->facebookID));
      
		  // set cookies
		  HeaderUtil::setCookie('userID', $user->userID, TIME_NOW + 365 * 24 * 3600);
		  HeaderUtil::setCookie('password', StringUtil::getSaltedHash($password, $user->salt), TIME_NOW + 365 * 24 * 3600);
      
      // update session
      WCF::getSession()->changeUser($user);
		  
		  // activation management
		  if (REGISTER_ACTIVATION_METHOD == 0) {
			  $this->message = 'wcf.user.register.success';
		  }
		  
		  if (REGISTER_ACTIVATION_METHOD == 1) {
			  $mail = new Mail(	array($user->username => $user->email),
			  			WCF::getLanguage()->get('wcf.user.register.needActivation.mail.subject', array('PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE))),
			  			WCF::getLanguage()->get('wcf.user.register.needActivation.mail',
			  				array('PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE), '$username' => $user->username, '$userID' => $user->userID, '$activationCode' => $activationCode, 'PAGE_URL' => PAGE_URL, 'MAIL_ADMIN_ADDRESS' => MAIL_ADMIN_ADDRESS)));
			  $mail->send();
			  $this->message = 'wcf.user.register.needActivation';
		  }
      
      if (REGISTER_ACTIVATION_METHOD == 2) {
			  $this->message = 'wcf.user.register.awaitActivation';
		  }
		  
		  // notify admin
		  if (REGISTER_ADMIN_NOTIFICATION) {
			  // get default language
			  $language = (WCF::getLanguage()->getLanguageID() != Language::getDefaultLanguageID() ? new Language(Language::getDefaultLanguageID()) : WCF::getLanguage());
			  $language->setLocale();
			  
			  // send mail
			  $mail = new Mail(	MAIL_ADMIN_ADDRESS, 
				  		$language->get('wcf.user.register.notification.mail.subject', array('PAGE_TITLE' => $language->get(PAGE_TITLE))),
					  	$language->get('wcf.user.register.notification.mail', array('PAGE_TITLE' => $language->get(PAGE_TITLE), '$username' => $user->username)));
			  $mail->send();
			  
			  WCF::getLanguage()->setLocale();
	  	}
	  	
	  	// send password
		  $subjectData = array('PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE));
		  $messageData = array(
		  	'PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE),
		  	'$username' => $user->username,
		  	'$userID' => $user->userID,
		  	'$newPassword' => $password,
		  	'PAGE_URL' => PAGE_URL,
		  	'MAIL_ADMIN_ADDRESS' => MAIL_ADMIN_ADDRESS
	  	);
		  $mail = new Mail(array($user->username => $user->email), WCF::getLanguage()->get('wcf.user.lostPassword.newPassword.mail.subject', $subjectData), WCF::getLanguage()->get('wcf.user.lostPassword.newPassword.mail', $messageData));
		  $mail->send();
		  
		  // delete captcha
      WCF::getSession()->unregister('captchaDone');
		  
		  // login user
		  UserAuth::getInstance()->storeAccessData($user, $user->username, $password);
		  
		  // forward to index page
		  WCF::getTPL()->assign(array(
			  'url' => 'index.php'.SID_ARG_1ST,
		  	'message' => WCF::getLanguage()->get($this->message, array('$username' => $user->username, '$email' => $user->email))
		  ));
		  WCF::getTPL()->display('redirect');
		  exit;
    } else {
      header('Location: index.php?page=Register'.SID_ARG_2ND_NOT_ENCODED);
      exit();
    }
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
