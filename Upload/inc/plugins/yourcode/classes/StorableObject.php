<?php
/**
 * storable object definition
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
 * standard interface for database storage/retrieval
 */
interface StorableObjectInterface
{
	public function load($data);
	public function save();
	public function remove();
}

/**
 * standard object for db storage/retrieval
 */
abstract class StorableObject extends MalleableObject implements StorableObjectInterface
{
	/**
	 * @var int both the objects ID and the ID of the table row
	 */
	protected $id;

	/**
	 * @var array the database table row
	 */
	protected $data = array();

	/**
	 * @var string the database table associated with this object
	 */
	protected $table_name = '';

	/**
	 * attempt to load and validate the object
	 *
	 * @param  int|array the db/object ID or the database table row
	 * @return void
	 */
	public function __construct($data = '')
	{
		// if there is data
		if($data)
		{
			// attempt to load it and return the results
			$this->valid = $this->load($data);
			return;
		}
		// new object
		$this->valid = false;
	}

	/**
	 * load the object from the database
	 *
	 * @param int|array the db/object ID or the database table row
	 * @return bool true on success false of fail
	 */
	public function load($data)
	{
		// is the data scalar? (and if so, do we have a table name?)
		if(!is_array($data) && $this->table_name)
		{
			// attempt to load the object by ID
			global $db;
			$data = (int) $data;
			$query = $db->simple_select($this->table_name, '*', "id='{$data}'");

			// if it exists
			if($db->num_rows($query) == 1)
			{
				// store it in our passed var
				$data = $db->fetch_array($query);
			}
		}

		// if we have a (hopefully) valid array
		if(is_array($data) && !empty($data))
		{
			// store it in the object
			foreach($data as $key => $val)
			{
				if(property_exists($this, $key))
				{
					$this->$key = $this->data[$key] = $val;
				}
			}
			return true;
		}
		// new blank object
		return false;
	}

	/**
	 * stores the objects data in the database
	 *
	 * @return mixed false on fail or the return of the database wrapper method called
	 */
	public function save()
	{
		// if we have a table name stored
		if($this->table_name)
		{
			global $db;

			$this->data = array();
			foreach($this as $property => $value)
			{
				if(in_array($property, array('id', 'valid', 'data', 'table_name')))
				{
					continue;
				}

				switch(gettype($this->$property))
				{
					case 'boolean':
						$this->data[$property] = (bool) $value;
						break;
					case 'integer':
						$this->data[$property] = (int) $value;
						break;
					case 'NULL':
						$this->data[$property] = NULL;
						break;
					case 'double':
						$this->data[$property] = (float) $value;
						break;
					case 'string':
						$this->data[$property] = $db->escape_string($value);
						break;
					case 'array':
					case 'object':
					case 'resource':
						$this->data[$property] = $db->escape_string(json_encode($value));
						break;
					default:
						continue;
				}
			}
			$this->data['dateline'] = TIME_NOW;

			// insert or update depending upon the content of ID
			if($this->id)
			{
				// return true/false
				return $db->update_query($this->table_name, $this->data, "id='{$this->id}'");
			}
			else
			{
				// return the ID on success/false on fail
				return $this->id = $db->insert_query($this->table_name, $this->data);
			}
		}
		// fail
		return false;
	}

	/**
	 * remove the object from the database
	 *
	 * @return mixed false on fail or the return of the database wrapper method called
	 */
	public function remove()
	{
		// valid ID and DB info?
		if($this->id && $this->table_name)
		{
			// nuke it and return true/false
			global $db;
			return $db->delete_query($this->table_name, "id='{$this->id}'");
		}
		return false;
	}
}

?>
