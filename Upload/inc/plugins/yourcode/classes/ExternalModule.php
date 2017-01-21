<?php
/**
 * Wildcard Helper Classes - External PHP Module Wrapper
 *
 * for facilitating the safe use of external PHP modules from within a MYBB
 * plugin
 *
 * @category  MyBB Plugins
 * @package   YourCode
 * @author    Mark Vincent <admin@rantcentralforums.com>
 * @copyright 2012-2014 Mark Vincent
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link      https://github.com/WildcardSearch/YourCode
 * @since     1.1
 */

if (!class_exists('MalleableObject')) {
	require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/MalleableObject.php';
}

/**
 * standard interface for external PHP modules
 *
 * can be used to wrap any external PHP module for secure loading,
 * validation and execution of its functions
 */
interface ExternalModuleInterface
{
	public function load($name);
	public function run($function_name, $args = '');
}

/**
 * a standard wrapper for external PHP routines built upon the
 * MalleableObject abstract class and abiding by ExternalModuleInterface
 */
abstract class ExternalModule extends MalleableObject implements ExternalModuleInterface
{
	/**
	 * @var string the module title
	 */
	protected $title = '';

	/**
	 * @var string the module description
	 */
	protected $description = '';

	/**
	 * @var string the module version
	 */
	protected $version = '0';

	/**
	 * @var the module path
	 */
	protected $path = '';

	/**
	 * @var the module prefix
	 */
	protected $prefix = '';

	/**
	 * @var the internal module name
	 */
	protected $base_name = '';

	/**
	 * attempt to load and validate the module
	 *
	 * @param  string base name of the module to load
	 * @param  string fully qualified path to the modules
	 * @return void
	 */
	public function __construct($module)
	{
		// if there is data
		if ($module) {
			// attempt to load it and return the results
			$this->valid = $this->load($module);
			return;
		}
		// new object
		$this->valid = false;
	}

	/**
	 * attempt to load the module's info
	 *
	 * @param  string base name of the module to load
	 * @return bool true on success, false on fail
	 */
	public function load($module)
	{
		// good info?
		if ($module &&
			$this->path &&
			$this->prefix) {
			// store the unique name
			$this->base_name = trim($module);

			// store the info
			$info = $this->run('info');
			return $this->set($info);
		}
		return false;
	}

	/**
	 * safely access the module's function
	 *
	 * @param  string the function to execute
	 * @param  array any data to pass to the function
	 * @return mixed|false the return value of the called module
	 * function or false on error
	 */
	public function run($function_name, $args = '')
	{
		$function_name = trim($function_name);
		if ($function_name &&
			$this->base_name &&
			$this->path &&
			$this->prefix) {
			$fullpath = "{$this->path}/{$this->base_name}.php";
			if (file_exists($fullpath)) {
				require_once $fullpath;

				$this_function = "{$this->prefix}_{$this->base_name}_{$function_name}";
				if (function_exists($this_function)) {
					return $this_function($args);
				}
			}
		}
		return false;
	}

	/**
	 * physically delete the module from the server
	 *
	 * @return bool true on success, false on fail
	 */
	public function remove()
	{
		$filename = "{$this->path}/{$this->base_name}.php";
		@unlink($filename);

		return !file_exists($filename);
	}
}

?>
