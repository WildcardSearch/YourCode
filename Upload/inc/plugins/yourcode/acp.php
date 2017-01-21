<?php
/**
 * ACP functionality for the plugin
 *
 * @category  MyBB Plugins
 * @package   YourCode
 * @author    Mark Vincent <admin@rantcentralforums.com>
 * @copyright 2012-2014 Mark Vincent
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link      https://github.com/WildcardSearch/YourCode
 * @since     1.0
 */

define('YOURCODE_URL', 'index.php?module=config-yourcode');
require_once MYBB_ROOT . 'inc/plugins/yourcode/functions_acp.php';
require_once MYBB_ROOT . 'inc/plugins/yourcode/install.php';

/**
 * the ACP page router
 *
 * @return void
 */
$plugins->add_hook('admin_load', 'yourcode_admin');
function yourcode_admin()
{
	// globalize as needed to save wasted work
	global $page;
	if (!in_array($page->active_action, array('mycode', 'yourcode'))) {
		// not our turn
		return false;
	}

	// now load up, this is our time
	global $mybb, $lang, $html;
	if (!$lang->yourcode) {
		$lang->load('yourcode');
	}

	// no need for all the classes and functions if it is just AJAX test
	if ($mybb->input['mode'] != 'xmlhttp') {
		require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/YourCode.php';
		require_once MYBB_ROOT . 'inc/plugins/yourcode/classes/HTMLGenerator.php';

		// URL, link and image markup generator
		$html = new HTMLGenerator(YOURCODE_URL, array('script', 'style', 'type', 'name'));

		$page->extra_header .= <<<EOF

	<script type="text/javascript">
	<!--
	var my_post_key = '{$mybb->post_code}';
	// -->
	</script>
EOF;
	}

	// if there is an existing function for the action
	$page_function = 'yourcode_admin_' . $mybb->input['action'];
	if (function_exists($page_function)) {
		// run it
		$page_function();
	} else {
		yourcode_admin_view();
	}
	// get out
	exit();
}

/**
 * ACP Page - View YourCode
 *
 * @return void
 */
function yourcode_admin_view()
{
	global $page, $mybb, $lang, $db, $html;

	if ($mybb->request_method == 'post') {
		if ($mybb->input['mode'] == 'inline') {
			// verify incoming POST request
			if (!verify_post_check($mybb->input['my_post_key'])) {
				flash_message($lang->invalid_post_verify_key2, 'error');
				admin_redirect($html->url(array("page" => $mybb->input['page'])));
			}

			if (!is_array($mybb->input['yc_inline_ids']) ||
				empty($mybb->input['yc_inline_ids'])) {
				flash_message($lang->yourcode_inline_selection_error, 'error');
				admin_redirect($html->url(array("page" => $mybb->input['page'])));
			}

			$job_count = 0;
			foreach ($mybb->input['yc_inline_ids'] as $id => $throw_away) {
				$this_code = new YourCode($id);
				if (!$this_code->isValid()) {
					continue;
				}

				switch ($mybb->input['inline_action']) {
				case 'delete':
					if (!$this_code->remove()) {
						continue 2;
					}
					break;
				default:
					$value = ($mybb->input['inline_action'] == 'activate');

					if (($this_code->get('active') &&
							$value) ||
						(!$this_code->get('active') &&
							!$value)) {
						continue 2;
					}

					$this_code->set('active', $value);
					if (!$this_code->save()) {
						continue 2;
					}
				}
				++$job_count;
			}
			$action = "{$mybb->input['inline_action']}d";
			flash_message($lang->sprintf($lang->yourcode_inline_success, $job_count, $lang->yourcode, $action), 'success');
			yourcode_build_cache();
			admin_redirect($html->url(array("page" => $mybb->input['page'])));
		}
	}

	if ($mybb->input['mode'] == 'delete') {
		// verify incoming POST request
		if (!verify_post_check($mybb->input['my_post_key'])) {
			flash_message($lang->invalid_post_verify_key2, 'error');
			admin_redirect($html->url());
		}

		// good info?
		if (isset($mybb->input['id']) && (int) $mybb->input['id']) {
			// then attempt deletion
			$this_code = new YourCode($mybb->input['id']);
			if ($this_code->isValid()) {
				$success = $this_code->remove();
			}
		}

		$action = "{$mybb->input['mode']}d";
		if ($success) {
			// yay for us
			flash_message($lang->sprintf($lang->yourcode_message_success, $lang->yourcode, $action), 'success');
			yourcode_build_cache();
		} else {
			// boo, we suck
			flash_message($lang->sprintf($lang->yourcode_message_fail, $lang->yourcode, $action), 'error');
		}
		admin_redirect($html->url(array("page" => $mybb->input['page'])));
	} elseif (in_array($mybb->input['mode'], array('activate', 'deactivate'))) {
		// verify incoming POST request
		if (!verify_post_check($mybb->input['my_post_key'])) {
			flash_message($lang->invalid_post_verify_key2, 'error');
			admin_redirect($html->url());
		}

		// good info?
		if (isset($mybb->input['id']) &&
			(int) $mybb->input['id']) {
			$this_code = new YourCode($mybb->input['id']);
			if ($this_code->isValid()) {
				$value = ($mybb->input['mode'] == 'activate');
				$this_code->set('active', $value);
				$success = $this_code->save();
			}
		}

		$action = "{$mybb->input['mode']}d";
		if ($success) {
			// yay for us
			flash_message($lang->sprintf($lang->yourcode_message_success, $lang->yourcode, $action), 'success');
			yourcode_build_cache();
		} else {
			// boo, we suck
			flash_message($lang->sprintf($lang->yourcode_message_fail, $lang->yourcode, $action), 'error');
		}
		admin_redirect($html->url(array("page" => $mybb->input['page'])));
	} elseif ($mybb->input['mode'] == 'export') {
		// good info?
		if (isset($mybb->input['id']) &&
			(int) $mybb->input['id']) {
			// then attempt deletion
			$this_code = new YourCode($mybb->input['id']);
			if ($this_code->isValid()) {
				$success = $this_code->export();
				exit;
			}
		}
		// boo, we suck
		flash_message($lang->sprintf($lang->yourcode_message_fail, $lang->yourcode, $lang->yourcode_exported), 'error');
		admin_redirect($html->url(array("page" => $mybb->input['page'])));
	}

	// start output
	$page->add_breadcrumb_item($lang->yourcode);
	$page->add_breadcrumb_item($lang->yourcode_admin_view);

	$page->extra_header .= <<<EOF

	<script type="text/javascript" src="jscripts/yourcode/yourcode_inline.js"></script>
	<script type="text/javascript">
	<!--
	YourCode.inline.setup({
		go: '{$lang->go}',
		noSelection: '{$lang->yourcode_inline_selection_error}',
	});
	// -->
	</script>
EOF;
	$page->output_header("{$lang->yourcode} - {$lang->yourcode_admin_view}");
	yourcode_output_tabs('yourcode_main');

	// get a total count on the YourCodes
	$query = $db->simple_select('yourcode', 'COUNT(id) AS resultCount');
	$resultCount = $db->fetch_field($query, 'resultCount');

	$perPage = 10;
	$totalPages = ceil($resultCount / $perPage);

	$form = new Form($html->url(array("action" => 'view', "mode" => 'inline')), 'post', 'inline_form', '', '', '', $onSubmit);

	$table = new Table;
	$table->construct_header($lang->yourcode_title, array("style" => 'width: 25%;'));
	$table->construct_header($lang->yourcode_description, array("style" => 'width: 40%;'));
	$table->construct_header($lang->yourcode_parse_order, array("style" => 'width: 8%;'));
	$table->construct_header($lang->yourcode_nestable, array("style" => 'width: 8%;'));
	$table->construct_header($lang->yourcode_active, array("style" => 'width: 11%;'));
	$table->construct_header($lang->yourcode_controls, array("style" => 'width: 8%'));
	$table->construct_header($form->generate_check_box('', '', '', array("id" => 'yc_select_all')));

	// adjust the page number if the user has entered manually or is returning to a page that no longer exists (deleted last YourCode on page)
	if (!isset($mybb->input['page']) ||
		$mybb->input['page'] == '' ||
		(int) $mybb->input['page'] < 1) {
		// no page, page = 1
		$mybb->input['page'] = 1;
	} else if ($mybb->input['page'] > $totalPages) {
		// past last page? page = last page
		$mybb->input['page'] = $totalPages;
	} else {
		// in range? page = # in link
		$mybb->input['page'] = (int) $mybb->input['page'];
	}

	// more than one page?
	$start = ($mybb->input['page'] - 1) * $perPage;
	if ($resultCount > $perPage) {
		// save the pagination for below and show it here as well
		$pagination = draw_admin_pagination($mybb->input['page'], $perPage, $resultCount, $html->url(array("action" => 'view')));
		echo($pagination . '<br />');
	}

	// get all the codes for this page
	$all_codes = yourcode_get_all($start, $perPage);

	if (is_array($all_codes) &&
		!empty($all_codes)) {
		$onClick = <<<EOF
return AdminCP.deleteConfirmation(this, '{$lang->yourcode_delete_warning_simple}');
EOF;

		// if so display them
		foreach ($all_codes as $id => $code) {
			$edit_url = $html->url(array("action" => 'edit',  "id" => $id, "my_post_key" => $mybb->post_code));
			$isActive = $code->get('active');
			if ($isActive) {
				$activeText = $lang->yourcode_deactivate;
				$activeUrl = $html->url(array("action" => 'view', "mode" => 'deactivate', "page" => $mybb->input['page'], "id" => $id, "my_post_key" => $mybb->post_code));
				$activeLink = $html->link($activeUrl, $activeText, array("title" => $lang->sprintf($lang->yourcode_message_active_status, strtolower($lang->yourcode_active), strtolower($activeText)), "style" => 'color: green'));
			} else {
				$activeText = $lang->yourcode_activate;
				$activeUrl = $html->url(array("action" => 'view', "mode" => 'activate', "page" => $mybb->input['page'], "id" => $id, "my_post_key" => $mybb->post_code));
				$activeLink = $html->link($activeUrl, $activeText, array("title" => $lang->sprintf($lang->yourcode_message_active_status, strtolower($lang->yourcode_inactive), strtolower($activeText)), "style" => 'color: red'));
			}

			if ($code->get('nestable') ||
				$code->get('callback')) {
				$nested_text = "<span style=\"font-weight: bold; color: green\">{$lang->yourcode_yes}</span>";
			} else {
				$nested_text = "<span style=\"color: #888;\"><em>{$lang->yourcode_no}</em></span>";
			}

			$table->construct_cell($html->link($edit_url, $code->get('title')), array("style" => 'font-weight: bold;'));
			$table->construct_cell($code->get('description'));
			$table->construct_cell($code->get('parse_order'));
			$table->construct_cell($nested_text);
			$table->construct_cell($activeLink);

			$popup = new PopupMenu("control_{$id}", $lang->yourcode_options);
			$popup->add_item($lang->yourcode_edit, $edit_url);
			$popup->add_item($activeText, $activeUrl);
			$popup->add_item($lang->yourcode_export, $html->url(array("action" => 'view', "mode" => 'export', "page" => $mybb->input['page'], "id" => $id)));
			$popup->add_item($lang->yourcode_delete, $html->url(array("action" => 'view', "mode" => 'delete', "page" => $mybb->input['page'], "id" => $id, "my_post_key" => $mybb->post_code)), $onClick);
			$table->construct_cell($popup->fetch());
			$table->construct_cell($form->generate_check_box("yc_inline_ids[{$id}]", '', '', array("class" => 'yc_check')));
			$table->construct_row();
		}
	} else {
		// if not whine about it a bit :*(
		$table->construct_cell("<span style=\"color: #888;\"><em>{$lang->yourcode_no_code}</em></span>", array("colspan" => 7));
			$table->construct_row();
	}

	$table->output($lang->yourcode);

	$inline = <<<EOF
<span class="float_right"><strong>{$lang->yourcode_inline_title}:</strong>&nbsp;
<select name="inline_action">
<option value="activate">{$lang->yourcode_activate}</option>
<option value="deactivate">{$lang->yourcode_deactivate}</option>
<option value="delete">{$lang->yourcode_delete}</option>
</select>
<input id="yc_inline_submit" type="submit" class="button" name="yc_inline_submit" value="{$lang->go} (0)"/>
<input id="yc_inline_clear" type="button" class="button" name="yc_inline_clear" value="{$lang->clear}"/>
<input type="hidden" name="page" value="{$mybb->input['page']}"/>
</span>
EOF;
	echo($inline);
	$form->end();
	echo('<br />');

	// more than one page?
	if ($resultCount > $perPage) {
		// if so show pagination on the right this time just to be weird
		echo($pagination);
	}

	// be done
	$page->output_footer();
}

/**
 * ACP Page: Add/Edit YourCode
 *
 * @return void
 */
function yourcode_admin_edit()
{
	global $page, $mybb, $lang, $db, $html;

	// verify incoming POST request
	if (!verify_post_check($mybb->input['my_post_key'])) {
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect($html->url());
	}

	// adding/updating
	if ($mybb->request_method == 'post') {
		if (in_array($mybb->input['add_code_submit'], array('Save and Return to Listing', 'Save and Continue Editing'))) {
			// create a new object from the passed info and save it
			$this_code = new YourCode($mybb->input);
			$success = $this_code->save();

			// past tense is very important to me :P
			if ($mybb->input['id']) {
				$action = $lang->yourcode_updated;
			} else {
				$action = $lang->yourcode_added;
			}

			if ($success) {
				// yay for us
				yourcode_build_cache();
				flash_message($lang->sprintf($lang->yourcode_message_success, $lang->yourcode, $action), 'success');

				if ($mybb->input['add_code_submit'] == 'Save and Return to Listing') {
					admin_redirect($html->url());
				} else if (!$mybb->input['id']) {
					$mybb->input['id'] = $success;
				}
			} else {
				// boo, we suck
				flash_message($lang->sprintf($lang->yourcode_message_fail, $lang->yourcode, $action), 'error');
			}
		} elseif ($mybb->input['mode'] == 'xmlhttp') {
			// send no cache headers
			header('Expires: Sat, 1 Jan 2000 01:00:00 GMT');
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			header('Content-type: text/html');

			// test the regex and echo it for AJAX
			$sandbox = yourcode_test_regex($mybb->input['regex'], $mybb->input['replacement'], $mybb->input['test_value'], $mybb->input);
			echo $sandbox['actual'];
			exit;
		}
	}

	// admin has no JS in ACP?
	if (isset($mybb->input['test']) && $mybb->input['test']) {
		// test the regex and warn them that save hasn't occurred
		$sandbox = yourcode_test_regex($mybb->input['regex'], $mybb->input['replacement'], $mybb->input['test_value'], $mybb->input);
		flash_message($lang->yourcode_sandbox_test_no_save_warning, 'error');
	}

	// start page output
	$page->add_breadcrumb_item($lang->yourcode);
	$page->add_breadcrumb_item($lang->yourcode_admin_edit);
	$page->extra_header .= '<script type="text/javascript" src="jscripts/yourcode/yourcode_edit.js"></script>';
	$page->output_header("{$lang->yourcode} - {$lang->yourcode_admin_edit}");
	yourcode_output_tabs('yourcode_edit');

	// default to adding
	$button_text = $lang->yourcode_add;
	$this_code = new YourCode($mybb->input['id']);

	if ($this_code->isValid()) {
		// editing? store the info for the form
		$data = $this_code->get('data');

		if (!$data['can_view']) {
			$data['can_view'] = 'all';
		}
		if (!$data['can_use']) {
			$data['can_use'] = 'all';
		}
		if ($data['callback']) {
			$data['nestable'] = 1;
		}

		$button_text = $lang->yourcode_update;
	} else {
		// creating? set up some defaults
		$data = array(
			"parse_order" => 10,
			"nestable" => 0,
			"case_sensitive" => 0,
			"multi_line" => 0,
			"eval" => 0,
			"can_view" => 'all',
			"can_use" => 'all',
			"callback" => 0,
		);
	}
	$em = ' <em>*</em>';

	// hmm could this be the form?
	$form = new Form($html->url(array("action" => 'edit')), 'post');

	$tabs = array(
		"general" => $lang->yourcode_tab_general,
		"permissions" => $lang->yourcode_tab_permissions,
		"advanced" => $lang->yourcode_tab_advanced,
		"sandbox" => $lang->yourcode_tab_sandbox,
	);

	$page->output_tab_control($tabs, true);

	echo "\n<div id=\"tab_general\">\n";
	$form_container = new FormContainer("{$button_text} {$lang->yourcode}");
	$form_container->output_row($lang->yourcode_title . $em, '', $form->generate_text_box('title', $data['title']));
	$form_container->output_row($lang->yourcode_description, '', $form->generate_text_area('description', $data['description']));
	$form_container->output_row($lang->yourcode_regular_expression . $em, $lang->yourcode_regular_expression_desc . '<br /><strong>' . $lang->yourcode_example . '</strong> \[b\](.*?)\[/b\]', $form->generate_text_area('regex', $data['regex'], array("id" => 'regex')));
	$form_container->output_row($lang->yourcode_replacement . $em, $lang->yourcode_replacement_desc . '<br /><strong>' . $lang->yourcode_example . '</strong> &lt;strong&gt;$1&lt;/strong&gt;', $form->generate_text_area('replacement', $data['replacement'], array("id" => 'replacement')));
	$form_container->output_row($lang->yourcode_parse_order, $lang->yourcode_parse_order_desc, $form->generate_text_box('parse_order', $data['parse_order']));
	$form_container->output_row($lang->yourcode_active, $lang->yourcode_active_desc, $form->generate_yes_no_radio('active', $data['active']) . $form->generate_hidden_field('id', $data['id']) . $form->generate_hidden_field('default_id', $data['default_id']));
	$form_container->end();

	echo "\n</div>\n<div id=\"tab_permissions\">\n";
	$form_container = new FormContainer("{$button_text} {$lang->yourcode}");

	// groups
	$options = array();
	$query = $db->simple_select('usergroups', 'gid, title', "gid != '1'", array('order_by' => 'title'));
	$options['all'] = $lang->yourcode_all_user_groups;
	while ($usergroup = $db->fetch_array($query)) {
		$options[(int)$usergroup['gid']] = $usergroup['title'];
	}
	$form_container->output_row($lang->yourcode_allowed_user_groups_use, $lang->yourcode_allowed_user_groups_use_desc, $form->generate_select_box('can_use[]', $options, explode(',', $data['can_use']), array('id' => 'can_use', 'multiple' => true, 'size' => 5)), 'can_use');
	$form_container->output_row($lang->yourcode_allowed_user_groups_view, $lang->yourcode_allowed_user_groups_view_desc, $form->generate_select_box('can_view[]', $options, explode(',', $data['can_view']), array('id' => 'can_view', 'multiple' => true, 'size' => 5)), 'can_view');
	$form_container->output_row($lang->yourcode_alternate_replacement, $lang->yourcode_alternate_replacement_desc, $form->generate_text_area('alt_replacement', $data['alt_replacement']));
	$form_container->end();

	echo "\n</div>\n<div id=\"tab_advanced\">\n";
	$form_container = new FormContainer("{$button_text} {$lang->yourcode}");

	$form_container->output_row($lang->yourcode_nestable_title, $lang->yourcode_nestable_desc, $form->generate_yes_no_radio('nestable', $data['nestable'], true, array("id" => 'nestable')));
	$form_container->output_row($lang->yourcode_case_sensitive, $lang->yourcode_case_sensitive_desc, $form->generate_yes_no_radio('case_sensitive', $data['case_sensitive'], true, array("id" => 'case_sensitive')));
	$form_container->output_row($lang->yourcode_single_line, $lang->yourcode_single_line_desc, $form->generate_yes_no_radio('single_line', $data['single_line'], true, array("id" => 'single_line')));
	$form_container->output_row($lang->yourcode_multi_line, $lang->yourcode_multi_line_desc, $form->generate_yes_no_radio('multi_line', $data['multi_line'], true, array("id" => 'multi_line')));
	$form_container->output_row($lang->yourcode_eval, $lang->yourcode_eval_desc, $form->generate_yes_no_radio('eval', $data['eval'], true, array("id" => 'eval')));
	$form_container->output_row($lang->yourcode_callback, $lang->yourcode_callback_desc, $form->generate_yes_no_radio('callback', $data['callback'], true, array("id" => 'callback')));
	$form_container->end();

	echo "\n</div>\n<div id=\"tab_sandbox\">\n";
	$form_container = new FormContainer("{$button_text} {$lang->yourcode}");

	$form_container->output_row($lang->yourcode_sandbox_desc);
	$form_container->output_row($lang->yourcode_test_value, $lang->yourcode_test_value_desc, $form->generate_text_area('test_value', $mybb->input['test_value'], array('id' => 'test_value')).'<br />'.$form->generate_submit_button($lang->yourcode_test, array('id' => 'test', 'name' => 'test')), 'test_value');
	$form_container->output_row($lang->yourcode_result_html, $lang->yourcode_result_html_desc, $form->generate_text_area('result_html', $sandbox['html'], array('id' => 'result_html', 'disabled' => 1)), 'result_html');
	$form_container->output_row($lang->yourcode_result_actual, $lang->yourcode_result_actual_desc, "<div id=\"result_actual\">{$sandbox['actual']}</div>");
	$form_container->end();

	echo("\n</div>");

	$buttons = array($form->generate_submit_button('Save and Continue Editing', array('name' => 'add_code_submit')), $form->generate_submit_button('Save and Return to Listing', array('name' => 'add_code_submit')));
	$form->output_submit_wrapper($buttons);

	// do our sandbox magic from admin/modules/config/mycode.php but use our own regex tester that supports moar cool stuff. Also had to add some options
	echo <<<EOF
<script type="text/javascript" src="./jscripts/yourcode/yourcode_sandbox.js"></script>
<script type="text/javascript">
$(document).ready(function() {
<!--
new YourCode.Sandbox("./index.php?module=config-yourcode&action=edit&mode=xmlhttp", {
		button: $("#test"),
		regexTextbox: $("#regex"),
		replacementTextbox: $("#replacement"),
		testTextbox: $("#test_value"),
		htmlTextbox: $("#result_html"),
		actualDiv: $("#result_actual"),
		nestableInput: $('#nestable'),
		caseSensitiveInput: $('#case_sensitive'),
		singleLineInput: $('#single_line'),
		multiLineInput: $('#multi_line'),
		evalInput: $('#eval'),
	});
});
// -->
</script>
EOF;

	$form->end();

	// be done
	$page->output_footer();
}

/**
 * ACP Page - Manage Modules
 *
 * @return void
 */
function yourcode_admin_module()
{
	global $page, $mybb, $lang, $db, $cache, $html;

	require_once MYBB_ROOT . "inc/plugins/yourcode/classes/YourCodeModule.php";

	// load our cache right away
	$yourcode = $cache->read('yourcode');
	$activeModules = $yourcode['active']['modules'];

	// make sure we have an array even if it is empty
	if (!is_array($activeModules)) {
		$activeModules = array();
	}

	if ($mybb->request_method == 'post') {
		if ($mybb->input['mode'] == 'inline') {
			$redirect = $html->url(array("action" => 'module', "page" => $mybb->input['page']));

			// verify incoming POST request
			if (!verify_post_check($mybb->input['my_post_key'])) {
				flash_message($lang->invalid_post_verify_key2, 'error');
				admin_redirect($redirect);
			}

			if (!is_array($mybb->input['yc_inline_ids']) ||
				empty($mybb->input['yc_inline_ids'])) {
				flash_message($lang->yourcode_inline_selection_error, 'error');
				admin_redirect($redirect);
			}

			$job_count = 0;
			foreach ($mybb->input['yc_inline_ids'] as $name => $throw_away) {
				$this_module = new YourCodeModule($name);
				if (!$this_module->isValid()) {
					continue;
				}

				$deleted = false;
				switch ($mybb->input['inline_action']) {
				case 'delete':
					if (!$this_module->remove()) {
						continue 2;
					}
					$deleted = true;
				default:
					if ($mybb->input['inline_action'] == 'activate') {
						if (in_array($name, $activeModules)) {
							continue 2;
						}

						// activate
						$activeModules[] = $yourcode['active']['modules'][] = $name;
					} elseif (in_array($mybb->input['inline_action'], array('deactivate', 'delete'))) {
						if (!in_array($name, $activeModules)) {
							if ($deleted) {
								continue 1;
							}
							continue 2;
						}

						// deactivate
						$yourcode['active']['modules'] = $activeModules = array_filter(
							$activeModules,
							function($var) use($name)
							{
								return $var != $name;
							}
						);
					}
				}
				++$job_count;
			}
			$action = "{$mybb->input['inline_action']}d";
			flash_message($lang->sprintf($lang->yourcode_inline_success, $job_count, $lang->yourcode_modules, $action), 'success');
			$cache->update('yourcode', $yourcode);
			admin_redirect($redirect);
		}
	}

	if ($mybb->input['mode'] == 'delete') {
		// verify incoming POST request
		if (!verify_post_check($mybb->input['my_post_key'])) {
			flash_message($lang->invalid_post_verify_key2, 'error');
			admin_redirect($html->url(array("action" => 'module')));
		}

		// then attempt deletion
		$this_module = new YourCodeModule($mybb->input['id']);
		if ($this_module->isValid()) {
			$success = $this_module->remove();
		}

		if ($success) {
			if (in_array($mybb->input['id'], $activeModules)) {
				// deactivate
				$yourcode['active']['modules'] = $activeModules = array_filter($activeModules, function($var)
				{
					global $mybb;
					return $var != $mybb->input['id'];
				});
			}
			// yay for us
			flash_message($lang->sprintf($lang->yourcode_message_success, $lang->yourcode_modules, 'deleted'), 'success');
			$cache->update('yourcode', $yourcode);
		} else {
			// boo, we suck
			flash_message($lang->sprintf($lang->yourcode_message_fail, $lang->yourcode_modules, 'deleted'), 'error');
		}
		admin_redirect($html->url(array("action" => 'module')));
	} elseif (in_array($mybb->input['mode'], array('activate', 'deactivate'))) {
		// verify incoming POST request
		if (!verify_post_check($mybb->input['my_post_key'])) {
			flash_message($lang->invalid_post_verify_key2, 'error');
			admin_redirect($html->url(array("action" => 'module')));
		}

		$this_module = new YourCodeModule($mybb->input['name']);

		if ($this_module->isValid()) {
			if ($mybb->input['mode'] == 'activate' &&
				!in_array($mybb->input['name'], $activeModules)) {
				// activate
				$activeModules[] = $yourcode['active']['modules'][] = $mybb->input['name'];
				$has_changed = true;
			} elseif ($mybb->input['mode'] == 'deactivate' &&
					  in_array($mybb->input['name'], $activeModules)) {
				// deactivate
				$yourcode['active']['modules'] = $activeModules = array_filter($activeModules, function($var)
				{
					global $mybb;
					return $var != $mybb->input['name'];
				});
				$has_changed = true;
			}
		} else {
			flash_message($lang->sprintf($lang->yourcode_invalid_module, $lang->yourcode_modules, $action), 'success');
			admin_redirect($html->url(array("action" => 'module')));
		}

		// the tense is past
		$action = "{$mybb->input['mode']}d";

		// only update cache if changes were made
		if ($has_changed) {
			// be happy and update cache
			$cache->update('yourcode', $yourcode);
			flash_message($lang->sprintf($lang->yourcode_message_success, $lang->yourcode_modules, $action), 'success');
		} else {
			// be sad and verbose simultaneously
			flash_message($lang->sprintf($lang->yourcode_message_fail . ' because it was already ' . $action, $lang->yourcode_modules, $action), 'error');
		}
		admin_redirect($html->url(array("action" => 'module')));
	}

	// start page output
	$page->add_breadcrumb_item($lang->yourcode);
	$page->add_breadcrumb_item($lang->yourcode_admin_module);
	$page->extra_header .= <<<EOF

	<script type="text/javascript" src="jscripts/yourcode/yourcode_inline.js"></script>
	<script type="text/javascript">
	<!--
	YourCode.inline.setup({
		go: '{$lang->go}',
		noSelection: '{$lang->yourcode_inline_selection_error}',
	});
	// -->
	</script>
EOF;

	$page->output_header("{$lang->yourcode} - {$lang->yourcode_admin_module}");
	yourcode_output_tabs('yourcode_module', $tab_extra);

	// get all modules and a count
	$allModules = yourcode_get_modules();
	$resultCount = count($allModules);

	$perPage = 10;
	$totalPages = ceil($resultCount / $perPage);
	if (!isset($mybb->input['page']) ||
		$mybb->input['page'] == '') {
		// unset: 1st page
		$mybb->input['page'] = 1;
	} else if ($mybb->input['page'] > $totalPages) {
		// too high: last page
		$mybb->input['page'] = $totalPages;
	} else {
		// just right: chosen page
		$mybb->input['page'] = (int) $mybb->input['page'];
	}

	// more than one page?
	$start = ($mybb->input['page'] - 1) * $perPage;
	if ($resultCount > $perPage) {
		// build pagination and show one copy here
		$pagination = draw_admin_pagination($mybb->input['page'], $perPage, $resultCount, $html->url(array("action" => 'module')));
		echo($pagination);
	}

	$form = new Form($html->url(array("action" => 'module', "mode" => 'inline')), 'post', 'inline_form', '', '', '', $onSubmit);

	$table = new Table;
	$table->construct_header($lang->yourcode_title, array("style" => 'width: 25%;'));
	$table->construct_header($lang->yourcode_description, array("style" => 'width: 52%;'));
	$table->construct_header($lang->yourcode_active, array("style" => 'width: 10%;'));
	$table->construct_header($lang->yourcode_controls, array("style" => 'width: 8%'));
	$table->construct_header($form->generate_check_box('', '', '', array("id" => 'yc_select_all')), array("style" => 'width: 1%'));

	// have modules?
	if (is_array($allModules) &&
		!empty($allModules)) {
		ksort($allModules);

		// show modules
		$counter = -1;
		foreach ($allModules as $id => $module) {
			++$counter;
			if ($counter < $start) {
				continue;
			} elseif ($counter >= ($start + $perPage)) {
				break;
			}

			$title = "{$module->get('title')} ({$module->get('version')})";
			$name = $module->get('base_name');
			$isActive = in_array($name, $activeModules);
			if ($isActive) {
				$activeText = 'Deactivate';
				$activeUrl = $html->url(array("action" => 'module', "mode" => 'deactivate', "page" => $mybb->input['page'], "name" => $name, "my_post_key" => $mybb->post_code));
				$activeLink = $html->link($activeUrl, $activeText, array("title" => 'This YourCode Module is currently active, click to deactivate', "style" => 'color: green'));
			} else {
				$activeText = 'Activate';
				$activeUrl = $html->url(array("action" => 'module', "mode" => 'activate', "page" => $mybb->input['page'], "name" => $name, "my_post_key" => $mybb->post_code));
				$activeLink = $html->link($activeUrl, $activeText, array("title" => 'This YourCode Module is currently inactive, click to activate', "style" => 'color: red'));
			}

			$table->construct_cell($title, array("style" => 'font-weight: bold;'));
			$table->construct_cell($module->get('description'));
			$table->construct_cell($activeLink);

			$popup = new PopupMenu("control_{$id}", $lang->yourcode_options);
			$popup->add_item($activeText, $activeUrl);
			$popup->add_item($lang->yourcode_delete, $html->url(array("action" => 'module', "mode" => 'delete', "page" => $mybb->input['page'], "id" => $id, "my_post_key" => $mybb->post_code)), $onClick);
			$table->construct_cell($popup->fetch());
			$table->construct_cell($form->generate_check_box("yc_inline_ids[{$id}]", '', '', array("class" => 'yc_check')));
			$table->construct_row();
		}
	} else {
		// this time we cry
		$table->construct_cell('<span style="color: #888;"><em>' . $lang->yourcode_no_modules . '</em></span>', array("colspan" => 5));
			$table->construct_row();
	}
	$table->output($lang->yourcode);

	$inline = <<<EOF
<span class="float_right"><strong>{$lang->yourcode_inline_title}:</strong>&nbsp;
<select name="inline_action">
	<option value="activate">{$lang->yourcode_activate}</option>
	<option value="deactivate">{$lang->yourcode_deactivate}</option>
	<option value="delete">{$lang->yourcode_delete}</option>
</select>
<input id="yc_inline_submit" type="submit" class="button" name="yc_inline_submit" value="{$lang->go} (0)"/>
<input id="yc_inline_clear" type="button" class="button" name="yc_inline_clear" value="{$lang->clear}"/>
<input type="hidden" name="page" value="{$mybb->input['page']}"/>
</span>
EOF;
	echo($inline);
	$form->end();

	// need pagination?
	if ($resultCount > $perPage) {
		// ahkay xD
		echo($pagination);
	}
	echo('<br /><br />');

	// be done
	$page->output_footer();
}

/**
 * ACP Page - Tools
 *
 * @return void
 */
function yourcode_admin_tools()
{
	global $page, $mybb, $lang, $db, $cache, $html;

	if ($mybb->input['mode'] == 'clear') {
		// verify incoming POST request
		if (!verify_post_check($mybb->input['my_post_key'])) {
			flash_message($lang->invalid_post_verify_key2, 'error');
			admin_redirect($html->url(array("action" => 'module')));
		}

		if (yourcode_clear_all()) {
			// yay for us
			flash_message($lang->yourcode_clear_success, 'success');
			yourcode_build_cache();
		} else {
			// boo, we suck
			flash_message($lang->yourcode_clear_fail, 'error');
		}
	} elseif ($mybb->input['mode'] == 'default') {
		if (yourcode_clear_all()) {
			$success = yourcode_port_old_mycode();
		}

		if ($success) {
			// yay for us
			yourcode_build_cache();
			flash_message($lang->yourcode_default_success, 'success');
			admin_redirect($html->url(array("action" => 'view')));
		} else {
			// boo, we suck
			flash_message($lang->yourcode_default_fail, 'error');
		}
	} elseif ($mybb->input['mode'] == 'backup') {
		yourcode_backup();
		exit;
	}

	// start page output
	$page->add_breadcrumb_item($lang->yourcode);
	$page->add_breadcrumb_item($lang->yourcode_admin_tools);
	$page->output_header("{$lang->yourcode} - {$lang->yourcode_admin_tools}");
	yourcode_output_tabs('yourcode_tools');

	$onSubmit = <<<EOF
if($('#file_s').val()) { return true; } alert('{$lang->yourcode_import_no_file}'); return false;
EOF;

	$form = new Form($html->url(array("action" => 'import')), 'post', '', 1, '', '', $onSubmit);
	$form_container = new FormContainer($lang->yourcode_import);
	$form_container->output_row($lang->yourcode_import_select_file, $lang->yourcode_import_select_file_desc, $form->generate_file_upload_box('file_s', array("id" => 'file_s')));
	$form_container->end();
	$buttons = array($form->generate_submit_button($lang->yourcode_import, array('name' => 'import')));
	$form->output_submit_wrapper($buttons);
	$form->end();

	echo('<br /><br />');

	$onClickClear = <<<EOF
return AdminCP.deleteConfirmation(this, '{$lang->yourcode_delete_warning_clear}');
EOF;

	$onClickDefault = <<<EOF
return AdminCP.deleteConfirmation(this, '{$lang->yourcode_delete_warning_default}');
EOF;

	$table = new Table;
	$table->construct_cell($html->link($html->url(array("action" => 'tools', "mode" => 'backup', "my_post_key" => $mybb->post_code)), $lang->yourcode_backup) . $lang->yourcode_backup_fin);
	$table->construct_row();
	$table->construct_cell($html->link($html->url(array("action" => 'tools', "mode" => 'clear', "my_post_key" => $mybb->post_code)), $lang->yourcode_clear, array("onclick" => $onClickClear)) . $lang->yourcode_clear_fin);
	$table->construct_row();
	$table->construct_cell($html->link($html->url(array("action" => 'tools', "mode" => 'default', "my_post_key" => $mybb->post_code)), $lang->yourcode_default, array("onclick" => $onClickDefault)) . $lang->yourcode_default_fin);
	$table->construct_row();
	$table->output($lang->yourcode_quick_links);

	// be done
	$page->output_footer();
}

/**
 * ACP Page - Import
 *
 * @return void
 */
function yourcode_admin_import()
{
	global $page, $mybb, $lang, $db, $cache, $html;

	// start page output
	$page->add_breadcrumb_item($lang->yourcode);
	$page->add_breadcrumb_item($lang->yourcode_admin_import);
	$page->output_header("{$lang->yourcode} - {$lang->yourcode_admin_import}");
	yourcode_output_tabs('yourcode_import');

	if ($mybb->request_method == 'post') {
		if ($mybb->input['import'] == $lang->yourcode_cancel) {
			admin_redirect($html->url(array("action" => 'tools')));
		}

		if ($mybb->input['mode'] == 'finish') {
			if (is_array($mybb->input['export_ids']) &&
				!empty($mybb->input['export_ids'])) {
				$contents = $mybb->input['contents'];
				if (!trim($contents)) {
					$error = $lang->yourcode_import_file_empty;
				}

				if (!$error) {
					$codes = yourcode_import_check($contents);
					$total = 0;
					if (is_array($codes) && !empty($codes)) {
						foreach ($codes as $id => $code) {
							if (isset($mybb->input['export_ids'][$id])) {
								$code->save();
								++$total;
							}
						}
					} else {
						$error = $lang->yourcode_import_file_empty;
					}
				}
			} else {
				$error = $lang->yourcode_import_selection_error;
			}

			if ($error) {
				flash_message($error, 'error');
				admin_redirect($html->url(array("action" => 'tools')));
			}
			flash_message($lang->sprintf($lang->yourcode_import_save_success, $total), 'success');
			admin_redirect($html->url(array("action" => 'view')));
		} else {
			$redirect = $html->url(array("action" => 'tools'));
			$contents = yourcode_check_uploaded_file('file_s', $redirect);

			$codes = yourcode_import_check($contents);
			if (!is_array($codes) ||
				empty($codes)) {
				flash_message($lang->yourcode_import_file_upload_error, 'error');
				admin_redirect($redirect);
			}

			$total = count($codes);
			$onClick = <<<EOF
var all_checks = $('input.checkbox_input'); if(this.checked) { checked = true; } for(x = 0; x < all_checks.length; x++) { all_checks[x].checked = checked; }
EOF;
			$onSubmit = <<<EOF
var all_checks = $('input.checkbox_input'); for(x = 0; x < all_checks.length; x++) { if(all_checks[x].checked) { return true; } } alert('{$lang->yourcode_import_selection_error}'); return false;
EOF;
			$form = new Form($html->url(array("action" => 'import', "mode" => 'finish')), 'post');
			$form_container = new FormContainer($lang->yourcode_import);

			$percentage = 45;
			$control_percentage = 10;
			if ($total > 1) {
				$percentage = 22;
				$control_percentage = 5;
				$form_container->output_row_header($lang->yourcode_title, array("style" => "width: {$percentage}%;"));
				$form_container->output_row_header($lang->yourcode_description, array("style" => "width: {$percentage}%;"));
				$form_container->output_row_header($form->generate_check_box("allbox", '', '', array("onclick" => $onClick)), array("style" => "width: {$control_percentage}%;"));
			}

			$form_container->output_row_header($lang->yourcode_title, array("style" => "width: {$percentage}%;"));
			$form_container->output_row_header($lang->yourcode_description, array("style" => "width: {$percentage}%;"));
			$form_container->output_row_header($form->generate_check_box("allbox", '', '', array("onclick" => $onClick)), array("style" => "width: {$control_percentage}%;"));

			$row = 1;
			// if so display them
			foreach ($codes as $id => $code) {
				$form_container->output_cell($code->get('title'));
				$form_container->output_cell($code->get('description'));
				$form_container->output_cell($form->generate_check_box("export_ids[{$id}]"));

				if ($row == 2 ||
					$total == 1) {
					$form_container->construct_row();
					$row = 0;
				}
				++$row;
			}

			// if the file contains more than one YourCode
			if ($total > 1) {
				// if we were on an odd row
				if ($row == 2) {
					// fill up the blank cell
					$form_container->output_cell('&nbsp;');
					$form_container->output_cell('&nbsp;');
					$form_container->output_cell('&nbsp;');
					$form_container->construct_row();
				}
				// then pad the left side of the hidden row
				$form_container->output_cell("<span style=\"font-weight: bold; font-size: 1.2em; color: blue;\">{$total} total YourCode in XML</span>");
				$form_container->output_cell('&nbsp;');
				$form_container->output_cell('&nbsp;');
			}

			// no matter the count, we need our hidden info
			$form_container->output_cell('&nbsp;');
			$form_container->output_cell($form->generate_hidden_field('contents', $contents));
			$form_container->output_cell('&nbsp;');
			$form_container->construct_row();
			$form_container->end();
			$buttons = array();
			$buttons[] = $form->generate_submit_button($lang->yourcode_import, array("onclick" => $onSubmit, "name" => 'import'));
			$buttons[] = $form->generate_submit_button($lang->yourcode_cancel, array("name" => 'import'));
			$form->output_submit_wrapper($buttons);
			$form->end();
		}
	}
	$page->output_footer();
}

/**
 * provide a replacement for MyBB's built-in regex sandbox backend
 *
 * @param  string the regular expression
 * @param  string the replacement HTML
 * @param  string the test text
 * @param  array regex modifiers
 * @return array the test info
 */
function yourcode_test_regex($regex, $replacement, $test_value, $options)
{
	global $lang;

	$modifiers = '';

	// these modifiers are off by default
	foreach (array("s" => 'single_line', "m" => 'multi_line', "e" => 'eval') as $modifier => $property) {
		if ($options[$property]) {
			$modifiers .= $modifier;
		}
	}

	// case INsensitive is default
	if (!$options['case_sensitive']) {
		$modifiers .= 'i';
	}

	// build the regex
	$regex = "#" . str_replace("\x0", "", $regex) . "#{$modifiers}";

	// nestable?
	if (!$options['nestable']) {
		// if not just do it one time
		$output = @preg_replace($regex, $replacement, $test_value);
	} else {
		// if so do it till you feel something break
		$output = $test_value;
		while (@preg_match($regex, $output)) {
			$output = @preg_replace($regex, $replacement, $output);
		}
	}

	// AJAX only uses actual, but we need both for those who don't use JS (like who? idk :s )
	return array("actual" => $output, "html" => htmlspecialchars_uni($output));
}

/**
 * adds YourCode to the list of possible actions
 *
 * @param  array the list of selectable items on the config tab
 * @return void
 */
$plugins->add_hook('admin_config_action_handler', 'yourcode_admin_action');
function yourcode_admin_action(&$action)
{
	$action['yourcode'] = array('active' => 'yourcode');
}

/**
 * add an entry to the ACP Config page menu
 *
 * @param  array the menu items
 * @return void
 */
$plugins->add_hook('admin_config_menu', 'yourcode_admin_menu');
function yourcode_admin_menu(&$sub_menu)
{
	global $lang;
	if (!$lang->yourcode) {
		$lang->load('yourcode');
	}

	// look for the default MyCode menu item
	foreach ($sub_menu as $key => $val_array) {
		if ($val_array['id'] == 'mycode') {
			$this_key = $key;
			break;
		}
	}

	$yourcode_menuitem = array(
		'id' 		=> 'yourcode',
		'title' 	=> $lang->yourcode,
		'link' 		=> YOURCODE_URL
	);

	// if we found one (and we will)
	if ($this_key) {
		// overwrite it with ours muahahaha!
		$sub_menu[$this_key] = $yourcode_menuitem;
	} else {
		// otherwise (in some alternate reality where people have deleted the MyCode module from the admin/modules/config directory or otherwise disabled it) add our item to the end of the menu
		end($sub_menu);
		$key = (key($sub_menu)) + 10;
		$sub_menu[$key] = $yourcode_menuitem;
	}
}

/**
 * add an entry to admin permissions list
 *
 * @param  array permission types
 * @return void
 */
$plugins->add_hook('admin_config_permissions', 'yourcode_admin_permissions');
function yourcode_admin_permissions(&$admin_permissions)
{
	global $lang;

	if (!$lang->yourcode) {
		$lang->load('yourcode');
	}
	$admin_permissions['yourcode'] = $lang->yourcode_admin_permissions_desc;
}

/**
 * output ACP tabs for our pages
 *
 * @param  string the tab currently being viewed
 * @return void
 */
function yourcode_output_tabs($current)
{
	global $page, $lang, $mybb, $html;

	// set up tabs
	$sub_tabs['yourcode_main'] = array(
		'title'       => $lang->yourcode_admin_view,
		'link'        => $html->url(),
		'description' => $lang->yourcode_admin_view_desc
	);
	$sub_tabs['yourcode_edit'] = array(
		'title'       => $lang->yourcode_admin_edit,
		'link'        => $html->url(array("action" => 'edit', "my_post_key" => $mybb->post_code)),
		'description' => $lang->yourcode_admin_edit_desc
	);
	$sub_tabs['yourcode_module'] = array(
		'title'       => $lang->yourcode_admin_module,
		'link'        => $html->url(array("action" => 'module')),
		'description' => $lang->yourcode_admin_module_desc
	);
	$sub_tabs['yourcode_tools'] = array(
		'title'       => $lang->yourcode_admin_tools,
		'link'        => $html->url(array("action" => 'tools')),
		'description' => $lang->yourcode_admin_tools_desc
	);
	if ($current == 'yourcode_import') {
		$sub_tabs['yourcode_import'] = array(
			'title'       => $lang->yourcode_admin_import,
			'link'        => $html->url(array("action" => 'import')),
			'description' => $lang->yourcode_admin_import_desc
		);
	}
	$page->output_nav_tabs($sub_tabs, $current);
}

?>
