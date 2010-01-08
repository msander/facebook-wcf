<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/User.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutput.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutputContactInformation.class.php');
/**
 * UserOptionOutputFacebook is an implementation of UserOptionOutput for the output of an facebook-url.
 * 
 * @author	Tim Wittenberg
 * @copyright	GneX
 * @url GneX.org 
 * @license	GPL
 */
class UserOptionOutputFacebook implements UserOptionOutput, UserOptionOutputContactInformation {
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
		if (!$user->facebookIdentifier) return '';
		
		$value = self::getFacebookURL($user);
		$value = StringUtil::encodeHTML($value);
		return '<a href="'.$value.'">'.WCF::getLanguage()->get('org.gnex.facebook.profile.link.description').StringUtil::encodeHTML($user->username).'</a>';
	}
	
	// UserOptionOutputContactInformation implementation
	/**
	 * @see UserOptionOutputContactInformation::getOutput()
	 */
	public function getOutputData(User $user, $optionData, $value) {
		if (!$user->facebookIdentifier) return null;
		
		$value = self::getFacebookURL($user);
		$value = StringUtil::encodeHTML($value);
		
		return array(
			'icon' => StyleManager::getStyle()->getIconPath('facebookM.png'),
			'title' => WCF::getLanguage()->get('wcf.user.option.'.$optionData['optionName']),
			'value' => WCF::getLanguage()->get('org.gnex.facebook.profile.link.description').StringUtil::encodeHTML($user->username),
			'url' => $value
		);
	}
	
	/**
	 * Generates an image button.
	 * 
	 * @see UserOptionOutput::getShortOutput()
	 */
	protected function getImage(User $user, $value, $imageSize = 'M') {
		if (!$user->facebookIdentifier) return '';
		
		$value = self::getFacebookURL($user);
		$title = WCF::getLanguage()->get('org.gnex.facebook.profile.link.description').StringUtil::encodeHTML($user->username).WCF::getLanguage()->get('org.gnex.facebook.profile.link.wdb');
		return '<a href="'.StringUtil::encodeHTML($value).'"><img src="'.StyleManager::getStyle()->getIconPath('facebook'.$imageSize.'.png').'" alt="" title="'.$title.'" /></a>';
	}
	
	/**
	 * Formats the FacebookURL.
	 * 
	 * @param	string		$url
	 * @return	string
	 */
	private static function getFacebookURL($user) {
		return 'http://facebook.com/profile.php?id='.$user->facebookIdentifier;
	}
}
?>