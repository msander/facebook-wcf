<?php
// wcf imports
require_once(WCF_DIR.'lib/data/facebook/FacebookAccount.class.php');

/**
 *
 * @author	Torben Brodt
 * @copyright	2010 easy-coding.de
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 * @package	de.easy-coding.wcf.facebook
 */
class FacebookAccountEditor extends FacebookAccount {

	/**
	 *
	 */
	public static function create($userID, $facebookID) {
		$sql = "REPLACE INTO	wcf".WCF_N."_user_to_facebook
					(userID, facebookID)
			VALUES		(".intval($userID).", ".intval($facebookID).")";

		WCF::getDB()->sendQuery($sql);
		
		return new self(null, array(
			'userID' => $userID,
			'facebookID' => $facebookID
		));
	}
}
