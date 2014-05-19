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

if(!class_exists('ExternalModule'))
{
	require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/ExternalModule.php';
}

/**
 * YourCode module class
 *
 * @see ExternalModule
 */
class YourCodeModule extends ExternalModule
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
