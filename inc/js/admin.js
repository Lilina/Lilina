$(document).ready(function() {
	$("#changer").hide();
	$("#changer_id").hide();
	$(".change_link").click(function() {
		feed_url = $(this).parent().prev().prev().text();
		feed_name = $(this).parent().prev().prev().prev().text();
		feed_num = $(this).parent().prev().prev().prev().prev().text();
		$("#change_url").val(feed_url);
		$("#change_name").val(feed_name);
		$("#change_id").val(feed_num);
		$("#changer").slideDown();
		return false;
	});
});