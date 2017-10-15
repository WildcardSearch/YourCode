<?php
/**
 * YourCodeModule class definition
 *
 * @category  MyBB Plugins
 * @package   YourCode
 * @author    Mark Vincent <admin@rantcentralforums.com>
 * @copyright 2012-2014 Mark Vincent
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link      https://github.com/WildcardSearch/YourCode
 * @since     1.1
 */

if (!interface_exists('MalleableObjectInterface010000')) {
	require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/MalleableObjectInterface010000.php';
}
if (!class_exists('MalleableObject010000')) {
	require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/MalleableObject010000.php';
}
if (!interface_exists('ExternalModuleInterface010000')) {
	require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/ExternalModuleInterface010000.php';
}
if (!class_exists('ExternalModule010000')) {
	require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/ExternalModule010000.php';
}

/**
 * YourCode module class
 *
 * @see ExternalModule
 */
class YourCodeModule extends ExternalModule010000 implements ExternalModuleInterface010000
{
	/**
	 * @var the path
	 */
	protected $path = YOURCODE_MOD_URL;

	/**
	 * @var the function prefix
	 */
	protected $prefix = 'yc';

	/**
	 * run the module parser routine
	 *
	 * @return string the return of the module routine
	 */
	public function parse_message($message)
	{
		return $this->run('parse_message', $message);
	}
}

?>
