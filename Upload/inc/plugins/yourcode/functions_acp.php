<?php
/**
 * functions for the ACP
 *
 * @category  MyBB Plugins
 * @package   YourCode
 * @author    Mark Vincent <admin@rantcentralforums.com>
 * @copyright 2012-2014 Mark Vincent
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link      https://github.com/WildcardSearch/YourCode
 * @since     1.1
 */

/**
 * build the cache of YourCode info
 *
 * @param  array active YourCodes
 * @return void
 */
function yourcode_build_cache($codelist = '')
{
	if(!is_array($codelist) || empty($codelist))
	{
		$codelist = yourcode_get_all();
	}

	$restricted_use_code = $restricted_view_code = array();
	/*
	 * if there is no YourCode at all then to prevent errors from occurring
	 * we need to add at the correct blank arrays
	 */
	$mycode_cache = array(
		"standard" => array(
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

			$regex = "#" . str_replace("\x0", '', $code->get('regex')) . "#{$modifiers}";

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

			$regex = "#" . str_replace("\x0", '', $code->get('regex')) . "#{$modifiers}";

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

/**
 * deletes all existing YourCode
 *
 * @return mixed the return of the database wrapper object delete method
 */
function yourcode_clear_all()
{
	global $db;
	return $db->delete_query('yourcode');
}

/**
 * performs an export of all existing YourCode
 *
 * @return void
 */
function yourcode_backup()
{
	global $lang;

	$all_codes = yourcode_get_all();
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

/**
 * examine the XML file and extract all the objects without saving them in the database
 *
 * @param  string the contents of the XML file to import
 * @return array|bool imported YourCode if all is well, false if not
 */
function yourcode_import_check($xml)
{
	if(!$xml)
	{
		return false;
	}

	require_once MYBB_ROOT . 'inc/class_xml.php';
	$parser = new XMLParser($xml);
	$tree = $parser->get_tree();

	// doing a multiple YourCode restore, fail if not multi
	if(!is_array($tree) || !is_array($tree['yourcode']) || !is_array($tree['yourcode']['attributes']))
	{
		return false;
	}

	$return_codes = array();
	foreach($tree['yourcode'] as $property => $this_entry)
	{
		// skip the info
		if(in_array($property, array('tag', 'value', 'attributes')) ||
		   !is_array($this_entry) ||
		   empty($this_entry))
		{
			continue;
		}

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
			$id = (int) $key_array[count($key_array) - 1];

			$data[$newkey] = $value['value'];
		}
		$return_codes[$id] = new YourCode($data);
	}
	return $return_codes;
}

/**
 * validate an uploaded file and return its contents
 *
 * @param  string the name of the file input
 * @param  string the redirect URL on error
 * @return string the file contents
 */
function yourcode_check_uploaded_file($name = 'file', $return_url = '')
{
	global $lang;

	if(!$_FILES[$name] || $_FILES[$name]['error'] == 4)
	{
		flash_message($lang->yourcode_import_no_file, 'error');
		admin_redirect($return_url);
	}

	if($_FILES[$name]['error'])
	{
		flash_message($lang->sprintf($lang->yourcode_import_file_error, $_FILES['file']['error']), 'error');
		admin_redirect($return_url);
	}

	if(!is_uploaded_file($_FILES[$name]['tmp_name']))
	{
		flash_message($lang->yourcode_import_file_upload_error, 'error');
		admin_redirect($return_url);
	}

	$content = @file_get_contents($_FILES[$name]['tmp_name']);
	@unlink($_FILES[$name]['tmp_name']);

	if(strlen(trim($content)) == 0)
	{
		flash_message($lang->yourcode_import_file_empty, 'error');
		admin_redirect($return_url);
	}
	return $content;
}

?>
