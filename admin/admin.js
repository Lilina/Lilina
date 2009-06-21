/**
 * Contains administrative functions for general pages
 */
var admin = {
	/**
	 * Initialise the page
	 */
	init: function () {
		// Feeds page only
		if ( $('body#admin-feeds').length != 0 ) {
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

			$(".nojs").remove();
			new FeedList();
		}
		else if ( $('body#admin-subscribe').length != 0) {
			$("#add_form").submit(function () {
				//feeds.add();
				return false;
			});
		}

		/** Make it look pretty */
		$("#alert").effect("highlight", { 
			color: "red" 
		}, 3000);

		$("#navigation li.has-submenu").hover(function() {
			$(this).addClass('hovering');
		}, function() {
			$(this).removeClass('hovering');
		});

		$("body").append("<div id='loading'></div>");
	},
	dialog: {
		alert: function(msg) {
			if (typeof(msg) == "string") {
				msg = {
					message: msg
				}
			}
			msg.title = (msg.title != undefined) ? msg.title: "Something Went Wrong!";
			msg.isError = (msg.isError != undefined) ? msg.isError: true;
			if (msg.isError) {
				var dialog = $('<div id="dialog" title="' + msg.title + '"><p>Error message:</p><p class="error-message">' + msg.message + '</p><p>If you think you shouldn\'t have received this error then <a href="http://code.google.com/p/lilina/issues">report a bug</a> quoting that message and how it happened.</p></div>')
			} else {
				var dialog = $("<div id='dialog' title='" + msg.title + "'><p>" + msg.message + "</p></div>")
			}
			$("body").append(dialog);
			dialog.dialog({
				draggable: false,
				resizable: false,
				modal: true,
				dialogClass: "error",
				close: function() {
					dialog.remove()
				}
			})
		},
		confirm: function (msg, callback, owner, additional_classes) {
			if (typeof(msg) == "string") {
				msg = {
					message: msg
				}
			}
			msg.title = (msg.title != undefined) ? msg.title: "Are You Sure?";
			msg.ok = (msg.ok != undefined) ? msg.ok: "OK";
			msg.cancel = (msg.cancel != undefined) ? msg.cancel: "Cancel";
			var dialog = $("<div title='" + msg.title + "'><p>" + msg.message + "</p><p class='buttons'><a href='#OK' class='doit'>" + msg.ok + "</a> <span class='cancel'>or <a href='#Cancel'>" + msg.cancel + "</a></span></p></div>");
			$("body").append(dialog);
			var dialogClasses = (additional_classes == undefined) ? "confirmation": "confirmation " + additional_classes;
			dialog.dialog({
				draggable: false,
				resizable: false,
				modal: true,
				dialogClass: dialogClasses,
				close: function() {
					dialog.remove()
				}
			});
			$("a.doit", dialog).click(function(e) {
				dialog.dialog("close");
				if (owner) {
					callback.call(owner)
				} else {
					callback()
				}
				return false
			});
			$("span.cancel a", dialog).click(function(e) {
				dialog.dialog("close");
				return false
			})
		},
		custom: function (msg) {
			if (typeof(msg) == "string") {
				msg = {
					message: msg
				}
			}
			msg.title = (msg.title != undefined) ? msg.title: "Dialog";
			var dialog = $("<div id='dialog' title='" + msg.title + "'>" + msg.message + "</p></div>");
			dialog.dialog({
				position: "center",
				width: 500,
				draggable: false,
				resizable: false,
				modal: true,
				dialogClass: "error",
				close: function() {
					dialog.remove()
				}
			})
		},
		update: function() {
			// This will do something when needed
		}
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

			$("#loading").show();

			data.method = action;
			$.ajax({
				data: data,
				dataType: return_type,
				complete: function() {
					$("#loading").hide();
				},
				error: function(request, textStatus, errorThrown) {
					var msg = {};
					try {
						res = JSON.parse(request.responseText);
						var code = parseInt(res.code);

						// 0-9 is a problem with the request
						if (10 <= code) {
							msg.message = res.msg;
							msg.isError = false;
							msg.title = "Whoops!"
						} else {
							// 900-999 means something needs updating
							if (900 <= code && code < 1000) {
								msg = false;
								admin.dialog.update(res.msg)
							} else {
								msg.message = res.msg + " (error-code: " + res.code + ")"
							}
						}
					} catch(e) {
						msg.message = "Failed to parse response: " + req.responseText
					}

					if (msg) {
						admin.dialog.alert(msg);
					}
				},
				success: callback,
				type: type,
				url: "admin-ajax.php"
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
		admin.ajax.post('feeds.add', {
				name: $("#add_name").val(),
				url: $("#add_url").val()
			}, function (data) {
				humanMsg.displayMsg(data.msg);
				// Clear the values
				$("#add_url, #add_name").val('');

				feeds.reload_table();
				return;
			});
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
		admin.ajax.post('feeds.change', {
				feed_id: $("#change_id").val(),
				name: $("#change_name").val(),
				url: $("#change_url").val()
			}, function (data) {
				humanMsg.displayMsg(data.msg);
				// Clear the values
				$("#change_url, #change_name, #change_id").val('');

				feeds.reload_table();
				return;
		});
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

/* List object, for use with the feeds list table (#feeds_list) */
FeedList = function() {
	this.feeds = [];
	this.load()
};
FeedList.prototype.feeds = [];
FeedList.prototype.load = function() {
	var t = this;
	admin.ajax.get('feeds.get', {}, function (data) {
		t.loadCallback(data);
	});
};
FeedList.prototype.loadCallback = function (data) {
	if(false == data[0]) {
		$("#nofeeds").show();
		return false;
	}
	var n = 0;
	var t = this;
	jQuery.each(data, function () {
		t.feeds.push(new FeedRow(this, n++));
	});
};

/* Row object (single feed), for use with FeedList */
FeedRow = function(data, id) {
	this.data = data;
	this.id = id;
	this.render()
};
FeedRow.prototype.data = null;
FeedRow.prototype.id = null;
FeedRow.prototype.row = null;
FeedRow.prototype.render = function() {
	var exists = true;
	if(!this.row) {
		exists = false;
		this.row = $('<tr><td class="name-col" /><td class="url-col" /><td class="remove-col" /></tr>');
	}
	$(this.row).attr('id', 'feed-' + this.id);
	$("td", this.row).html("<span />");
	$(".name-col span", this.row).text(this.data.name);
	$(".url-col span", this.row).text(this.data.feed);
	$(".remove-col span", this.row).text('Delete');
	this.bindEvents();
	if(!exists)
		$('#feeds_list tbody').append(this.row)
};
FeedRow.prototype.bindEvents = function() {
	var url = $(".url-col", this.row);
	var name = $(".name-col", this.row);
	var delete_span = $(".remove-col span", this.row);
	url.unbind("click dblclick");
	name.unbind("click dblclick");
	delete_span.unbind("click");

	// We define this so that "this" refers to the FeedRow object, rather than
	// the element that the event was triggered for
	var me = this;
	url.dblclick(function() {
		return me.edit("feed")
	});
	name.dblclick(function() {
		return me.edit("name")
	});
	delete_span.click(function() {
		return me.remove()
	});
};
FeedRow.prototype.edit = function(type) {
	switch (type) {
		case "feed":
			var value = $(".url-col span", this.row);
			var input_field = $('<input type="text" />').val(this.data.feed);
			break;
		case "name":
			var value = $(".name-col span", this.row);
			var input_field = $('<input type="text" />').val(this.data.name);
			break;
	}
	value.replaceWith($("<span />").append(input_field));
	input_field.focus();
	input_field.select();
	var me = this;
	input_field.bind("blur keypress", function(e) {
		if (e.type == "keypress" && e.which == 13) {
			me.save();
		}
		if (e.type == "blur") {
			me.save();
		}
	});
	return false
};
FeedRow.prototype.remove = function () {
	admin.ajax.post("feeds.remove", {feed_id: this.id}, this.removeComplete);
};
FeedRow.prototype.removeComplete = function (data) {
	this.row.remove();
	humanMsg.displayMsg(data.msg);
};
FeedRow.prototype.save = function () {
	var new_data = {feed_id: this.id};

	if($(".url-col input", this.row).length)
		new_data.url = $(".url-col input", this.row).val();
	else
		new_data.url = this.data.feed;

	if($(".name-col input", this.row).length)
		new_data.name = $(".name-col input", this.row).val();

	var me = this;
	admin.ajax.post("feeds.change", new_data, function (data) {
			me.saveComplete(data);
		});
};
FeedRow.prototype.saveComplete = function(data) {
	this.data.feed = data.url;
	if(data.name.length != 0)
		this.data.name = data.name;
	this.render();
	humanMsg.displayMsg(data.msg);
	this.bindEvents();
};

$(document).ready(function () {
	admin.init();
	//console.log(new FeedRow({url: 'http://example.com', name: 'yo', id: 682}));
});

//Alias for feeds.add
function add_feed(url, title) {
	return feeds.add(url, title);
}