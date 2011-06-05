<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/data/user/blog/UserBlogEntryList.class.php');


/**
 * blog entries on facebook page
 * 
 * @author      Torben Brodt
 * @url         http://trac.easy-coding.de/trac/wcf/wiki/FacebookPage
 * @license     GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class FacebookPageBlogListener implements EventListener {
        protected $limit = 5;

        /**
         * @see EventListener::execute()
         */
        public function execute($eventObj, $className, $eventName) {
                switch($eventName) {
                        case 'readData':
                                $eventObj->boxLinks['blog'] = 'Blog';
                                $eventObj->boxContents['blog'] = 'facebookPageBlog';

                                $this->entryList = new UserBlogEntryList();
                                $this->entryList->sqlConditions .= ' user_blog.isPublished = 1';

                                $this->entryList->sqlLimit = $this->limit;
                                $this->entryList->readObjects();
                        break;
                        case 'assignVariables':
                                WCF::getTPL()->assign(array(
                                        'blogEntries' => $this->entryList->getObjects()
                                ));
                        break;
                }
        }
}
?>
