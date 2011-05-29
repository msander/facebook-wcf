<?php
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * facebook page
 *
 * @author	Torben Brodt
 * @package	de.easy-coding.wcf.facebook
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class FacebookPage extends AbstractPage {
	public $templateName = 'facebook';

	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
	}
}
?>
