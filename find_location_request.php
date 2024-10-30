<?php
require_once('comment-geo-maps.php');
$data = cgm_dogeocode($_GET['location']);
$status = substr($data,0,3);
if (strcmp($status, "200") == 0) {
  // Successful geocode
  $data = explode(",",$data);
  // Format: Longitude, Latitude, Altitude
  $lon = $data[3];
  $lat = $data[2];
  echo "{'lat':$lat, 'lon':$lon, 'status': $status}";
} else {
  // failure to geocode
  echo "{'status': $status}";
}
?>
