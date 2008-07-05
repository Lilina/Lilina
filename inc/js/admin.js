var feeds = {
	add: function () {
		if( !$("#add_url").val() ) {
			humanMsg.displayMsg('No feed URL supplied');
			return false;
		}
		$.post("admin.php", {
			action: "add",
			ajax: true,
			page: "feeds",
			add_name: $("#add_name").val(),
			add_url: $("#add_url").val()
		}, function (data) {
			humanMsg.displayMsg(data);

			// Clear the values
			$("#add_url").val('');
			$("#add_name").val('');

			feeds.reload_table();
		});
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
		$.post("admin.php", {
			action: "change",
			ajax: true,
			page: "feeds",
			change_name: $("#change_name").val(),
			change_id: $("#change_id").val(),
			change_url: $("#change_url").val()
		}, function (data) {
			humanMsg.displayMsg(data);

			// Clear the values
			$("#change_url").val('');
			$("#change_name").val('');
			$("#change_id").val('');

			admin.reload_table();
		});
	},
	reload_table: function () {
		$.get("admin.php", {ajax: true, list: true, page: 'feeds'}, function (data) {
			$("#feeds_list tbody").html(data);
		});
	}
};

$(document).ready(function () {
	$("#navigation li.current").hover(function () {
		$("#navigation").addClass("hover");
	}, function () {
		$("#navigation").removeClass("hover");
	});

	$("#changer").hide();
	$("#changer_id").hide();
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
	$("#alert .tech_notice").append("<a href=\"javascript:void(0)\" class=\"note_link\">Show technical explanation</a>").children(".actual_notice").hide();
	$("#alert .tech_notice a.note_link").click(function () {
		$(this).siblings(".actual_notice").show();
		$(this).remove();
	});
	
	$("#alert").effect("highlight", { 
		color: "red" 
	}, 3000);
	
	/* Feeds */
	$("#add_form").submit(function () {
		feeds.add();
		return false;
	});
	$("#change_form").submit(function () {
		feeds.change();
		return false;
	});
});