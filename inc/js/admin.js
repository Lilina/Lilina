$(document).ready(function() {
	$("#changer").hide();
	$("#changer_id").hide();
	$(".change_link").click(function() {
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
	$("#alert .tech_notice a.note_link").click(function() {
		$(this).siblings(".actual_notice").show();
		$(this).remove();
	});
	Fat.fade_all();
});