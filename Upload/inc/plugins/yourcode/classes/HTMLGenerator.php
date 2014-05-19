<?php
/**
 * Wildcard Helper Classes - HTML Generator
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
 * generates a small set of HTML elements
 */
class HTMLGenerator
{
	/**
	 * @var string default URL for links
	 */
	public $base_url = 'index.php';

	/**
	 * @var string[] allowed $_GET/$mybb->input variable names
	 */
	public $allowed_url_keys = array(
		'module',
		'action',
		'mode',
		'id',
		'uid',
		'tid',
		'page',
		'my_post_key'
	);

	/**
	 * @var string[] allowed <img> tag attrubutes
	 */
	public $allowed_img_properties = array(
		'id',
		'name',
		'title',
		'alt',
		'style',
		'class',
		'onclick'
	);

	/**
	 * @var string[] allowed <a> tag attrubutes
	 */
	public $allowed_link_properties = array(
		'id',
		'name',
		'title',
		'style',
		'class',
		'onclick'
	);

	/**
	 * create a new HTML Generator object
	 *
	 * @param  string the base URL for all links and URLs
	 * @param string|array key name or names to allow
	 *
	 * @return void
	 */
	public function __construct($url = '', $extra_keys = '')
	{
		// custom base URL?
		if(trim($url))
		{
			$this->base_url = trim($url);
		}

		foreach((array) $extra_keys as $key)
		{
			$key = trim($key);
			if($key && !in_array($key, $this->allowed_url_keys))
			{
				$this->allowed_url_keys[] = $key;
			}
		}
	}

	/**
	 * builds a URL from standard options array
	 *
	 * @param  array keyed to standard URL options
	 * @param  string overrides the default URL base if present
	 * @param  bool override URL encoded ampersand (for JS mostly)
	 * @return string the URL
	 */
	public function url($options = array(), $base_url = '', $encoded = true)
	{
		if($base_url && trim($base_url))
		{
			$url = $base_url;
		}
		else
		{
			$url = $this->base_url;
		}

		$amp = '&';
		if($encoded)
		{
			$amp = '&amp;';
		}
		$sep = $amp;
		if(strpos($url, '?') === false)
		{
			$sep = '?';
		}

		// check for the allowed options
		foreach((array) $this->allowed_url_keys as $item)
		{
			if(isset($options[$item]) && $options[$item])
			{
				// and add them if set
				$url .= "{$sep}{$item}={$options[$item]}";
				$sep = $amp;
			}
		}
		return $url;
	}

	/**
	 * builds an HTML anchor from the provided options
	 *
	 * @param  string the address
	 * @param  string the title of the link
	 * @param  array options to affect the HTML output
	 * @return string the anchor HTML
	 */
	public function link($url = '', $caption = '', $options = '', $icon_options = array())
	{
		$properties = $this->build_property_list($options, $this->allowed_link_properties);

		if(isset($options['icon']))
		{
			$icon_img = $this->img($options['icon'], $icon_options);
			$icon_link = <<<EOF
<a href="{$url}">{$icon_img}</a>&nbsp;
EOF;
		}

		if(!$url)
		{
			$url = $this->url();
		}
		if(!isset($caption) || !$caption)
		{
			$caption = $url;
		}

		return <<<EOF
{$icon_link}<a href="{$url}"{$properties}>{$caption}</a>
EOF;
	}

	/**
	 * generate HTML <img> markup
	 *
	 * @param string <img> src attribute
	 * @param array options to be generated
	 * @return string the image HTML
	 */
	public function img($url, $options = array())
	{
		$properties = $this->build_property_list($options, $this->allowed_img_properties);

		return <<<EOF
<img src="{$url}"{$properties}/>
EOF;
	}

	/**
	 * generate an HTML attribute list
	 *
	 * @param  array properties
	 * @param  array allowable property names
	 * @return string a list of properties
	 */
	protected function build_property_list($options = array(), $allowed = array())
	{
		if(!is_array($options) || !is_array($allowed))
		{
			return false;
		}

		foreach($allowed as $key)
		{
			if(isset($options[$key]) && $options[$key])
			{
				$property_list .= <<<EOF
 {$key}="{$options[$key]}"
EOF;
			}
		}
		return $property_list;
	}
}

?>
