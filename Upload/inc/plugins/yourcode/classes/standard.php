<?php
/*
 * Plugin Name: YourCode for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 */

if(!class_exists('MalleableObject'))
{
	require_once MYBB_ROOT . "inc/plugins/yourcode/classes/malleable.php";
}
if(!class_exists('StorableObject'))
{
	require_once MYBB_ROOT . "inc/plugins/yourcode/classes/storable.php";
}
if(!class_exists('PortableObject'))
{
	require_once MYBB_ROOT . "inc/plugins/yourcode/classes/portable.php";
}

/*
 * a concrete class built on a MalleableObject extended by StorableObject for db functions and a PortableObject for import/export with properties and __construct specifically for YourCodes
 */
class YourCode extends PortableObject
{
	protected $title = '';
	protected $description = '';
	protected $parse_order = 0;
	protected $nestable = false;
	protected $active = false;
	protected $case_sensitive = false;
	protected $single_line = false;
	protected $multi_line = false;
	protected $eval = false;
	protected $regex = '';
	protected $replacement = '';
	protected $alt_replacement = '';
	protected $can_use = '';
	protected $can_view = '';

	/*
	 * __construct()
	 *
	 * @param - $data - (mixed) passed to StorableObject's inherited __construct after setting the table name
	 */
	public function __construct($data = '')
	{
		$this->table_name = 'yourcode';
		
		if($data)
		{
			if(is_array($data))
			{
				$data['regex'] = str_replace("\x0", "", $data['regex']);

				if(is_array($data['can_view']))
				{
					$data['can_view'] = implode(',', $data['can_view']);
				}
				if(is_array($data['can_use']))
				{
					$data['can_use'] = implode(',', $data['can_use']);
				}

				if(strpos($data['can_view'], 'all') !== false)
				{
					$data['can_view'] = '';
				}
				if(strpos($data['can_use'], 'all') !== false)
				{
					$data['can_use'] = '';
				}
			}
			parent::__construct($data);
		}
	}
}

?>
