<?php
require_once(WCF_DIR.'lib/util/FacebookUtil.class.php');

/**
 * login will display facebook login button and manage all the login stuff
 * 
 * @author	Torben Brodt
 * @url		http://trac.easy-coding.de/trac/wcf/wiki/facebook
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class UserLoginFacebookListener implements EventListener {
	protected static $ignoreForms = array(
		'rulesagree',
		'userprofileedit',
		'accountmanagement',
	);
	protected static $ignorePages = array(
		'legalnotice'
	);

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (!MODULE_FACEBOOK || !FACEBOOK_APPID || !FACEBOOK_SECRET) {
			return;
		}
		
		switch($className) {
			
			// did agree with rules?
			default:
				// didInit
				if(false && $eventObj instanceof SessionFactory) $this->validateRuleAgree($eventObj->session);
			break;

			// login or register with facebook
			case 'UserLoginForm':
				// readData
				$user = FacebookUtil::loginOrRegister($eventObj);		

				if($user) {

					// UserLoginForm should not write cookie, since interfaces only support unhashed password
					$eventObj->useCookies = 0;
					$eventObj->useCaptcha = 0;

					// set cookies
					UserAuth::getInstance()->storeAccessData($user, $user->username, $user->password);
					HeaderUtil::setCookie('password', $user->password, TIME_NOW + 365 * 24 * 3600);

					// save cookie and redirect
					$eventObj->user = $user;
					$eventObj->save();

					exit;
				}
			break;
			
			// registered user links with facebook
			case 'UserProfileEditForm':
				// assignVariables
				if($eventObj->activeCategory == 'settings.general') {
					FacebookUtil::updateCurrentUser();
				}
			break;
			
			// password validations can be skipped if facebook auth is successfully
			case 'AccountManagementForm':
				// validate
				if($eventName == 'validate' && FacebookUtil::isValidFacebookUser()) {

					// bypass password query with a little hack
					$password = UserRegistrationUtil::getNewPassword((REGISTER_PASSWORD_MIN_LENGTH > 9 ? REGISTER_PASSWORD_MIN_LENGTH : 9));
					WCF::getUser()->password = StringUtil::getDoubleSaltedHash($password, WCF::getUser()->salt);
					$eventObj->password = $password;
				}
				
				// hide password input
				else if($eventName == 'assignVariables' && FacebookUtil::hasFacebookAccount(WCF::getUser()->userID)) {
					WCF::getTPL()->append('additionalFields', '<script type="text/javascript">
						$($("password").parentNode.parentNode).hide();
					</script>');
				}
			break;
		}
	}

	/**
	 * 
	 */
	protected function validateRuleAgree($session) {
		
		// if the modul deactivated, or the user must no agree the rules, we can leave the event.
		// if we log out or on the rulesagree page, we also leave the event.
		if (!defined('MODULE_RULE') || MODULE_RULE == 0 || !$session || $session->getUser()->getPermission('admin.general.canIgnoreRules')) return;
		if ((isset($_REQUEST['action']) && strtolower($_REQUEST['action']) == 'userlogout') || (isset($_REQUEST['form']) && in_array(strtolower($_REQUEST['form']), self::$ignoreForms)) || (isset($_REQUEST['page']) && in_array(strtolower($_REQUEST['page']), self::$ignorePages))) return;
		
		// if the modul activate and the user is facebook user he must agree the rules after a change, and the user is not a guest.
		if ($session->getUser()->userID && FacebookUtil::hasFacebookAccount($session->getUser()->userID)) {
			// select all packageids of the packages where the user is agree with the rules.
			$packageIDs = $session->getVar('package_agrees');
			
			// if the packageid array null or the current package is not in the id, must check the agreement.
			if (is_null($packageIDs) || !in_array(PACKAGE_ID, $packageIDs)) {
				// we check the agreement, is the user agree with the rules, we put the package id in to the array and leave the event.
				if (Ruleset::isUserAgree($session->getUser()->userID, PACKAGE_ID)) {
					if (is_null($packageIDs) || !is_array($packageIDs)) 
						$packageIDs = array(PACKAGE_ID);
					else $packageIDs[] = PACKAGE_ID;
						$session->register('package_agrees', $packageIDs);
					return;
				}
				HeaderUtil::redirect('index.php?form=RulesAgree'.SID_ARG_2ND_NOT_ENCODED, false);
				exit;
			}
		}
	}
}
?>
