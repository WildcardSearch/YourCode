<?php
/*
 * Plugin Name: YourCode for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 */

require_once MYBB_ROOT . "inc/plugins/yourcode/install.php";

/*
 * yourcode_admin()
 *
 * the ACP page router
 */
$plugins->add_hook('admin_load', 'yourcode_admin');
function yourcode_admin()
{
	// globalize as needed to save wasted work
	global $page;
	if(!in_array($page->active_action, array('mycode', 'yourcode')))
	{
		// not our turn
		return false;
	}

	// now load up, this is our time
	global $mybb, $lang;
	if(!$lang->yourcode)
	{
		$lang->load('yourcode');
	}

	// no need for all the classes and functions if it is just AJAX test
	if($mybb->input['mode'] != 'xmlhttp')
	{
		require_once MYBB_ROOT . "inc/plugins/yourcode/classes/standard.php";
		require_once MYBB_ROOT . "inc/plugins/yourcode/functions.php";
	}

	// default page is view YourCode
	if(!isset($mybb->input['action']) || $mybb->input['action'] == '')
	{
		$mybb->input['action'] = 'view';
	}

	// if there is an existing function for the action
	$page_function = 'yourcode_admin_' . $mybb->input['action'];
	if(function_exists($page_function))
	{
		// run it
		$page_function();
	}
	else
	{
		yourcode_admin_view();
	}
	// get out
	exit();
}

/*
 * yourcode_admin_view()
 *
 * ACP Page - View YourCode
 */
function yourcode_admin_view()
{
	global $page, $mybb, $lang, $db;

	if($mybb->input['mode'] == 'delete')
	{
		// good info?
		if(isset($mybb->input['id']) && (int) $mybb->input['id'])
		{
			// then attempt deletion
			$this_code = new YourCode($mybb->input['id']);
			if($this_code->is_valid())
			{
				$success = $this_code->remove();
			}
		}

		// we in the past tense now dawg
		$action = "{$mybb->input['mode']}d";
		if($success)
		{
			// yay for us
			flash_message($lang->sprintf($lang->yourcode_message_success, $lang->yourcode, $action), 'success');
			_yc_build_cache();
		}
		else
		{
			// boo, we suck
			flash_message($lang->sprintf($lang->yourcode_message_fail, $lang->yourcode, $action), 'error');
		}
	}
	elseif(in_array($mybb->input['mode'], array('activate', 'deactivate')))
	{
		// good info?
		if(isset($mybb->input['id']) && (int) $mybb->input['id'])
		{
			$this_code = new YourCode($mybb->input['id']);
			if($this_code->is_valid())
			{
				$value = ($mybb->input['mode'] == 'activate');
				$this_code->set('active', $value);
				$success = $this_code->save();
			}
		}

		$action = "{$mybb->input['mode']}d";
		if($success)
		{
			// yay for us
			flash_message($lang->sprintf($lang->yourcode_message_success, $lang->yourcode, $action), 'success');
			_yc_build_cache();
		}
		else
		{
			// boo, we suck
			flash_message($lang->sprintf($lang->yourcode_message_fail, $lang->yourcode, $action), 'error');
		}
	}
	elseif($mybb->input['mode'] == 'export')
	{
		// good info?
		if(isset($mybb->input['id']) && (int) $mybb->input['id'])
		{
			// then attempt deletion
			$this_code = new YourCode($mybb->input['id']);
			if($this_code->is_valid())
			{
				$success = $this_code->export();
				exit;
			}
		}
		// boo, we suck
		flash_message($lang->sprintf($lang->yourcode_message_fail, $lang->yourcode, $lang->yourcode_exported), 'error');
	}

	$page->extra_header .= "
	<script type=\"text/javascript\">
	var my_post_key = '".$mybb->post_code."';
	</script>";

	// start output
	$page->add_breadcrumb_item($lang->yourcode);
	$page->add_breadcrumb_item($lang->yourcode_admin_view);
	$page->output_header("{$lang->yourcode} - {$lang->yourcode_admin_view}");
	yourcode_output_tabs('yourcode_main', $tab_extra);

	// get a total count on the YourCodes
	$query = $db->simple_select('yourcode', 'COUNT(id) AS num_results');
	$num_results = $db->fetch_field($query, 'num_results');

	// TODO: make this a setting or tie it in to a relevant setting if it exists
	$perpage = 10;
	$total_pages = ceil($num_results / $perpage);

	if($total_pages)
	{
		// adjust the page number if the user has entered manually or is returning to a page that no longer exists (deleted last YourCode on page)
		if(!isset($mybb->input['page']) || $mybb->input['page'] == '' || (int) $mybb->input['page'] < 1)
		{
			// no page, page = 1
			$mybb->input['page'] = 1;
		}
		else if($mybb->input['page'] > $total_pages)
		{
			// past last page? page = last page
			$mybb->input['page'] = $total_pages;
		}
		else
		{
			// in range? page = # in link
			$mybb->input['page'] = (int) $mybb->input['page'];
		}

		// more than one page?
		$start = ($mybb->input['page'] - 1) * $perpage;
		if($num_results > $perpage)
		{
			// save the pagination for below and show it here as well
			$pagination = draw_admin_pagination($mybb->input['page'], $perpage, $num_results, _yc_url(array("action" => 'view')));
			echo($pagination . '<br />');
		}

		// get all the codes for this page
		$all_codes = _yc_get_all($start, $perpage);
		$table = new Table;
		$table->construct_header($lang->yourcode_title, array("style" => 'width: 25%;'));
		$table->construct_header($lang->yourcode_description, array("style" => 'width: 40%;'));
		$table->construct_header($lang->yourcode_parse_order, array("style" => 'width: 8%;'));
		$table->construct_header($lang->yourcode_nestable, array("style" => 'width: 8%;'));
		$table->construct_header($lang->yourcode_active, array("style" => 'width: 11%;'));
		$table->construct_header($lang->yourcode_controls, array("style" => 'width: 8%'));

		$onclick = <<<EOF
return AdminCP.deleteConfirmation(this, '{$lang->yourcode_delete_warning_simple}');
EOF;

		// any codes?
		if(is_array($all_codes) && !empty($all_codes))
		{
			// if so display them
			foreach($all_codes as $id => $code)
			{
				$edit_url = _yc_url(array("action" => 'edit',  "id" => $id));
				$is_active = $code->get('active');
				if($is_active)
				{
					$active_text = $lang->yourcode_deactivate;
					$active_url = _yc_url(array("action" => 'view', "mode" => 'deactivate', "page" => $mybb->input['page'], "id" => $id));
					$active_link = _yc_link($active_url, $active_text, array("title" => $lang->sprintf($lang->yourcode_message_active_status, strtolower($lang->yourcode_active), strtolower($active_text)), "style" => 'color: green'));
				}
				else
				{
					$active_text = $lang->yourcode_activate;
					$active_url = _yc_url(array("action" => 'view', "mode" => 'activate', "page" => $mybb->input['page'], "id" => $id));
					$active_link = _yc_link($active_url, $active_text, array("title" => $lang->sprintf($lang->yourcode_message_active_status, strtolower($lang->yourcode_inactive), strtolower($active_text)), "style" => 'color: red'));
				}

				if($code->get('nestable'))
				{
					$nested_text = "<span style=\"font-weight: bold; color: green\">{$lang->yourcode_yes}</span>";
				}
				else
				{
					$nested_text = "<span style=\"color: #888;\"><em>{$lang->yourcode_no}</em></span>";
				}

				$table->construct_cell(_yc_link($edit_url, $code->get('title')), array("style" => 'font-weight: bold;'));
				$table->construct_cell($code->get('description'));
				$table->construct_cell($code->get('parse_order'));
				$table->construct_cell($nested_text);
				$table->construct_cell($active_link);

				$popup = new PopupMenu("control_{$id}", $lang->yourcode_options);
				$popup->add_item($lang->yourcode_edit, $edit_url);
				$popup->add_item($active_text, $active_url);
				$popup->add_item($lang->yourcode_export, _yc_url(array("action" => 'view', "mode" => 'export', "page" => $mybb->input['page'], "id" => $id)));
				$popup->add_item($lang->yourcode_delete, _yc_url(array("action" => 'view', "mode" => 'delete', "page" => $mybb->input['page'], "id" => $id, "my_post_key" => $mybb->post_code)), $onclick);
				$table->construct_cell($popup->fetch());
				$table->construct_row();
			}
		}
		else
		{
			// if not whine about it a bit :*(
			$table->construct_cell("<span style=\"color: #888;\">{$lang->yourcode_no_code}</span>", array("colspan" => 6));
				$table->construct_row();
		}
		$table->output($lang->yourcode);

		// more than one page?
		if($num_results > $perpage)
		{
			// if so show pagination on the right this time just to be weird
			echo('<br />' . $pagination);
		}
	}
	else
	{
		echo("<span style=\"color: #888;\">{$lang->yourcode_no_code}</span>");
	}

	// be done
	$page->output_footer();
}

/*
 * yourcode_admin_edit()
 *
 * ACP Page: Add/Edit YourCode
 */
function yourcode_admin_edit()
{
	global $page, $mybb, $lang, $db;

	// adding/updating
	if($mybb->request_method == 'post')
	{
		if(in_array($mybb->input['add_code_submit'], array('Save and Return to Listing', 'Save and Continue Editing')))
		{
			// create a new object from the passed info and save it
			$this_code = new YourCode($mybb->input);
			$success = $this_code->save();

			// past tense is very important to me :P
			if($mybb->input['id'])
			{
				$action = $lang->yourcode_updated;
			}
			else
			{
				$action = $lang->yourcode_added;
			}

			if($success)
			{
				// yay for us
				_yc_build_cache();
				flash_message($lang->sprintf($lang->yourcode_message_success, $lang->yourcode, $action), 'success');

				if($mybb->input['add_code_submit'] == 'Save and Return to Listing')
				{
					admin_redirect(YOURCODE_URL);
				}
				else if(!$mybb->input['id'])
				{
					$mybb->input['id'] = $success;
				}
			}
			else
			{
				// boo, we suck
				flash_message($lang->sprintf($lang->yourcode_message_fail, $lang->yourcode, $action), 'error');
			}
		}
		elseif($mybb->input['mode'] == 'xmlhttp')
		{
			// send no cache headers
			header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			header("Content-type: text/html");

			// test the regex and echo it for AJAX
			$sandbox = yourcode_test_regex($mybb->input['regex'], $mybb->input['replacement'], $mybb->input['test_value'], $mybb->input);
			echo $sandbox['actual'];
			exit;
		}
	}

	// admin has no JS in ACP?
	if(isset($mybb->input['test']) && $mybb->input['test'])
	{
		// test the regex and warn them that save hasn't occurred
		$sandbox = yourcode_test_regex($mybb->input['regex'], $mybb->input['replacement'], $mybb->input['test_value'], $mybb->input);
		flash_message($lang->yourcode_sandbox_test_no_save_warning, 'error');
	}

	// for the sandbox
	$page->extra_header .= "
	<script type=\"text/javascript\">
	var my_post_key = '".$mybb->post_code."';
	</script>";

	// start page output
	$page->add_breadcrumb_item($lang->yourcode);
	$page->add_breadcrumb_item($lang->yourcode_admin_edit);
	$page->output_header("{$lang->yourcode} - {$lang->yourcode_admin_edit}");
	yourcode_output_tabs('yourcode_edit', $tab_extra);

	// default to adding
	$button_text = $lang->yourcode_add;
	$this_code = new YourCode($mybb->input['id']);

	// no ID means a new YourCode
	if($this_code->is_valid())
	{
		// editing? store the info for the form
		$data = $this_code->get('data');

		if(!$data['can_view'])
		{
			$data['can_view'] = 'all';
		}
		if(!$data['can_use'])
		{
			$data['can_use'] = 'all';
		}

		$button_text = $lang->yourcode_update;
	}
	else
	{
		// creating? set up some defaults
		$data = array
			(
				"parse_order" => 10,
				"nestable" => 0,
				"case_sensitive" => 0,
				"multi_line" => 0,
				"eval" => 0,
				"can_view" => 'all',
				"can_use" => 'all'
			);
	}
	$em = " <em>*</em>";

	// hmm could this be the form?
	$form = new Form(_yc_url(array("action" => 'edit')), "post");
	$form_container = new FormContainer("{$button_text} {$lang->yourcode}");
	$form_container->output_row($lang->yourcode_title . $em, '', $form->generate_text_box('title', $data['title']));
	$form_container->output_row($lang->yourcode_description, '', $form->generate_text_area('description', $data['description']));
	$form_container->output_row($lang->yourcode_regular_expression . $em, $lang->yourcode_regular_expression_desc . '<br /><strong>' . $lang->yourcode_example . '</strong> \[b\](.*?)\[/b\]', $form->generate_text_area('regex', $data['regex'], array("id" => 'regex')));
	$form_container->output_row($lang->yourcode_replacement . $em, $lang->yourcode_replacement_desc . '<br /><strong>' . $lang->yourcode_example . '</strong> &lt;strong&gt;$1&lt;/strong&gt;', $form->generate_text_area('replacement', $data['replacement'], array("id" => 'replacement')));
	$form_container->output_row($lang->yourcode_nestable_title, $lang->yourcode_nestable_desc, $form->generate_yes_no_radio('nestable', $data['nestable']));
	$form_container->output_row($lang->yourcode_case_sensitive, $lang->yourcode_case_sensitive_desc, $form->generate_yes_no_radio('case_sensitive', $data['case_sensitive']));
	$form_container->output_row($lang->yourcode_single_line, $lang->yourcode_single_line_desc, $form->generate_yes_no_radio('single_line', $data['single_line']));
	$form_container->output_row($lang->yourcode_multi_line, $lang->yourcode_multi_line_desc, $form->generate_yes_no_radio('multi_line', $data['multi_line']));
	$form_container->output_row($lang->yourcode_eval, $lang->yourcode_eval_desc, $form->generate_yes_no_radio('eval', $data['eval']));
	$form_container->output_row($lang->yourcode_parse_order, $lang->yourcode_parse_order_desc, $form->generate_text_box('parse_order', $data['parse_order']));
	$form_container->output_row($lang->yourcode_active, $lang->yourcode_active_desc, $form->generate_yes_no_radio('active', $data['active']) . $form->generate_hidden_field('id', $data['id']));

	// groups
	$options = array();
	$query = $db->simple_select("usergroups", "gid, title", "gid != '1'", array('order_by' => 'title'));
	$options['all'] = $lang->yourcode_all_user_groups;
	while($usergroup = $db->fetch_array($query))
	{
		$options[(int)$usergroup['gid']] = $usergroup['title'];
	}
	$form_container->output_row($lang->yourcode_allowed_user_groups_use, $lang->yourcode_allowed_user_groups_use_desc, $form->generate_select_box('can_use[]', $options, explode(",", $data['can_use']), array('id' => 'can_use', 'multiple' => true, 'size' => 5)), 'can_use');
	$form_container->output_row($lang->yourcode_allowed_user_groups_view, $lang->yourcode_allowed_user_groups_view_desc, $form->generate_select_box('can_view[]', $options, explode(",", $data['can_view']), array('id' => 'can_view', 'multiple' => true, 'size' => 5)), 'can_view');
	$form_container->output_row($lang->yourcode_alternate_replacement, $lang->yourcode_alternate_replacement_desc, $form->generate_text_area('alt_replacement', $data['alt_replacement']));
	$form_container->end();
	$buttons = array($form->generate_submit_button('Save and Continue Editing', array('name' => 'add_code_submit')), $form->generate_submit_button('Save and Return to Listing', array('name' => 'add_code_submit')));
	$form->output_submit_wrapper($buttons);

	// sandbox form
	echo "<br />\n";
	$form_container = new FormContainer($lang->yourcode_sandbox);
	$form_container->output_row($lang->yourcode_sandbox_desc);
	$form_container->output_row($lang->yourcode_test_value, $lang->yourcode_test_value_desc, $form->generate_text_area('test_value', $mybb->input['test_value'], array('id' => 'test_value'))."<br />".$form->generate_submit_button($lang->yourcode_test, array('id' => 'test', 'name' => 'test')), 'test_value');
	$form_container->output_row($lang->yourcode_result_html, $lang->yourcode_result_html_desc, $form->generate_text_area('result_html', $sandbox['html'], array('id' => 'result_html', 'disabled' => 1)), 'result_html');
	$form_container->output_row($lang->yourcode_result_actual, $lang->yourcode_result_actual_desc, "<div id=\"result_actual\">{$sandbox['actual']}</div>");
	$form_container->end();

	// do out sandbox magic from admin/modules/config/mycode.php but use our own regex tester that supports moar cool stuff. Also had to add some options
	echo '<script type="text/javascript" src="./jscripts/mycode_sandbox.js"></script>';
	echo <<<EOF
<script type="text/javascript">

Event.observe(window, "load", function() {
//<![CDATA[
    new MyCodeSandbox("./index.php?module=config-yourcode&action=edit&mode=xmlhttp&nestable={$data['nestable']}&case_sensitive={$data['case_sensitive']}&single_line={$data['single_line']}&multi_line={$data['multi_line']}&eval={$data['eval']}", $("test"), $("regex"), $("replacement"), $("test_value"), $("result_html"), $("result_actual"));
});
//]]>
</script>
EOF;

	$form->end();

	// be done
	$page->output_footer();
}

/*
 * yourcode_admin_tools()
 *
 * ACP Page - Tools
 */
function yourcode_admin_tools()
{
	global $page, $mybb, $lang, $db, $cache;

	if($mybb->request_method == "post")
	{
		if($mybb->input['mode'] == 'restore')
		{
			if(!$_FILES['file'] || $_FILES['file']['error'] == 4)
			{
				$error = $lang->yourcode_import_no_file;
			}
			elseif($_FILES['file']['error'])
			{
				$error = $lang->sprintf($lang->yourcode_import_file_error, $_FILES['file']['error']);
			}
			else
			{
				if(!is_uploaded_file($_FILES['file']['tmp_name']))
				{
					$error = $lang->yourcode_import_file_upload_error;
				}
				else
				{
					$contents = @file_get_contents($_FILES['file']['tmp_name']);
					@unlink($_FILES['file']['tmp_name']);
					if(!trim($contents))
					{
						$error = $lang->yourcode_import_file_empty;
					}
				}
			}

			if(!$error)
			{
				if(_yc_clear_all())
				{
					$success = _yc_restore($contents);

					if($success)
					{
						flash_message($lang->yourcode_restore_success, 'success');
						admin_redirect(_yc_url(array("action" => 'view')));
					}
					else
					{
						$error = $lang->yourcode_restore_fail;
					}
				}
				else
				{
					$error = $lang->yourcode_restore_fail_clear;
				}
			}
			flash_message($error, 'error');
			admin_redirect(_yc_url(array("action" => 'tools')));
		}
	}

	if($mybb->input['mode'] == 'clear')
	{
		if(_yc_clear_all())
		{
			// yay for us
			flash_message($lang->yourcode_clear_success, 'success');
			_yc_build_cache();
		}
		else
		{
			// boo, we suck
			flash_message($lang->yourcode_clear_fail, 'error');
		}
	}
	elseif($mybb->input['mode'] == 'default')
	{
		if(_yc_clear_all())
		{
			$success = yourcode_port_old_mycode();
		}

		if($success)
		{
			// yay for us
			_yc_build_cache();
			flash_message($lang->yourcode_default_success, 'success');
			admin_redirect(_yc_url(array("action" => 'view')));
		}
		else
		{
			// boo, we suck
			flash_message($lang->yourcode_default_fail, 'error');
		}
	}
	elseif($mybb->input['mode'] == 'backup')
	{
		_yc_backup();
		exit;
	}

	$page->extra_header .= "
	<script type=\"text/javascript\">
	var my_post_key = '".$mybb->post_code."';
	</script>";

	// start page output
	$page->add_breadcrumb_item($lang->yourcode);
	$page->add_breadcrumb_item($lang->yourcode_admin_tools);
	$page->output_header("{$lang->yourcode} - {$lang->yourcode_admin_tools}");
	yourcode_output_tabs('yourcode_tools', $tab_extra);

	$onsubmit = <<<EOF
if($('file_s').value) { return true; } else { alert('{$lang->yourcode_import_no_file}'); return false; }
EOF;

	$form = new Form(_yc_url(array("action" => 'import')), 'post', '', 1, '', '', $onsubmit);
	$form_container = new FormContainer($lang->yourcode_import);
	$form_container->output_row($lang->yourcode_import_select_file, $lang->yourcode_import_select_file_desc, $form->generate_file_upload_box('file_s', array("id" => 'file_s')) . $form->generate_hidden_field('my_post_key', $mybb->post_code));
	$form_container->end();
	$buttons = array($form->generate_submit_button($lang->yourcode_import, array('name' => 'import')));
	$form->output_submit_wrapper($buttons);
	$form->end();

	echo('<br /><br />');

	$onclick_clear = <<<EOF
return AdminCP.deleteConfirmation(this, '{$lang->yourcode_delete_warning_clear}');
EOF;

	$onclick_default = <<<EOF
return AdminCP.deleteConfirmation(this, '{$lang->yourcode_delete_warning_default}');
EOF;

	$table = new Table;
	$table->construct_cell(_yc_link(_yc_url(array("action" => 'tools', "mode" => 'backup', "my_post_key" => $mybb->post_code)), $lang->yourcode_backup) . $lang->yourcode_backup_fin);
	$table->construct_row();
	$table->construct_cell(_yc_link(_yc_url(array("action" => 'tools', "mode" => 'clear', "my_post_key" => $mybb->post_code)), $lang->yourcode_clear, array("onclick" => $onclick_clear)) . $lang->yourcode_clear_fin);
	$table->construct_row();
	$table->construct_cell(_yc_link(_yc_url(array("action" => 'tools', "mode" => 'default', "my_post_key" => $mybb->post_code)), $lang->yourcode_default, array("onclick" => $onclick_default)) . $lang->yourcode_default_fin);
	$table->construct_row();
	$table->output($lang->yourcode_quick_links);

	// be done
	$page->output_footer();
}

/*
 * yourcode_admin_import()
 *
 * ACP Page - Import
 */
function yourcode_admin_import()
{
	global $page, $mybb, $lang, $db, $cache;

	$page->extra_header .= <<<EOF
	<script type="text/javascript">
	var my_post_key = '{$mybb->post_code}';
	</script>
EOF;

	// start page output
	$page->add_breadcrumb_item($lang->yourcode);
	$page->add_breadcrumb_item($lang->yourcode_admin_import);
	$page->output_header("{$lang->yourcode} - {$lang->yourcode_admin_import}");
	yourcode_output_tabs('yourcode_import', $tab_extra);

	if($mybb->request_method == "post")
	{
		if($mybb->input['mode'] == 'finish')
		{
			if(is_array($mybb->input['export_ids']) && !empty($mybb->input['export_ids']))
			{
				$contents = $mybb->input['contents'];
				if(!trim($contents))
				{
					$error = $lang->yourcode_import_file_empty;
				}

				if(!$error)
				{
					$codes = _yc_import_check($contents);
					$total = 0;
					if(is_array($codes) && !empty($codes))
					{
						foreach($codes as $id => $code)
						{
							if(isset($mybb->input['export_ids'][$id]))
							{
								$code->save();
								++$total;
							}
						}
					}
					else
					{
						$error = $lang->yourcode_import_file_empty;
					}
				}
			}
			else
			{
				$error = $lang->yourcode_import_selection_error;
			}

			if($error)
			{
				flash_message($error, 'error');
				admin_redirect(_yc_url(array("action" => 'tools')));
			}
			flash_message($lang->sprintf($lang->yourcode_import_save_success, $total), 'success');
			admin_redirect(_yc_url(array("action" => 'view')));
		}
		else
		{
			if(!$_FILES['file_s'] || $_FILES['file_s']['error'] == 4)
			{
				$error = $lang->yourcode_import_no_file;
			}
			elseif($_FILES['file_s']['error'])
			{
				$error = $lang->sprintf($lang->yourcode_import_file_error, $_FILES['file_s']['error']);
			}
			else
			{
				if(!is_uploaded_file($_FILES['file_s']['tmp_name']))
				{
					$error = $lang->yourcode_import_file_upload_error;
				}
				else
				{
					$contents = @file_get_contents($_FILES['file_s']['tmp_name']);
					@unlink($_FILES['file_s']['tmp_name']);
					if(!trim($contents))
					{
						$error = $lang->yourcode_import_file_empty;
					}
				}
			}

			if(!$error)
			{
				$codes = _yc_import_check($contents);
				if(is_array($codes) && !empty($codes))
				{
					$total = count($codes);
					$onclick = <<<EOF
var all_checks = $$('input.checkbox_input'); if(this.checked) { checked = true; } for(x = 0; x < all_checks.length; x++) { all_checks[x].checked = checked; }
EOF;
					$onsubmit = <<<EOF
var all_checks = $$('input.checkbox_input'); for(x = 0; x < all_checks.length; x++) { if(all_checks[x].checked) { return true; } } alert('{$lang->yourcode_import_selection_error}'); return false;
EOF;
					$form = new Form(_yc_url(array("action" => 'import', "mode" => 'finish')), 'post', '', '', '', '', $onsubmit);
					$form_container = new FormContainer($lang->yourcode_import);

					$percentage = 45;
					$control_percentage = 10;
					if($total > 1)
					{
						$percentage = 22;
						$control_percentage = 5;
						$form_container->output_row_header($lang->yourcode_title, array("style" => "width: {$percentage}%;"));
						$form_container->output_row_header($lang->yourcode_description, array("style" => "width: {$percentage}%;"));
						$form_container->output_row_header($form->generate_check_box("allbox", '', '', array("onclick" => $onclick)), array("style" => "width: {$control_percentage}%;"));
					}

					$form_container->output_row_header($lang->yourcode_title, array("style" => "width: {$percentage}%;"));
					$form_container->output_row_header($lang->yourcode_description, array("style" => "width: {$percentage}%;"));
					$form_container->output_row_header($form->generate_check_box("allbox", '', '', array("onclick" => $onclick)), array("style" => "width: {$control_percentage}%;"));

					$row = 1;
					// if so display them
					foreach($codes as $id => $code)
					{
						$form_container->output_cell($code->get('title'));
						$form_container->output_cell($code->get('description'));
						$form_container->output_cell($form->generate_check_box("export_ids[{$id}]"));

						if($row == 2 || $total == 1)
						{
							$form_container->construct_row();
							$row = 0;
						}
						++$row;
					}

					// if the file contains more than one YourCode
					if($total > 1)
					{
						// if we were on an odd row
						if($row == 2)
						{
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
					$form_container->output_cell($form->generate_hidden_field('my_post_key', $mybb->post_code));
					$form_container->construct_row();
					$form_container->end();
					$buttons = array($form->generate_submit_button($lang->yourcode_import, array('name' => 'import')));
					$form->output_submit_wrapper($buttons);
					$form->end();
				}
				else
				{
					flash_message($lang->yourcode_import_file_upload_error, 'error');
					admin_redirect(_yc_url(array("action" => 'import')));
				}
			}
			else
			{
				flash_message($lang->yourcode_import_file_upload_error, 'error');
				admin_redirect(_yc_url(array("action" => 'import')));
			}
		}
	}
	$page->output_footer();
}

/*
 * yourcode_test_regex()
 *
 * @param - $regex - (string)
 * @param - $replacement - (string)
 * @param - $test_value - (string)
 * @param - $options - (array) regex modifiers
 */
function yourcode_test_regex($regex, $replacement, $test_value, $options)
{
	global $lang;

	$modifiers = '';

	// these modifiers are off by default
	foreach(array("s" => 'single_line', "m" => 'multi_line', "e" => 'eval') as $modifier => $property)
	{
		if($options[$property])
		{
			$modifiers .= $modifier;
		}
	}

	// case INsensitive is default
	if(!$options['case_sensitive'])
	{
		$modifiers .= 'i';
	}

	// build the regex
	$regex = "#" . str_replace("\x0", "", $regex) . "#{$modifiers}";

	// nestable?
	if(!$options['nestable'])
	{
		// if not just do it one time
		$output = @preg_replace($regex, $replacement, $test_value);
	}
	else
	{
		// if so do it till you feel something break
		$output = $test_value;
		while(@preg_match($regex, $output))
		{
			$output = @preg_replace($regex, $replacement, $output);
		}
	}

	// AJAX only uses actual, but we need both for those who don't use JS (like who? idk :s )
	return array("actual" => $output, "html" => htmlspecialchars_uni($output));
}

/*
 * yourcode_admin_action(&$action)
 *
 * @param - &$action is an array containing the list of selectable items on the config tab
 */
$plugins->add_hook('admin_config_action_handler', 'yourcode_admin_action');
function yourcode_admin_action(&$action)
{
	$action['yourcode'] = array('active' => 'yourcode');
}

/*
 * yourcode_admin_menu()
 *
 * Add an entry to the ACP Config page menu
 *
 * @param - &$sub_menu is the menu array we will add a member to
 */
$plugins->add_hook('admin_config_menu', 'yourcode_admin_menu');
function yourcode_admin_menu(&$sub_menu)
{
	global $lang;
	if(!$lang->yourcode)
	{
		$lang->load('yourcode');
	}

	// look for the default MyCode menu item
	foreach($sub_menu as $key => $val_array)
	{
		if($val_array['id'] == 'mycode')
		{
			$this_key = $key;
			break;
		}
	}

	$yourcode_menuitem = array
		(
			'id' 		=> 'yourcode',
			'title' 	=> $lang->yourcode,
			'link' 		=> YOURCODE_URL
		);

	// if we found one (and we will)
	if($this_key)
	{
		// overwrite it with ours muahahaha!
		$sub_menu[$this_key] = $yourcode_menuitem;
	}
	else
	{
		// otherwise (in some alternate reality where people have deleted the MyCode module from the admin/modules/config directory or otherwise disabled it) add our item to the end of the menu
		end($sub_menu);
		$key = (key($sub_menu)) + 10;
		$sub_menu[$key] = $yourcode_menuitem;
	}
}

/*
 * yourcode_admin_permissions()
 *
 * Add an entry to admin permissions list
 *
 * @param - &$admin_permissions is the array of permission types we are adding an element to
 */
$plugins->add_hook('admin_config_permissions', 'yourcode_admin_permissions');
function yourcode_admin_permissions(&$admin_permissions)
{
	global $lang;

	if(!$lang->yourcode)
	{
		$lang->load('yourcode');
	}
	$admin_permissions['yourcode'] = $lang->yourcode_admin_permissions_desc;
}

/*
 * yourcode_output_tabs()
 *
 * Output ACP tabs for our pages
 *
 * @param - $current is the tab currently being viewed
 * @param - $extra - (string) any additional GET info
 */
function yourcode_output_tabs($current, $extra='')
{
	global $page, $lang, $mybb;

	// set up tabs
	$sub_tabs['yourcode_main'] = array
	(
		'title'					=> $lang->yourcode_admin_view,
		'link'					=> YOURCODE_URL . $extra,
		'description'		=> $lang->yourcode_admin_view_desc
	);
	$sub_tabs['yourcode_edit'] = array
	(
		'title'					=> $lang->yourcode_admin_edit,
		'link'					=> YOURCODE_URL . '&amp;action=edit' . $extra,
		'description'		=> $lang->yourcode_admin_edit_desc
	);
	$sub_tabs['yourcode_tools'] = array
	(
		'title'					=> $lang->yourcode_admin_tools,
		'link'					=> YOURCODE_URL . '&amp;action=tools' . $extra,
		'description'		=> $lang->yourcode_admin_tools_desc
	);
	if($current == 'yourcode_import')
	{
		$sub_tabs['yourcode_import'] = array
		(
			'title'					=> $lang->yourcode_admin_import,
			'link'					=> YOURCODE_URL . '&amp;action=import' . $extra,
			'description'		=> $lang->yourcode_admin_import_desc
		);
	}
	$page->output_nav_tabs($sub_tabs, $current);
}

?>
