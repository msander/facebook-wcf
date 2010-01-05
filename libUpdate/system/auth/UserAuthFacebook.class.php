<?php
require_once(WCF_DIR.'lib/system/auth/UserAuthDefault.class.php');
/**
 * Facebook-API
 * 
 * @author	Tim Wittenberg
 * @copyright	GneX
 * @license	GneX*Lizenz
 */
class UserAuthFacebook extends UserAuthDefault {

	/**
	 * Creates a new UserAuthDefault object.
	 */
	public function __construct() {
		if (!MODULE_FACEBOOK) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('org.gnex.facebook.disabled'));
		}
		
		parent::__construct();
	}
	
	/**
	 * Loads instance of UserAuthFacebook instead of UserAuthDefault
	 *
	 */
	public static function loadInstance() {
		if (self::$instance === null) self::$instance = new UserAuthFacebook();
	}
	
	public static function loginManuallyFacebook($facebookID, $userClassname = 'UserSession') {
	  $user = null;
	  
    $sql = "SELECT userID, facebookIdentifierHash, facebookIdentifierSalt FROM wcf".WCF_N."_user
            WHERE facebookIdentifier = '".escapeString($facebookID)."'";
    $row = WCF::getDB()->getFirstRow($sql);
    
    if (!$row['userID'] || !$row['facebookIdentifierHash'] || !$row['facebookIdentifierSalt']) return false;
    if ($row['facebookIdentifierHash'] != StringUtil::encrypt($row['facebookIdentifierSalt'] . StringUtil::getSaltedHash($facebookID, $row['facebookIdentifierSalt']))) return false;
    
    $user = new $userClassname($row['userID']);
    return $user;
	}
	
	public function storeAccessDataFacebook(User $user, $facebookID) {
		HeaderUtil::setCookie('userID', $user->userID, TIME_NOW + 365 * 24 * 3600);
		HeaderUtil::setCookie('facebookIdentifier', StringUtil::getSaltedHash($facebookID, $user->facebookIdentifierSalt), TIME_NOW + 365 * 24 * 3600);
	}
	
	/**
	 * @see UserAuth::loginAutomatically()
	 */
	public function loginAutomatically($persistent = false, $userClassname = 'UserSession') {
		if (!$persistent) return null;
		
		$user = null;
		if (isset($_COOKIE[COOKIE_PREFIX.'userID']) && isset($_COOKIE[COOKIE_PREFIX.'facebookIdentifier'])) {
			if (!($user = $this->getUserAutomatically(intval($_COOKIE[COOKIE_PREFIX.'userID']), $_COOKIE[COOKIE_PREFIX.'facebookIdentifier'], $userClassname))) {
				$user = null;
				// reset cookie
				HeaderUtil::setCookie('userID', '');
				HeaderUtil::setCookie('facebookIdentifier', '');
			}
		}
		
		return $user;
	}
	
	/**
	 * Returns a user object or null on failure.
	 * 
	 * @param	integer		$userID
	 * @param	string		$facebookID
	 * @param	string		$userClassname
	 * @return	User	
	 */
	protected function getUserAutomatically($userID, $facebookID, $userClassname = 'UserSession') {
		$user = new $userClassname($userID);
		if (!$user->userID || !$this->checkCookieFacebookID($user, $facebookID)) {
			$user = null;
		}
		
		return $user;
	}
	
	/**
	 * Validates the cookie facebookID.
	 * 
	 * @param	User		$user
	 * @param	string		$facebookID
	 * @return	boolean
	 */
	protected function checkCookieFacebookID($user, $facebookID) {
		return ($user->facebookIdentifierHash == StringUtil::encrypt($user->facebookIdentifierSalt . $facebookID));
	}
}
?>