<?php
date_default_timezone_set('Europe/Paris');

function var_dump_ret($mixed = null) {
  ob_start();
  var_dump($mixed);
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}

//$ENV = 'demo';
$ENV = 'prod';

//$DEBUG_FILE = false;
$DEBUG_FILE = true;

$ADD_PHOTO_IF_NO_PHOTO = true;

require_once('api_url.inc.php');

//$photo_path = "C:\\Users\\c_akhemi\\Downloads\\";
//$photo_path2 = '/C:/Users/c_akhemi/Downloads/';
//$photo_path = "C:\\temp\\";
$photo_path = DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR;
$photo_name = '';
$igg_list = array();
$logs_dir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
if (!is_dir($logs_dir)) {
	mkdir($logs_dir);
	$msg = "Logs directory created\n";
	echo $msg;
}
$debug_filename = $logs_dir . basename(__FILE__, '.php') . '_' . $ENV . '_' . date("Ymd_His") . '.log';

echo "\n$ENV  $url_sts\n";

$msg = "Start time : " . date("Y-m-d H:i:s") . "\n";
echo $msg;
if ($DEBUG_FILE) {
	file_put_contents($debug_filename, $msg, FILE_APPEND);
	echo "Writing log to " . $debug_filename . " ... \n";
}

/*partie 1: Get TOKEN ClearID API */
$curl1 = curl_init();
curl_setopt_array($curl1, array(
//    CURLOPT_URL => $url_sts . '/connect/token?client_id=' . $client_id . '&grant_type=' . $grant_type . '&client_secret=' . $client_secret,
    CURLOPT_URL => $url_sts . '/connect/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => 'client_id=' . $client_id . '&grant_type=' . $grant_type . '&client_secret=' . $client_secret,
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/x-www-form-urlencoded'
    ),
  ));

$response1 = curl_exec($curl1);
/* Tester le retour curl */
if (!$response1) {
	$msg = "\nCurl errror : " . curl_error($curl1);
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	die();
}
curl_close($curl1);

$response1 = json_decode($response1, true);
//print_r($response1);

/*
tester le retour clearid si retour error 
{"error":"????"}
*/
if (array_key_exists('error', $response1)) {
	$msg = "Token errror : " . json_encode($response1);
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	die();
}

$token=$response1['access_token'];
$msg = "\nLe token est : " . substr($token, 0, 10) . "....";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);


//Recupération des users de ClearId qui sont actifs
$continuation = '';
$num = 1;
$numNo = 1;
do {
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
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

	$igg_list = $response8['identities'];

	/*  Pour chaque identité ClearID */
	foreach ($igg_list as $igg) {

		if ($igg['status'] != 'Active') {
			$msg = "\n\nIdentity " . $numNo . " : " . $igg['firstName'] . " " . $igg['lastName'] . " / " . $igg['identityId'];
			echo $msg;
			if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

			if($igg['status'] != 'Active') {
				$msg = "\n -> Status : " . $igg['status'];
				echo $msg;
				if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
				$inactive = true;
			}

			$numNo++;
		}

		$num++;
	}
} while ($continuation != '');

$msg = "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

$msg = "\nEnd time : " . date("Y-m-d H:i:s") . "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
?>