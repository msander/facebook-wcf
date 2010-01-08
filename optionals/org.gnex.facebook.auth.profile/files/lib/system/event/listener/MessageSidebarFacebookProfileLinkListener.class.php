<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

class MessageSidebarFacebookProfileLinkListener implements EventListener {

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		foreach ($eventObj->messageSidebars as $id => $sidebar) {
	    if($sidebar->getUser()->facebookIdentifier) {
	      $sidebar->addUserContact('<a href="http://facebook.com/profile.php?id='.$sidebar->getUser()->facebookIdentifier.'"><img src="'.StyleManager::getStyle()->getIconPath('facebookS.png').'" alt="" /></a>');
	    }
		}
	}
}
?>