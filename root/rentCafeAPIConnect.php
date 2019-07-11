<?php

$apiToken = 'OTMzOTY%3d-enFIA8m7EO4%3d';
$voyagerPropertyCode = 'p1806';
$propertyCode = 'p0697959';
$requestType = (isset($_GET['requestType'])) ? $_GET['requestType'] : 'floorplan';
$floorplanId = $_GET['floorplanId'];

// Build the rentcafe API URL
if($requestType == 'floorplan') {
	$targetURL = "https://api.rentcafe.com/rentcafeapi.aspx?requestType=".$requestType."&apiToken=".$apiToken."&VoyagerPropertyCode=".$voyagerPropertyCode;
}

// The following parameters are passed in the AJAX call
// requestType=apartmentavailability
// &floorplanId=2399541 (floorplanID is dynamic)
else {
	$targetURL = "https://api.rentcafe.com/rentcafeapi.aspx?requestType=".$requestType."&floorplanId=".$floorplanId."&apiToken=".$apiToken."&propertyCode=".$propertyCode;
}

//build curl request
function getSSLPage($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSLVERSION,1);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

// Display the JSON result for use with Javascript floorplans.js
$APIresult=getSSLPage($targetURL);
if (is_numeric(substr($APIresult, -1))){
	echo substr($APIresult, 0, -1);
}

// Uncomment below this line and comment above it to return the rent cafe URL for testing what is coming back directly from rent cafe. Doing so will cause the floor plan search to stop working, but if it is working why are you testing?
// echo "https://api.rentcafe.com/rentcafeapi.aspx?requestType=floorplan&apiToken=$apiToken&VoyagerPropertyCode=$voyagerPropertyCode";

?>