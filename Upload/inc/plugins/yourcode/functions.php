<?php
/**
 * functions for the entire project
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
 * get all of the YourCode stored in the db and return them as an array of objects
 *
 * @param  int first record to query
 * @param  int max records to query
 * @return array the YourCodes if any exist
 */
function yourcode_get_all($start = 0, $limit = '')
{
	global $db;

	$return_array = array();
	$options = array("order_by" => 'parse_order ASC, title ASC', "limit_start" => $start);
	if($limit)
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

/**
 * retrieve all modules optionally filtered by active state
 *
 * @param  array an unindexed array of active module basenames
 * @return array any modules found
 */
function yourcode_get_modules($actives = '')
{
	$modules = array();
	foreach(new DirectoryIterator(YOURCODE_MOD_URL) as $file)
	{
		if(!$file->isFile() ||
		   $file->isDot() ||
		   $file->isDir() ||
		   pathinfo($file->getFilename(), PATHINFO_EXTENSION) != 'php')
		{
			continue;
		}

		// extract the base_name from the module filename
		$filename = $file->getFilename();
		$module = substr($filename, 0, strlen($filename) - 4);

		if(is_array($actives) && !empty($actives) && !in_array($module, $actives))
		{
			continue;
		}

		// attempt to load the module
		$modules[$module] = new YourCodeModule($module);
	}
	return $modules;
}

?>
