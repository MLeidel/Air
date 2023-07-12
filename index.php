<?php
/***
/air/index.php
Demonstrates the use of three different API with Javascript
in a web page, and session Mmanagement with a cookie.

90210 default zipcode
user must enter zip code or City, ST
location NOT obtained from browser
location for AirNow obtained from loc_zip.db
***/

extract($_POST);

$stateArray = array(
    "AL" => "Alabama",
    "AK" => "Alaska",
    "AZ" => "Arizona",
    "AR" => "Arkansas",
    "CA" => "California",
    "CO" => "Colorado",
    "CT" => "Connecticut",
    "DE" => "Delaware",
    "FL" => "Florida",
    "GA" => "Georgia",
    "HI" => "Hawaii",
    "ID" => "Idaho",
    "IL" => "Illinois",
    "IN" => "Indiana",
    "IA" => "Iowa",
    "KS" => "Kansas",
    "KY" => "Kentucky",
    "LA" => "Louisiana",
    "ME" => "Maine",
    "MD" => "Maryland",
    "MA" => "Massachusetts",
    "MI" => "Michigan",
    "MN" => "Minnesota",
    "MS" => "Mississippi",
    "MO" => "Missouri",
    "MT" => "Montana",
    "NE" => "Nebraska",
    "NV" => "Nevada",
    "NH" => "New Hampshire",
    "NJ" => "New Jersey",
    "NM" => "New Mexico",
    "NY" => "New York",
    "NC" => "North Carolina",
    "ND" => "North Dakota",
    "OH" => "Ohio",
    "OK" => "Oklahoma",
    "OR" => "Oregon",
    "PA" => "Pennsylvania",
    "RI" => "Rhode Island",
    "SC" => "South Carolina",
    "SD" => "South Dakota",
    "TN" => "Tennessee",
    "TX" => "Texas",
    "UT" => "Utah",
    "VT" => "Vermont",
    "VA" => "Virginia",
    "WA" => "Washington",
    "WV" => "West Virginia",
    "WI" => "Wisconsin",
    "WY" => "Wyoming"
);

$cityId = $_COOKIE["AIR"];  // default for weather & Pollen

function getLatLonFromZip($zipcode) {
  $db = new SQLite3('loc_zip.db');
  $sql = "SELECT lat, lon, state from table1 where zip = '". $zipcode . "'";
  $results = $db->query($sql);
  $ar = $results->fetchArray();
  if (!$ar) {
    setcookie("AIR", "90210", (time()+3600)*24*364 );  /* expire in 1 year */
    header("Location: index.php");
  }
  return $ar;
}

function getzip($ziptext) {
  global $stateArray;
  $parts = explode(",", $ziptext);
  $city = trim($parts[0]);
  $stID = trim($parts[1]);
  $stname = $stateArray[$stID];
  $db = new SQLite3('loc_zip.db');
  $sql = "SELECT zip from table1 WHERE state = '$stname' AND city = '$city'";
  $results = $db->query($sql);
  $ar = $results->fetchArray();
  return $ar[0];
}

if (isset($ziptext)) {  // user wants to set cookie
  if (is_numeric($ziptext)) {
    $cityId = $ziptext;
  } else { // lookup city state in loc_zip.db
    $cityId = getzip($ziptext);
  }
  setcookie("AIR", $cityId, time()+60*60*24*365 );  // expire in 30 days
}

$loc = getLatLonFromZip($cityId);

$Api = "http://api.openweathermap.org/data/2.5/weather?zip=$cityId,us&appid=cfca2c7683ae69176bbae12951355fa6";
$response = file_get_contents($Api);
// Decode the JSON response
$data = json_decode($response, true);
// Store the weather information
$WeaInfo = "<h3>" . $data["name"] . "</h3>" .
$loc[2] . "<br>" .
"Temperature: <b>" . round( $data["main"]["temp"] * 9/5 - 459.67) . " </b>degrees<br>" .
"Humidity: " . $data["main"]["humidity"] . "% <br>".
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
  h3 {
    margin: 0;
    padding: 0;
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
  <br>
  <small><a href="aqicnv.html" target="_blank">PM to AQI conversions</a></small>
  <br>
  <!-- This is the Pollen widget with default zip location -->
  <div id="allergyalert" data-aa_account="10647" data-aa_location="53186"
    style="width:300px;height:250px;background-color:#eee;position:relative;display:inline-block;">
  </div>
  <form name="frm" method="post"> <!-- save zip location for weather and Pollen info -->
    <input type="text" name="ziptext" id="ZIP" placeholder="City, ST or zip 99999" />
    <input type="submit" name="sub" value="set" title="Set New Zip Here">
  </form>

  <br>
  <span id="longitude"></span>&nbsp;&nbsp;<span id="latitude"></span>

  </center>
<script>
  var lat = <?php echo $loc[0] ?>;
  var lon = <?php echo $loc[1] ?>;

  var source = "https://widget.airnow.gov/aq-dial-widget/?latitude=" + lat + "&longitude=" + lon;
  // load the control
  JS.doq("#GEO").src = source;  // AirNow API
  JS.doq("#latitude").innerHTML = "Lat: " + lat;
  JS.doq("#longitude").innerHTML = "Lon: " + lon;

  let cval = JS.getCookie( "AIR" );  // persistent zip location for weather and Pollen info
  JS.attr("#allergyalert", "data-aa_location", cval);  // sets requested zip location
  JS.val("#ZIP", cval);
</script>
</body>
</html>
