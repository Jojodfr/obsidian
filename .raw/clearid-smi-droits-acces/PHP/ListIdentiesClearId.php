<?php
/*
 * https://frsellpappepa02.atlas.hermes/clearid/accctrl/ListIdentiesClearId.php
 * /var/www/clearid/accctrl/ListIdentiesClearId.php
 *
 * options : 
 * --noarray : dont generate array file
 * --nocsv : dont generate csv file
 * --fastlog : dont log identity name
 *
 */

set_time_limit(0);
//ini_set('memory_limit', '512M');

if (php_sapi_name() === "cli") {
//	echo("Running from CLI"); 
} else {
	@apache_setenv('no-gzip', 1);
	@ini_set('zlib.output_compression', 0);
	@ini_set('implicit_flush', 1);
	for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
	@ob_end_flush();
	header('Cache-Control: no-cache');
	flush();
}

date_default_timezone_set('Europe/Paris');

require_once('functions.inc.php');

//$ENV = 'demo';
$ENV = 'prod';

$DEBUG = false;
//$DEBUG_FILE = false;
$DEBUG_FILE = true;

require_once('api_url.inc.php');
require_once('api_functions.inc.php');

$options = getopt("", array("noarray", "nocsv", "fastlog"));
//print_r($options);
$noarray = (array_key_exists('noarray', $options) ? true : false);
$nocsv = (array_key_exists('nocsv', $options) ? true : false);
$fastlog = (array_key_exists('fastlog', $options) ? true : false);

echo "<pre>\n";
echo "ClearID : liste des identitées\n\n";

$igg_list = array();
$logs_dir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
if (!is_dir($logs_dir)) {
	mkdir($logs_dir);
	if ($DEBUG) $msg = "Logs directory created\n";
	if ($DEBUG) echo_flush($msg);
}
$debug_filename = $logs_dir . basename(__FILE__, '.php') . '_' . $ENV . '_' . date("Ymd_His") . '.log';
$csv_dir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'csv' . DIRECTORY_SEPARATOR;
if (!is_dir($csv_dir)) {
	mkdir($csv_dir);
	if ($DEBUG) $msg = "CSV directory created\n";
	if ($DEBUG) echo_flush($msg);
}
$csv_filename = $csv_dir . basename(__FILE__, '.php') . '_' . $ENV . '_' . date("Ymd_His") . '.csv';
$csv_filename_web = 'csv' . DIRECTORY_SEPARATOR . basename(__FILE__, '.php') . '_' . $ENV . '_' . date("Ymd_His") . '.csv';
$php_array_identities = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'array_identities_' . $ENV . '.inc.php';

if ($DEBUG) echo "$ENV $url_sts\n";

$origin = new DateTimeImmutable(date("Y-m-d H:i:s"));
$msg = "Start time : " . date("Y-m-d H:i:s") . "\n";
echo_flush($msg);
if ($DEBUG_FILE) {
	file_put_contents($debug_filename, $msg, FILE_APPEND);
	if ($DEBUG) echo "Writing log to " . $debug_filename . " ...\n";
}

if ($noarray == false) {
	// PHP array identities
	$array_identities = "<?php" . PHP_EOL . "\$array_identities = ";
	file_put_contents($php_array_identities, $array_identities);
	$array_identities_a = [];
}

if ($nocsv == false) {
	// CSV header + BOM
	$csv = chr(0xEF) . chr(0xBB) . chr(0xBF) . "identityId;firstName;lastName;companyName;departmentName;externalId;status" . PHP_EOL;
	file_put_contents($csv_filename, $csv, FILE_APPEND);
	echo_flush("Generating CSV file, please wait ...\n");
}
//Recupération des users de ClearId
$continuation = '';
$num = 0;
do {
	if ($num % 500 == 0) {
//		echo_flush($num . "\n");
		echo "\n" . $num;
	}
	// Refresh token every 2000 identities
	if ($num % 2000 == 0) {
		$token = get_token($url_sts, $client_id, $client_secret, $grant_type);
	}

	$curl8 = curl_init();

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
	if (!$response8) {
		$msg = "\nCurl error - get identities : " . curl_error($curl8);
		$msg .= "\nCurl HTTP code : " . curl_getinfo($curl8, CURLINFO_HTTP_CODE);
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
	foreach ($igg_list as $igg) {
		$inactive = false;
		
//print_r($igg); die();

		$msg = "\n\nIdentity " . $num . " : " . $igg['firstName'] . " " . $igg['lastName'];
		if ($DEBUG) echo_flush($msg);
		if ($DEBUG_FILE && !$fastlog) file_put_contents($debug_filename, $msg, FILE_APPEND);
		
		$costCenter = '';
		if (isset($igg['systemData']['customFields'])) {
			foreach($igg['systemData']['customFields'] as $customFields) {
				// find cost center
				if (isset($customFields["customFieldName"]) && $customFields["customFieldName"] == "cost_center") {
					$costCenter = @$customFields["customFieldValue"];
					// if (empty($costCenter)) {
						// print_r($igg['systemData']['customFields']);
						// $msg = "\nNo cost center for : " . $igg['identityId'];
						// if ($DEBUG) echo_flush($msg);
						// if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
					// }
				}
			}
		}
		$csv_a = array(
			'identityId' => $igg['identityId'],
			'firstName' => $igg['firstName'],
			'lastName' => $igg['lastName'],
			'displayName' => $igg['displayName'],
			'companyName' => (isset($igg['companyData']['companyName']) ? $igg['companyData']['companyName'] : ''),
			'departmentName' => (isset($igg['companyData']['departmentName']) ? $igg['companyData']['departmentName'] : ''),
			'externalId' => (isset($igg['systemData']['externalId']) ? $igg['systemData']['externalId'] : ''),
			'status' => $igg['status']
		);
		if ($nocsv == false) {
			$csv = implode(';', $csv_a) . PHP_EOL;
			file_put_contents($csv_filename, $csv, FILE_APPEND);
		}

		if ($noarray == false) {
			$array_identities_a[$igg['identityId']] = $csv_a;
			$array_identities_a[$igg['identityId']] += ['costCenter' => $costCenter, 'email' => (isset($igg['email']) ? $igg['email'] : '')];
		}

		$num++;
	}

	$msg = "\n\nNext continuation : " . date("Y-m-d H:i:s") . "\n";
	if ($DEBUG) echo_flush($msg);
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

} while ($continuation != '');

$msg = "\n".  $num . " done\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

if ($noarray == false) {
	$array_identities = var_export($array_identities_a, true);
	file_put_contents($php_array_identities, $array_identities, FILE_APPEND);
	$array_identities = ";" . PHP_EOL;
	file_put_contents($php_array_identities, $array_identities, FILE_APPEND);
}

$target = new DateTimeImmutable(date("Y-m-d H:i:s"));
$interval = $origin->diff($target);
$msg = "\n\nEnd time : " . date("Y-m-d H:i:s");
$msg .= "\nDuration : " . $interval->format('%Hh %Im %Ss') . "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

if ($nocsv == false) {
	echo "\n<a href='" . $csv_filename_web . "'>Download CSV</a>\n";
}
echo "</pre>\n";
@ob_end_flush();
flush();
?>