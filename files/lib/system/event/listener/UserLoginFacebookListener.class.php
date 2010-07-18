<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/data/facebook/Facebook.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/AvatarEditor.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');

/**
 * login will display facebook login button and manage all the login stuff
 * 
 * @author	Tim Wittenberg, Torben Brodt
 * @url		http://trac.easy-coding.de/trac/wcf/wiki/facebook
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class UserLoginFacebookListener implements EventListener {
	/**
	 * from eventlistener
	 *
	 * @var UserLoginForm
	 */
	protected $eventObj;
	
	/**
	 * from eventlistener
	 *
	 * @var string
	 */
	protected $className;

	/**
	 * facebook api object
	 *
	 * @var Facebok
	 */
	public $facebook;

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (!MODULE_FACEBOOK || !FACEBOOK_APPID || !FACEBOOK_SECRET) {
			return;
		}

		$this->eventObj = $eventObj;
		$this->className = $className;

		if(method_exists($this, $eventName)) {
			$this->$eventName();
		}
	}

	/**
	 * is there an existing user with given facebook id?
	 *
	 * @param	array		$me
	 * @return	User
	 */
	protected function getFacebookEnabledUser($me) {
		$sql = "SELECT		utb.userID
			FROM 		wcf".WCF_N."_user_to_facebook utb
			WHERE		utb.facebookID = ".intval($me['id']);
		$row = WCF::getDB()->getFirstRow($sql);

		$user = $row ? new User($row['userID']) : null;
		return $user && $user->userID ? $user : null;
	}

	/**
	 * adds facebook link to user
	 *
	 * @param	integer		$facebookID
	 * @param	User		$user
	 * @return	boolean
	 */
	protected function addFacebookUser($facebookID, $user) {
		$sql = "REPLACE INTO	wcf".WCF_N."_user_to_facebook
					(facebookID, userID)
			VALUES		(".intval($facebookID).", ".intval($user->userID).")";

		return WCF::getDB()->sendQuery($sql);
	}

	/**
	 * is there an existing user matching the given email address from facebook?
	 *
	 * @param	array		$me
	 * @return	User
	 */
	protected function getEmailUser($me) {
		$user = new User(null, null, null, $me['email']);
		return $user->userID ? $user : null;
	}

	/**
	 * @see UserLoginForm::readData
	 */
	public function readData() {

		// Create our Application instance.
		$this->facebook = new Facebook(array(
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
		$session = $this->facebook->getSession();

		$me = null;
		$uid = array();

		// Session based API call.
		if ($session) {
			try {
				$uid = $this->facebook->getUser();
				$me = $this->facebook->api('/me');
			} catch (FacebookApiException $e) {
				error_log($e);
			}
		}

		// no permissions granted, ask for login
		if(!$me) {
			$loginUrl = $this->facebook->getLoginUrl(array(
				'req_perms' => 'email',
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
		$user = $this->getFacebookEnabledUser($me);

		// facebook permissions granted but no login exists
		if(!$user) {

			// facebook is ultimativly trusted, if you get an account there,
			// you can get the account with the same email here!
			$user = $this->getEmailUser($me);

			// totally unknown, add a new user
			if(!$user) {
				$user = $this->registerUser($me);
			}

			// update avatar
			$this->updateAvatar('https://graph.facebook.com/'.$me['id'].'/picture', $user);

			// either user is new, oder just got a link, but add a facebook link
			$this->addFacebookUser($me['id'], $user);
		}

		if($user) {

			// UserLoginForm should not write cookie, since interfaces only support unhashed password
			$this->eventObj->useCookies = 0;

			// set cookies
			UserAuth::getInstance()->storeAccessData($user, $user->username, $user->password);
			HeaderUtil::setCookie('password', $user->password, TIME_NOW + 365 * 24 * 3600);

			// save cookie and redirect
			$this->eventObj->user = $user;
			$this->eventObj->save();

			exit;
		}
	}

	/**
	 * get a available username
	 *
	 * @param	string		$username
	 * @return	string
	 */
	protected function findUsername($username) {
		if(!UserUtil::isValidUsername($username)) {
			return null;
		}

		if(UserUtil::isAvailableUsername($username)) {
			return $username;
		}

		// try to increase last digit
		if(preg_match('/(\d+)$/', $username, $res)) {
			return $this->findUsername(preg_replace('/(\d+)/', ($res[1] + 1), $username));
		} else {
			return $this->findUsername($username.'2');
		}
	}

	/**
	 * registers a new user with valid username
	 *
	 * @param	array		$me
	 * @return	User
	 */
	protected function registerUser($me) {
		$user = null;
		// get a valid username
		$username = $this->findUsername($me['name']);

		// create new user
		if($username) {
			$user = $this->createNewUser(
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
	protected function createNewUser($username, $email, $facebookLink) {

		$password = UserRegistrationUtil::getNewPassword((REGISTER_PASSWORD_MIN_LENGTH > 9 ? REGISTER_PASSWORD_MIN_LENGTH : 9));
		$groups = array();
		$activeOptions = array();

		$additionalFields = array();
		$additionalFields['languageID'] = WCF::getLanguage()->getLanguageID();
		$additionalFields['registrationIpAddress'] = WCF::getSession()->ipAddress;

		$visibleLanguages = $this->getAvailableLanguages();
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
	protected function getAvailableLanguages() {
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
	protected function updateAvatar($avatarURL, $user) {
		// existing avatar? skip facebook download
		if ($user->avatarID || empty($avatarURL)) {
			return false;
		}

		try {
			$tmpName = FileUtil::downloadFileFromHttp($avatarURL, 'avatar');
		}
		catch (SystemException $e) {

			// skip, download is not that important
			return false;
		}

		$avatarID = AvatarEditor::create($tmpName, $avatarURL, 'avatarURL', $user->userID);

		// update user
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	avatarID = ".$avatarID."
			WHERE	userID = ".$user->userID;
		return WCF::getDB()->sendQuery($sql);
	}
}
?>
