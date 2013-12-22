<?php
/*
 * Plugin Name: YourCode for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 */

/*
 * function _yc_get_all()
 *
 * get all of the YourCode stored in the db and return them as an array of objects
 *
 * @param - $start - (int) first record to query
 * @param - $limit - (int) max records to query
 */
function _yc_get_all($start = 0, $limit = '')
{
	global $db;

	$return_array = array();
	$options = array("order_by" => 'parse_order ASC, title ASC', "limit_start" => $start);
	if(isset($limit) && $limit)
	{
		$options['limit'] = $limit;
	}
	$query = $db->simple_select('yourcode', '*', '', $options);
	if($db->num_rows($query) > 0)
	{
		while($this_data = $db->fetch_array($query))
		{
			$return_array[$this_data['id']] = new YourCode($this_data);
		}
	}
	return $return_array;
}

/*
 * function _yc_build_cache()
 *
 * @param - $codelist (array) an array of active YourCodes
 */
function _yc_build_cache($codelist = '')
{
	if(!is_array($codelist) || empty($codelist))
	{
		$codelist = _yc_get_all();
	}

	$restricted_use_code = $restricted_view_code = array();
	/*
	 * if there is no YourCode at all then to prevent errors from occurring
	 * we need to add at the correct blank arrays
	 */
	$mycode_cache = array
	(
		"standard" => array
		(
			"find" => array(),
			"replacement" => array()
		),
		"nestable" => array(),
		"callback" => array()
	);

	if(is_array($codelist) && !empty($codelist))
	{
		foreach($codelist as $code)
		{
			if(!$code->get('active'))
			{
				continue;
			}

			$modifiers = '';
			foreach(array("s" => 'single_line', "m" => 'multi_line', "e" => 'eval') as $modifier => $property)
			{
				if($code->get($property))
				{
					$modifiers .= $modifier;
				}
			}

			if(!$code->get('case_sensitive'))
			{
				$modifiers .= 'i';
			}

			$regex = "#" . str_replace("\x0", "", $code->get('regex')) . "#{$modifiers}";

			if($code->get('can_view'))
			{
				if($code->get('nestable'))
				{
					$restricted_view_code['nestable'][(int) $code->get('id')] = array("find" => $regex, "replacement" => $code->get('replacement'), "alt_replacement" => $code->get('alt_replacement'), "can_view" => $code->get('can_view'));
				}
				else
				{
					$restricted_view_code['standard'][(int) $code->get('id')]['find'] = $regex;
					$restricted_view_code['standard'][(int) $code->get('id')]['replacement'] = $code->get('replacement');
					$restricted_view_code['standard'][(int) $code->get('id')]['alt_replacement'] = $code->get('alt_replacement');
					$restricted_view_code['standard'][(int) $code->get('id')]['can_view'] = $code->get('can_view');
				}
			}
			else
			{
				if($code->get('nestable'))
				{
					$mycode_cache['nestable'][] = array('find' => $regex, 'replacement' => $code->get('replacement'));
				}
				else
				{
					$mycode_cache['standard']['find'][] = $regex;
					$mycode_cache['standard']['replacement'][] = $code->get('replacement');
				}
			}

			$regex = "#" . str_replace("\x0", "", $code->get('regex')) . "#{$modifiers}";

			if($code->get('can_use'))
			{
				if($code->get('nestable'))
				{
					$restricted_use_code['nestable'][(int) $code->get('id')] = array("regex" => $regex, "can_use" => $code->get('can_use'));
				}
				else
				{
					$restricted_use_code['standard'][(int) $code->get('id')]['regex'] = $regex;
					$restricted_use_code['standard'][(int) $code->get('id')]['can_use'] = $code->get('can_use');
				}
			}
		}
	}

	global $cache;
	$yourcode = $cache->read('yourcode');
	$yourcode['active']['simple'] = $mycode_cache;
	$yourcode['active']['restricted_view'] = $restricted_view_code;
	$yourcode['active']['restricted_use'] = $restricted_use_code;
	$cache->update('yourcode', $yourcode);
}

/*
 * _yc_url()
 *
 * builds a url from standard options array
 *
 * @param - $options - (array) keyed to standard url options
 */
function _yc_url($options, $url = '')
{
	if(!$url)
	{
		$url = YOURCODE_URL;
	}

	$sep = '&amp;';
	if(strpos($url, '?') === false)
	{
		$sep = '?';
	}

	// check for the allowed options
	foreach(array('script', 'style', 'action', 'mode', 'type', 'name', 'id', 'page', 'my_post_key') as $item)
	{
		if(isset($options[$item]) && $options[$item])
		{
			// and add them if set
			$url .= "{$sep}{$item}={$options[$item]}";
			$sep = '&amp;';
		}
	}
	return $url;
}

/*
 * _yc_link()
 *
 * builds an HTML anchor from the provided options
 *
 * @param - $url - (string) the address
 * @param - $title - (string) the title of the link
 * @param - $options - (array) options to effect the HTML output
 */
function _yc_link($url, $caption = "", $options = "")
{
	if(is_array($options) && !empty($options))
	{
		foreach(array('onclick', 'style', 'class', 'title') as $key)
		{
			if(isset($options[$key]) && $options[$key])
			{
				$$key = <<<EOF
{$key}="{$options[$key]}"
EOF;
			}
		}
	}

	if(!isset($caption) || !$caption)
	{
		$caption = $url;
	}
	if(!isset($title) || !$title)
	{
		$title = " title=\"{$caption}\"";
	}

	return <<<EOF
<a href="{$url}"{$title}{$onclick}{$style}{$class}>{$caption}</a>
EOF;
}

/*
 * function _yc_clear_all()
 *
 * deletes all existing YourCode
 */
function _yc_clear_all()
{
	global $db;
	return $db->delete_query('yourcode');
}

/*
 * function _yc_backup()
 *
 * performs an export of all existing YourCode
 */
function _yc_backup()
{
	global $lang;

	$all_codes = _yc_get_all();
	$rows = '';

	foreach($all_codes as $code)
	{
		$this_row = $code->build_row();
		$id = $code->get('id');
		$rows .= <<<EOF
<yourcode_{$id}>
{$this_row}	</yourcode_{$id}>

EOF;
	}

	// set up the XML
	$xml = <<<EOF
<?xml version="1.0" encoding="{$lang->settings['charset']}"?>
<yourcode version="1.0" xmlns="http://www.wildcardsworld.com">
{$rows}</yourcode>
EOF;

	// send out headers (opens a save dialogue)
	header('Content-Disposition: attachment; filename=yourcode_full-backup.xml');
	header('Content-Type: application/xml');
	header('Content-Length: ' . strlen($xml));
	header('Pragma: no-cache');
	header('Expires: 0');
	echo $xml;
}

/*
 * function _yc_restore()
 *
 * clears all existing YourCode and restores to the contents of an external backup file
 *
 * @param - $xml - (string) the contents of the XML file to import
 */
function _yc_restore($xml)
{
	if($xml)
	{
		require_once MYBB_ROOT . 'inc/class_xml.php';
		$parser = new XMLParser($xml);
		$tree = $parser->get_tree();

		// doing a multiple YourCode restore, fail if not multi
		if(is_array($tree) && is_array($tree['yourcode']) && is_array($tree['yourcode']['attributes']))
		{
			foreach($tree['yourcode'] as $property => $this_entry)
			{
				// skip the info
				if(in_array($property, array('tag', 'value', 'attributes')))
				{
					continue;
				}

				// if there is data
				if(is_array($this_entry) && !empty($this_entry))
				{
					$data = array();

					foreach($this_entry as $key => $value)
					{
						// skip the info
						if(in_array($key, array('tag', 'value')))
						{
							continue;
						}

						// get the field name from the array key
						$key_array = explode('-', $key);
						$newkey = $key_array[0];

						$data[$newkey] = $value['value'];
					}
					$this_code = new YourCode($data);
					$this_code->save();
				}
			}
			return true;
		}
	}
	return false;
}

/*
 * function _yc_import_check()
 *
 * examine the XML file and extract all the objects without saving them in the database
 *
 * @param - $xml - (string) the contents of the XML file to import
 */
function _yc_import_check($xml)
{
	if($xml)
	{
		require_once MYBB_ROOT . 'inc/class_xml.php';
		$parser = new XMLParser($xml);
		$tree = $parser->get_tree();

		// doing a multiple YourCode restore, fail if not multi
		if(is_array($tree) && is_array($tree['yourcode']) && is_array($tree['yourcode']['attributes']))
		{
			$return_codes = array();

			foreach($tree['yourcode'] as $property => $this_entry)
			{
				// skip the info
				if(in_array($property, array('tag', 'value', 'attributes')))
				{
					continue;
				}

				// if there is data
				if(is_array($this_entry) && !empty($this_entry))
				{
					$data = array();

					foreach($this_entry as $key => $value)
					{
						// skip the info
						if(in_array($key, array('tag', 'value')))
						{
							continue;
						}

						// get the field name from the array key
						$key_array = explode('-', $key);
						$newkey = $key_array[0];
						$id = (int) $key_array[(int) count($key_array) - 1];

						$data[$newkey] = $value['value'];
					}
					$this_code = new YourCode($data);
					$return_codes[(int) $id] = $this_code;
				}
			}
			return $return_codes;
		}
	}
	return false;
}

?>
