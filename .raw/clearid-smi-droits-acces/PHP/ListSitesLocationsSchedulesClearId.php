<?php
/*
 * https://frsellpappepa02.atlas.hermes/clearid/accctrl/ListSitesLocationsSchedulesClearId.php
 * /var/www/clearid/accctrl/ListSitesLocationsSchedulesClearId.php
 *
 * options : 
 * --noarray : dont generate array file
 * --nocsv : dont generate csv file
 *
 * Ex:
 * C:\exe\php-8.2.13-Win32-vs16-x64\php.exe ListSitesLocationsSchedulesClearId.php --noarray
 * C:\exe\php-8.2.13-Win32-vs16-x64\php.exe ListSitesLocationsSchedulesClearId.php --nocsv
 *
 */

set_time_limit(0);
date_default_timezone_set('Europe/Paris');

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

require_once('functions.inc.php');

//$ENV = 'demo';
$ENV = 'prod';

$DEBUG = false;
//$DEBUG_FILE = false;
$DEBUG_FILE = true;

$ADD_PHOTO_IF_NO_PHOTO = true;

require_once('api_url.inc.php');
require_once('api_functions.inc.php');

$options = getopt("", array("noarray", "nocsv"));
//print_r($options);
$noarray = (array_key_exists('noarray', $options) ? true : false);
$nocsv = (array_key_exists('nocsv', $options) ? true : false);

echo "<pre>\n";
echo "ClearID : liste des sites / secteurs / horaires\n\n";

//$photo_path = "C:\\Users\\c_akhemi\\Downloads\\";
//$photo_path2 = '/C:/Users/c_akhemi/Downloads/';
//$photo_path = "C:\\temp\\";
$photo_path = DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR;
$photo_name = '';
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
$php_array_sites = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'array_sites_' . $ENV . '.inc.php';
$php_array_locations = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'array_locations_' . $ENV . '.inc.php';
$php_array_locations_sites = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'array_locations_sites_' . $ENV . '.inc.php';
$php_array_schedules = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'array_schedules_' . $ENV . '.inc.php';

if ($DEBUG) echo "$ENV $url_sts\n";

$origin = new DateTimeImmutable(date("Y-m-d H:i:s"));
$msg = "Start time : " . date("Y-m-d H:i:s") . "\n";
echo_flush($msg);
if ($DEBUG_FILE) {
	file_put_contents($debug_filename, $msg, FILE_APPEND);
	if ($DEBUG) echo "Writing log to " . $debug_filename . " ... \n";
}

if ($noarray == false) {
	// PHP array site
	$array_site = "<?php" . PHP_EOL . "\$array_site = array(" . PHP_EOL;
	file_put_contents($php_array_sites, $array_site);
	// PHP array location
	$array_location = "<?php" . PHP_EOL . "\$array_location = array(" . PHP_EOL;
	file_put_contents($php_array_locations, $array_location);
	// PHP array location/site
	$array_location_site = "<?php" . PHP_EOL . "\$array_location_site = array(" . PHP_EOL;
	file_put_contents($php_array_locations_sites, $array_location_site);
	// PHP array horaire
	$array_schedule = "<?php" . PHP_EOL . "\$array_schedule = array(" . PHP_EOL;
	file_put_contents($php_array_schedules, $array_schedule);
}
$all_schedules = [];

if ($nocsv == false) {
	// CSV header + BOM
	$csv = chr(0xEF) . chr(0xBB) . chr(0xBF) . "siteId;siteName;siteIsDeleted;locationId;locationName;locationVisibility;scheduleId;scheduleName;scheduleState" . PHP_EOL;
	file_put_contents($csv_filename, $csv);
	echo_flush("Generating CSV file, please wait ...\n");
}

/*partie 1: Get TOKEN ClearID API */
$token = get_token($url_sts, $client_id, $client_secret, $grant_type);

//Recupération des sites
$num = 0;

$sites = get_sites();
//print_r($sites);
//$locations = get_locations();
//print_r($locations); die();

/*  Pour chaque site ClearID */
foreach ($sites as $site) {
	$num++;

	$msg = "Site " . $num . " : " . $site['siteId'] . " " . $site['siteName'] . "\n";
	if ($DEBUG) echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

	if ($noarray == false) {
		$array_site = "  \"" . $site['siteId'] . "\" => \"" . $site['siteName'] . "\"," . PHP_EOL;
		file_put_contents($php_array_sites, $array_site, FILE_APPEND);
	}

//	$locations_site = @$locations[$site['siteId']];
	$locations_site = get_locations_from_site($site['siteId']);
// echo $site['siteId']  . " - " . $site['siteName'] . PHP_EOL;
// print_r($locations_site);
	if (!empty($locations_site)) {
		
		/*  Pour chaque locations dans site ClearID */
		foreach($locations_site as $location) {
			if ($noarray == false) {
				$array_location = "  \"" . $location['locationId'] . "\" => \"" . $location['locationName'] . "\"," . PHP_EOL;
				file_put_contents($php_array_locations, $array_location, FILE_APPEND);
				$array_location_site = "  \"" . $location['locationId'] . "\" => \"" . $site['siteId'] . "\"," . PHP_EOL;
				file_put_contents($php_array_locations_sites, $array_location_site, FILE_APPEND);
			}

			$schedules = get_schedules_from_location($location['locationId']);
// echo "horaires : " . $location['locationName'] . PHP_EOL;
// print_r($schedules);
			
			if (!empty($schedules)) {
				/*  Pour chaque schedules dans location ClearID */
				foreach ($schedules as $schedule) {
					$all_schedules[$schedule['scheduleId']] = $schedule['scheduleName'];
					if ($nocsv == false) {
						$csv_a = array('siteId' => $site['siteId'], 
										'siteName' => $site['siteName'], 
										'siteIsDeleted' => $site['siteIsDeleted'], 
										'locationId' => $location['locationId'], 
										'locationName' => $location['locationName'], 
										'locationVisibility' => $location['locationVisibility'],
										'scheduleId' => $schedule['scheduleId'], 
										'scheduleName' => $schedule['scheduleName'], 
										'scheduleState' => $schedule['scheduleState']
									);
						//print_r($csv_a);
						$csv = implode(';', $csv_a) . PHP_EOL;
						file_put_contents($csv_filename, $csv, FILE_APPEND);
					}
				}
			}
			else {
				if ($nocsv == false) {
					$csv_a = array('siteId' => $site['siteId'], 
									'siteName' => $site['siteName'], 
									'siteIsDeleted' => $site['siteIsDeleted'], 
									'locationId' => $location['locationId'], 
									'locationName' => $location['locationName'], 
									'locationVisibility' => $location['locationVisibility'],
									'scheduleId' => '', 
									'scheduleName' => '', 
									'scheduleState' => ''
								);
					//print_r($csv_a);
					$csv = implode(';', $csv_a) . PHP_EOL;
					file_put_contents($csv_filename, $csv, FILE_APPEND);
				}
			}
		}
	}
	else {
		if ($nocsv == false) {
			$csv_a = array('siteId' => $site['siteId'], 
							'siteName' => $site['siteName'], 
							'siteIsDeleted' => $site['siteIsDeleted'], 
							'locationId' => '',
							'locationName' => '',
							'locationVisibility' => '',
							'scheduleId' => '', 
							'scheduleName' => '', 
							'scheduleState' => ''
						);
			//print_r($csv_a);
			$csv = implode(';', $csv_a) . PHP_EOL;
			file_put_contents($csv_filename, $csv, FILE_APPEND);
		}
	}
}

if ($noarray == false) {
	$array_site = ");" . PHP_EOL;
	file_put_contents($php_array_sites, $array_site, FILE_APPEND);
	$array_location = ");" . PHP_EOL;
	file_put_contents($php_array_locations, $array_location, FILE_APPEND);
	$array_location_site = ");" . PHP_EOL;
	file_put_contents($php_array_locations_sites, $array_location_site, FILE_APPEND);
	foreach ($all_schedules as $k => $v) {
		$array_schedule = "  \"" . $k . "\" => \"" . $v . "\"," . PHP_EOL;
		file_put_contents($php_array_schedules, $array_schedule, FILE_APPEND);
	}
	$array_schedule = ");" . PHP_EOL;
	file_put_contents($php_array_schedules, $array_schedule, FILE_APPEND);
}

$msg = $num . " sites done\n";
echo $msg;
if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

$target = new DateTimeImmutable(date("Y-m-d H:i:s"));
$interval = $origin->diff($target);
$msg = "\nEnd time : " . date("Y-m-d H:i:s");
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