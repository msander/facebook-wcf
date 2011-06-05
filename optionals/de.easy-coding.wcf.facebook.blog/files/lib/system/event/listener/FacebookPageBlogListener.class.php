<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * blog entries on facebook page
 * 
 * @author	Torben Brodt
 * @url		http://trac.easy-coding.de/trac/wcf/wiki/FacebookPage
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class FacebookPageBlogListener implements EventListener {

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		switch($eventName) {
			case 'readData':
				$eventObj->boxLinks['blog'] = 'Blog';
				$eventObj->boxContents['blog'] = 'facebookPageBlog';
				
				$this->entryList = new UserBlogFeedEntryList();
				$this->entryList->sqlConditions .= ' AND user_blog.time > '.($this->hours ? (TIME_NOW - $this->hours * 3600) : (TIME_NOW - 30 * 86400));
				$this->entryList->sqlConditions .= ' AND user_blog.isPublished = 1';
				
		                $this->entryList->sqlLimit = $this->limit;
		                $this->entryList->readObjects();
			break;
			case 'assignVariables':
				WCF::getTPL()->assign(
					'blogEntries' => $this->entryList->getObjects()
				);
			break;
		}
	}
}
?>
