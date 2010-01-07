<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/system/auth/UserAuthFacebook.class.php');
/**
 * Facebook-API
 * 
 * @author	Tim Wittenberg
 * @copyright	GneX
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class UserAuthLoadInstanceFacebookListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_FACEBOOK) UserAuthFacebook::$eventName();
	}
}
?>