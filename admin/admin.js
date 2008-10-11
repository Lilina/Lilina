var admin = {
	init: function () {
		/** Hide some stuff */
		$("#changer").hide();
		$("#changer_id").hide();
		
		/** Set up events */
		$(".change_link").click(function () {
			feed_url = $(this).parent().siblings(".url-col").text();
			feed_name = $(this).parent().siblings(".name-col").text();
			feed_id= $(this).parent().siblings(".id-col").text();
			$("#change_url").val(feed_url);
			$("#change_name").val(feed_name);
			$("#change_id").val(feed_id);
			$("#changer").slideDown();
			return false;
		});
		$("#add_form").submit(function () {
			feeds.add($("#add_url").val(), $("#add_name").val(), false, feeds.reload_table);
			return false;
		});
		$("#change_form").submit(function () {
			feeds.change();
			return false;
		});
		
		/** Add some content */
		$("#alert .tech_notice").append("<a href=\"javascript:void(0)\" class=\"note_link\">Show technical explanation</a>").children(".actual_notice").hide();
		
		/** Make it look pretty */
		$("#alert").effect("highlight", { 
			color: "red" 
		}, 3000);
		
		$("#navigation li.has-submenu").hover(function() {
			$(this).addClass('hovering');
		}, function() {
			$(this).removeClass('hovering');
		});
	}
};

var feeds = {
	count: 0,

	add: function (url, name, errors_only, callback, callback2) {
		if(errors_only == undefined)
			errors_only = false;

		if( !url ) {
			humanMsg.displayMsg('No feed URL supplied');
			return false;
		}
		$.post("admin-ajax.php", {
			action: "add",
			type: "json",
			name: name,
			url: url
		}, function (data) {
			console.log(data);
			if(!data.errors || data.errors.length == 0) {
				// Clear the values
				$("#add_url").val('');
				$("#add_name").val('');

				if(!errors_only) {
					jQuery.each(data.messages, function (index, message) {
						humanMsg.displayMsg(message['message']);
					});
				}
				if(callback != undefined)
					callback.call(null, data);
				return;
			}
			jQuery.each(data.errors, function(index, error) {
				humanMsg.displayMsg(error['message'], 'error', -1);
			});
		},
		"json"
		);
		if(callback2 != undefined)
			callback2.call(null, data);
	},
	change: function () {
		if( !$("#change_url").val() ) {
			humanMsg.displayMsg('No feed URL supplied');
			return false;
		}
		else if( !$("#change_id").val() ) {
			humanMsg.displayMsg('No feed ID supplied');
			return false;
		}
		$.post("admin-ajax.php", {
			action: "change",
			type: "json",
			feed_id: $("#change_id").val(),
			name: $("#change_name").val(),
			url: $("#change_url").val()
		}, function (data) {
			console.log(data);
			if(data.errors.length == 0) {
				// Clear the values
				$("#change_url").val('');
				$("#change_name").val('');
				$("#change_id").val('');

				jQuery.each(data.messages, function (index, message) {
					humanMsg.displayMsg(message['message']);
				});
				feeds.reload_table();
				return;
			}
			jQuery.each(data.errors, function(index, error) {
				humanMsg.displayMsg(error['message'], 'error');
			});
		},
		"json"
		);
	},
	reload_table: function () {
		$.get("admin-ajax.php", {action: "list", type: "raw"}, function (data) {
			$("#feeds_list tbody").html(data);
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