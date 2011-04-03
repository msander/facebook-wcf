<?php
/**
 * @author	Torben Brodt
 * @copyright	2010 easy-coding.de
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
// update package name
$sql = "UPDATE	wcf".WCF_N."_package
	SET	package = 'de.easy-coding.wcf.facebook'
	WHERE	package = 'org.gnex.facebook.auth'";
WCF::getDB()->sendQuery($sql);

if (!function_exists('curl_init')) {
  throw new SystemException('Facebook needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new SystemException('Facebook needs the JSON PHP extension.');
}

// try to delete this file
@unlink(__FILE__);
?>
