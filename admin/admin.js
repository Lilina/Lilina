/**
 * Contains administrative functions for general pages
 */
var admin = {
	/**
	 * Initialise the page
	 */
	init: function () {
		/** Hide some stuff */
		$("#changer, #changer_id").hide();
		/** Set up events */
		$(".change_link").live('click', function() {
			$("#change_url").val(
				$(this).parent().siblings(".url-col").text()
			);
			$("#change_name").val(
				$(this).parent().siblings(".name-col").text()
			);
			$("#change_id").val(
				$(this).parents("tr:first").attr("id").split("-")[1]
			);
			$("#changer").slideDown("normal").scrollTo('#changer', 400);
			return false;
		});

		$("#add_form").submit(function () {
			feeds.add();
			return false;
		});

		$("#change_form").submit(function () {
			feeds.change();
			return false;
		});

		/** Make it look pretty */
		$("#alert").effect("highlight", { 
			color: "red" 
		}, 3000);

		$("#navigation li.has-submenu").hover(function() {
			$(this).addClass('hovering');
		}, function() {
			$(this).removeClass('hovering');
		});
	},
	/**
	 * Disables submission buttons within a form passed as the selector parameter
	 *
	 * Disables submit buttons then shows a loading indicator
	 *
	 * @param selector CSS selector, or jQuery object
	 */
	disable_button: function (selector) {
		$(selector)
			.children("input.submit")
				.attr('disabled', 'disabled')
				.hide()
			.end()
			.children(".loading")
				.show();
	},
	/**
	 * Enables submission buttons within a form passed as the selector parameter
	 *
	 * Enables submit buttons then hides the loading indicator
	 *
	 * @param selector CSS selector, or jQuery object
	 */
	enable_button: function (selector) {
		$(selector)
			.children("input.submit")
				.removeAttr('disabled')
				.show()
			.end()
			.children(".loading")
				.hide();
	},
	/**
	 * Administration Ajax API
	 */
	ajax: {
		/**
		 * Send a remote request to the admin ajax API
		 *
		 * @param {String} action "action" parameter to send to admin-ajax.php
		 * @param {Array} data Extra data to send to admin-ajax.php
		 * @param callback Callback function to call on successful request
		 * @param {String} return_type A valid jQuery dataType as per http://docs.jquery.com/Ajax/jQuery.ajax#options
		 * @param {String} type A valid HTTP request type. Usually "POST" or "GET"
		 */
		request: function (action, data, callback, return_type, type) {
			if (!action) {
				return false;
			}
			data = data || {};
			callback = callback || false;
			return_type = return_type || 'json';
			type = type || 'GET';
			
			data.method = action;
			$.ajax({
				type: type,
				url: "admin-ajax.php",
				data: data,
				success: callback,
				dataType: return_type
			})
		},
		/**
		 * Convienience function for GET requests
		 * @see #request
		 */
		get: function (action, data, callback, return_type) {
			admin.ajax.request(action, data, callback, return_type, "GET");
		},
		/**
		 * Convienience function for POST requests
		 * @see #request
		 */
		post: function (action, data, callback, return_type) {
			admin.ajax.request(action, data, callback, return_type, "POST");
		},
	}
};

var feeds = {
	count: 0,

	add: function () {
		if( !$("#add_url").val() ) {
			humanMsg.displayMsg('No feed URL supplied', 'error');
			return false;
		}
		admin.disable_button("#add");
		admin.ajax.post('feeds.add', {name: $("#add_name").val(), url: $("#add_url").val()}, feeds.add_callback);
	},
	add_callback: function (data) {
		admin.enable_button("#add");
		if(!data.error) {
			// Clear the values
			$("#add_url, #add_name").val('');

			feeds.reload_table();
			return;
		}
	},
	change: function () {
		if( !$("#change_url").val() ) {
			humanMsg.displayMsg('No feed URL supplied', 'error');
			return false;
		}
		else if( !$("#change_id").val() ) {
			humanMsg.displayMsg('No feed ID supplied', 'error');
			return false;
		}
		admin.disable_button("#change");
		admin.ajax.post('change', {feed_id: $("#change_id").val(), name: $("#change_name").val(), url: $("#change_url").val()}, feeds.change_callback);
	},
	change_callback: function (data) {
		admin.enable_button("#change");
		if(!data.error) {
			// Clear the values
			$("#change_url, #change_name, #change_id").val('');

			humanMsg.displayMsg(data.msg);
			feeds.reload_table();
			return;
		}
		humanMsg.displayMsg(data.msg, 'error');
	},
	reload_table: function () {
		admin.ajax.get('feeds.list', {}, function (data) {
			$("#feeds_list tbody").html(data.table);
		});
	},
	begin_processing: function (items) {
		feeds.current_feeds = items;
		console.log(feeds);
		feeds.process();
	},
	process: function () {
		feed = feeds.current_feeds.shift();
		console.log('#' + feeds.count + ': ' + feed['title'] + ' - ' + feed['url']);
		add_feed(feed['url'], feed['title'], true, undefined, feeds.log);
		feeds.count++;
	},
};

$(document).ready(function () {
	admin.init();
});

//Alias for feeds.add
function add_feed(url, title) {
	return feeds.add(url, title);
}