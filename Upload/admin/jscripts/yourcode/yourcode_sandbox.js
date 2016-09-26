/**
 * sand box
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
 * @var Object the YourCode sand box class
 */
var YourCode = (function(yc) {
	/**
	 * constructor
	 *
	 * @param  string the XMLHTTP url
	 * @param  object the form elements
	 * @return void
	 */
	function Sandbox(url, elements) {
		var allGood = true;

		$.each(['button', 'regexTextbox', 'replacementTextbox', 'testTextbox', 'htmlTextbox', 'actualDiv', 'nestableInput', 'caseSensitiveInput', 'singleLineInput', 'multiLineInput', 'evalInput'], function(k, key) {
			if (elements && elements[key]) {
				this[key] = elements[key];
			} else {
				allGood = false;
				return false;
			}
		}.bind(this));

		if (!allGood) {
			return;
		}

		this.url = url;
		this.button.click($.proxy(this.update, this));
	}

	/**
	 * perform the test using current values
	 *
	 * @param  object the event
	 * @return void
	 */
	function update(e) {
		var fn = encodeURIComponent;
		e.preventDefault();

		// build the data
		postData = "regex=" + fn(this.regexTextbox.val()) +
			"&replacement=" + fn(this.replacementTextbox.val()) +
			"&test_value=" + fn(this.testTextbox.val()) +
			"&my_post_key=" + fn(my_post_key) +
			(this.nestableInput.prop("checked") ? '&nestable=1' : '') +
			(this.caseSensitiveInput.prop("checked") ? '&case_sensitive=1' : '') +
			(this.singleLineInput.prop("checked") ? '&single_line=1' : '') +
			(this.multiLineInput.prop("checked") ? '&multi_line=1' : '') +
			(this.evalInput.prop("checked") ? '&eval=1' : '');

		$.jGrowl("updating...");
		
		$.ajax({
			type: 'post',
			url: this.url,
			data: postData,
			complete: $.proxy(this.onComplete, this),
		});
	}

	/**
	 * handle the response
	 *
	 * @param  object the server response
	 * @return void
	 */
	function onComplete (transport) {
		if (transport.responseText.match(/<error>(.*)<\/error>/)) {
			message = transport.responseText.match(/<error>(.*)<\/error>/);

			if (!message[1]) {
				message[1] = "An unknown error occurred.";
			}
			$.jGrowl('There was an error fetching the test results.\n\n' + message[1]);
		} else if(transport.responseText) {
			this.actualDiv.html(transport.responseText);
			this.htmlTextbox.val(transport.responseText);
		}
		return true;
	}

	Sandbox.prototype = {
		url: null,
		button: null,
		regexTextbox: null,
		replacementTextbox: null,
		testTextbox: null,
		htmlTextbox: null,
		actualDiv: null,
		nestableInput: null,
		caseSensitiveInput: null,
		singleLineInput: null,
		multiLineInput: null,
		evalInput: null,
		update: update,
		onComplete: onComplete,
	}

	yc.Sandbox = Sandbox;

	return yc;
})(YourCode || {});
