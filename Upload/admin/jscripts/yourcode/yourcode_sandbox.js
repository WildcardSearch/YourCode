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

		['button', 'regexTextbox', 'replacementTextbox', 'testTextbox', 'htmlTextbox', 'actualDiv', 'nestableInput', 'caseSensitiveInput', 'singleLineInput', 'multiLineInput', 'evalInput'].each(function(key) {
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
		this.button.observe('click', this.update.bindAsEventListener(this));
	}

	/**
	 * perform the test using current values
	 *
	 * @param  object the event
	 * @return void
	 */
	function update(e) {
		var fn = encodeURIComponent;
		Event.stop(e);

		// build the data
		postData = "regex=" + fn(this.regexTextbox.value) +
			"&replacement=" + fn(this.replacementTextbox.value) +
			"&test_value=" + fn(this.testTextbox.value) +
			"&my_post_key=" + fn(my_post_key) +
			(this.nestableInput.checked ? '&nestable=1' : '') +
			(this.caseSensitiveInput.checked ? '&case_sensitive=1' : '') +
			(this.singleLineInput.checked ? '&single_line=1' : '') +
			(this.multiLineInput.checked ? '&multi_line=1' : '') +
			(this.evalInput.checked ? '&eval=1' : '');

		this.spinner = new ActivityIndicator("body", {
			image: this.spinnerImage
		});

		new Ajax.Request(this.url, {
			method: 'post',
			postBody: postData,
			onComplete: this.onComplete.bind(this)
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
			alert('There was an error fetching the test results.\n\n' + message[1]);
		} else if(transport.responseText) {
			this.actualDiv.innerHTML = transport.responseText;
			this.htmlTextbox.value = transport.responseText;
		}

		this.spinner.destroy();
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
		spinnerImage: "../images/spinner_big.gif",
		update: update,
		onComplete: onComplete,
	}

	yc.Sandbox = Sandbox;

	return yc;
})(YourCode || {});
