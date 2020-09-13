<?php
require_once __DIR__ . '/src/com/carlgo11/tempfiles/Autoload.php';

function getCURL(string $id, string $password) {
	require_once __DIR__ . '/src/com/carlgo11/tempfiles/Autoload.php';
	global $conf;
	$d_url = sprintf($conf['api-download-url'], $id, $password);

	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => $d_url,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => [
			"cache-control: no-cache"
		]
	]);
	return $curl;
}

function return404() {
	header($_SERVER['SERVER_PROTOCOL'] . " 404 File Not Found");
	header('Location: https://tempfiles.download/download/?404=1');
	exit;
}

$url = explode('/', strtolower($_SERVER['REQUEST_URI']));
$id = filter_var($url[1]);
$password = filter_input(INPUT_GET, "p");

$curl = getCURL($id, $password);

// Execute cURL command and get response data
$response = json_decode(curl_exec($curl));

$error = curl_error($curl);
if ($error !== "") {
	error_log($error);
	return404();
}

if ($response->success) {

	// Set headers
	header("Content-Description: File Transfer");
	header("Expires: 0");
	header("Pragma: public");
	header("Content-Type: {$response->type}");
	header("Content-Disposition: inline; filename=\"{$response->filename}\"");
	header("Content-Length: {$response->length}");

	// output file contents
	echo base64_decode($response->data);

} else return404();
exit;