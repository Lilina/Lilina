var admin = {
	init: function () {
		/** Hide some stuff */
		$("#changer, #changer_id").hide();
		$("form#add_form").append('<p class="loading">Adding feed...</p>');
		$("form#change_form").append('<p class="loading">Changing feed...</p>');
		
		/** Set up events */
		$(".change_link").click(function () {
			$("#change_url").val( $(this).parent().siblings(".url-col").text() );
			$("#change_name").val( $(this).parent().siblings(".name-col").text() );
			$("#change_id").val( $(this).parents("tr:first").attr("id").split("-")[1] );
			$("#changer").slideDown();
			return false;
		});
		$("#add_form").submit(function () {
			admin.disable_button(this);
			
			feeds.add($("#add_url").val(), $("#add_name").val(), false, feeds.reload_table);
			
			admin.enable_button(this);
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
	disable_button: function (selector) {
		$(selector)
			.children("input.submit")
				.attr('disabled', 'disabled')
			.end()
			.children(".loading")
				.show();
	},
	enable_button: function (selector) {
		$(selector)
			.children("input.submit")
				.attr('disabled', '')
			.end()
			.children(".loading")
				.hide();
	}
};

var feeds = {
	count: 0,

	add: function (url, name, errors_only, callback, callback2) {
		if(errors_only == undefined)
			errors_only = false;

		if( !url ) {
			humanMsg.displayMsg('No feed URL supplied', 'error');
			return false;
		}
		$.post("admin-ajax.php", {
			action: "add",
			type: "json",
			name: name,
			url: url
		}, function (data) {
			if(!data.errors || data.errors.length == 0) {
				// Clear the values
				$("#add_url, #add_name").val('');

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
			humanMsg.displayMsg('No feed URL supplied', 'error');
			return false;
		}
		else if( !$("#change_id").val() ) {
			humanMsg.displayMsg('No feed ID supplied', 'error');
			return false;
		}
		$.post("admin-ajax.php", {
			action: "change",
			type: "json",
			feed_id: $("#change_id").val(),
			name: $("#change_name").val(),
			url: $("#change_url").val()
		}, function (data) {
			if(!data.errors || data.errors.length == 0) {
				// Clear the values
				$("#change_url, #change_name, #change_id").val('');

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