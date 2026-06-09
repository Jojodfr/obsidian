<?php
date_default_timezone_set('Europe/Paris');

require_once('functions.inc.php');

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
$debug_filename = $logs_dir . basename(__FILE__, '.php') . '_' . $ENV . '_' .  date("Ymd_His") . '.log';
$import_filename = $argv[1]; // data\ImportPrestataires.csv

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

$identities = file_get_contents($import_filename);
if (substr($identities, 0, 3) == $BOM) {
	$identities = substr($identities, 3); // remove BOM
	$msg = "BOM header removed\n";
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
}
$identities = explode(PHP_EOL, $identities);
//print_r($identities);

/*partie 1: Get TOKEN ClearID API */
$token = get_token($url_sts, $client_id, $client_secret, $grant_type);

//Recupération des identities
$num = 1;

	
$SitePrincipal = '72b455d1-5e21-46a1-afef-390b2f95b67d';
$Division = 'HERMES SERVICES GROUPE';

/*  Pour chaque ligne à importer */
foreach ($identities as $identity) {
	$identity = trim($identity);
	// Skip empty line
	if (empty($identity)) continue;

	list($WorkerType, $Company, $Manager0, $Department, $LocationCode, $Worklocation, $Division0, $FisrtName, $LastName, $MissionTitle, $Arrival, $Departure, $SitePrincipal0, $ExternalCompany) = explode(";", $identity);

	// If CSV line header SKIP
	if ($WorkerType == 'Worker type') continue;

/*
	// Date sample from CSV : 2025-01-02 -> CleadID champ custom 2025-01-02 
	if (!empty($Arrival)) {
		$Arrival0 = DateTime::createFromFormat('Y-m-d', $Arrival);
		if ($Arrival0 === false) {
			$msg = "Arrival error : " . $Arrival;
			echo $msg;
			if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
			continue;
		} else {
			$Arrival0->setTimezone(new DateTimeZone("UTC"));
			$Arrival = $Arrival0->format('Y-m-d');
		}
	}

	if (!empty($Departure)) {
		$Departure0 = DateTime::createFromFormat('d/m/Y H:i', $Departure);
		if ($Departure0 === false) {
			$msg = "Departure error : " . $Departure;
			echo $msg;
			if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
			continue;
		} else {
			$Departure0->setTimezone(new DateTimeZone("UTC"));
			$Departure = $endDateTime->format('Y-m-d');
		}
	}
*/
	$msg = "\n\nPrenom Nom : " . $FisrtName . ' ' . $LastName;
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	
	$search_value = $Manager0;
	$array_keymap = array_recursive_search_key_map($search_value, $array_identities);
	$ManagerId = array_get_nested_value($array_keymap, $array_identities);
	if ($ManagerId  === false) {
		$msg = "\n -> Manager NOT found";
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	} else {
		$msg = "\n -> Manager found : " . $array_identities[$ManagerId]['firstName'] . ' ' . $array_identities[$ManagerId]['lastName'];
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	}
	$ExternalId = 'SST-' . md5($FisrtName . $LastName . $Arrival . $Departure . $ExternalCompany);
	$exists = get_identity_by_externalid($ExternalId);
//print_r($exists);
//die();
	if (isset($exists['identities'][0]) && is_array($exists['identities'][0])) {
		$msg = "\n -> Already imported, skipping";
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		continue;
	}

	$Manager = $array_identities[$ManagerId]['firstName'] . ' ' . $array_identities[$ManagerId]['lastName'];
	$CostCenter = @$array_identities[$ManagerId]['costCenter'];
	$ManagerExtId = $array_identities[$ManagerId]['externalId'];
	$ManagerId = $array_identities[$ManagerId]['identityId'];
	$ManagerEmail = $array_identities[$ManagerId]['email'];

	$import_result = import_identity($WorkerType, $Company, $Manager, $Department, $LocationCode, $Worklocation, $Division, $FisrtName, $LastName, $MissionTitle, $Arrival, $Departure, $SitePrincipal, $ExternalCompany, $CostCenter, $ManagerExtId, $ManagerId, $ManagerEmail, $ExternalId);
	// print_r($import_result);
	
	if (isset($import_result['statusCode'])) {
		$msg = "\n -> Import ERROR : " . $import_result['type'] . "\n";
		if (isset($import_result['details'])) {
			$msg .= "  details :\n" . $import_result['message'] . "\n";
		}
		$msg .= "    message : " . var_export($import_result['message'], true) . "\n";
		$msg .= "    statusCode - statusText : " . $import_result['statusCode'] . " - " . $import_result['statusText'];
		// $msg .= "type : " . $import_result['type'] . "\n";
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	}
	else {
		$msg = "\n -> Import OK";
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	}

//die();
	$num++;
}

$msg = "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

$msg = "\nEnd time : " . date("Y-md- H:i:s") . "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
?>