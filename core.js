Razor = {};
Razor.init = function () {
	//RazorAPI.init();
	RazorUI.init();
};
Razor.shortenString = function(string, length) {
	if (string.length > length) {
		var shorterLength = length - 3;
		return string.slice(0, shorterLength) + '...';
	} else {
		return string;
	}
}
/*String.prototype['shorten'] = function(length) {
	if (this.length > length) {
		var shorterLength = length - 3;
		return this.slice(0, shorterLength) + '...';
		return this.substring(0, shorterLength) + "...";
	} else {
		return this;
	}
}*/

$(document).ready(Razor.init);