<?php
/**
 * YourCode class definition
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
if (!class_exists('StorableObject')) {
	require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/StorableObject.php';
}
if (!class_exists('PortableObject')) {
	require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/PortableObject.php';
}

/**
 * the YourCode database object wrapper
 *
 * a concrete class built on a MalleableObject extended by StorableObject
 * for db functions and a PortableObject for import/export with properties
 * for YourCodes
 */
class YourCode extends PortableObject
{
	/**
	 * @var string the title
	 */
	protected $title = '';

	/**
	 * @var string the code description
	 */
	protected $description = '';

	/**
	 * @var int the order in which YourCode is parsed (lowest first)
	 */
	protected $parse_order = 0;

	/**
	 * @var bool whether the code can be nested
	 */
	protected $nestable = false;

	/**
	 * @var bool whether the code is active
	 */
	protected $active = false;

	/**
	 * @var bool whether the code is case sensitive
	 */
	protected $case_sensitive = false;

	/**
	 * @var bool whether the code uses the single line regex modifier
	 */
	protected $single_line = false;

	/**
	 * @var bool whether the code uses the multi line regex modifier
	 */
	protected $multi_line = false;

	/**
	 * @var bool whether the code uses the eval regex modifier
	 */
	protected $eval = false;

	/**
	 * @var bool whether uses a callback
	 */
	protected $callback = false;

	/**
	 * @var string the regular expression
	 */
	protected $regex = '';

	/**
	 * @var string the replacement HTML
	 */
	protected $replacement = '';

	/**
	 * @var string the alternate replacement HTML for those with no permissions
	 */
	protected $alt_replacement = '';

	/**
	 * @var string a comma-separated list of groups that can use this code
	 */
	protected $can_use = '';

	/**
	 * @var string a comma-separated list of groups that can view this code
	 */
	protected $can_view = '';

	/**
	 * @var int internal
	 */
	protected $default_id = 0;

	/**
	 * create a new YourCode object
	 *
	 * @see StorableObject::__construct()
	 */
	public function __construct($data = '')
	{
		$this->tableName = 'yourcode';

		if ($data) {
			if (is_array($data)) {
				$data['regex'] = str_replace("\x0", '', $data['regex']);

				if (is_array($data['can_view'])) {
					$data['can_view'] = implode(',', $data['can_view']);
				}
				if (is_array($data['can_use'])) {
					$data['can_use'] = implode(',', $data['can_use']);
				}

				if (strpos($data['can_view'], 'all') !== false) {
					$data['can_view'] = '';
				}
				if (strpos($data['can_use'], 'all') !== false) {
					$data['can_use'] = '';
				}
			}
			parent::__construct($data);
		}
	}
}

?>
