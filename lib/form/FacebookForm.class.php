<?php
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');
require_once(WCF_DIR.'lib/data/facebook/facebook-client/facebook.php');
require_once(WCF_DIR.'lib/system/session/Session.class.php');
/**
 * Facebook-API
 * 
 * @author	Tim Wittenberg
 * @copyright	GneX
 * @url GneX.org 
 * @license	GPL
 */
class FacebookForm extends AbstractForm {
  public $templateName = 'facebookSettings';
  
	public $password = '';
	public $api_key = FACEBOOK_KEY_PUBLIC;
  public $secret = FACEBOOK_KEY_PRIVATE;
	public $facebook;
	public $identityFacebook = '';
	public $facebookID = '';
	
  public function readParameters() {
    parent::readParameters();
    
    $this->facebook = new Facebook($this->api_key, $this->secret);
    $this->facebookID = $this->facebook->get_loggedin_user();
  }
  
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['password'])) $this->password = $_POST['password'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
    if (empty($this->password)) {
      throw new UserInputException('passwordFacebook');
    }
    
    if (!WCF::getUser()->checkPassword($this->password)) {
      throw new UserInputException('passwordFacebook', 'false');
    }
    
    if (empty($this->facebookID) || !$this->facebookID || $this->identityFacebook == $this->facebookID) {
      return;
    }
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		if (count($_POST)) {
			$this->submit();
		}
		
		AbstractPage::readData();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'password' => $this->password,
			'identityFacebook' => $this->identityFacebook,
			'facebookID' => $this->facebookID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!MODULE_FACEBOOK) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('org.gnex.facebook.disabled'));
		}
		
		if (!WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		// check permission
		//WCF::getUser()->checkPermission('');
		
		// set active tab
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.profile.facebook');
		
		$this->identityFacebook = WCF::getUser()->facebookIdentifier;
		
		parent::show();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		$editor = WCF::getUser()->getEditor();
		
		$facebookIdentifierSalt = StringUtil::getRandomID();
		$facebookIdentifierHash = StringUtil::getDoubleSaltedHash($this->facebookID, $facebookIdentifierSalt);
		
		// update facebook
		$editor->update('', '', '', null, null, array('facebookIdentifier' => $this->facebookID, 'facebookIdentifierHash' => $facebookIdentifierHash, 'facebookIdentifierSalt' => $facebookIdentifierSalt));
		
		$message = (WCF::getUser()->facebookIdentifier) ? WCF::getLanguage()->get('org.gnex.facebook.updateSuccess') : WCF::getLanguage()->get('org.gnex.facebook.reUpdateSuccess');
		
		Session::resetSessions(WCF::getUser()->userID);
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', $message);
		
		// reset fields
		$this->password = '';
	}
}
?>