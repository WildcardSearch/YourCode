/**
 * ACP edit functions
 *
 * @category  MyBB Plugins
 * @package   YourCode
 * @author    Mark Vincent <admin@rantcentralforums.com>
 * @copyright 2012-2014 Mark Vincent
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link      https://github.com/WildcardSearch/YourCode
 * @since     2.1
 */

!function($) {
	"use strict";

	/**
	 * initialize
	 *
	 * @return void
	 */
	function init() {
		$("#callback").change(callbackChange);
		$("#yourcodeForm").validate({
			rules: {
				title: "required",
				regex: "required",
			},
			errorPlacement: function(error, element) {
				error.css({
					color: "red",
					paddingLeft: "5px",
				});
				error.insertAfter(element);
			},
		});
	}

	/**
	 * link the nestable and callback settings
	 *
	 * @return void
	 */
	function callbackChange() {
		if (this.checked) {
			$("#nestable").prop("checked", "checked");
		}
	}

	$(init);
}(jQuery);
