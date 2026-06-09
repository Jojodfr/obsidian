<?php
if ($ENV == 'demo') {
	$client_id = '2a9327aa-bf77-4e58-a6b9-eb5e5e7e9808';
	$client_secret = 'Pdfjx7uNcDFMQDKTx8ScSzn0lzsXkkpriVoV98FxIcA';
	$grant_type = 'client_credentials';
	$account_id = 'x9qxn4iaq2';

	$url_sts = 'https://sts-demo.clearid.io';
	$url_identityservice = 'https://identityservice-demo.clearid.io';
	$url_searchservice = 'https://searchservice-demo.clearid.io';
	$url_locationservice = 'https://locationservice-demo.clearid.io';
	$url_siteservice = 'https://siteservice-demo.clearid.io';
	$url_roleservice = 'https://roleservice-demo.clearid.io';
	$url_vrservice = 'https://vrservice-demo.clearid.io';
	$url_rps = 'https://rps-demo.clearid.io';
	$url_principalservice = 'https://principalservice-demo.clearid.io';
	$url_systemservice = 'https://systemservice-demo.clearid.io';
}
else {
	// Interface Postman-ClearID (don't use)
	# $client_id = 'f4614e6e-9555-45d8-ae28-c503f3064068';
	# $client_secret = 'Way-YH8lm7cgoJR6ViNLVXSKbbEO185ZmJ2V6bVQJoo';
	// Scripts PHP-ClearID
	$client_id = '270fd542-e7e5-457e-b47a-31952f9158f3';
	$client_secret = '3JNaxwwUAw_liVQdA3Qp9ZiYqv0XGFQ2SPDvTXDtmdU';

	$grant_type = 'client_credentials';
	$account_id = 'j3gg5ror3f';

	$url_sts = 'https://sts.eu.clearid.io';
	$url_identityservice = 'https://identityservice.eu.clearid.io';
	$url_searchservice = 'https://searchservice.eu.clearid.io';
	$url_locationservice = 'https://locationservice.eu.clearid.io';
	$url_siteservice = 'https://siteservice.eu.clearid.io';
	$url_roleservice = 'https://roleservice.eu.clearid.io';
	$url_vrservice = 'https://vrservice.eu.clearid.io';
	$url_rps = 'https://rps.eu.clearid.io';
	$url_principalservice = 'https://principalservice.eu.clearid.io';
	$url_systemservice = 'https://systemservice.eu.clearid.io';
}
