var ItemUpdater = {
	feeds: [],
	current_id: 0,
	errors: 0,
	location: "",
	api: function (method, params, callback, error_callback, type) {
		params = params || {};
		callback = callback || false;
		error_callback = error_callback || false;
		var request_params = { 'method': method };

		$.extend(request_params, params);

		$.ajax({
			'cache': false,
			'data': request_params,
			'dataType': 'json',
			'error': error_callback,
			'success': callback,
			'type': (type) ? type : 'GET',
			'url': this.location
		});
	}
	init: function () {
		var me = this;
		this.api('update', {"action": "test", "format": "json"}, function (data) { me.test_success(); }, function(data) { me.test_failure(); });
	},
	test_success: function () {
		$('.js-hide').hide();
		$('#updatelist').show();
		this.process();
	},
	test_failure: function () {
		// This should never happen, which is why it's not translated.
		var message = $('<li>Some sort of connection error occurred. Please <a href="http://lilina.googlecode.com/">report this as a bug</a>.</li>');
		$('#updatelist').append(message);
	},
	process: function () {
		var me = this;
		if(this.current_id >= this.feeds.length) {
			$('#finished').show();
			return;
		}
		$('#loading').show();
		this.api('update', {"action": "single", "id": this.feeds[this.current_id], "format": "json"},
			function (data) { me.process_success(data); },
			function (xhr, status, error) { me.process_fail(xhr, status, error); }
		);
		this.current_id++;
	},
	process_success: function (data) {
		$('#loading').hide();
		$.each(data.msgs, function() {
			var message = $('<li></li>').text(String(this.msg));
			if(this.updated > 0)
				message.addClass('updated');
			$('#updatelist').append(message);
		});
		this.process();
	},
	process_fail: function (xhr, status, error) {
		$('#loading').hide();
		var message;
		try {
			var res = JSON.parse(xhr.responseText);
			message = $('<li class="error"></li>').html(res.msg);
		} catch(e) {
			message = $('<li class="error">Failed to parse response: ' + xhr.responseText + '</li>');
		}
		$('#updatelist').append(message);
		this.errors++;
		this.process();
	}
};

$(document).ready(function () {
	ItemUpdater.init();
});