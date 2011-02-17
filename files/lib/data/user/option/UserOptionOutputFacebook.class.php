<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/User.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutput.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutputContactInformation.class.php');

/**
 *
 * @author	Torben Brodt
 * @copyright	2010 easy-coding.de
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 * @package	de.easy-coding.wcf.facebook
 */
class UserOptionOutputFacebook implements UserOptionOutput, UserOptionOutputContactInformation {
	protected $type = 'facebook';
	// UserOptionOutput implementation
	/**
	 * @see UserOptionOutput::getShortOutput()
	 */
	public function getShortOutput(User $user, $optionData, $value) {
		return $this->getImage($user, $value, 'S');
	}
	
	/**
	 * @see UserOptionOutput::getMediumOutput()
	 */
	public function getMediumOutput(User $user, $optionData, $value) {
		return $this->getImage($user, $value);
	}
	
	/**
	 * @see UserOptionOutput::getOutput()
	 */
	public function getOutput(User $user, $optionData, $value) {
		if (empty($value) || $value == 'http://') return '';
		
		$value = self::getURL($value);
		$value = StringUtil::encodeHTML($value);
		return '<a href="'.$value.'">'.$value.'</a>';
	}
	
	// UserOptionOutputContactInformation implementation
	/**
	 * @see UserOptionOutputContactInformation::getOutput()
	 */
	public function getOutputData(User $user, $optionData, $value) {
		if (empty($value) || $value == 'http://') return null;
		
		$value = self::getURL($value);
		$value = StringUtil::encodeHTML($value);
		
		return array(
			'icon' => StyleManager::getStyle()->getIconPath($this->type.'M.png'),
			'title' => WCF::getLanguage()->get('wcf.user.option.'.$optionData['optionName']),
			'value' => $value,
			'url' => $value
		);
	}
	
	/**
	 * Generates an image button.
	 * 
	 * @see UserOptionOutput::getShortOutput()
	 */
	protected function getImage(User $user, $value, $imageSize = 'M') {
		if (empty($value) || $value == 'http://') return '';
		
		$value = self::getURL($value);
		$title = WCF::getLanguage()->get('wcf.user.profile.facebook.title', array('$username' => StringUtil::encodeHTML($user->username)));
		return '<a href="'.StringUtil::encodeHTML($value).'"><img src="'.StyleManager::getStyle()->getIconPath($this->type.$imageSize.'.png').'" alt="" title="'.$title.'" /></a>';
	}
	
	/**
	 * Formats the URL.
	 * 
	 * @param	string		$url
	 * @return	string
	 */
	private static function getURL($url) {
		if (!preg_match('~^https?://facebook\.com/~i', $url)) {
			$url = 'http://facebook.com/'.$url;
		}
		
		return $url;
	}
}
?>
