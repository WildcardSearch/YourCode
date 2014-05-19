<?php
/**
 * YourCode default module
 *
 * @category   MyBB Plugins
 * @package    YourCode
 * @subpackage Addon Modules
 * @name       Marquee
 * @author     Mark Vincent <admin@rantcentralforums.com>
 * @copyright  2012-2014 Mark Vincent
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://github.com/WildcardSearch/YourCode
 * @since      1.1
 * @example
 *
 * [marquee={behavior},{direction},{width}x{height},{scrollAmount},{scrollDelay}]
 * This is an example sentence.
 * [/marquee]
 */

/**
 * yc_marquee_info()
 *
 * returns the module info
 *
 * @return array the module info
 */
function yc_marquee_info()
{
	return array(
		"title" => 'Marquee',
		"description" => 'allows users to insert scrolling marquees into their posts',
		"version" => '1.0',
	);
}

/*
 * yc_marquee_parse_message()
 *
 * parses the marquee BB Code
 *
 * @param  string the message
 * @return string the altered message
 */
function yc_marquee_parse_message($message)
{
	/*
	 * this pattern contains named subpatterns of all the attributes
	 * we will use extract to build local variables of the same names
	 * and simplify building the HTML
	 */
	$pattern = '#\[marquee=?(?P<behavior>alternate|scroll|slide)?[,| ]?(?P<direction>left|right|up|down)?[,| ]?(?P<width>\d{2,3}?%?)?[x|,| ]?(?P<height>\d{2,3}?%?)?[,| ]?(?P<scrolldelay>\d{1,3})?[,| ]?(?P<scrollamount>\d{1,2})?\](?P<content>.*?)\[/marquee\]#is';

	preg_match_all($pattern, $message, $matches, PREG_SET_ORDER);

	if (!empty($matches)) {
		foreach ($matches as $match) {
			/*
			 * store each array member with a string key in a local
			 * variable of the same name
			 */
			extract($match);

			/*
			 * If scroll amount is blank assign the default value
			 * (so the marquee will scroll)
			 */
			if (!$scrollamount) {
				$scrollamount = 6;
			}

			// store our patterns and replacements
			$patterns[] = $match[0];
			$replacements[] = <<<EOF
<marquee style="border: solid;" behavior="{$behavior}" direction="{$direction}" width="{$width}" height="{$height}" scrolldelay="{$scrolldelay}" scrollamount="{$scrollamount}">{$content}</marquee>
EOF;
		}
		// use the less expensive str_replace() to handle all the replacements at once
		$message = str_replace($patterns, $replacements, $message);
	}
	return $message;
}

?>
