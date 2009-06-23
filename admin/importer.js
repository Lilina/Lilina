var importer = {
	done: 0,
	feeds: [],
	end_callback: null,

	init: function (feeds) {
		importer.feeds = feeds;
		importer.iterate();
	},
	iterate: function () {
		var feed = importer.feeds.shift();
		if(feed == undefined) {
			if(this.end_callback != null)
				this.end_callback();
			return;
		}

		var me = this;
		$.ajax({
			data: {name: feed['title'], url: feed['url'], method: "feeds.add"},
			dataType: "json",
			complete: function(request) {
				try {
					data = JSON.parse(request.responseText);
					me.log(data);
					me.iterate();
				} catch(e) {
					me.log("Error parsing response.");
					me.iterate();
				}
			},
			type: "POST",
			url: "admin-ajax.php"
		})
	},
	log: function (data) {
		console.log(data);
		if(data.error) {
			$("#log").append('<li class="error">' + data.msg + '</li>');
		}

		if(data.success) {
			$("#log").append('<li class="message">' + data.msg + '</li>');
		}

		this.done++;
		$('#import-progress .done').text(this.done);
	}
};