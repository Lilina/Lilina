LilinaAPI = {};
LilinaAPI.call = function (method, params, callback, error_callback, type, location) {
	params = params || {};
	callback = callback || false;
	error_callback = error_callback || false;
	location = location || "index.php";
	var request_params = { 'method': "api", "action": method };

	$.extend(request_params, params);

	$.ajax({
		'cache': false,
		'data': request_params,
		'dataType': 'json',
		'error': error_callback,
		'success': callback,
		'type': (type) ? type : 'GET',
		'url': location
	});
};
LilinaAPI.get = function (method, params, callback, error_callback, location) { return this.call(method, params, callback, error_callback, "GET", location); };
LilinaAPI.post = function (method, params, callback, error_callback, location) { return this.call(method, params, callback, error_callback, "POST", location); };