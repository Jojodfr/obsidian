<?php
/*
 * https://frsellpappepa02.atlas.hermes/clearid/accctrl/SynchronizeIdentitiesClearId.php
 * /var/www/clearid/accctrl/SynchronizeIdentitiesClearId.php
 *
 */
date_default_timezone_set('Europe/Paris');

require_once('functions.inc.php');

//$ENV = 'demo';
$ENV = 'prod';

$DEBUG = false;
//$DEBUG_FILE = false;
$DEBUG_FILE = true;

require_once('api_url.inc.php');
require_once('api_functions.inc.php');

$excluded_ids = array(
	'b1e145e9-45a0-478e-836c-3b1e04b3839e',
	'e431b77d-9fa1-47cc-aca7-ac751752d814'
	);

echo "<pre>\n";
echo "ClearID : synchro des identitées\n\n";

//$photo_path = "C:\\Users\\c_akhemi\\Downloads\\";
//$photo_path2 = '/C:/Users/c_akhemi/Downloads/';
//$photo_path = "C:\\temp\\";
//$photo_name = '';
$igg_list = array();
$logs_dir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
if (!is_dir($logs_dir)) {
	mkdir($logs_dir);
	if ($DEBUG) $msg = "Logs directory created\n";
	if ($DEBUG) echo_flush($msg);
}
$debug_filename = $logs_dir . basename(__FILE__, '.php') . '_' . $ENV . '_' . date("Ymd_His") . '.log';

if ($DEBUG) echo "$ENV $url_sts\n";

$msg = "Start time : " . date("Y-m-d H:i:s") . "\n";
echo_flush($msg);
if ($DEBUG_FILE) {
	file_put_contents($debug_filename, $msg, FILE_APPEND);
	if ($DEBUG) echo "Writing log to " . $debug_filename . " ...\n";
}

//Recupération des users de ClearId
$continuation = '';
$num = 0;
do {
	// Refresh token every 2000 identities
	if ($num % 2000 == 0) {
		$token = get_token($url_sts, $client_id, $client_secret, $grant_type);
		echo_flush($num . "\n");
	}

	$curl8 = curl_init();

	//echo $url_identityservice . '/api/v3/accounts/' . $account_id . '/identities' . "\n";
	curl_setopt_array($curl8, array(
		CURLOPT_URL => $url_identityservice . '/api/v3/accounts/' . $account_id . '/identities?continuation=' . $continuation,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => array(
			'Authorization: Bearer ' . $token
		),
	));

	$response8 = curl_exec($curl8);
	//print_r($response8);

	// test retour curl
	if ($response8 === false) {
		$msg = "\nCurl errror : " . curl_error($curl8);
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		die();
	}
	curl_close($curl8);

	$response8 = json_decode($response8, true);
	//print_r($response8);
	$continuation = @$response8['continuation'];
	$msg = "\n\n[continuation : " . $continuation . "]";
	if ($DEBUG) echo_flush($msg);
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

	$igg_list = $response8['identities'];

	/*  Pour chaque identité ClearID */
	$entities = [];
	$entities = array_column($igg_list, 'identityId');
	foreach($entities as $k => $v) {
		if (!in_array($v, $excluded_ids)) $entities[$k] = '"' . $v . '"';
		else {
			unset($entities[$k]);
			$msg = "Excluded : " . $v . PHP_EOL;
			echo_flush($msg);
			if ($DEBUG_FILE) file_put_contents($debug_filename, PHP_EOL . $msg, FILE_APPEND);
		}
		$num++;
	}
//print_r($entities);die();
	$imp_identities = implode(', ', $entities);
	$msg = "\n\nidentityIds : ";
	$msg .= $imp_identities;
	if ($DEBUG) echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

	// Synchronize
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url_identityservice . '/api/v3/accounts/' . $account_id . '/identities/synchronize',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS =>'{
"identityIds": [ ' . $imp_identities . ']
}',
		CURLOPT_HTTPHEADER => array(
				'accept: text/plain',
				'Content-Type: application/json-patch+json',
				'Authorization: Bearer ' . $token
			),
	));

	$response = curl_exec($curl);
	//print_r($response8);

	// test retour curl
	if ($response === false) {
		$msg = "\nCurl errror : " . curl_error($curl);
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		die();
	}
	curl_close($curl);

	$response = json_decode($response, true);
	$msg = "\n\n -> Response :\n";
	$msg .= var_dump_ret($response);
	if ($DEBUG) echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
//	die();

} while ($continuation != '');

$msg = $num . " done\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

$msg = "End time : " . date("Y-m-d H:i:s") . "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

echo "</pre>\n";
@ob_end_flush();
flush();
?>