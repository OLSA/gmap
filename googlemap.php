<?php
// googlemap.php for Google map Joomla plugin, v1.0.0

// load Joomla framework
define( '_JEXEC', 1 );
define( 'DS', DIRECTORY_SEPARATOR );
define( 'JPATH_BASE', realpath( dirname(__FILE__).DS.'..'.DS.'..'.DS.'..' ) );
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

$app = JFactory::getApplication('site');
$app->initialise();

// check for token
$token = false;
$token = JSession::checkToken( 'get' );
if ($token === false){
	header('Location: /index.php?');
	exit();
}

// GET map params
$lat = $app->input->get('lat', 0.1, 'FLOAT');
$lng = $app->input->get('lng', 0.1, 'FLOAT');

$address = trim($app->input->getString('address', ''));
$address = $address ? str_replace(' ', ', ', $address) : '';

$maptype = trim($app->input->getString('maptype', 'roadmap'));

// settings mode
$setmode = 0;
$setmode = $app->input->get('setmode', 0, 'INT');

// define map center
$center_by_address = ( $lat && $lng ) ? false : true;

// marker title
$title = $address ? str_replace('+', ' ', $address) : 'Our Location';
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Google map</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <style>
      html, body, #map-canvas{
        height: 100%;
		    margin: 0px auto;
        padding: 0px
      }      
    </style>
    <script>
var geocoder;
var map;
function initialize() {
geocoder = new google.maps.Geocoder();
  var latlng = new google.maps.LatLng(<?php echo $lat.','.$lng; ?>);
  var mapOptions = {
    zoom: 15,
    center: latlng,
    mapTypeId: <?php echo json_encode($maptype);?>
  };
  var map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);
  var marker = new google.maps.Marker({
		position: latlng,
		map: map,
		title: <?php echo json_encode($title);?>
	});
<?php if ($center_by_address) : ?>
	geocoder.geocode( { 'address': <?php echo json_encode($address);?>}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			map.setCenter(results[0].geometry.location);
			marker.setPosition(results[0].geometry.location);
		} else {
			alert('Geocode was not successful for the following reason: ' + status);
		}
	});
<?php endif; ?>
<?php if ($setmode): ?>
	google.maps.event.addListener(map, "rightclick", function(event) {
		var lat = event.latLng.lat();
		var lng = event.latLng.lng();
		var zoomLevel = map.getZoom();
		var msg = "lat=" + lat.toFixed(6) + " "+"lng=" + lng.toFixed(6) + "\n"+"zoom=" + zoomLevel;
		alert( msg );
	});
<?php endif; ?>
}
function loadScript() {
  var script = document.createElement('script');
  script.type = 'text/javascript';
  script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&' +
      'callback=initialize';
  document.body.appendChild(script);
}
window.onload = loadScript;
</script>
</head>
  <body>
    <div id="map-canvas"></div>
  </body>
</html>