<?php
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * facebook fan page
 *
 * @author	Torben Brodt
 * @package	de.easy-coding.wcf.facebook
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class FacebookPage extends AbstractPage {
	public $templateName = 'facebook';
	public $boxContents = array();
	public $boxLinks = array();

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'boxContents' => $this->boxContents,
			'boxLinks' => $this->boxLinks,
		));
	}
}
?>
