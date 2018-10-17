<?php
/**
 * the main plugin file; splits forum and ACP scripts to decrease footprint
 *
 * @category  MyBB Plugins
 * @package   YourCode
 * @author    Mark Vincent <admin@rantcentralforums.com>
 * @copyright 2012-2014 Mark Vincent
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link      https://github.com/WildcardSearch/YourCode
 * @since     1.0
 */

// disallow direct access to this file for security reasons
if (!defined('IN_MYBB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

define('YOURCODE_MOD_URL', MYBB_ROOT. 'inc/plugins/yourcode/modules');
define('YOURCODE_VERSION', '2.1.3');

// register custom class autoloader
spl_autoload_register('yourCodeClassAutoLoad');

require_once MYBB_ROOT . 'inc/plugins/yourcode/functions.php';

// load the install/admin routines only if in ACP.
if (defined('IN_ADMINCP')) {
    require_once MYBB_ROOT . 'inc/plugins/yourcode/acp.php';
} else {
	require_once MYBB_ROOT . 'inc/plugins/yourcode/forum.php';
}

 /**
  * class autoloader
  *
  * @param string the name of the class to load
  */
function yourCodeClassAutoLoad($className) {
	$path = MYBB_ROOT . "inc/plugins/yourcode/classes/{$className}.php";

	if (file_exists($path)) {
		require_once $path;
	}
}

?>
