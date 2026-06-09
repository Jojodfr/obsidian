<?php
/*
 * ListAccessesByLocationClearId.php
 *
 * But : lister les identités ("propriétaires") ayant un accès à chaque location (secteur)
 *       en utilisant l'API ClearID Access v1.
 *
 * Endpoint : GET /api/v1/accounts/{accountId}/locations/{locationId}/accesses
 * Réponse : AccessesModel { accessModels: { identityId: [AccessModel] } }
 *
 * Prérequis :
 *  - Exécuter ListIdentiesClearId.php pour mettre à jour array_identities_{ENV}.inc.php
 *  - Exécuter ListSitesLocationsSchedulesClearId.php pour mettre à jour array_locations_{ENV}.inc.php
 *  - Configurer $url_accessapi ci-dessous (ou ajouter à api_url.inc.php)
 *
 * Options :
 *  --nocsv       : ne pas générer de fichier CSV
 *  --locationId  : filtrer sur un UUID de location unique
 *  --identityId  : filtrer les résultats sur un UUID d'identité (post-filtre)
 *  --includeExpired : inclure les accès expirés (paramètre de requête API)
 *
 * Exemple CLI :
 *  php.exe ListAccessesByLocationClearId.php
 *  php.exe ListAccessesByLocationClearId.php --locationId=aa00ffde-3b50-4527-8f72-bf63fdc99303
 *  php.exe ListAccessesByLocationClearId.php --nocsv --includeExpired
 *
 * Exemple Web :
 *  https://frsellpappepa02.atlas.hermes/clearid/accctrl/ListAccessesByLocationClearId.php?locationId=...
 */

set_time_limit(0);
date_default_timezone_set('Europe/Paris');

$scriptDir = dirname(__FILE__);

require_once($scriptDir . DIRECTORY_SEPARATOR . 'functions.inc.php');

$ENV = 'prod';

$DEBUG = false;
$DEBUG_FILE = true;

require_once($scriptDir . DIRECTORY_SEPARATOR . 'api_url.inc.php');
require_once($scriptDir . DIRECTORY_SEPARATOR . 'api_functions.inc.php');

// ---------------------------------------------------------------------------
// URL de base de l'Access API v1 — fallback sur $url_rps + '/access' si non définie
// TODO : vérifier avec la DETS le hostname exact de l'Access API en production.
// Patterns communs :  $url_rps . '/access'  OU  un endpoint accessapi dédié
// ---------------------------------------------------------------------------
if (!isset($url_accessapi)) {
	// Tente de dériver à partir d'une URL de service connue en remplaçant le préfixe hostname
	// ex. https://rps.eu.clearid.io => https://accessapi.eu.clearid.io (supposition)
	$parsed = parse_url($url_rps);
	$host = @$parsed['host'];
	$scheme = @$parsed['scheme'];
	if ($host) {
		$host_accessapi = preg_replace('/^(.*?)/', 'accessapi', $host);
		$url_accessapi = $scheme . '://' . $host_accessapi;
	} else {
		$url_accessapi = 'https://accessapi.eu.clearid.io';
	}
}

// Écrasement via option CLI si besoin
$options = getopt("", array("locationId:", "identityId:", "includeExpired", "nocsv", "debug"));
$filter_locationId = (isset($options['locationId']) ? $options['locationId'] : '');
$filter_identityId = (isset($options['identityId']) ? $options['identityId'] : '');
$includeExpired = (isset($options['includeExpired']) ? true : false);
$nocsv = (isset($options['nocsv']) ? true : false);
if (isset($options['debug'])) $DEBUG = true;

$logs_dir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
if (!is_dir($logs_dir)) {
	mkdir($logs_dir);
	if ($DEBUG) echo "Répertoire logs créé\n";
}
$debug_filename = $logs_dir . basename(__FILE__, '.php') . '_' . $ENV . '_' . date("Ymd_His") . '.log';

$csv_dir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'csv' . DIRECTORY_SEPARATOR;
if (!is_dir($csv_dir)) {
	mkdir($csv_dir);
	if ($DEBUG) echo "Répertoire CSV créé\n";
}
$csv_filename = $csv_dir . basename(__FILE__, '.php') . '_' . $ENV . '_' . date("Ymd_His") . '.csv';
$csv_filename_web = 'csv' . DIRECTORY_SEPARATOR . basename(__FILE__, '.php') . '_' . $ENV . '_' . date("Ymd_His") . '.csv';

$msg = "Heure de début : " . date("Y-m-d H:i:s") . "\n";
$msg .= "URL base Access API v1 : $url_accessapi\n";
$msg .= "Environnement : $ENV\n";
$msg .= "Filtre locationId : $filter_locationId\n";
$msg .= "Filtre identityId : $filter_identityId\n";
$msg .= "IncludeExpired : " . ($includeExpired ? 'true' : 'false') . "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

// ---------------------------------------------------------------------------
// Chargement des tableaux mis en cache
// ---------------------------------------------------------------------------
$array_identities_file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'array_identities_' . $ENV . '.inc.php';
$array_locations_file  = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'array_locations_'  . $ENV . '.inc.php';
$array_locations_sites_file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'array_locations_sites_' . $ENV . '.inc.php';
$array_sites_file      = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'array_sites_'      . $ENV . '.inc.php';
$array_schedules_file  = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'array_schedules_'  . $ENV . '.inc.php';

if (!file_exists($array_identities_file)) {
	$msg = "ERREUR : fichier manquant $array_identities_file\nVeuillez d'abord exécuter ListIdentiesClearId.php.\n";
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	die();
}
if (!file_exists($array_locations_file)) {
	$msg = "ERREUR : fichier manquant $array_locations_file\nVeuillez d'abord exécuter ListSitesLocationsSchedulesClearId.php.\n";
	echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	die();
}

require_once($array_identities_file);
require_once($array_locations_file);
if (file_exists($array_locations_sites_file)) require_once($array_locations_sites_file);
if (file_exists($array_sites_file)) require_once($array_sites_file);
if (file_exists($array_schedules_file)) require_once($array_schedules_file);

// ---------------------------------------------------------------------------
// En-tête CSV
// ---------------------------------------------------------------------------
if (!$nocsv) {
	$csv = chr(0xEF) . chr(0xBB) . chr(0xBF)
		 . "locationId;locationName;siteId;siteName;identityId;firstName;lastName;externalId;scheduleMapId;scheduleName;startDateTimeUtc;endDateTimeUtc;description;approvedById;approvedDateTimeUtc;approverPrincipalType" . PHP_EOL;
	file_put_contents($csv_filename, $csv);
	echo "Génération du fichier CSV, veuillez patienter ...\n";
}

// ---------------------------------------------------------------------------
// Fonctions utilitaires
// ---------------------------------------------------------------------------
function get_location_accesses($locationId, $includeExpired = false) {
	global $DEBUG_FILE, $debug_filename, $url_accessapi, $account_id, $token;

	$url = $url_accessapi . '/api/v1/accounts/' . $account_id . '/locations/' . $locationId . '/accesses';
	if ($includeExpired) {
		$url .= '?includeExpired=true';
	}

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => array(
			'Authorization: Bearer ' . $token,
			'accept: application/json'
		),
	));

	$response = curl_exec($curl);
	if ($response === false) {
		$msg = "\nErreur Curl : " . curl_error($curl);
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		curl_close($curl);
		return false;
	}

	$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ($http_code < 200 || $http_code > 299) {
		$msg = "\nCode HTTP : $http_code pour la location $locationId\n";
		$msg .= "Réponse : $response\n";
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		curl_close($curl);
		return false;
	}
	curl_close($curl);

	$response = json_decode($response, true);
	return $response;
}

// ---------------------------------------------------------------------------
// Boucle principale sur les locations
// ---------------------------------------------------------------------------
$token = get_token($url_sts, $client_id, $client_secret, $grant_type);

$num_locations = 0;
$num_accesses = 0;
$num_errors = 0;

$locations_to_scan = [];
if (!empty($filter_locationId)) {
	if (isset($array_location[$filter_locationId])) {
		$locations_to_scan[$filter_locationId] = $array_location[$filter_locationId];
	} else {
		$msg = "ERREUR : locationId $filter_locationId introuvable dans array_locations\n";
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		die();
	}
} else {
	$locations_to_scan = $array_location;
}

$total_locations = count($locations_to_scan);

foreach ($locations_to_scan as $locationId => $locationName) {
	$num_locations++;

	// Flush web optionnel
	$msg = "Location $num_locations / $total_locations : $locationName ($locationId)\n";
	if ($num_locations % 10 == 0) echo_flush("\n$msg");
	if ($DEBUG) echo_flush($msg);
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

	$accesses = get_location_accesses($locationId, $includeExpired);
	if ($accesses === false) {
		$num_errors++;
		continue;
	}

	$siteId = '';
	$siteName = '';
	if (isset($array_location_site[$locationId])) {
		$siteId = $array_location_site[$locationId];
		$siteName = isset($array_site[$siteId]) ? $array_site[$siteId] : '';
	}

	$accessModels = isset($accesses['accessModels']) ? $accesses['accessModels'] : [];
	if (empty($accessModels)) {
		$msg = "  -> Aucun accès individuel\n";
		if ($DEBUG) echo_flush($msg);
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	}

	foreach ($accessModels as $identityId => $accessList) {
		// Post-filtre par identityId si demandé
		if (!empty($filter_identityId) && $identityId !== $filter_identityId) {
			continue;
		}

		$firstName = '';
		$lastName  = '';
		$externalId = '';
		if (isset($array_identities[$identityId])) {
			$firstName = $array_identities[$identityId]['firstName'];
			$lastName  = $array_identities[$identityId]['lastName'];
			$externalId = isset($array_identities[$identityId]['externalId']) ? $array_identities[$identityId]['externalId'] : '';
		}

		if (!is_array($accessList)) continue;

		foreach ($accessList as $access) {
			$scheduleMapId = isset($access['scheduleMapId']) ? $access['scheduleMapId'] : '';
			$scheduleName = isset($array_schedule[$scheduleMapId]) ? $array_schedule[$scheduleMapId] : '';
			if (empty($scheduleName) && isset($access['scheduleId']) && !empty($access['scheduleId'])) {
				$scheduleName = isset($array_schedule[$access['scheduleId']]) ? $array_schedule[$access['scheduleId']] : '';
			}

			if (!$nocsv) {
				$csv_row = array(
					'locationId' => $locationId,
					'locationName' => $locationName,
					'siteId' => $siteId,
					'siteName' => $siteName,
					'identityId' => $identityId,
					'firstName' => $firstName,
					'lastName' => $lastName,
					'externalId' => $externalId,
					'scheduleMapId' => $scheduleMapId,
					'scheduleName' => $scheduleName,
					'startDateTimeUtc' => isset($access['startDateTimeUtc']) ? $access['startDateTimeUtc'] : '',
					'endDateTimeUtc' => isset($access['endDateTimeUtc']) ? $access['endDateTimeUtc'] : '',
					'description' => isset($access['description']) ? $access['description'] : '',
					'approvedById' => isset($access['approvedById']) ? $access['approvedById'] : '',
					'approvedDateTimeUtc' => isset($access['approvedDateTimeUtc']) ? $access['approvedDateTimeUtc'] : '',
					'approverPrincipalType' => isset($access['approverPrincipalType']) ? $access['approverPrincipalType'] : ''
				);
				$csv_line = implode(';', $csv_row) . PHP_EOL;
				file_put_contents($csv_filename, $csv_line, FILE_APPEND);
			}

			$num_accesses++;
		}
	}

	// Traite aussi les accès par équipe s'ils existent
	$teamAccessModels = isset($accesses['teamAccessModels']) ? $accesses['teamAccessModels'] : [];
	if (!empty($teamAccessModels)) {
		$msg = "  -> " . count($teamAccessModels) . " entrées d'accès par équipe trouvées\n";
		if ($DEBUG) echo_flush($msg);
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	}
}

// ---------------------------------------------------------------------------
// Récapitulatif
// ---------------------------------------------------------------------------
$msg = "\n" . $num_locations . " locations scannées\n";
$msg .= $num_accesses . " lignes d'accès exportées\n";
$msg .= $num_errors . " erreurs\n";
$msg .= "Heure de fin : " . date("Y-m-d H:i:s") . "\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

if (!$nocsv) {
	echo "\n<a href='$csv_filename_web'>Télécharger le CSV</a>\n";
}

@ob_end_flush();
flush();
?>