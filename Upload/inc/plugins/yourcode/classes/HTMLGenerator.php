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
	public $baseUrl = 'index.php';

	/**
	 * @var string[] allowed $_GET/$mybb->input variable names
	 */
	public $allowedUrlKeys = array(
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
	public $allowedImageProperties = array(
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
	public $allowedLinkProperties = array(
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
	public function __construct($url = '', $extraKeys = '')
	{
		// custom base URL?
		if (trim($url)) {
			$this->baseUrl = trim($url);
		}

		foreach ((array) $extraKeys as $key) {
			$key = trim($key);
			if ($key && !in_array($key, $this->allowedUrlKeys)) {
				$this->allowedUrlKeys[] = $key;
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
	public function url($options = array(), $baseUrl = '', $encoded = true)
	{
		if ($baseUrl &&
			trim($baseUrl)) {
			$url = $baseUrl;
		} else {
			$url = $this->baseUrl;
		}

		$amp = '&';
		if ($encoded) {
			$amp = '&amp;';
		}
		$sep = $amp;
		if (strpos($url, '?') === false) {
			$sep = '?';
		}

		// check for the allowed options
		foreach ((array) $this->allowedUrlKeys as $item) {
			if (isset($options[$item]) &&
				$options[$item]) {
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
	public function link($url = '', $caption = '', $options = '', $iconOptions = array())
	{
		$properties = $this->buildPropertyList($options, $this->allowedLinkProperties);

		if (isset($options['icon'])) {
			$icon_img = $this->img($options['icon'], $iconOptions);
			$icon_link = <<<EOF
<a href="{$url}">{$icon_img}</a>&nbsp;
EOF;
		}

		if (!$url) {
			$url = $this->url();
		}
		if (!isset($caption) ||
			!$caption) {
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
		$properties = $this->buildPropertyList($options, $this->allowedImageProperties);

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
	protected function buildPropertyList($options = array(), $allowed = array())
	{
		if (!is_array($options) ||
			!is_array($allowed)) {
			return false;
		}

		foreach ($allowed as $key) {
			if (isset($options[$key]) &&
				$options[$key]) {
				$propertyList .= <<<EOF
 {$key}="{$options[$key]}"
EOF;
			}
		}
		return $propertyList;
	}
}

?>
