<?php
/**
 * the forum-side routines are housed here
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
 * implements the parser hook to check the mycode cache and
 * store our codes before MyBB has a chance to
 *
 * @param  string the post message
 * @return string the altered message
 */
$plugins->add_hook('parse_message_start', 'yourcode_run', 1);
function yourcode_run($message)
{
	global $parser;
	static $yourcode;

	/*
	 * if the parser isn't valid or its MyCode cache is
	 * anything other than the default value then there is
	 * nothing to do
	 */
	if ($parser instanceof postParser != true ||
		$parser->mycode_cache !== 0) {
		return $message;
	}

	// we have to consider several sequential iterations calling the method we have hooked into
	if (!isset($yourcode) ||
		!is_array($yourcode) ||
		empty($yourcode)) {
		// load the cache if this is the first run
		global $cache;
		$yourcode = $cache->read('yourcode');
	}

	if (is_array($yourcode['active']['restricted_view']['standard']) &&
		!empty($yourcode['active']['restricted_view']['standard'])) {
		foreach ($yourcode['active']['restricted_view']['standard'] as $code) {
			if ($code['can_view']) {
				if (!yourcode_check_user_permissions($code['can_view'])) {
					$code['replacement'] = $code['alt_replacement'];
				}
			}
			$yourcode['active']['simple']['standard']['find'][] = $code['find'];
			$yourcode['active']['simple']['standard']['replacement'][] = $code['replacement'];
		}
	}

	$yourcode['active']['simple']['standard_count'] = count($yourcode['active']['simple']['standard']);

	if (is_array($yourcode['active']['restricted_view']['nestable']) &&
		!empty($yourcode['active']['restricted_view']['nestable'])) {
		foreach ($yourcode['active']['restricted_view']['nestable'] as $code) {
			if ($code['can_view']) {
				if (!yourcode_check_user_permissions($code['can_view'])) {
					$code['replacement'] = $code['alt_replacement'];
				}
			}
			$yourcode['active']['simple']['nestable'][] = array('find' => $code['find'], 'replacement' => $code['replacement']);
		}
	}

	$yourcode['active']['simple']['nestable_count'] = count($yourcode['active']['simple']['nestable']);

	if (is_array($yourcode['active']['restricted_view']['callback']) &&
		!empty($yourcode['active']['restricted_view']['callback'])) {
		foreach ($yourcode['active']['restricted_view']['callback'] as $code) {
			if ($code['can_view'] &&
				!yourcode_check_user_permissions($code['can_view'])) {
				$yourcode['active']['simple']['nestable'][] = array('find' => $code['find'], 'replacement' => $code['alt_replacement']);
				$yourcode['active']['simple']['nestable_count']++;
			} else {
				$yourcode['active']['simple']['callback'][] = array('find' => $code['find'], 'replacement' => $code['replacement']);
			}
		}
	}
	$yourcode['active']['simple']['callback_count'] = count($yourcode['active']['simple']['callback']);

	foreach ($yourcode['active']['simple']['callback'] as &$code) {
		$code['replacement'] = array($parser, $code['replacement']);
	}

	$parser->mycode_cache = $yourcode['active']['simple'];
	return $message;
}

/**
 * allows any active modules to parse the message
 *
 * @param  string the post message
 * @return string the altered message
 */
$plugins->add_hook('parse_message', 'yourcode_run_modules', 1);
function yourcode_run_modules($message)
{
	static $yourcode, $all_modules;

	// we have to consider several sequential iterations calling the method we have hooked into
	if (!isset($yourcode) ||
		!is_array($yourcode) ||
		empty($yourcode)) {
		// load the cache if this is the first run
		global $cache;
		$yourcode = $cache->read('yourcode');
	}

	// are there any active modules?
	if (is_array($yourcode['active']['modules']) &&
		!empty($yourcode['active']['modules'])) {
		if (!$all_modules) {
			// load all the active modules as an array of objects
			require_once MYBB_ROOT . "inc/plugins/yourcode/functions.php";
			$all_modules = yourcode_get_modules($yourcode['active']['modules']);
		}

		// check again to be sure there were results
		if (is_array($all_modules) &&
			!empty($all_modules)) {
			foreach ($all_modules as $module) {
				// then let each module have their way with the message >:D
				$message = $module->parse_message($message);
			}
		}
	}

	// give back what was freely given to us
	return $message;
}

/**
 * blocks unauthorized users from posting restricted YourCode on Quick Reply
 *
 * @param  object the post data object
 * @return void
 */
$plugins->add_hook('datahandler_post_update', 'yourcode_datahandler_post_update');
function yourcode_datahandler_post_update($this_post)
{
	global $mybb, $cache, $posthandler, $db, $message;
	if (THIS_SCRIPT == 'xmlhttp.php' &&
		$mybb->input['action'] == 'edit_subject') {
		return;
	}

	$yourcode = $cache->read('yourcode');

	$all_codes = array_merge((array) $yourcode['active']['restricted_use']['standard'], (array) $yourcode['active']['restricted_use']['nestable'], (array) $yourcode['active']['restricted_use']['callback']);

	$message = yourcode_police_message($all_codes, $this_post->data['message']);
	$posthandler->post_update_data['message'] = $db->escape_string($message);
}

/**
 * blocks unauthorized users from posting restricted YourCode on new thread/reply full
 *
 * @return void
 */
$plugins->add_hook('newreply_do_newreply_start', 'yourcode_newreply_do_new_start');
$plugins->add_hook('newthread_do_newthread_start', 'yourcode_newreply_do_new_start');
function yourcode_newreply_do_new_start()
{
	global $mybb, $cache;
	$yourcode = $cache->read('yourcode');

	$all_codes = array_merge((array) $yourcode['active']['restricted_use']['standard'], (array) $yourcode['active']['restricted_use']['nestable'], (array) $yourcode['active']['restricted_use']['nestable']);

	$mybb->input['message'] = yourcode_police_message($all_codes, $mybb->input['message']);
}

/**
 * removes restricted YourCode from a disallowed user's post message
 *
 * @param  array the restricted YourCode (note: not in object form)
 * @param  string the post contents
 * @return string the altered message
 */
function yourcode_police_message(array $codes, $message)
{
	if ($message) {
		foreach ($codes as $code) {
			if (!$code['can_use'] ||
				yourcode_check_user_permissions($code['can_use'])) {
				continue;
			}

			$has_changed = true;
			while ($message && preg_match($code['regex'], $message)) {
				$message = preg_replace($code['regex'], '', $message);
			}

			if (!$message) {
				break;
			}
		}
	}
	return $message;
}

/**
 * check the current user's permissions against a list of allowed groups
 *
 * @param - $good_groups - (mixed) an unindexed array of allowed group IDs or a comma-separated list of allowed group IDs
 * @return bool true if the user can view the YourCode, false if not
 */
function yourcode_check_user_permissions($good_groups)
{
	// array-ify the list if necessary
	if (!is_array($good_groups)) {
		$good_groups = explode(',', $good_groups);
	}

	// no groups = all groups says wildy
	if (empty($good_groups)) {
		return true;
	}

	// get all the user's groups in one array
	global $mybb;
	$users_groups = array($mybb->user['usergroup']);
	$adtl_groups = explode(',', $mybb->user['additionalgroups']);
	array_merge($users_groups, $adtl_groups);

	// !empty = true for allow and false for disallow
	return !empty(array_intersect($users_groups, $good_groups));
}

?>
