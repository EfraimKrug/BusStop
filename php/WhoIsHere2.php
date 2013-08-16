<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> 
<html lang="en" > <!--<![endif]-->
<?php
session_start();
include 'DB2.php';
// based on 'now' - get minumum and maximum times
// for a 40 minute period
// Create date objects, add/subtract time, then format them to strings

date_default_timezone_set("UTC");
$TimeZone = new DateTimeZone('America/New_York');
//$DtTime = new DateTime('now', $TimeZone);
$dtMin = new DateTime('now', $TimeZone);
$dtMax = new DateTime('now', $TimeZone);

$dI = new DateInterval('P0000-00-00T00:20:00');
$dtMax->add($dI);
$dtMin->sub($dI);

$dtMinF = $dtMin->format('Y-m-d H:i:s');
$dtMaxF = $dtMax->format('Y-m-d H:i:s');

//$dtMax = new DateTime(date('Y-m-d H:i:s'));


//keep the times in two arrays 
$timeMinArr = explode(":", $dtMin->format('H:i'));
$timeMaxArr = explode(":", $dtMax->format('H:i'));
$timeMin = $dtMin->format('H:i:s');
$timeMax = $dtMax->format('H:i:s');

//what day is it?
$wd = date('l');

//analyze geo coordinates from the form -
//parse minumum and maximum
$arrLon = explode('.', $_POST['Lon2']);
$lonMin = (int)substr($arrLon[1],0,7);
$lonMin -= 8;
$lonMax =  $lonMin + 16;
$arrLat = explode('.', $_POST['Lat2']);
$latMin = (int)substr($arrLat[1],0,7);
$latMin -= 8;
$latMax = $latMin + 16;

if($_SERVER["REQUEST_METHOD"] == "POST"){
//SELECT EVERYONE WHO COMES TO THE STATION AT THE TIME...	
$where = " WHERE  
		P.ID = X.PERSON_ID and
		S.ID = X.STATION_ID and
		T.ID = X.TIME_ID and
		S.LONG_BASE = $arrLon[0] and
		S.LAT_BASE = $arrLat[0] and
		S.LONG_DEC <= $lonMax and
		S.LONG_DEC >= $lonMin and
		S.LAT_DEC <= $latMax and
		S.LAT_DEC >= $latMin and
		T.WEEK_DAY = '$wd' and
		T.TIME_OF_DAY >= '$timeMin' and
		T.TIME_OF_DAY <= '$timeMax'";
		}
?>
<head><meta charset="utf-8" />
  <meta name="viewport" content="width=device-width" />
  <title>I Wonder Who Is Here!</title>

  <link rel="stylesheet" href="../css/foundation.css" />
  <link rel="stylesheet" href="../css/busstop.css" />
 
  <script src="../js/vendor/custom.modernizr.js"></script>
  <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
  <script type="text/javascript" src="../js/vendor/jquery.js" ></script>

<script type="text/javascript">
function doit(){
	alert('hello');
	}
</script>
<script>
// this script is flipping the picture - individuals to people engaged in 
// animated conversation... 
$(document).ready(function() {
	$('.circular').mouseover(function()
		{
			jQuery('.circular').css("background-image", "url('./img/meeting04.jpg')");
		});
	$('.circular').mouseout(function()
		{
		
			$(".circular").stop().animate({opacity: 0},1000,function(){
					$(this).css({'background-image': "url('./img/BWCrowd.jpg')"})
					.animate({opacity: 1},{duration:1000});
				});
		});
	$('#Place1').val('kansas');
	getLocation();
	});
</script>
<script>
// geolocation code
function getLocation()
  {
  if (navigator.geolocation)
    {
    navigator.geolocation.getCurrentPosition(showPosition);
    }
  }
function showPosition(position)
  {
  $('#Latitude1').val(position.coords.latitude);
  $('#Latitude2').val(position.coords.latitude);
  $('#Longitude1').val(position.coords.longitude);
  $('#Longitude2').val(position.coords.longitude);
  }
</script>
<style>
.circular {
	width: 300px;
	height: 300px;
	border-radius: 150px;
	-webkit-border-radius: 150px;
	-moz-border-radius: 150px;
	background: url('./img/BWCrowd.jpg') no-repeat; 
	}
</style>
</head>
<body>
	<div class="row">
		<div class="large-12 columns">
			<h2>Waiting For The (metaphorical) Bus</h2>
			<hr />
		</div>
	</div>

	<div class="row">
		<div class="large-8 columns">
			<?php 
			if(isset($_SESSION['user']))
			{
				echo "<h3>The people... welcome you (metaphorically) back!</h3>";
			} else {
				$_SESSION['user'] = 'USER';
				echo "<h3>The People... make contact!</h3>";
			} ?>

			<!-- Database access: find other people for the same times/geoCodes/Days -->
			<div class="row">
				<div class="large-6 columns">
					<?php
					$firstRow = True;
					$dbObject = DBFactory::getFactory()->getDB("Local");
					$someoneThere = 0;
					if ($dbObject->selectBX($where))
					{
					if($dbObject->getRowCount() > 0){
						$repeatName = array();
						echo "<table><form action='./foundOne.php' method='post'>";
						while($row = $dbObject->getNextRecord())
						{
							if($firstRow)
							{
								echo "<h5>" . $row['FORMATTED_ADDRESS'] . "</h5>";
								$firstRow = False;
							}
							foreach ($row as $idx => $val){
							if(!is_int($idx)){	
								if($idx == "DATE_TIME"){
									if(!in_array($row['NAME'], $repeatName)){
										if(time_is_right($val)){
											$someoneThere = 1;
											display_record($row);
											$repeatName[] = $row['NAME'];
											}
										}
									}
								}
							}
						}
					echo "<tr><td></td><td></td><td><input type='submit' name='submit' value='Remember!'></td></tr></form></table>";
					}
					// Go get an "alone" quotation... if nobody else is listed!
					// Opportunity to get a gift...
					if ($someoneThere < 1){
						echo "<br><font color=red>Quote: Alone, alone, all all alone, alone on the wide wide sea...";
						echo "<br>Be the first to text us title and author - win something big!";
						echo "<br><br></font><font color=blue>Text to: 34546</font>";
						}
					}
					$dbObject->DBClose();
					?>
				</div>
			</div>

			<h3><hr></h3>

      <div class="row">
    </div>
	</div>

		<div class="large-4 columns">
			<h4>Getting Started</h4>
			<p>We think this is a great idea... and we hope you do to! When you are standing someplace, waiting and there are others around who are also waiting... 
			maybe you can meet someone else! Check out if they logged onto our site... the logon matches day of the week and time of day and location - so 
			anyone who was waiting in about the same place, about the same time... if they signed in here you will see them!</p>
			<p>If you find each other, tell us and get points! 248 points gets you a free meal at a locally participating restaurant!</p>

			<h4>Other Stuff</h4>
			<!--
			<p>You should check out:</p>
			<ul class="disc">
				<li><a href="http://foundation.zurb.com/docs">Foundation Documentation</a><br />Everything you need to know about using the framework.</li>
				<li><a href="http://github.com/zurb/foundation">Foundation on Github</a><br />Latest code, issue reports, feature requests and more.</li>
				<li><a href="http://twitter.com/foundationzurb">@foundationzurb</a><br />Ping us on Twitter if you have questions. If you build something with this we'd love to see it (and send you a totally boss sticker).</li>
			</ul>
			-->
		</div>
	</div>

  <script>
  document.write('<script src=' +
  ('__proto__' in {} ? 'js/vendor/zepto' : 'js/vendor/jquery') +
  '.js><\/script>')
  </script>
  
  <script src="js/foundation.min.js"></script>
  
  <script src="js/foundation/foundation.js"></script>
  
  <script src="js/foundation/foundation.interchange.js"></script>
  
  <script src="js/foundation/foundation.dropdown.js"></script>
  
  <script src="js/foundation/foundation.placeholder.js"></script>
  
  <script src="js/foundation/foundation.forms.js"></script>
  
  <script src="js/foundation/foundation.alerts.js"></script>
  
  <script src="js/foundation/foundation.magellan.js"></script>
  
  <script src="js/foundation/foundation.reveal.js"></script>
  
  <script src="js/foundation/foundation.tooltips.js"></script>
  
  <script src="js/foundation/foundation.clearing.js"></script>
  
  <script src="js/foundation/foundation.cookie.js"></script>
  
  <script src="js/foundation/foundation.joyride.js"></script>
  
  <script src="js/foundation/foundation.orbit.js"></script>
  
  <script src="js/foundation/foundation.section.js"></script>
  
  <script src="js/foundation/foundation.topbar.js"></script>
 
  
  <script>
    $(document).foundation();
  </script>
</body>
</html>
<?php
function display_record($r){
	echo "<tr><td><div class=name>" . 
		$r['NAME'] . "</div></td>:<td><div class=description> " . 
		$r['DESCRIPTION'] . "</div></td><td><input type='checkbox' name=" . $r['NAME'] . " value=True></td></tr>";
}

// comparing tabled time on accessed record to
// real system time.... in MinArr and MaxArr
// arrays are {Hours, Minutes}

function time_is_right($val){
global $timeMinArr, $timeMaxArr;
$dt = new datetime($val);
$tableTimeArr = explode(":", $dt->format('H:i'));

if(($tableTimeArr[0] == $timeMinArr[0]) && ($tableTimeArr[0] == $timeMaxArr[0])){
	if(($tableTimeArr[1] >= $timeMinArr[1]) && ($tableTimeArr[1] <= $timeMaxArr[1])){
			return True;
			}
	else {
			return False;
			}
	}
if(($tableTimeArr[0] > $timeMinArr[0]) && ($tableTimeArr[0] == $timeMaxArr[0])){
	if($tableTimeArr[1] <= $timeMaxArr[1]){
			return True;
			}
	else {
			return False;
			}
	}

if(($tableTimeArr[0] == $timeMinArr[0]) && ($tableTimeArr[0] < $timeMaxArr[0])){
	if($tableTimeArr[1] >= $timeMinArr[1]){
			return True;
			}
	else {
			return False;
			}
	}
}

/*
 * test harness for time_is_right...
 */
function test_time_is_right(){
global $dtMin, $dtMax;
global $timeMinArr, $timeMaxArr;

date_default_timezone_set("UTC");
$TimeZone = new DateTimeZone('America/New_York');
$DtTime = new DateTime('now', $TimeZone);
//$FormattedDate = $DtTime->format('Y-m-d H:i:s');

$tableFind = new DateTime($DtTime);
$dtMin = $tableFind;
$dtMax = $tableFind;
	
//$tableFind = new DateTime(date('2013-07-17 12:14:25'));

//$dtMin = new DateTime(date('2013-07-17 12:35:25'));
//$dtMax = new DateTime(date('2013-07-17 12:35:25'));

$dtMax->add(new DateInterval('P0000-00-00T00:20:00'));
$dtMin->sub(new DateInterval('P0000-00-00T00:20:00'));

$timeMinArr = explode(":", $dtMin->format('H:i'));
$timeMaxArr = explode(":", $dtMax->format('H:i'));

$i = 0;
while ($i < 15){
	$i++;
	echo "<br>Minimum: " . $dtMin->format('Y-m-d H:i:s');
	echo "<br>Maximum: " . $dtMax->format('Y-m-d H:i:s');
	echo "<br>Target: {{" . $tableFind->format('Y-m-d H:i:s') . "}}";
	if(time_is_right($tableFind->format('Y-m-d H:i:s'))){
		echo "MIDDLE";
		}
	else {
		echo "NOT";
		}
	$tableFind->add(new DateInterval('P0000-00-00T00:13:00'));
	}
}
	
?>