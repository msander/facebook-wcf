<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * displays facebook button at registration
 */
class RegisterFormFacebookListener implements EventListener {

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {

		switch ($eventName) {
			case 'assignVariables':
				$this->assignVariables();
				break;
		}
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		WCF::getTPL()->append(
			'additionalFields',
			$this->getFacebookButton()
		);
	}
	
	/**
	 * since register modules are often changed by woltlab, the easiest way to plugin the the payment process header is by javascript
	 */
	protected function getFacebookButton() {
		return '<script type="text/javascript">
			onloadEvents.push(function() {
				$("mainContainer").select("form > .formSubmit");
			});
			</script>
		';
	}
}
?>
