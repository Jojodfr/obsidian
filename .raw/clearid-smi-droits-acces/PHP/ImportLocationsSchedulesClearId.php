<?php
/*
 * ImportLocationsSchedulesClearId.php
 * 
 * Pré-requis : 
 * - Mettre à jour les données array_xxx.php : exécuter le script ListSitesLocationsSchedulesClearId.php
 * - Convertir le fichier csv en utf8 avec BOM avant import
 *
 *
 * Update Identity array first :
 * ex : C:\exe\php-8.2.13-Win32-vs16-x64\php.exe  .\ListSitesLocationsSchedulesClearId.php --nocsv
 * ex (OR) : C:\exe\php-8.2.13-Win32-vs16-x64\php.exe  .\ListSitesLocationsSchedulesClearId.php --genidarray
 *
 * cd '\\cifs-frsel\etudes\Applications Départementales\PEPS France\07 - Doc DSI\ClearID (interface, plugins)\Interface SMI-ClearID (droits d'acces)\PHP'
 * C:\exe\php-8.2.13-Win32-vs16-x64\php.exe ListSitesLocationsSchedulesClearId.php
 * .\data\ImportSecteursHorairesXXX.csv à convertir en UTF8-BOM
 * C:\exe\php-8.2.13-Win32-vs16-x64\php.exe ImportLocationsSchedulesClearId.php .\data\ImportSecteursHorairesXXX.csv
 *
 */

set_time_limit(0);
date_default_timezone_set('Europe/Paris');

$options = getopt("", array("nocache", "genidarray"));
$nocache = (array_key_exists('nocache', $options) ? true : false);
$genidarray = (array_key_exists('genidarray', $options) ? true : false);
//var_dump($nocache); die();

require_once('functions.inc.php');

/*
 * Import identity/schedule for location
 *
 */
function import_schedule_to_location($locationId, $scheduleId) {
	global $DEBUG_FILE, $debug_filename, $url_locationservice, $account_id, $token;

	$retry = 5; // Seconde
	$max_retry = 5;
	$try = 1;
	$curl_error = false;
	do {

		$curl = curl_init();

/*
		echo $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations/' . $locationId . '/schedules' . "\n";
		echo '{
	  "scheduleIds": [
		"' . $scheduleId . '"
	  ],
	  "forceSynchronizationNow": true"
	}';
*/
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations/' . $locationId . '/schedules',
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
				  "forceSynchronizationNow": true
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
			$msg .= "\nRetrying " . $try . "...";
			echo $msg;
			if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

			echo "\n" . $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations/' . $locationId . '/schedules' . "\n";
			echo '{
			  "scheduleIds": [
				"' . $scheduleId . '"
			  ],
			  "forceSynchronizationNow": true"
			}' . "\n";

			$curl_error = true;
			//die();
		}
		else {
			if ($curl_error) {
				$msg = "\nRetry successful";
				echo $msg;
				if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
			}
			$curl_error = false;
		}
		curl_close($curl);

		$try++;
	} while ($try <= $max_retry && $curl_error == true);

// print_r($response);
// die();

	return $response_a;
}

/*
 * Import identity/schedule for location
 *
 */
function get_schedule_to_location($locationId, $scheduleId) {
	global $DEBUG_FILE, $debug_filename, $url_locationservice, $account_id, $token;

	$curl = curl_init();

/*
	echo $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations/' . $locationId . '/schedules' . "\n";
	echo '{
  "scheduleIds": [
    "' . $scheduleId . '"
  ],
  "forceSynchronizationNow": true"
}';
*/
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations/' . $locationId . '/schedules',
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
			'accept: text/plain',
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

	echo "\n" . $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations/' . $locationId . '/schedules' . "\n";

		die();
	}
	curl_close($curl);

// print_r($response);
// die();

	$response_a['found'] = false;
	foreach ($response_a['schedules'] as $schedule) {
		if ($schedule['scheduleId'] == $scheduleId) {
			$response_a['found'] = true;
			break;
		}
	}

	return $response_a;
}


//$ENV = 'demo';
$ENV = 'prod';

if ($genidarray) {
	echo "Generating Sites/Locations/Schedules arrays...\n";
	passthru('C:\exe\php-8.2.13-Win32-vs16-x64\php.exe  .\ListSitesLocationsSchedulesClearId.php --nocsv');
	echo "Done generating Sites/Locations/Schedules arrays\n";
}

require_once('array_sites_' . $ENV . '.inc.php');
require_once('array_locations_' . $ENV . '.inc.php');
require_once('array_locations_sites_' . $ENV . '.inc.php');
require_once('array_schedules_' . $ENV . '.inc.php');
//require_once('array_identities_' . $ENV . '.inc.php');

//$DEBUG_FILE = false;
$DEBUG_FILE = true;

$BOM = chr(0xEF) . chr(0xBB) . chr(0xBF);

require_once('api_url.inc.php');
require_once('api_functions.inc.php');

$igg_list = array();
$logs_dir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
if (!is_dir($logs_dir)) {
	mkdir($logs_dir);
	$msg = "Logs directory created\n";
	echo $msg;
}
$debug_filename = $logs_dir . basename(__FILE__, '.php') . '_' . $ENV . '_' .  date("Ymd_His") . '.log';
$import_filename = $argv[1]; // data\ImportSecteursHoraires.csv

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
$schedules = explode(PHP_EOL, $schedules);
//print_r($schedules);

$error_count = 0;
$id_error_count = 0;
$error_api = 0;
$total_skipped = 0;
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
	if (empty($schedule)) {
		$msg = "\nProgression : " . round(($num / $total_lines * 100), 2) . " % - " . $num . " / " . $total_lines . "\n";
		$msg .= "\n -> skip empty line" . "\n";
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		continue;
	}

//	list($siteId, $locationId, $scheduleId) = explode(";", $schedule);
	list($locationId, $scheduleId) = explode(";", $schedule);

	// If CSV line header SKIP
	if ($locationId == 'locationId') {
		$msg = "\nProgression : " . round(($num / $total_lines * 100), 2) . " % - " . $num . " / " . $total_lines . "\n";
		$msg .= "\n -> skip header" . "\n";
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		continue;
	}

	$msg = "\nProgression : " . round(($num / $total_lines * 100), 2) . " % - " . $num . " / " . $total_lines . "\n";
	$msg .= "Line : " . $schedule . "\n";
	$msg .= "siteId : " . $array_location_site[$locationId] . " / " . @$array_site[$array_location_site[$locationId]] . "\n";
	$msg .= "locationId : " . $locationId . " / " . @$array_location[$locationId] . "\n";
	$msg .= "scheduleId : " . $scheduleId . " / " . @$array_schedule[$scheduleId]  . "\n";
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	
	$check_schedule = get_schedule_to_location($locationId, $scheduleId);
	if ($check_schedule['found']) {
		$msg = "\n -> Exists, skipped...\n";
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		$total_skipped++;
	} else {
		$import_result = import_schedule_to_location($locationId, $scheduleId);
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
	}

// die();
}

$msg = "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

$total_id = $num++;

$msg =  "\nNb lignes dans fichier : " . $total_id;
$msg .= "\nNb lignes ignorées : " . $total_skipped;
$msg .= "\nNb lignes ajoutées : " . $total_ok;
//$msg .= "\nNb erreurs id : " . $id_error_count;
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