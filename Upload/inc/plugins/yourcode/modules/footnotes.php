<?php
/**
 * YourCode default module
 *
 * @category   MyBB Plugins
 * @package    YourCode
 * @subpackage Addon Modules
 * @name   Footnotes
 * @author     Mark Vincent <admin@rantcentralforums.com>
 * @copyright  2012-2014 Mark Vincent
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://github.com/WildcardSearch/YourCode
 * @since      1.1
 * @example
 *
 * This is an example[note]a rather pointless example at that[/note] sentence.
 */

/**
 * yc_footnotes_info()
 *
 * returns the module info
 *
 * @return array the module info
 */
function yc_footnotes_info()
{
	return array(
		"title" => 'Footnotes',
		"description" => 'allows users to append footnotes to expound upon certain portions of their post',
		"version" => '1.0',
	);
}

/*
 * yc_footnotes_parse_message()
 *
 * parses the footnotes BB Code
 *
 * @param  string the message
 * @return string the altered message
 */
function yc_footnotes_parse_message($message)
{
	global $post;

	preg_match_all("#\[note\](.*?)\[/note\]#i", $message, $matches, PREG_SET_ORDER);

	$note_num = 1;
	$addendum = '';
	foreach((array) $matches as $match)
	{
		$id = "footnote_{$post['pid']}_{$note_num}";

		// replace the note with a link
		$replacement = <<<EOF
<sup><a href="#{$id}" style="color: #1AB21A;">[{$note_num}]</a></sup>
EOF;
		$message = str_replace($match[0], $replacement, $message);

		/*
		 * and add the note content into the footer of the post
		 * after of course creating the footer (and synthesizing the need
		 * for a footer in the first place) and add an achor to snap to
		 */
		$addendum .= <<<EOF

	<li style="padding-bottom: 3px;"><a id="{$id}"></a>{$match[1]}</li>
EOF;

		// let footnotes order themselves
		++$note_num;
	}

	if($addendum)
	{
		$message .= <<<EOF
<hr style="width: 25%;"/>
<span class="smalltext" style="font-weight: bold;">Footnotes:</span>
<ol class="smalltext">{$addendum}
</ol>
EOF;
	}
	return $message;
}

?>
