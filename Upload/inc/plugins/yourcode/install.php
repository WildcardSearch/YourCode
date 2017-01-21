<?php
/**
 * installation functionality
 *
 * @category  MyBB Plugins
 * @package   YourCode
 * @author    Mark Vincent <admin@rantcentralforums.com>
 * @copyright 2012-2014 Mark Vincent
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link      https://github.com/WildcardSearch/YourCode
 * @since     1.0
 */

/**
 * provide relevant information about the plugin and link users to updates
 *
 * @return array the info
 */
function yourcode_info()
{
	global $mybb, $lang, $cp_style;

	if (!$lang->yourcode) {
		$lang->load('yourcode');
	}

	if (yourcode_is_installed()) {
		$extra_links = "<ul><li style=\"list-style-image: url(styles/{$cp_style}/images/yourcode/manage.gif)\"><a href=\"" . YOURCODE_URL . "\" title=\"{$lang->yourcode_admin_view}\">{$lang->yourcode_admin_view}</a></li></ul>";

		$button_pic = "styles/{$cp_style}/images/yourcode/donate.gif";
		$border_pic = "styles/{$cp_style}/images/yourcode/pixel.gif";
		$yourcode_description = <<<EOF
<table width="100%">
	<tbody>
		<tr>
			<td>{$lang->yourcode_plugin_description}<br/>{$extra_links}
			</td>
			<td style="text-align: center;">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="VA5RFLBUC4XM4">
					<input style="" type="image" src="{$button_pic}" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="{$border_pic}" width="1" height="1">
				</form>
			</td>
		</tr>
	</tbody>
</table>
EOF;
	} else {
		$extra_links = '<br />';
		$yourcode_description = $lang->yourcode_plugin_description;
	}

	$name = <<<EOF
<span style="font-familiy: arial; font-size: 1.5em; color: #BB0000; text-shadow: 2px 2px 2px #880000;">{$lang->yourcode}</span>
EOF;
	$author = <<<EOF
</a></small></i><a href="http://www.rantcentralforums.com" title="Rant Central"><span style="font-family: Courier New; font-weight: bold; font-size: 1.2em; color: #0e7109;">Wildcard</span></a><i><small><a>
EOF;

	// This array returns information about the plugin, some of which was prefabricated above based on whether the plugin has been installed or not.
	return array(
		"name" => $name,
		"description" => $yourcode_description,
		"website" => 'https://github.com/WildcardSearch/YourCode',
		"author" => $author,
		"authorsite" => 'http://www.rantcentralforums.com',
		"version" => YOURCODE_VERSION,
		"compatibility" => '18*',
		"guid" => '36a18ebc285a181a42561141adfd1d7f',
	);
}

/**
 * inform the MyBB ACP of installation status
 *
 * @return bool true if installed/false if not
 */
function yourcode_is_installed()
{
	global $db;
	return $db->table_exists('yourcode');
}

/**
 * create a new table and fill it with all the existing MyCodes
 * converted, of course, to the much moar powerful YourCode version
 *
 * @return void
 */
function yourcode_install()
{
	if (!class_exists('WildcardPluginInstaller')) {
		require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/WildcardPluginInstaller.php';
	}
	$installer = new WildcardPluginInstaller(MYBB_ROOT . 'inc/plugins/yourcode/install_data.php');
	$installer->install();

	// store all the internal MyCodes that are normally cached and also the default Custom MyCodes as new, moar betterer YourCodes :)
	yourcode_port_old_mycode();
}

/**
 * update the version info and admin permissions
 *
 * @return void
 */
function yourcode_activate()
{
	$old_version = yourcode_get_cache_version();
	if (version_compare($old_version, YOURCODE_VERSION, '<')) {
		$removed_files = array(
			'standard',
			'malleable',
			'storable',
			'portable',
		);
		foreach ($removed_files as $file) {
			@unlink(MYBB_ROOT . "inc/plugins/yourcode/classes/{$file}.php");
		}
		$fullpath = MYBB_ROOT . 'inc/plugins/yourcode/images';
		if (is_dir($fullpath)) {
			@my_rmdir_recursive($fullpath);
			@rmdir($fullpath);
		}
	}
	yourcode_set_cache_version();

	// change the permissions to on by default
	change_admin_permission('config', 'yourcode');

	// rebuild the cache just in case admin has upgraded to fix this error:
	// https://github.com/WildcardSearch/YourCode/issues/4
	require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/YourCode.php';
	yourcode_build_cache();
}

/**
 * disables admin permissions for YourCode
 *
 * @return void
 */
function yourcode_deactivate()
{
	// remove the permissions
	change_admin_permission('config', 'yourcode', -1);
}

/**
 * undoes db changes and clears this plugin's cache entry
 *
 * @return void
 */
function yourcode_uninstall()
{
	if (!class_exists('WildcardPluginInstaller')) {
		require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/WildcardPluginInstaller.php';
	}
	$installer = new WildcardPluginInstaller(MYBB_ROOT . 'inc/plugins/yourcode/install_data.php');
	$installer->uninstall();

	yourcode_unset_cache();
}

/**
 * check cached version info
 *
 * @return string|int the version or 0
 */
function yourcode_get_cache_version()
{
	global $cache;

	// get currently installed version, if there is one
	$yourcode = $cache->read('yourcode');
	if (is_array($yourcode) &&
		isset($yourcode['version'])) {
        return $yourcode['version'];
	}
    return 0;
}

/**
 * set cached version info
 *
 * @return true
 */
function yourcode_set_cache_version()
{
	global $cache;

	// update version cache to latest
	$yourcode = $cache->read('yourcode');
	$yourcode['version'] = YOURCODE_VERSION;
	$cache->update('yourcode', $yourcode);

    return true;
}

/**
 * remove cached info
 *
 * @return true
 */
function yourcode_unset_cache()
{
	global $cache;

	$yourcode = $cache->read('yourcode');
	$yourcode = null;
	$cache->update('yourcode', $yourcode);

    return true;
}

/**
 * store all the internal MyCodes that are normally cached and
 * the default Custom MyCodes as new, moar betterer YourCodes
 *
 * @return true
 */
function yourcode_port_old_mycode()
{
	global $cache, $db;

	// get the Custom MyCodes
	$query = $db->simple_select('mycode');
	if ($db->num_rows($query) > 0) {
		while ($mycode = $db->fetch_array($query)) {
			$mycode['regex'] = str_replace("\x0", '', $mycode['regex']);
			$mycode['parse_order'] = $mycode['parseorder'];
			$mycode['single_line'] = true;
			$all_mycode[] = $mycode;
		}
	}

	// get the internal MyCode definitions and the YourCode tools
	require_once MYBB_ROOT . 'inc/plugins/yourcode/definitions.php';
	require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/YourCode.php';

	foreach ($all_mycode as $code) {
		// create and load a new object with the stored info and then save it to the db
		$this_code = new YourCode($code);
		$this_code->save();

		// store the actives to save a query when building the cache
		if ($this_code->get('active')) {
			$active_mycode[] = $this_code;
		}
	}

	// store the info in our cache entry just as MyBB stores internally cached MyCode
	yourcode_build_cache($active_mycode);
	return true;
}

?>
