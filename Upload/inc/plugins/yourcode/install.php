<?php
/*
 * Plugin Name: YourCode for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 */

// used by MyBB to provide relevant information about the plugin and also link users to updates.
function yourcode_info()
{
	global $mybb, $lang;

	if(!$lang->yourcode)
	{
		$lang->load('yourcode');
	}

	if(yourcode_is_installed())
	{
		$extra_links = "<ul><li><a href=\"" . YOURCODE_URL . "\" title=\"{$lang->yourcode_admin_view}\">{$lang->yourcode_admin_view}</a></li></ul>";
	}
	else
	{
		$extra_links = "<br />";
	}

	$button_pic = $mybb->settings['bburl'] . '/inc/plugins/yourcode/images/donate.gif';
	$border_pic = $mybb->settings['bburl'] . '/inc/plugins/yourcode/images/pixel.gif';
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

	$name = "<span style=\"font-familiy: arial; font-size: 1.5em; color: #BB0000; text-shadow: 2px 2px 2px #880000;\">{$lang->yourcode}</span>";
	$author = "</a></small></i><a href=\"http://www.wildcardsworld.com\" title=\"Wildcard's World\"><span style=\"font-family: Courier New; font-weight: bold; font-size: 1.2em; color: #0e7109;\">Wildcard</span></a><i><small><a>";

	// This array returns information about the plugin, some of which was prefabricated above based on whether the plugin has been installed or not.
	return array(
		"name"					=> $name,
		"description"			=> $yourcode_description,
		"website"				=> "http://wildcardsworld.com",
		"author"				=> $author,
		"authorsite"			=> "http://www.rantcentralforums.com",
		"version"				=> "1.0.1",
		"compatibility" 		=> "16*",
		"guid" 					=> "36a18ebc285a181a42561141adfd1d7f",
	);
}

/*
 * yourcode_is_installed()
 *
 * returns true if installed/false if not
 */
function yourcode_is_installed()
{
	global $db;
	return $db->table_exists('yourcode');
}

/*
 * yourcode_install()
 *
 * create a new table and fill it with all the existing MyCodes converted, of course to the much moar powerful YourCode version :D
 */
function yourcode_install()
{
	global $db;

	$collation = $db->build_create_table_collation();
	$db->write_query
	(
		"CREATE TABLE " . TABLE_PREFIX . "yourcode
		(
			id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			title VARCHAR(100),
			description TEXT,
			parse_order INT(10) NOT NULL,
			nestable INT(2),
			active INT(2),
			case_sensitive  INT(2),
			single_line  INT(2),
			multi_line  INT(2),
			eval  INT(2),
			regex TEXT,
			replacement TEXT,
			alt_replacement TEXT,
			can_use TEXT,
			can_view TEXT,
			dateline INT(10)
		) ENGINE=MyISAM{$collation};"
	);

	// store all the internal MyCodes that are normally cached and also the default Custom MyCodes as new, moar betterer YourCodes :)
	yourcode_port_old_mycode();
}

/*
 * yourcode_activate()
 *
 * update the version info and admin permissions
 */
function yourcode_activate()
{
	yourcode_set_cache_version();

	// change the permissions to on by default
	change_admin_permission('config', 'yourcode');

	// rebuild the cache just in case admin has upgraded to fix this error:
	// https://github.com/WildcardSearch/YourCode/issues/4
	require_once MYBB_ROOT . "inc/plugins/yourcode/classes/standard.php";
	require_once MYBB_ROOT . "inc/plugins/yourcode/functions.php";
	_yc_build_cache();
}

/*
 * yourcode_deactivate()
 *
 * disables admin permissions for YourCode
 */
function yourcode_deactivate()
{
	global $mybb;

	// remove the permissions
	change_admin_permission('config', 'yourcode', -1);
}

/*
 * yourcode_uninstall()
 *
 * undoes db changes and clears this plugin's cache entry
 */
function yourcode_uninstall()
{
	global $db;

	$db->drop_table('yourcode');
	yourcode_unset_cache();
}

/*
 * yourcode_get_cache_version()
 *
 * check cached version info
 *
 * derived from the work of pavemen in MyBB Publisher
 */
function yourcode_get_cache_version()
{
	global $cache;

	// get currently installed version, if there is one
	$yourcode = $cache->read('yourcode');
	if(is_array($yourcode) && isset($yourcode['version']))
	{
        return $yourcode['version'];
	}
    return 0;
}

/*
 * yourcode_set_cache_version()
 *
 * set cached version info
 *
 * derived from the work of pavemen in MyBB Publisher
 */
function yourcode_set_cache_version()
{
	global $cache;

	// get version from this plugin file
	$yourcode_info = yourcode_info();

	// update version cache to latest
	$yourcode = $cache->read('yourcode');
	$yourcode['version'] = $yourcode_info['version'];
	$cache->update('yourcode', $yourcode);

    return true;
}

/*
 * yourcode_unset_cache_version()
 *
 * remove cached info
 *
 * derived from the work of pavemen in MyBB Publisher
 */
function yourcode_unset_cache()
{
	global $cache;

	$yourcode = $cache->read('yourcode');
	$yourcode = null;
	$cache->update('yourcode', $yourcode);

    return true;
}

/*
 * yourcode_port_old_mycode()
 *
 * store all the internal MyCodes that are normally cached and also the default Custom MyCodes as new, moar betterer YourCodes :)
 */
function yourcode_port_old_mycode()
{
	global $cache, $db;

	// get the Custom MyCodes
	$query = $db->simple_select('mycode');
	if($db->num_rows($query) > 0)
	{
		while($mycode = $db->fetch_array($query))
		{
			$mycode['regex'] = str_replace("\x0", "", $mycode['regex']);
			$mycode['parse_order'] = $mycode['parseorder'];
			$mycode['single_line'] = true;
			$all_mycode[] = $mycode;
		}
	}

	// get the internal MyCode definitions and the YourCode tools
	require_once MYBB_ROOT . "inc/plugins/yourcode/definitions.php";
	require_once MYBB_ROOT . "inc/plugins/yourcode/classes/standard.php";
	require_once MYBB_ROOT . "inc/plugins/yourcode/functions.php";

	foreach($all_mycode as $code)
	{
		// create and load a new object with the stored info and then save it to the db
		$this_code = new YourCode($code);
		$this_code->save();

		// store the actives to save a query when building the cache
		if($this_code->get('active'))
		{
			$active_mycode[] = $this_code;
		}
	}

	// store the info in our cache entry just as MyBB stores internally cached MyCode
	_yc_build_cache($active_mycode);
	return true;
}

?>
