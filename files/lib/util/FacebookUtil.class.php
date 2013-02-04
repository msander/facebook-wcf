<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/data/facebook/Facebook.class.php');
require_once(WCF_DIR.'lib/data/facebook/FacebookAccountEditor.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/AvatarEditor.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/util/UserRegistrationUtil.class.php');

/**
 * login will display facebook login button and manage all the login stuff
 * 
 * @author	Torben Brodt, Marcel Sander
 * @copyright	2010 easy-coding.de
 * @url		http://trac.easy-coding.de/trac/wcf/wiki/facebook
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class FacebookUtil {

	/**
	 * facebook api object
	 *
	 * @var Facebook
	 */
	public static $facebook;

	/**
	 * is there an existing user with given facebook id?
	 *
	 * @param	array		$me
	 * @return	User
	 */
	protected static function getFacebookEnabledUser($me) {
		$sql = "SELECT		utb.userID
			FROM 		wcf".WCF_N."_user_to_facebook utb
			WHERE		utb.facebookID = ".intval($me['id']);
		$row = WCF::getDB()->getFirstRow($sql);

		$user = $row ? new User($row['userID']) : null;
		return $user && $user->userID ? $user : null;
	}

	/**
	 * is there an existing user with given facebook id?
	 *
	 * @param	integer		$userID
	 * @return	boolean
	 */
	public static function hasFacebookAccount($userID) {
		$sql = "SELECT		utb.facebookID
			FROM 		wcf".WCF_N."_user_to_facebook utb
			WHERE		utb.userID = ".intval($userID);
		$row = WCF::getDB()->getFirstRow($sql);

		return $row && $row['facebookID'] > 0;
	}
	
	protected static function getSession() {
		// Create our Application instance.
		self::$facebook = new Facebook(array(
			'appId'  => FACEBOOK_APPID,
			'secret' => FACEBOOK_SECRET,
			'cookie' => true,
		));

		// We may or may not have this data based on a $_GET or $_COOKIE based session.
		//
		// If we get a session here, it means we found a correctly signed session using
		// the Application Secret only Facebook and the Application know. We dont know
		// if it is still valid until we make an API call using the session. A session
		// can become invalid if it has already expired (should not be getting the
		// session back in this case) or if the user logged out of Facebook.
		
		return self::$facebook->getUser();
	}

	/**
	 *
	 */
	public static function isValidFacebookUser() {
	
		// did connect?
		if(!self::hasFacebookAccount(WCF::getUser()->userID)) {
			return false;
		}

		// We may or may not have this data based on a $_GET or $_COOKIE based session.
		$session = self::getSession();

		$me = null;
		$uid = array();

		// Session based API call.
		if ($session) {
			try {
				$uid = self::$facebook->getUser();
				$me = self::$facebook->api('/me');
			} catch (FacebookApiException $e) {
				error_log($e);
			}
		}

		// no permissions granted, ask for login
		if(!$me) {
			$loginUrl = self::$facebook->getLoginUrl(array(
				'scope' => 'email',
				'display' => 'popup'
			));

			WCF::getTPL()->assign(array(
				'session' => $session,
				'loginUrl' => $loginUrl,
			));

			WCF::getTPL()->append('additionalFields', WCF::getTPL()->fetch('facebookLogin'));
			return false;
		}

		// facebook permissions granted, does an login exist?
		$user = self::getFacebookEnabledUser($me);

		// facebook permissions granted and login is linked with current user
		return $user && $user->userID == WCF::getUser()->userID;
	}

	/**
	 * @see UserLoginForm::readData
	 */
	public static function updateCurrentUser() {

		// We may or may not have this data based on a $_GET or $_COOKIE based session.
		$session = self::getSession();

		$me = null;
		$uid = array();

		// Session based API call.
		if ($session) {
			try {
				$uid = self::$facebook->getUser();
				$me = self::$facebook->api('/me');
			} catch (FacebookApiException $e) {
				error_log($e);
			}
		}

		// no permissions granted, ask for login
		if(!$me) {
			$loginUrl = self::$facebook->getLoginUrl(array(
				'scope' => 'email',
				'display' => 'popup'
			));

			WCF::getTPL()->assign(array(
				'session' => $session,
				'loginUrl' => $loginUrl,
			));

			WCF::getTPL()->append('additionalFields', '<fieldset>
				<legend>'.WCF::getLanguage()->get('wcf.facebook.title').'</legend>
				'.WCF::getTPL()->fetch('facebookLogin').'
			</fieldset>');
			return;
		}

		// facebook permissions granted, does an login exist?
		$user = self::getFacebookEnabledUser($me);

		// facebook login not used yet, facebook permissions granted, update account info
		if(!$user && WCF::getUser()->userID) {
			$user = WCF::getUser();

			// update avatar (only if avatar is not given)
			self::updateAvatar('https://graph.facebook.com/'.$me['id'].'/picture?type=large', $user);

			// either user is new, oder just got a link, but add a facebook link
			FacebookAccountEditor::create($user->userID, $me['id']);

			return true;
		}
		
		// facebook login not used yet, current user not logged in, so just forward to login page
		else if(!$user && WCF::getUser()->userID == 0) {
			HeaderUtil::redirect('index.php?form=UserLogin');
			exit;
		}

		// user already exists, and login is linked to the current account
		else if($user && $user->userID == WCF::getUser()->userID) {
			WCF::getTPL()->append('additionalFields', '<fieldset>
				<legend>'.WCF::getLanguage()->get('wcf.facebook.title').'</legend>
				<div class="formElement">
					<div class="formFieldLabel">
						<img src="'.RELATIVE_WCF_DIR.'icon/facebookM.png" alt="" />
					</div>
					<div class="formField">
						'.WCF::getLanguage()->get('wcf.facebook.status.connected').'
					</div>
				</div>
			</fieldset>');
		}

		// user already exists, but the login is linked to another account
		else {
			WCF::getTPL()->append('additionalFields', '<fieldset>
				<legend>'.WCF::getLanguage()->get('wcf.facebook.title').'</legend>
				'.WCF::getLanguage()->getDynamicVariable('wcf.facebook.status.alreadyAssigned', array(
					'username' => $user->username
				)).'
			</fieldset>');
		}
	}

	/**
	 * @see UserLoginForm::readData
	 */
	public static function loginOrRegister() {

		$session = self::getSession();

		$me = null;
		$uid = array();

		// Session based API call.
		if ($session) {
			try {
				$uid = self::$facebook->getUser();
				$me = self::$facebook->api('/me');
			} catch (FacebookApiException $e) {
				error_log($e);
			}
		}

		// no permissions granted, ask for login
		if(!$me || !array_key_exists('code',$_REQUEST)) {
			$loginUrl = self::$facebook->getLoginUrl(array(
				'scope' => 'email',
				'display' => 'popup'
			));

			WCF::getTPL()->assign(array(
				'session' => $session,
				'loginUrl' => $loginUrl,
			));

			WCF::getTPL()->append('additionalFields', WCF::getTPL()->fetch('facebookLogin'));
			return;
		}

		// facebook permissions granted, does an login exist?
		$user = self::getFacebookEnabledUser($me);

		// facebook permissions granted but no login exists
		if(!$user) {
		
			if(!isset($me['email'])) {
				throw new SystemException('missing email privileges for user: '.
					(isset($me['name']) ? $me['name'] : 'unknown'));
			}

			// check if email address is already in use
			$user = new User(null, null, null, $me['email']);
			
			// email is already in use, stop!
			if($user && $user->userID) {
				WCF::getTPL()->append('userMessages', '<p class="error">
					'.WCF::getLanguage()->getDynamicVariable('wcf.facebook.status.alreadyEmail', array(
						'username' => $user->username
					)).'
				</p>');
				return;
			}

			// totally unknown, add a new user
			if(!$user->userID) {
				$user = self::registerUser($me);
			}

			// update avatar
			self::updateAvatar('https://graph.facebook.com/'.$me['id'].'/picture?type=large', $user);

			// either user is new, oder just got a link, but add a facebook link
			FacebookAccountEditor::create($user->userID, $me['id']);
		}
		return $user;
	}

	/**
	 * get a available username
	 *
	 * @param	string		$username
	 * @return	string
	 */
	protected static function findUsername($username) {
		if(!UserUtil::isValidUsername($username)) {
			return null;
		}

		if(UserUtil::isAvailableUsername($username)) {
			return $username;
		}

		// try to increase last digit
		if(preg_match('/(\d+)$/', $username, $res)) {
			return self::findUsername(preg_replace('/(\d+)/', ($res[1] + 1), $username));
		} else {
			return self::findUsername($username.'2');
		}
	}

	/**
	 * registers a new user with valid username
	 *
	 * @param	array		$me
	 * @return	User
	 */
	protected static function registerUser($me) {
		$user = null;
		// get a valid username
		$username = self::findUsername($me['name']);

		// create new user
		if($username) {
			$user = self::createNewUser(
				$username,
				$me['email'],
				$me['link']
			);
		} else {
			throw new SystemException('invalid facebook username: '.$me['name']);
		}
		return $user;
	}

	/**
	 * adds a new wcf user and sends, bypasses all registration steps and send out mails
	 *
	 * @param	string		$username
	 * @param	string		$email
	 * @param	string		$facebookLink
	 * @return	User
	 */
	protected static function createNewUser($username, $email, $facebookLink) {

		$password = UserRegistrationUtil::getNewPassword((REGISTER_PASSWORD_MIN_LENGTH > 9 ? REGISTER_PASSWORD_MIN_LENGTH : 9));
		$groups = array();
		$activeOptions = array();

		$additionalFields = array();
		$additionalFields['languageID'] = WCF::getLanguage()->getLanguageID();
		$additionalFields['registrationIpAddress'] = WCF::getSession()->ipAddress;

		$visibleLanguages = self::getAvailableLanguages();
		$visibleLanguages = array_keys($visibleLanguages);
		$addDefaultGroups = true;

		// create the user
		if(($user = UserEditor::create($username, $email, $password, $groups, $activeOptions, $additionalFields, $visibleLanguages, $addDefaultGroups))) {

			// notify admin
			if (REGISTER_ADMIN_NOTIFICATION) {
				// get default language
				$language = (WCF::getLanguage()->getLanguageID() != Language::getDefaultLanguageID()
					? new Language(Language::getDefaultLanguageID())
					: WCF::getLanguage());
				$language->setLocale();

				// send mail
				require_once(WCF_DIR.'lib/data/mail/Mail.class.php');
				$mail = new Mail(
					MAIL_ADMIN_ADDRESS,
					$language->get('wcf.user.register.notification.mail.subject', array(
						'PAGE_TITLE' => $language->get(PAGE_TITLE)
					)),
					$language->get('wcf.user.register.notification.mail', array(
						'PAGE_TITLE' => $language->get(PAGE_TITLE),
						'$username' => $user->username
					))
				);
				$mail->send();

				WCF::getLanguage()->setLocale();
			}
		}

		return $user;
	}

	/**
	 * Returns a list of all available languages.
	 *
	 * @return	array
	 */
	protected static function getAvailableLanguages() {
		$availableLanguages = array();
		foreach (Language::getAvailableLanguages(PACKAGE_ID) as $language) {
			$availableLanguages[$language['languageID']] = WCF::getLanguage()->get('wcf.global.language.'.$language['languageCode']);
		}

		// sort languages
		StringUtil::sort($availableLanguages);

		return $availableLanguages;
	}

	/**
	 * downloads facebook image and saves as avatar
	 *
	 * @param	string		$avatarURL
	 * @param	User		$user
	 * @return	boolean
	 */
	protected static function updateAvatar($avatarURL, $user) {
		// existing avatar? skip facebook download
		if (!$user || $user->avatarID || empty($avatarURL)) {
			return false;
		}

		try {
			$avatarID = 0;
			$tmpName = FileUtil::downloadFileFromHttp($avatarURL, 'avatar');
			$avatarID = AvatarEditor::create($tmpName, $avatarURL, 'avatarURL', $user->userID);
		}
		catch (Exception $e) {

			// skip, download is not that important
			return false;
		}

		if($avatarID) {
			return $user->getEditor()->updateFields(array(
				'avatarID' => $avatarID
			));
		}
	}
}
?>
