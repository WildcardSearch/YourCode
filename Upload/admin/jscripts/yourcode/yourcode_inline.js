/**
 * inline controls
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
 * @var object the inline controls module
 */
var YourCode = (function(yc) {
	/**
	 * @var int the currently checked
	 */
	var checkCount = 0,

	/**
	 * @var object the default language
	 */
	lang = {
		go: 'Go',
		noSelection: 'You did not select anything.',
	};

	/**
	 * initiate the selected count and observe inputs
	 *
	 * @return void
	 */
	function init() {
		initialCount();
		$('#yc_select_all').click(selectAll);
		$('.yc_check').click(keepCount);
		$('#yc_inline_clear').click(clearAll);
		$('#yc_inline_submit').click(submitCheck);
	}

	/**
	 * allow custom language overrides
	 *
	 * @param  object the custom language
	 * @return void
	 */
	function setup(language) {
		$.extend(lang, language || {});
	}

	/**
	 * squeal if admin is submitting inline with nothing checked
	 *
	 * @param  object the event
	 * @return void
	 */
	function submitCheck(e) {
		if (!checkCount) {
			e.preventDefault();
			$.jGrowl(lang.noSelection);
		}
	}

	/**
	 * sync all check boxes on this page with the master
	 *
	 * @param  object the event
	 * @return void
	 */
	function selectAll(e) {
		var onOff = false;

		if($(this).prop("checked")) {
			onOff = true;
		}
		setAllChecks(onOff);
	}

	/**
	 * set all check boxes on this page on/off
	 *
	 * @param  bool true for checked, false for unchecked
	 * @return void
	 */
	function setAllChecks(onOff) {
		if (onOff !== true) {
			onOff = false;
		}
		checkCount = 0;
		$('#yc_select_all').prop("checked", onOff);
		$('.yc_check').each(function(k, check) {
			$(check).prop("checked", onOff);
			if (onOff) {
				++checkCount;
			}
		});
		updateCheckCount();
	}

	/**
	 * adjust checked count on-the-fly
	 *
	 * @param  object the event
	 * @return void
	 */
	function keepCount(e) {
		if(this.checked) {
			++checkCount;
		} else {
			--checkCount;
		}
		updateCheckCount();
	}

	/**
	 * update the go button text to reflect the currently checked count
	 *
	 * @return void
	 */
	function updateCheckCount() {
		$('#yc_inline_submit').val(lang.go + ' (' + checkCount + ')');
	}

	/**
	 * clear all check boxes when the clear button is clicked
	 *
	 * @param  object the event
	 * @return void
	 */
	function clearAll(e) {
		setAllChecks();
	}

	/**
	 * count the initially checked boxes
	 *
	 * @return void
	 */
	function initialCount() {
		checkCount = 0;
		$('.yc_check').each(function(k, check) {
			if ($(check).prop("checked")) {
				++checkCount;
			}
		});
		updateCheckCount();
	}

	$(init);

	// the public method
	yc.inline = {
		setup: setup,
	};

	return yc;
})(YourCode || {});
