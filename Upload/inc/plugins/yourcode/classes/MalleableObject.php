<?php
/**
 * malleable object definition
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
 * provides standard data methods and validation for inheritance
*/
interface MalleableObjectInterface
{
	public function get($properties);
	public function set($properties, $value = '');
	public function is_valid();
}

/**
 * provides standard data methods and validation for inheritance
 */
abstract class MalleableObject implements MalleableObjectInterface
{
	/**
	 * @var bool a flag indicating this object was loaded successfully
	 */
	protected $valid = false;

	/**
	 * retrieves a named property or a list of properties
	 *
	 * @param  array|string an array of property names or a single name
	 * @return a keyed array of properties and values or a single value
	 */
	public function get($properties)
	{
		if(is_array($properties))
		{
			$return_array = array();
			foreach($properties as $property)
			{
				if(property_exists($this, $property))
				{
					$return_array[$property] = $this->$property;
				}
			}
			return $return_array;
		}
		else
		{
			if(property_exists($this, $properties))
			{
				return $this->$properties;
			}
			return false;
		}
	}

	/**
	 * sets a single property or multiple properties at once
	 *
	 * @param  array|string of properties and their values or a single name
	 * @param  mixed the property value
	 * @return bool true if successful (property exists) false otherwise
	 */
	public function set($properties, $value = '')
	{
		if(is_array($properties))
		{
			foreach($properties as $property => $value)
			{
				if(property_exists($this, $property))
				{
					$this->$property = $value;
				}
			}
			return true;
		}
		elseif(isset($value))
		{
			$this->$properties = $value;
			return true;
		}
		return false;
	}

	/**
	 * allows access to the protected valid property
	 *
	 * @return bool the valid property value
	 */
	public function is_valid()
	{
		return $this->valid;
	}
}

?>
