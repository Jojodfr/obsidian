<?php
/*
 * Get token
 *
 */
function get_token($url_sts, $client_id, $client_secret, $grant_type) {
	global $DEBUG, $DEBUG_FILE, $debug_filename;

	/* Get TOKEN ClearID API */
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
		$msg = "\nCurl error - get_token : " . curl_error($curl1);
		$msg .= "\nCurl HTTP code : " . curl_getinfo($curl1, CURLINFO_HTTP_CODE);
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
		$msg = "Token error : " . json_encode($response1);
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		die();
	}

	$token = $response1['access_token'];
	$msg = "\nLe token est : " . substr($token, 0, 10) . "....\n";
	if ($DEBUG) echo $msg;
	if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);

	return $token;
}


/*
 * Get all sites
 *
 */
function get_sites() {
	global $DEBUG_FILE, $debug_filename, $url_siteservice, $account_id, $token;

	$curl = curl_init();

	//echo $url_siteservice . '/api/v2/accounts/' . $account_id . '/sites', . "\n";
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url_siteservice . '/api/v2/accounts/' . $account_id . '/sites',
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

	$response = curl_exec($curl);
	//print_r($response);

	// test retour curl
	if ($response === false) {
		$msg = "\nCurl errror : " . curl_error($curl);
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		die();
	}
	curl_close($curl);

	$response = json_decode($response, true);
	//print_r($response);

	$sites = $response['sites'];

	/*  Pour chaque location ClearID */
	$sites_a = [];
	foreach ($sites as $site) {
		$sites_a[$site['siteId']] = array('siteId' => $site['siteId'], 'siteName' => $site['name'], 'siteIsDeleted' => $site['isDeleted']);
	}
//print_r($sites_a); echo count($sites_a); die();
	return $sites_a;
}

/*
 * Get all sites
 *
 */
function get_locations_from_site($siteId) {
	global $DEBUG_FILE, $debug_filename, $url_locationservice, $account_id, $token;

	if (empty($siteId)) {
		die("Error - missing siteId");
	}

	$curl = curl_init();

	//echo $url_siteservice . '/api/v2/accounts/' . $account_id . '/sites', . "\n";
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations?Take=200&SiteId=' . $siteId,
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

	$response = curl_exec($curl);
	//print_r($response);

	// test retour curl
	if ($response === false) {
		$msg = "\nCurl errror : " . curl_error($curl);
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		die();
	}
	curl_close($curl);

	$response = json_decode($response, true);
	//print_r($response);

	$locations = $response['results'];
	if (count($locations) != $response['totalItems']) {
		echo "WARNING : totalItems " . $response['totalItems'] . " !=  returned results " . count($locations) . PHP_EOL;
		// update Take=xxxx to somethin higher
	}

	/*  Pour chaque location ClearID */
	$locations_a = [];
	foreach ($locations as $location) {
		$locations_a[$location['locationId']] = array('siteId' => $location['siteId'], 'locationId' => $location['locationId'], 'locationName' => $location['name'], 'locationVisibility' => $location['visibility']);
	}
//print_r($locations_a); echo count($locations_a); die();
	return $locations_a;
}

/*
 * Get all locations
 *
 */
function get_locations() {
	global $DEBUG_FILE, $debug_filename, $url_locationservice, $account_id, $token;

	$curl = curl_init();

	//echo $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations' . "\n";
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations?Take=1000',
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

	$response = curl_exec($curl);
	//print_r($response);

	// test retour curl
	if ($response === false) {
		$msg = "\nCurl errror : " . curl_error($curl);
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		die();
	}
	curl_close($curl);

	$response = json_decode($response, true);
	//print_r($response);

	$locations = $response['results'];
	if (count($locations) != $response['totalItems']) {
		echo "WARNING : totalItems " . $response['totalItems'] . " !=  returned results " . count($locations) . PHP_EOL;
		// update Take=xxxx to somethin higher
	}

	/*  Pour chaque location ClearID */
	$locations_a = [];
	foreach ($locations as $location) {
		$locations_a[$location['siteId']][$location['locationId']] = array('siteId' => $location['siteId'], 'locationId' => $location['locationId'], 'locationName' => $location['name'], 'locationVisibility' => $location['visibility']);
	}
//print_r($locations_a); echo count($locations_a); die();
	return $locations_a;
}

/*
 * Get schedules from location
 *
 */
function get_schedules_from_location($locationId) {
	global $DEBUG_FILE, $debug_filename, $url_locationservice, $account_id, $token;
	
	if (empty($locationId)) {
		die("Error - missing locationId");
	}

	$curl = curl_init();

	//echo $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations' . "\n";
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
			'Authorization: Bearer ' . $token
		),
	));

	$response = curl_exec($curl);
	//print_r($response);

	// test retour curl
	if ($response === false) {
		$msg = "\nCurl errror : " . curl_error($curl);
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		die();
	}
	curl_close($curl);

	$response = json_decode($response, true);
	//print_r($response);

	$schedules = $response['schedules'];

	/*  Pour chaque location ClearID */
	$schedules_a = [];
	foreach ($schedules as $schedules) {
		$schedules_a[$schedules['scheduleId']] = array('scheduleId' => $schedules['scheduleId'], 'scheduleName' => $schedules['name'], 'scheduleState' => $schedules['state']);
	}
	
	return $schedules_a;
}

/*
 * Get identity
 *
 */
function get_identity($identityId) {
	global $DEBUG_FILE, $debug_filename, $url_identityservice, $account_id, $token;

	if (empty($identityId)) return false;

	$curl = curl_init();

	//echo $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations' . "\n";
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url_identityservice . '/api/v3/accounts/' . $account_id . '/identities/' . $identityId,
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

	$response = curl_exec($curl);
	//print_r($response);

	// test retour curl
	if ($response === false) {
		$msg = "\nCurl errror : " . curl_error($curl);
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		die();
	}
	curl_close($curl);

	$response = json_decode($response, true);
	//print_r($response);

	return $response;
}

/*
 * Get identity by externalId
 *
 */
function get_identity_by_externalid($externalId) {
	global $DEBUG_FILE, $debug_filename, $url_identityservice, $account_id, $token;

	if (empty($externalId)) return false;
	
	$url = $url_identityservice . '/api/v3/accounts/' . $account_id . '/identities?externalId=' . $externalId;
//echo $url; die();

	$curl = curl_init();

	//echo $url_locationservice . '/api/v3/accounts/' . $account_id . '/locations' . "\n";
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
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

	$response = curl_exec($curl);
	//print_r($response);

	// test retour curl
	if ($response === false) {
		$msg = "\nCurl errror : " . curl_error($curl);
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
		die();
	}
	curl_close($curl);

	$response = json_decode($response, true);
	//print_r($response);

	return $response;
}


/*
 * Import identity (prestataires externe)
 *
 */
function import_identity($WorkerType, $Company, $Manager, $Department, $LocationCode, $Worklocation, $Division, $FisrtName, $LastName, $MissionTitle, $Arrival, $Departure, $SitePrincipal, $ExternalCompany, $costCenter, $ManagerExtId, $ManagerId, $ManagerEmail, $ExternalId) {
	global $DEBUG_FILE, $debug_filename, $url_identityservice, $account_id, $token;

	$curl = curl_init();

	$url = $url_identityservice . '/api/v3/accounts/' . $account_id . '/identities';
//echo $url . "\n";
	$json = '{
    "companyData": {
        "approvers": [
            {
                "approverId": "' . $ManagerId . '"
            }
        ],
        "supervisorName": "' . $Manager . '",
        "departmentName": "' . $Department . '",
        "jobTitle": "' . $MissionTitle . '",
        "siteId": "72b455d1-5e21-46a1-afef-390b2f95b67d",
        "companyName": "' . $Company . '",
        "workerTypeDescription": "Externe"
    },
    "systemData": {
        "externalId": "' . $ExternalId . '",
        "customFields": [
            {
                "customFieldType": "Text",
                "customFieldName": "login_atlas",
                "customFieldValue": ""
            },
            {
                "customFieldType": "Boolean",
                "customFieldName": "collaborator",
                "customFieldValue": "false"
            },
            {
                "customFieldType": "Date",
                "customFieldName": "start_date",
                "customFieldValue": "' . $Arrival . '"
            },
            {
                "customFieldType": "Date",
                "customFieldName": "end_date",
                "customFieldValue": "' . $Departure . '"
            },
            {
                "customFieldType": "Text",
                "customFieldName": "division",
                "customFieldValue": "' . $Division . '"
            },
            {
                "customFieldType": "Text",
                "customFieldName": "mch_etablissement",
                "customFieldValue": ""
            },
            {
                "customFieldType": "Text",
                "customFieldName": "abu_building_name",
                "customFieldValue": ""
            },
            {
                "customFieldType": "Text",
                "customFieldName": "manager_id",
                "customFieldValue": "' . $ManagerExtId . '"
            },
            {
                "customFieldType": "Text",
                "customFieldName": "mch_contract_type",
                "customFieldValue": ""
            },
            {
                "customFieldType": "Text",
                "customFieldName": "work_location",
                "customFieldValue": ""
            },
            {
                "customFieldType": "Text",
                "customFieldName": "contract_type",
                "customFieldValue": "SST"
            },
            {
                "customFieldType": "Text",
                "customFieldName": "external_company",
                "customFieldValue": "' . $ExternalCompany . '"
            },
            {
                "customFieldType": "Text",
                "customFieldName": "manager_email",
                "customFieldValue": "' . $ManagerEmail . '"
            },
            {
                "customFieldType": "Text",
                "customFieldName": "cost_center",
                "customFieldValue": "' . $costCenter . '"
            }
        ]
    },
    "status": "Active",
    "firstName": "' . $FisrtName . '",
    "lastName": "' . $LastName . '",
    "displayName": "' . $FisrtName . ' ' . $LastName . '",
    "countryCode": "FRA"
}';

//    "email": "' . $ExternalId . '@atlas.hermes",
//echo $json . "\n";

	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $json,
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
		$msg = "\nCurl errror : " . curl_error($curl);
		echo $msg;
		if ($DEBUG_FILE) file_put_contents($debug_filename, $msg, FILE_APPEND);
	}
	curl_close($curl);

	$response = json_decode($response, true);
// print_r($response);
// die();

	return $response;
}
