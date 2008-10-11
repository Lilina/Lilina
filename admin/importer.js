var importer = {
	item_count: 0,
	feeds: [],
	end_callback: null,

	init: function (feeds) {
		importer.feeds = feeds;
		importer.iterate();
	},
	iterate: function () {
		var feed = importer.feeds.shift();
		if(feed == undefined) {
			if(importer.end_callback != null)
				importer.end_callback();
			return;
		}

		$.post("admin-ajax.php", {
			action: "add",
			type: "json",
			name: feed['title'],
			url: feed['url']
		}, function (data) {
			importer.log(data);
			importer.iterate();
		},
		"json");
	},
	log: function (data) {
		console.log(data);
		if(data.errors) {
			jQuery.each(data.errors, function(index, error) {
				$("#log").append('<li class="error">' + error['message'] + '</li>');
			});
		}

		if(data.messages) {
			jQuery.each(data.messages, function(index, message) {
				$("#log").append('<li class="message">' + message['message'] + '</li>');
			});
		}
	}
};