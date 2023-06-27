<?php
/***
/air/index.php
Demonstrates the use of three different API with Javascript
in a web page, and session Mmanagement with a cookie.
***/

extract($_POST);

$cityId = "53186";  // default for weather & Pollen

if (isset($ziptext)) {  // user wants to set cookie
  setcookie("AIR", $ziptext, time()+60*60*24*365 );	// expire in 30 days
  $cityId = $ziptext;
}
// replace "appid" with your registered id
$Api = "http://api.openweathermap.org/data/2.5/weather?zip=$cityId,us&appid=xxxxxxxxxxxxxxxxxxxx";
$response = file_get_contents($Api);
// Decode the JSON response
$data = json_decode($response, true);
// Store the weather information
$WeaInfo =
"City: " . $data["name"] . "<br>" .
"Temperature: <b>" . round( $data["main"]["temp"] * 9/5 - 459.67) . " </b>degrees<br>" .
"Wind: " . round($data["wind"]["speed"] * 2.2369362920544) . " mph<br>" .
"Description: " . $data["weather"][0]["description"] . "<br>";
?>

<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset='UTF-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1'>
	<title>Air Web Widget</title>
  <script src="//www.pollenapps.com/df/tools/aa/js/one.1.0.min.js"></script>
  <link rel="stylesheet" href="//www.pollenapps.com/df/tools/aa/css/load.css">
  <script type="text/javascript" src="../js/myJS-1.2.min.js"></script>
  <style>
  body {
    font: normal 14pt sans-serif;
  }
  span {
    font: normal 10pt sans-serif;
  }
  </style>
</head>

<body>
<br><br>
  <center>

  <!-- Weather info from openweathermap -->
  <?php echo $WeaInfo ?>
  <br>

  <!-- This is the AQI API (widget) -->
  <iframe title="AQI" height="340" id="GEO"
    style="border: none; border-radius: 35px;" width="230">  <!-- source set in showPosition -->
  </iframe>
  <br><br>
  <!-- This is the Pollen widget with default zip location -->
  <div id="allergyalert" data-aa_account="10647" data-aa_location="53186"
    style="width:300px;height:250px;background-color:#eee;position:relative;display:inline-block;">
  </div>
  <form name="frm" method="post"> <!-- save zip location for weather and Pollen info -->
  	<input type="text" name="ziptext" placeholder="save your zip code here" title="AQI Geo Loc only" />
  	<input type="submit" name="sub" value="set">
  </form>

  <br>
  <span id="longitude"></span>&nbsp;&nbsp;
  <span id="latitude"></span>

  </center>
<script>

  function getLocation() {
    // Check if Geolocation is supported.
    if (navigator.geolocation) {
      // Get the user's current location.
      navigator.geolocation.getCurrentPosition(showPosition, showError);
    } else {
      // Geolocation is not supported.
      document.getElementById("latitude").innerHTML = "Geolocation is not supported.";
      document.getElementById("longitude").innerHTML = "";
    }
  }

  function showPosition(position) {
    // Get the latitude and longitude.
    var latitude = position.coords.latitude;
    var longitude = position.coords.longitude;
    var source = "https://widget.airnow.gov/aq-dial-widget/?latitude=" + latitude + "&longitude=" + longitude;
    // load the control
    JS.doq("#GEO").src = source;  // AirNow API
    JS.doq("#latitude").innerHTML = "Lat: " + latitude;
    JS.doq("#longitude").innerHTML = "Lon: " + longitude;
  }

  function showError(error) {
    // if error display the error message.
    switch (error.code) {
      case 0:
        JS.doq("#latitude").innerHTML = "Unknown error.";
        break;
      case 1:
        JS.doq("#latitude").innerHTML = "Permission denied.";
        break;
      case 2:
        JS.doq("#latitude").innerHTML = "Position unavailable.";
        break;
      case 3:
        JS.doq("#latitude").innerHTML = "Timeout.";
        break;
    }
    JS.doq("#longitude").innerHTML = "";
  }

  let cval = JS.getCookie( "AIR" );  // persistent zip location for weather and Pollen info
  JS.attr("#allergyalert", "data-aa_location", cval);  // sets requested zip location
  getLocation();  // get lat & lon from browser, and set AQI widget
</script>
</body>
</html>
