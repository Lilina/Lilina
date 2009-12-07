function LilinaAPI(location) {
	this.location = location;
}
LilinaAPI.prototype.location = '';
LilinaAPI.prototype.call = function (method, params, callback, error_callback, type) {
	params = params || {};
	callback = callback || false;
	error_callback = error_callback || false;
	var request_params = { 'method': "api", "action": method };

	$.extend(request_params, params);

	$.ajax({
		'cache': false,
		'data': request_params,
		'dataType': 'json',
		'error': error_callback,
		'success': callback,
		'type': (type) ? type : 'GET',
		'url': this.location
	});
};
LilinaAPI.prototype.get = function (method, params, callback, error_callback) { return this.call(method, params, callback, error_callback, "GET"); };
LilinaAPI.prototype.post = function (method, params, callback, error_callback) { return this.call(method, params, callback, error_callback, "POST"); };