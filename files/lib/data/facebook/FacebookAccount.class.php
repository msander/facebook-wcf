<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a facebook account.
 *
 * @author	Torben Brodt
 * @copyright	2010 easy-coding.de
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 * @package	de.easy-coding.wcf.facebook
 */
class FacebookAccount extends DatabaseObject {

	/**
	 * Creates a new facebook account object.
	 *
	 * @param	integer		$userID
	 * @param 	array<mixed>	$row
	 */
	public function __construct($userID, $row = null) {
		if ($userID !== null) {
			$sql = "SELECT	*
				FROM 	wcf".WCF_N."_user_to_facebook
				WHERE 	userID = ".intval($userID);
			$row = WCF::getDB()->getFirstRow($sql);
		}
		parent::__construct($row);
	}
}
?>
