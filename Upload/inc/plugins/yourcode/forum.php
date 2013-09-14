<?php
/*
 * Plugin Name: YourCode for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 */

/*
 * yourcode_run($message)
 *
 * implements the parser hook to check the mycode cache and store our codes before MyBB has a chance to
 */
$plugins->add_hook('parse_message_start', 'yourcode_run', 1);
function yourcode_run($message)
{
	global $parser;
	static $yourcode;

	// if the parser's MyCode cache is anything other than the default value then we have already been here
	if($parser->mycode_cache == 0)
	{
		// we have to consider several sequential iterations calling the method we have hooked into
		if(!isset($yourcode) || !is_array($yourcode) || empty($yourcode))
		{
			// load the cache if this is the first run
			global $cache;
			$yourcode = $cache->read('yourcode');
		}

		if(is_array($yourcode['active']['restricted_view']['standard']) && !empty($yourcode['active']['restricted_view']['standard']))
		{
			foreach($yourcode['active']['restricted_view']['standard'] as $code)
			{
				if($code['can_view'])
				{
					if(!yourcode_check_user_permissions($code['can_view']))
					{
						$code['replacement'] = $code['alt_replacement'];
					}
				}
				$yourcode['active']['simple']['standard']['find'][] = $code['find'];
				$yourcode['active']['simple']['standard']['replacement'][] = $code['replacement'];
			}
		}
		if(is_array($yourcode['active']['restricted_view']['nestable']) && !empty($yourcode['active']['restricted_view']['nestable']))
		{
			foreach($yourcode['active']['restricted_view']['nestable'] as $code)
			{
				if($code['can_view'])
				{
					if(!yourcode_check_user_permissions($code['can_view']))
					{
						$code['replacement'] = $code['alt_replacement'];
					}
				}
				$yourcode['active']['simple']['nestable'][] = array('find' => $code['find'], 'replacement' => $code['replacement']);
			}
		}
		$parser->mycode_cache = $yourcode['active']['simple'];
	}
	// give back what was freely given to us
	return $message;
}

/*
 * yourcode_datahandler_post_update()
 *
 * blocks unauthorized users from posting restricted YourCode on Quick Reply
 */
$plugins->add_hook("datahandler_post_update", "yourcode_datahandler_post_update");
function yourcode_datahandler_post_update($this_post)
{
	global $cache, $posthandler, $db, $message;
	$yourcode = $cache->read('yourcode');

	$all_codes = array_merge((array) $yourcode['active']['restricted_use']['standard'], (array) $yourcode['active']['restricted_use']['nestable']);

	$message = yourcode_police_message($all_codes, $this_post->data['message']);
	$posthandler->post_update_data['message'] = $db->escape_string($message);
}

/*
 * yourcode_newreply_do_new_start()
 *
 * blocks unauthorized users from posting restricted YourCode on new thread/reply full
 */
$plugins->add_hook('newreply_do_newreply_start', 'yourcode_newreply_do_new_start');
$plugins->add_hook('newthread_do_newthread_start', 'yourcode_newreply_do_new_start');
function yourcode_newreply_do_new_start()
{
	global $mybb, $cache;
	$yourcode = $cache->read('yourcode');

	$all_codes = array_merge((array) $yourcode['active']['restricted_use']['standard'], (array) $yourcode['active']['restricted_use']['nestable']);

	$mybb->input['message'] = yourcode_police_message($all_codes, $mybb->input['message']);
}

/*
 * yourcode_police_message()
 *
 * removes restricted YourCode from a disallowed user's post message
 *
 * @param - $codes - (array) the restricted YourCode (note: not in object form)
 * @param - $message - (string) the post contents
 */
function yourcode_police_message(array $codes, $message)
{
	if($message)
	{
		foreach($codes as $code)
		{
			if($code['can_use'])
			{
				if(!yourcode_check_user_permissions($code['can_use']))
				{
					$has_changed = true;
					while(preg_match($code['regex'], $message))
					{
						$message = preg_replace($code['regex'], '', $message);
					}
				}
			}
			if(!$message)
			{
				break;
			}
		}
	}
	return $message;
}

/*
 * function yourcode_check_user_permissions()
 *
 * check the current user's permissions against a list of allowed groups
 *
 * @param - $good_groups - (mixed) an unindexed array of allowed group IDs or a comma-separated list of allowed group IDs
 */
function yourcode_check_user_permissions($good_groups)
{
	// array-ify the list if necessary
	if(!is_array($good_groups))
	{
		$good_groups = explode(',', $good_groups);
	}

	// no groups = all groups says wildy
	if(empty($good_groups))
	{
		return true;
	}

	// get all the user's groups in one array
	global $mybb;
	$users_groups = array($mybb->user['usergroup']);
	$adtl_groups = explode(',', $mybb->user['additionalgroups']);
	array_merge($users_groups, $adtl_groups);

	/* if any overlaps occur then they will be in $test_array,
	 * empty returns true/false so !empty = true for allow and false for disallow
	 */
	$test_array = array_intersect($users_groups, $good_groups);
	return !empty($test_array);
}

?>
