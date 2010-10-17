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

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (!MODULE_FACEBOOK || !FACEBOOK_APPID || !FACEBOOK_SECRET) {
			return;
		}
		
		switch($className) {
			// login or register with facebook
			case 'UserLoginForm':
				// readData
				FacebookUtil::loginOrRegister($eventObj);
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
				if(FacebookUtil::isValidFacebookUser()) {

					// bypass password query with a little hack
					$password = UserRegistrationUtil::getNewPassword((REGISTER_PASSWORD_MIN_LENGTH > 9 ? REGISTER_PASSWORD_MIN_LENGTH : 9));
					WCF::getUser()->password = StringUtil::getDoubleSaltedHash($password, WCF::getUser()->salt);
					$eventObj->password = $password;
				}
			break;
		}
	}
}
?>
