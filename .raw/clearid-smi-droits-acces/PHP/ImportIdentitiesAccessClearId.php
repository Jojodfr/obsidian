<?php
/*
 * 
 * Pré-requis : 
 * - Mettre à jour les données array_xxx.php : exécuter les scripts ListSitesLocationsSchedulesClearId.php & ListIdentiesClearId.php
 * - Convertir le fichier csv en utf8 avec BOM avant import
 *
 * cd '\\cifs-frsel\etudes\Applications Départementales\PEPS France\07 - Doc DSI\ClearID (interface, plugins)\Interface SMI-ClearID (droits d'acces)\PHP'
 * C:\exe\php-8.2.13-Win32-vs16-x64\php.exe ListIdentiesClearId.php
 * C:\exe\php-8.2.13-Win32-vs16-x64\php.exe ListSitesLocationsSchedulesClearId.php
 * .\data\ImportHorairesXXX.csv à convertir en UTF8-BOM
 * C:\exe\php-8.2.13-Win32-vs16-x64\php.exe ImportIdentitiesAccessClearId.php .\data\ImportHorairesXXX.csv
 *
 */

set_time_limit(0);
date_default_timezone_set('Europe/Paris');

require_once('functions.inc.php');

/*
 * Import identity/schedule for location
 *
 */
function import_schedule_to_location($identityIds, $locationId, $scheduleId, $startDateTimeUtc, $endDateTimeUtc, $description) {
	global $DEBUG_FILE, $debug_filename, $url_locationservice, $account_id, $token;

	$curl = curl_init();

/*
	echo $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations/' . $locationId . '/accesses' . "\n";
	echo '{
  "scheduleIds": [
    "' . $scheduleId . '"
  ],
  "identityIds": [
    "' . $identityIds . '"
  ],
  "startDateTimeUtc": "' . $startDateTimeUtc . '",
  "endDateTimeUtc": "' . $endDateTimeUtc . '",
  "description": "' . $description . '"
}';
*/
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations/' . $locationId . '/accesses',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'PATCH',
		CURLOPT_POSTFIELDS =>'{
  "scheduleIds": [
    "' . $scheduleId . '"
  ],
  "identityIds": [
    "' . $identityIds . '"
  ],
  "startDateTimeUtc": "' . $startDateTimeUtc . '",
  "endDateTimeUtc": "' . $endDateTimeUtc . '",
  "description": "' . $description . '"
}',
		CURLOPT_HTTPHEADER => array(
			'accept: text/plain',
			'Content-Type: application/json-patch+json',
			'Authorization: Bearer ' . $token
		),
	));

	$response = curl_exec($curl);
//	print_r($response);

	// test retour curl
	if ($response === false) {
		$msg = "\nCurl error : " . curl_error($curl);
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		die();
	}
	$response_a = json_decode($response, true);
//$info = curl_getinfo($curl);
//print_r($info);
	$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ($http_code < 200 ||
		$http_code > 300) {
		$msg = "\nCurl HTTP code : " . $http_code;
		$msg .= "\nCurl response : " . $response;
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		die();
	}
	curl_close($curl);

// print_r($response);
// die();

	return $response_a;
}


//$ENV = 'demo';
$ENV = 'prod';

require_once('array_sites_' . $ENV . '.inc.php');
require_once('array_locations_' . $ENV . '.inc.php');
require_once('array_locations_sites_' . $ENV . '.inc.php');
require_once('array_schedules_' . $ENV . '.inc.php');
require_once('array_identities_' . $ENV . '.inc.php');

//$DEBUG_FILE = false;
$DEBUG_FILE = true;

$BOM = chr(0xEF) . chr(0xBB) . chr(0xBF);

require_once('api_url.inc.php');
require_once('api_functions.inc.php');

//$photo_path = "C:\\Users\\c_akhemi\\Downloads\\";
//$photo_path2 = '/C:/Users/c_akhemi/Downloads/';
$photo_path = "C:\\temp\\";
$photo_name = '';
$igg_list = array();
$logs_dir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
if (!is_dir($logs_dir)) {
	mkdir($logs_dir);
	$msg = "Logs directory created\n";
	echo $msg;
}
$debug_filename = $logs_dir . basename(__FILE__, '.php') . '_' . $ENV . '_' .  date("Ymd_His") . '.log';
$import_filename = $argv[1]; // data\ImportHoraires.csv

$origin = new DateTimeImmutable(date("Y-m-d H:i:s"));
$msg = "Start time : " . date("Y-m-d H:i:s") . "\n";
echo $msg;
if ($DEBUG_FILE) {
	file_put_contents($debug_filename, $msg, FILE_APPEND);
	echo "Writing log to " . $debug_filename . " ... \n";
}

if (empty($import_filename)) {
	$msg = "Error - Missing argument : import filename\n";
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	die();
}
if (!file_exists($import_filename)) {
	$msg = "Error - File not found : " . $import_filename . "\n";
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	die();
}

$msg = "\nReading file : " . $import_filename . "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

$schedules = file_get_contents($import_filename);
if (substr($schedules, 0, 3) == $BOM) {
	$schedules = substr($schedules, 3); // remove BOM
	$msg = "BOM header removed\n";
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
}
else {
	$msg = "Missing BOM header : aborting...\n";
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	die();
}
$schedules = explode(PHP_EOL, $schedules);
//print_r($schedules);

$error_count = 0;
$date_error_count = 0;
$id_error_count = 0;
$error_api = 0;
$total_ok = 0;

//Recupération des sites
$num = 0;
$total_lines = count($schedules);

/*  Pour chaque ligne à importer */
foreach ($schedules as $schedule) {
	// Refresh token every 2000 lines
	if ($num % 2000 == 0) {
		$token = get_token($url_sts, $client_id, $client_secret, $grant_type);
		echo_flush($num . "\n");
	}

	$num++;
	$schedule = trim($schedule);
	// Skip empty line
	if (empty($schedule)) continue;

	list($identityIds, $locationId, $scheduleId, $startDateTimeUtc, $endDateTimeUtc, $description) = explode(";", $schedule);

	// If CSV line header SKIP
	if ($identityIds == 'identityIds') continue;


	$msg = "\nProgression : " . round(($num / $total_lines * 100), 2) . " % - " . $num . " / " . $total_lines . "\n";
	$msg .= "Line : " . $schedule . "\n";
	$msg .= "identityIds : " . $identityIds . " / " . @$array_identities[$identityIds]['firstName'] . ' ' . @$array_identities[$identityIds]['lastName'] . "\n";
	$msg .= "siteId : " . $array_location_site[$locationId] . " / " . @$array_site[$array_location_site[$locationId]] . "\n";
	$msg .= "locationId : " . $locationId . " / " . @$array_location[$locationId] . "\n";
	$msg .= "scheduleId : " . $scheduleId . " / " . @$array_schedule[$scheduleId]  . "\n";
	$msg .= "startDateTimeUtc : " . $startDateTimeUtc . "\n";
	$msg .= "endDateTimeUtc : " . $endDateTimeUtc . "\n";
	$msg .= "description : " . $description . "\n";
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

	// Date sample from CSV : 31/12/2025 19:00 -> CleadID 2025-12-31T20:00:00Z
	if (!empty($startDateTimeUtc)) {
		$startDateTime = DateTime::createFromFormat('d/m/Y H:i', $startDateTimeUtc);
		if ($startDateTime === false) {
			$startDateTime = DateTime::createFromFormat('d/m/Y H:i:s', $startDateTimeUtc);
		}
		if ($startDateTime === false) {
			$msg = "n -> Date ERROR : startDateTimeUtc : " . $startDateTimeUtc . "\n";
			echo $msg;
			if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
			$date_error_count++;
			continue;
		}
		$startDateTime->setTimezone(new DateTimeZone("UTC"));
		$startDateTimeUtc = $startDateTime->format('Y-m-d\TH:i:sp');
	}
	if (!empty($endDateTimeUtc)) {
		$endDateTime = DateTime::createFromFormat('d/m/Y H:i', $endDateTimeUtc);
		if ($endDateTime === false) {
			$endDateTime = DateTime::createFromFormat('d/m/Y H:i:s', $endDateTimeUtc);
		}
		if ($endDateTime === false) {
			$msg = "n -> Date ERROR : endDateTimeUtc : " . $endDateTimeUtc . "\n";
			echo $msg;
			if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
			$date_error_count++;
			continue;
		}
		$endDateTime->setTimezone(new DateTimeZone("UTC"));
		$endDateTimeUtc = $endDateTime->format('Y-m-d\TH:i:sp');
		
		if ($startDateTime->getTimestamp() > $endDateTime->getTimestamp()) {
			$msg = "\n -> Date ERROR : startDateTimeUtc : " . $startDateTimeUtc . " > endDateTimeUtc : " . $endDateTimeUtc . "\n";
			echo $msg;
			if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
			$date_error_count++;
			continue;
		}
	}
	if (!isset($array_identities[$identityIds])) {
		$msg = "\n -> identityID ERROR : " . $identityIds. "\n";
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		$id_error_count++;
		continue;
	}

	$import_result = import_schedule_to_location($identityIds, $locationId, $scheduleId, $startDateTimeUtc, $endDateTimeUtc, $description);
	// print_r($import_result);
	
	if (isset($import_result['statusCode'])) {
		$msg = "\n -> Import ERROR : " . $import_result['type'] . "\n";
		if (isset($import_result['details'])) {
			$msg .= "  details :\n" . $import_result['message'] . "\n";
		}
		$msg .= "    message : " . var_export($import_result['message'], true) . "\n";
		$msg .= "    statusCode - statusText : " . $import_result['statusCode'] . " - " . $import_result['statusText'] . "\n";
		// $msg .= "type : " . $import_result['type'] . "\n";
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		$error_api++;
	}
	else {
		$msg = "\n -> Import OK\n";
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		$total_ok++;
	}

// die();
}

$msg = "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

$total_id = $num++;

$msg =  "\nNb lignes dans fichier : " . $total_id;
$msg .= "\nNb lignes ajoutées : " . $total_ok;
$msg .= "\nNb erreurs date : " . $date_error_count;
$msg .= "\nNb erreurs id : " . $id_error_count;
$msg .= "\nNb erreurs appels curl API : " . $error_api;
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

$target = new DateTimeImmutable(date("Y-m-d H:i:s"));
$interval = $origin->diff($target);
$msg = "\n\nEnd time : " . date("Y-m-d H:i:s");
$msg .= "\nDuration : " . $interval->format('%Hh %Im %Ss') . "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
?>