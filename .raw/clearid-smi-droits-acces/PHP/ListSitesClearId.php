<?php
date_default_timezone_set('Europe/Paris');

require_once('functions.inc.php');

//$ENV = 'demo';
$ENV = 'prod';

//$DEBUG_FILE = false;
$DEBUG_FILE = true;

$ADD_PHOTO_IF_NO_PHOTO = true;

require_once('api_url.inc.php');

//$photo_path = "C:\\Users\\c_akhemi\\Downloads\\";
//$photo_path2 = '/C:/Users/c_akhemi/Downloads/';
$photo_path = "C:\\temp\\";
$photo_name = '';
$igg_list = array();
$debug_filename = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . basename(__FILE__, '.php') . '_' . $ENV . '_' .  date("Ymd_His") . '.log';
$debug_csv = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . basename(__FILE__, '.php') . '_' . $ENV . '_' . date("Ymd_His") . '.csv';

echo "\n$ENV  $url_sts\n";

$msg = "Start time : " . date("Y-m-d H:i:s") . "\n";
echo $msg;
if ($DEBUG_FILE) {
	file_put_contents($debug_filename, $msg, FILE_APPEND);
	echo "Writing log to " . $debug_filename . " ... \n";
}

// CSV header + BOM
$csv = chr(0xEF) . chr(0xBB) . chr(0xBF) . "siteId;name;isDeleted;createdDatetimeUtc" . PHP_EOL;
file_put_contents($debug_csv, $csv, FILE_APPEND);

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


//Recupération des sites
$num = 1;
$curl8 = curl_init();

//echo $url_siteservice . '/api/v2/accounts/' . $account_id . '/sites' . "\n";
curl_setopt_array($curl8, array(
	CURLOPT_URL => $url_siteservice . '/api/v2/accounts/' . $account_id . '/sites?continuation=' . $continuation,
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

$sites = $response8['sites'];

/*  Pour chaque identité ClearID */
foreach ($sites as $site) {

	$msg = "\n\nSite " . $num . " : " . $site['siteId'] . " " . $site['name'];
	$msg .= "\nisDeleted  : " . $site['isDeleted'];
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	
	$cas_a = array($site['siteId'], $site['name'], $site['isDeleted'], $site['createdDatetimeUtc']);
	$csv = implode(';', $cas_a) . PHP_EOL;
	file_put_contents($debug_csv, $csv, FILE_APPEND);

	$num++;
}

$msg = "\n\nCSV file : " . $debug_csv;
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

$msg = "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

$msg = "End time : " . date("Y-md- H:i:s") . "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
?>