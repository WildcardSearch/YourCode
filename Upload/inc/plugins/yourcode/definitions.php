<?php
/*
 * Plugin Name: YourCode for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 */

// these are (formerly) internally cached MyCodes
$all_mycode[] = array
	(
		"title" => 'Format - Bold',
		"description" => 'MyBB Default Bold MyCode',
		"active" => true,
		"parse_order" => 10,
		"single_line" => true,
		"regex" => "\[b\](.*?)\[/b\]",
		"replacement" => "<span style=\"font-weight: bold;\">$1</span>"
	);
$all_mycode[] = array
	(
		"title" => 'Format - Underline',
		"description" => 'MyBB Default Underline MyCode',
		"active" => true,
		"parse_order" => 10,
		"single_line" => true,
		"regex" => "\[u\](.*?)\[/u\]",
		"replacement" => "<span style=\"text-decoration: underline;\">$1</span>"
	);
$all_mycode[] = array
	(
		"title" => 'Format - Italics',
		"description" => 'MyBB Default Italics MyCode',
		"active" => true,
		"parse_order" => 10,
		"single_line" => true,
		"regex" => "\[i\](.*?)\[/i\]",
		"replacement" => "<span style=\"font-style: italic;\">$1</span>"
	);
$all_mycode[] = array
	(
		"title" => 'Format - Strike-through',
		"description" => 'MyBB Default Strike-through MyCode',
		"active" => true,
		"parse_order" => 10,
		"single_line" => true,
		"regex" => "\[s\](.*?)\[/s\]",
		"replacement" => "<del>$1</del>"
	);
$all_mycode[] = array
	(
		"title" => 'Copyright',
		"description" => 'MyBB Default Copyright MyCode',
		"active" => true,
		"parse_order" => 10,
		"regex" => "\(c\)",
		"replacement" => "&copy;"
	);
$all_mycode[] = array
	(
		"title" => 'Trademark',
		"description" => 'MyBB Default Trademark MyCode',
		"active" => true,
		"parse_order" => 10,
		"regex" => "\(tm\)",
		"replacement" => "&#153;"
	);
$all_mycode[] = array
	(
		"title" => 'Registered',
		"description" => 'MyBB Default Registered MyCode',
		"active" => true,
		"parse_order" => 10,
		"regex" => "\(r\)",
		"replacement" => "&reg;"
	);
$all_mycode[] = array
	(
		"title" => 'URL - Simple #1',
		"description" => 'MyBB Default URL MyCode',
		"active" => true,
		"parse_order" => 10,
		"single_line" => true,
		"eval" => true,
		"regex" => "\[url\]([a-z]+?://)([^\r\n\"<]+?)\[/url\]",
		"replacement" => "\$this->mycode_parse_url(\"$1$2\")"
	);
$all_mycode[] = array
	(
		"title" => 'URL - Simple #2',
		"description" => 'MyBB Default URL MyCode',
		"active" => true,
		"parse_order" => 10,
		"eval" => true,
		"regex" => "\[url\]([^\r\n\"<]+?)\[/url\]",
		"replacement" => "\$this->mycode_parse_url(\"$1\")"
	);
$all_mycode[] = array
	(
		"title" => 'URL - Complex #1',
		"description" => 'MyBB Default URL MyCode',
		"active" => true,
		"parse_order" => 10,
		"single_line" => true,
		"eval" => true,
		"regex" => "\[url=([a-z]+?://)([^\r\n\"<]+?)\](.+?)\[/url\]",
		"replacement" => "\$this->mycode_parse_url(\"$1$2\", \"$3\")"
	);
$all_mycode[] = array
	(
		"title" => 'URL - Complex #2',
		"description" => 'MyBB Default URL MyCode',
		"active" => true,
		"parse_order" => 10,
		"single_line" => true,
		"eval" => true,
		"regex" => "\[url=([^\r\n\"<&\(\)]+?)\](.+?)\[/url\]",
		"replacement" => "\$this->mycode_parse_url(\"$1\", \"$2\")"
	);
$all_mycode[] = array
	(
		"title" => 'Email - Simple',
		"description" => 'MyBB Default Email MyCode',
		"active" => true,
		"parse_order" => 10,
		"eval" => true,
		"regex" => "\[email\](.*?)\[/email\]",
		"replacement" => "\$this->mycode_parse_email(\"$1\")"
	);
$all_mycode[] = array
	(
		"title" => 'Email - Complex',
		"description" => 'MyBB Default Email MyCode',
		"active" => true,
		"parse_order" => 10,
		"eval" => true,
		"regex" => "\[email=(.*?)\](.*?)\[/email\]",
		"replacement" => "\$this->mycode_parse_email(\"$1\", \"$2\")"
	);
$all_mycode[] = array
	(
		"title" => 'Horizontal Rule',
		"description" => 'MyBB Default Horizontal Rule MyCode',
		"active" => true,
		"parse_order" => 10,
		"single_line" => true,
		"regex" => "\[hr\]",
		"replacement" => "<hr />"
	);

// these are all nestable, internally cached MyCodes
$all_mycode[] = array
	(
		"title" => 'Format - Color',
		"description" => 'MyBB Default Color MyCode',
		"active" => true,
		"parse_order" => 10,
		"nestable" => true,
		"single_line" => true,
		"regex" => "\[color=([a-zA-Z]*|\#?[0-9a-fA-F]{6})](.*?)\[/color\]",
		"replacement" => "<span style=\"color: $1;\">$2</span>"
	);
$all_mycode[] = array
	(
		"title" => 'Format - Size (string params)',
		"description" => 'MyBB Default Size MyCode',
		"active" => true,
		"parse_order" => 10,
		"nestable" => true,
		"single_line" => true,
		"regex" => "\[size=(xx-small|x-small|small|medium|large|x-large|xx-large)\](.*?)\[/size\]",
		"replacement" => "<span style=\"font-size: $1;\">$2</span>"
	);
$all_mycode[] = array
	(
		"title" => 'Format - Size (numeric params)',
		"description" => 'MyBB Default Size MyCode',
		"active" => true,
		"parse_order" => 10,
		"nestable" => true,
		"single_line" => true,
		"eval" => true,
		"regex" => "\[size=([0-9\+\-]+?)\](.*?)\[/size\]",
		"replacement" => "\$this->mycode_handle_size(\"$1\", \"$2\")"
	);
$all_mycode[] = array
	(
		"title" => 'Format - Font',
		"description" => 'MyBB Default Font MyCode',
		"active" => true,
		"parse_order" => 10,
		"nestable" => true,
		"single_line" => true,
		"regex" => "\[font=([a-z ]+?)\](.+?)\[/font\]",
		"replacement" => "<span style=\"font-family: $1;\">$2</span>"
	);
$all_mycode[] = array
	(
		"title" => 'Format - Alignment',
		"description" => 'MyBB Default Font MyCode',
		"active" => true,
		"parse_order" => 10,
		"nestable" => true,
		"single_line" => true,
		"regex" => "\[align=(left|center|right|justify)\](.*?)\[/align\]",
		"replacement" => "<div style=\"text-align: $1;\">$2</div>"
	);

?>
