<?php
/**
 * ACP language pack (English)
 *
 * @category  MyBB Plugins
 * @package   YourCode
 * @author    Mark Vincent <admin@rantcentralforums.com>
 * @copyright 2012-2014 Mark Vincent
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link      https://github.com/WildcardSearch/YourCode
 * @since     1.0
 */

$l['yourcode'] = 'YourCode';
$l['yourcode_plugin_description'] = "Forget MyCode. Take control of YourCode's more powerful way to manage custom BB codes and even the internally cached MyCodes.";

// general
$l['yourcode_yes'] = 'Yes';
$l['yourcode_no'] = 'No';
$l['yourcode_cancel'] = 'Cancel';

// columns and labels
$l['yourcode_activate'] = 'Activate';
$l['yourcode_add'] = 'Add';
$l['yourcode_backup'] = 'Backup';
$l['yourcode_controls'] = 'Controls';
$l['yourcode_deactivate'] = 'Deactivate';
$l['yourcode_delete'] = 'Delete';
$l['yourcode_description'] = 'Description';
$l['yourcode_edit'] = 'Edit';
$l['yourcode_export'] = 'Export';
$l['yourcode_import'] = 'Import';
$l['yourcode_inactive'] = 'Inactive';
$l['yourcode_options'] = 'Options';
$l['yourcode_quick_links'] = 'Quick Links';
$l['yourcode_regex'] = 'Regex';
$l['yourcode_restore'] = 'Restore';
$l['yourcode_save'] = 'Save';
$l['yourcode_title'] = 'Title';
$l['yourcode_type'] = 'Type';
$l['yourcode_update'] = 'Update';
$l['yourcode_upgrade'] = 'Upgrade';
$l['yourcode_finish'] = 'Finish';
$l['yourcode_proceed'] = 'Proceed';
$l['yourcode_restore_select_file'] = $l['yourcode_import_select_file'] = 'Select a local file:';

// actions
$l['yourcode_added'] = 'added';
$l['yourcode_exported'] = 'exported';
$l['yourcode_updated'] = 'updated';
$l['yourcode_created'] = 'created';
$l['yourcode_activated'] = 'activated';
$l['yourcode_deleted'] = 'deleted';
$l['yourcode_deactivated'] = 'deactivated';

// acp
$l['yourcode_admin_permissions_desc'] = 'Can use YourCode?';
$l['yourcode_admin_view'] = 'Manage YourCode';
$l['yourcode_admin_view_desc'] = 'view and manage YourCode';
$l['yourcode_admin_edit'] = 'Add YourCode';
$l['yourcode_admin_edit_desc'] = 'add and edit YourCode';
$l['yourcode_admin_module'] = "Manage Modules";
$l['yourcode_admin_module_desc'] = "activate and deactivate YourCode Modules";
$l['yourcode_admin_tools'] = 'Tools';
$l['yourcode_admin_tools_desc'] = 'import, export and further manage YourCode';
$l['yourcode_admin_import'] = 'Import Manager';
$l['yourcode_admin_import_desc'] = 'confirm your import choices';

// messages
$l['yourcode_no_code'] = 'no YourCode';

// sandbox
$l['yourcode_sandbox'] = 'Sandbox';
$l['yourcode_sandbox_desc'] = 'You can use this area to test the regular expression and replacement above before saving your changes.';
$l['yourcode_test_value'] = 'Test Value';
$l['yourcode_test_value_desc'] = 'Enter in text to be tested in the box below.';
$l['yourcode_test'] = 'Test';
$l['yourcode_result_html'] = 'HTML Result';
$l['yourcode_result_html_desc'] = 'The text area below shows the resulting HTML using the regular expression on the test value.';
$l['yourcode_result_actual'] = 'Actual Result';
$l['yourcode_result_actual_desc'] = 'The area below shows the actual result when the HTML is rendered.';
$l['yourcode_sandbox_test_no_save_warning'] = 'Test results below. Warning: Your changes (if any) have not been saved';

// edit
$l['yourcode_regular_expression'] = 'Regular Expression';
$l['yourcode_regular_expression_desc'] = 'Enter a regular expression that will search for a specific combination of characters. You must make sure the regular expression is valid and safe &mdash; no validation is performed.';
$l['yourcode_example'] = 'Example';
$l['yourcode_replacement'] = 'Replacement';
$l['yourcode_alternate_replacement'] = 'Alternate Replacement';
$l['yourcode_alternate_replacement_desc'] = 'This replacement is used for those who do not have permission to view this YourCode (will have no effect unless view permissions are set)';
$l['yourcode_replacement_desc'] = 'Enter a replacement for the regular expression.';
$l['yourcode_parse_order'] = 'Parse Order';
$l['yourcode_parse_order_desc'] = 'YourCodes will be parsed in ascending order relative to other YourCodes.';
$l['yourcode_nestable'] = 'Nestable';
$l['yourcode_nestable_title'] = 'Nestable?';
$l['yourcode_nestable_desc'] = 'In this mode the tag can contain instances of itself.';
$l['yourcode_case_sensitive'] = 'Case-sensitive?';
$l['yourcode_case_sensitive_desc'] = 'In this mode tag case must match exactly.';
$l['yourcode_single_line'] = 'Single line mode?';
$l['yourcode_single_line_desc'] = 'In this mode, the dot matches newlines.';
$l['yourcode_multi_line'] = 'Multi-line mode?';
$l['yourcode_multi_line_desc'] = 'In this mode, the caret (^) and dollar ($) match before and after newlines.';
$l['yourcode_eval'] = "eval()'d?";
$l['yourcode_eval_desc'] = "In this mode the replacement is eval()'d after it has been matched. (Set this option to 'No' unless you know what you are doing.)";
$l['yourcode_callback'] = "Callback?";
$l['yourcode_callback_desc'] = "In this mode the match is sent to the named PHP function";
$l['yourcode_active'] = 'Active';
$l['yourcode_active_desc'] = 'Should this YourCode be effective now?';
$l['yourcode_allowed_user_groups_use'] = 'Who can use this YourCode?';
$l['yourcode_allowed_user_groups_use_desc'] = "When users that aren't allowed attempt to use the YourCode it is blanked from their post before it is inserted.";
$l['yourcode_allowed_user_groups_view'] = 'Who can view this YourCode?';
$l['yourcode_allowed_user_groups_view_desc'] = "When users that aren't allowed view posts in which the YourCode is used an alternate replacement is used.";
$l['yourcode_all_user_groups'] = 'All User Groups';
$l['yourcode_save_and_continue'] = 'Save and Continue Editing';
$l['yourcode_save_and_return'] = 'Save and Return to Listing';

// tabs
$l['yourcode_tab_general'] = 'General';
$l['yourcode_tab_permissions'] = 'Permissions';
$l['yourcode_tab_advanced'] = 'Advanced';
$l['yourcode_tab_sandbox'] = 'Sandbox';

// messages
$l['yourcode_message_success'] = '{1} {2} successfully';
$l['yourcode_message_fail'] = "{1} couldn't be {2} successfully";
$l['yourcode_message_fail_because'] = "{1} couldn't be {2} successfully because it was already {2}";
$l['yourcode_message_active_status'] = 'YourCode is currently {1}, click to {2}';
$l['yourcode_module_message_active_status'] = 'Module is currently {1}, click to {2}';

// import
$l['yourcode_import_select_file_desc'] = 'Use this form to import YourCode that was exported from this plugin.';

$l['yourcode_import_no_file'] = 'No file to import.';
$l['yourcode_import_file_error'] = 'Bad or empty file.';
$l['yourcode_import_selection_error'] = 'You did not select any YourCode from the list.';
$l['yourcode_import_file_upload_error'] = 'There was a problem uploading the file.';
$l['yourcode_import_file_empty'] = 'The file you uploaded is empty or currupt.';
$l['yourcode_import_save_success'] = 'Successfully imported {1} YourCode.';
$l['yourcode_xml_count'] = '{1} total YourCode in XML';

$l['yourcode_delete_warning_clear'] = "Proceeding will result in all YourCode being deleted.\\nIt is recommended to backup first to avoid losing your work.\\nProceed?";
$l['yourcode_delete_warning_simple'] = 'Do you want to permanently delete this YourCode?';
$l['yourcode_delete_warning_default'] = "This action will restore all the YourCodes from their respective derivatives in the state which they were in previous to installation and will result in losing any customizations you have made.\\n\\nIt is recommended to backup your work. Proceed?";
$l['yourcode_delete_warning_restore'] = "You are about to replace all YourCode with the contents of this XML file losing any customizations you have made.\\nIt is recommended to backup your work.\\nProceed?";

// restore (from file)
$l['yourcode_restore_select_file_desc'] = 'Use this form to import a mass backup of YourCode. All of the existing codes will be removed to avoid redundancy beforehand. It is advisable to create a secure backup first.';
$l['yourcode_restore_fail'] = "Couldn't restore YourCode from backup.";
$l['yourcode_restore_success'] = 'YourCode restored from backup.';

// restore (to defaults)
$l['yourcode_default'] = 'Restore all defaults';
$l['yourcode_default_fin'] = ' and Custom MyCodes to installation point';
$l['yourcode_default_success'] = 'All YourCode restored from defaults.';
$l['yourcode_default_fail'] = "Couldn't restore YourCode from defaults.";

// backup
$l['yourcode_backup_fin'] = ' all of your existing YourCode into one file';

// clear
$l['yourcode_clear'] = 'Clear';
$l['yourcode_clear_fin'] = ' all of the YourCode and start over';
$l['yourcode_clear_success'] = 'All YourCode deleted.';
$l['yourcode_clear_fail'] = $l['yourcode_restore_fail_clear'] = "Couldn't clear existing YourCode.";

// modules
$l['yourcode_modules'] = 'YourCode module(s)';
$l['yourcode_inline_title'] = 'Inline Edits';
$l['yourcode_inline_selection_error'] = 'You did not select anything.';
$l['yourcode_inline_success'] = '{1} {2} successfully {3}';
$l['yourcode_invalid_module'] = 'Invalid module';
$l['yourcode_no_modules'] = 'no modules';

$l['yourcode_folders_requirement_warning'] = 'One or more folders are not writable. These folders need to be writable during installation and upgrades for themeable items to be upgraded on a per-theme basis.<br /><strong>Folder(s):</strong><br />';
$l['yourcode_subfolders_unwritable'] = 'One or more subfolders in <span style="font-family: Courier New; font-weight: bolder; font-size: small; color: black;">{1}</span>';
$l['yourcode_cannot_be_installed'] = 'YourCode cannot be installed!';

?>
