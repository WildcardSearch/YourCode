<?php
/**
 * this file contains data used by classes/installer.php
 *
 * @category  MyBB Plugins
 * @package   YourCode
 * @author    Mark Vincent <admin@rantcentralforums.com>
 * @copyright 2012-2014 Mark Vincent
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link      https://github.com/WildcardSearch/YourCode
 * @since     1.1
 */

$tables = array(
	'pgsql' => array(	
		'yourcode' => array(
			'id' => 'SERIAL',
			'title' => 'VARCHAR(100)',
			'description' => 'TEXT',
			'parse_order' => 'INT NOT NULL',
			'nestable' => 'INT',
			'active' => 'INT',
			'case_sensitive' => 'INT',
			'single_line' => 'INT',
			'multi_line' => 'INT',
			'eval' => 'INT',
			'callback' => 'INT',
			'regex' => 'TEXT',
			'replacement' => 'TEXT',
			'alt_replacement' => 'TEXT',
			'can_use' => 'TEXT',
			'can_view' => 'TEXT',
			'default_id' => 'INT',
			'dateline' => 'INT NOT NULL, PRIMARY KEY(id)',
		),
	),
	"yourcode" => array(
		"id" => 'INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		"title" => 'VARCHAR(100)',
		"description" => 'TEXT',
		"parse_order" => 'INT(10) NOT NULL',
		"nestable" => 'INT(2)',
		"active" => 'INT(2)',
		"case_sensitive" => 'INT(2)',
		"single_line" => 'INT(2)',
		"multi_line" => 'INT(2)',
		"eval" => 'INT(2)',
		"callback" => 'INT(1)',
		"regex" => 'TEXT',
		"replacement" => 'TEXT',
		"alt_replacement" => 'TEXT',
		"can_use" => 'TEXT',
		"can_view" => 'TEXT',
		"default_id" => 'INT(10)',
		"dateline" => 'INT(10)',
	),
);

$settings = array(
	"yourcode_settings" => array(
		"group" => array(
			"name" => 'yourcode_settings',
			"title" => $lang->yourcode_settings_title,
			"description" => $lang->yourcode_settingsgroup_description,
			"disporder" => '103',
			"isdefault" => 0,
		),
		"settings" => array(
			"yourcode_minimize_js" => array(
				"sid" => 'NULL',
				"name" => 'yourcode_minimize_js',
				"title" => $lang->yourcode_minimize_js_title,
				"description" => $lang->yourcode_minimize_js_description,
				"optionscode" => 'yesno',
				"value" => '1',
				"disporder" => '10'
			),
		),
	),
);


$images = array(
	"folder" => 'yourcode',
	"acp" => array(
		"donate.gif" => array(
			"image" => <<<EOF
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
EOF
		),
		"pixel.gif" => array(
			"image" => <<<EOF
R0lGODlhAQABAID/AMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==
EOF
		),
		"manage.gif" => array(
			"image" => <<<EOF
R0lGODlhEAAQAOMGAAAAACUlJSYmJkREREZGRk1NTY6Ojv///46Ojo6Ojo6Ojo6Ojo6Ojo6Ojo6Ojo6OjiH5BAEKAAgALAAAAAAQABAAAARMEMhJhzn4AMO7KYCVadeIheWZjii7Zi38nkHhwlQupblgHyDAT0MhlAy6nBGDHDgHAYCAQF0ekB5QwXMEmLwmJhgWFn+3XM74lJxEAAA7
EOF
		),
		"settings.png" => array(
			"image" => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAJ1BMVEUAAAAAAAADAwMGBgYKCgoNDQ0aGhodHR1JSUlYWFiHh4eWlpb///+1t4d2AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfhAgUXBgVjiycpAAAAfUlEQVQI12NgYGDg3sAABhyrqxpA9IQu6cIVnEAGZ6F0ofgEIKN9enRhZQVQxcLK6vLpUg0MXILps3eWCC5g2F5evuf07t27GYB4z+ny8mqglPjsncVAKaDi8u3TJYDa2aerb6ssABsovhFs4IQu8Y1gKxg4lu9qgFgPdgYAiBgpM0PxWJYAAAAASUVORK5CYII=
EOF
		),
		"logo.png" => array(
			"image" => <<<EOF
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
EOF
		),
	),
);

?>
