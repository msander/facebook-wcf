<?php
/**
 * @author	Torben Brodt
 * @copyright	2010 easy-coding.de
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */

if (!function_exists('curl_init')) {
  throw new SystemException('Facebook needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new SystemException('Facebook needs the JSON PHP extension.');
}

// try to delete this file
@unlink(__FILE__);
?>
