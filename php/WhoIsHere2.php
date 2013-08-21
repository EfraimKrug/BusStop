<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> 
<html lang="en" > <!--<![endif]-->
<?php
$ENVIRONMENT = "Test";
// check geo coordinates - if we don't have them go back...
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if(isset($_POST['Lat2']) && isset($_POST['Lon2'])){
		if (($_POST['Lat2'] == 0) || ($_POST['Lon2'] == 0)){
			header("Location: ../index.php?problem=nogeo");
			}
		}
	else {
		header("Location: ../index.php?problem=noname");
		}
	}
/*************************************************************
 * WhoIsHere2.php:
 * 1) starts the session
 * 2) finds the username: determines action script for the form
 *		- checks cookie
 *		- checks session
 *		- checks $_GET parameters
 * 3) set the $_SESSION variables from the incoming form
 * 4) figure out the time parameters for attendees at station
 * 5) figure out the geo parameters for attendees at station
 * 6) present form -
 *		- everyone who has signed in at this station/time
 *************************************************************
 * 1) start the session
 * 2) find the username: determines action script for the form
 **/
session_start();
include 'DB2.php';
if(isset($_COOKIE['user'])){
	$name = $_COOKIE['user'];
	}
if (isset($_SESSION['user'])){
	$name = $_SESSION['user'];
	}
if(isset($_GET['name'])){	
	$name = $_GET['name'];
	}

$_SESSION['user'] = $name;

//if($_SESSION['user'] == ""){
//	header("Location: ../index.php?problem=noname");
//	exit;
//	}

 /**
 * 3) set the $_SESSION variables from the incoming form
 **/
if(!isset($_POST['Lon2']))
{
	$Lon2 = $_GET['Lon2'];
}
else 
{
	$Lon2 = $_POST['Lon2'];
}
$_SESSION['longitude'] = $Lon2;
if(!isset($_POST['Lat2']))
{
	$Lat2 = $_GET['Lat2'];
}
else 
{
	$Lat2 = $_POST['Lat2'];
}
$_SESSION['latitude'] = $Lat2;
if(!isset($_POST['Place2']))
{
	$Place2 = $_GET['Place2'];
}
else 
{
	$Place2 = $_POST['Place2'];
}
$_SESSION['place'] = $Place2;

/**
 * 4) figure out the time parameters for attendees at station
 * Station attendance is anyone who arrives on that day of the week
 * within a 40 minute time block. We calculate the 40 minutes by adding
 * and subtracting 20 minutes from the current time
 **/
date_default_timezone_set("UTC");
$TimeZone = new DateTimeZone('America/New_York');
$dtMin = new DateTime('now', $TimeZone);
$dtMax = new DateTime('now', $TimeZone);

//what day is it?
$wd = $dtMin->format('l');

$dI = new DateInterval('P0000-00-00T00:20:00');
$dtMax->add($dI);
$dtMin->sub($dI);

$dtMinF = $dtMin->format('Y-m-d H:i:s');
$dtMaxF = $dtMax->format('Y-m-d H:i:s');

//keep the times in two arrays 
$timeMinArr = explode(":", $dtMin->format('H:i'));
$timeMaxArr = explode(":", $dtMax->format('H:i'));
$timeMin = $dtMin->format('H:i:s');
$timeMax = $dtMax->format('H:i:s');

/**
 * 5) figure out the geo parameters for attendees at station
 * analyze geo coordinates from the form -
 * parse minumum and maximum (about 20 foot radius)
 **/
$arrLon = explode('.', $Lon2);
$lonMin = (int)substr($arrLon[1],0,7);
$lonMin -= 8;
$lonMax =  $lonMin + 16;
$arrLat = explode('.', $Lat2);
$latMin = (int)substr($arrLat[1],0,7);
$latMin -= 8;
$latMax = $latMin + 16;

//*************************************************************
// select clauses to find everyone at the station
// store information in geo coordinates
// but retrieve it by location! let google figure it out...
//*************************************************************
$where = " WHERE  
		P.ID = X.PERSON_ID and
		S.ID = X.STATION_ID and
		T.ID = X.TIME_ID and
		S.FORMATTED_ADDRESS = '" . $Place2 . "' and
		T.WEEK_DAY = '$wd' and
		T.TIME_OF_DAY >= '$timeMin' and
		T.TIME_OF_DAY <= '$timeMax'";

$_where = " WHERE  
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

// in case of midnight riders...
if($timeMin > $timeMax){
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
		((T.TIME_OF_DAY >= '$timeMin' and T.TIME_OF_DAY <= '24:00:00') or
		(T.TIME_OF_DAY <= '$timeMax' and T.TIME_OF_DAY >= '00:00:00'))";
		}
?>
<head><meta charset="utf-8" />
  <meta name="viewport" content="width=device-width" />
  <title>Everybody gotta find somebody!</title>

  <link rel="stylesheet" href="../css/foundation.css" />
  <link rel="stylesheet" href="../css/busstop.css" />
 
  <script src="../js/vendor/custom.modernizr.js"></script>
  <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
  <script type="text/javascript" src="../js/vendor/jquery.js" ></script>
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
				echo "<h4>" . $_SESSION['place'] . "<br>(metaphorically) speaking!</h4>";
				$user = $_SESSION['user'];
			} else {
				$_SESSION['user'] = 'USER';
				echo "<h3>The People... make contact!</h3>";
			} ?>

			<!-- Database access: find other people for the same times/geoCodes/Days -->
			<div class="row">
				<div class="large-12 columns">
					<?php
					/**
					 * 6) present form -
					 *		- everyone who has signed in at this station/time
					 *		- if we don't have user - set form action back to index.php
					 **/

					$firstRow = True;
					$dbObject = DBFactory::getFactory()->getDB($ENVIRONMENT);
					$someoneThere = 0;
					if ($dbObject->selectBX($where))
					{
					if($dbObject->getRowCount() > 0){
						$repeatName = array();
						// no name - allow them to see who is listed.... and go back to index.php
						if($user == ""){
							echo "<table class=wideform><form action='../index.php?problem=noname' method='get'>";
							}
						else {
							echo "<table class=wideform><form action='./foundOne.php' method='post'>";
							}
						echo "<input type=hidden name=lat value=" . $_SESSION['latitude'] . ">";
						echo "<input type=hidden name=lon value=" . $_SESSION['longitude'] . ">";
						echo "<input type=hidden name=name value=" . $_SESSION['user'] . ">";
						while($row = $dbObject->getNextRecord())
						{
							if($firstRow)
							{
								//echo "<h5>" . $row['FORMATTED_ADDRESS'] . "</h5>";
								echo "<input type=hidden name=TimeID value=" . $row['TIME_ID'] . ">";
								echo "<input type=hidden name=StationID value=" . $row['STATION_ID'] . ">";
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
					echo "<tr><td></td><td></td><td></td><td><input type='submit' name='submit' value='Remember!'></td></tr></form></table>";
					}
					// Go get an "alone" quotation... if nobody else is listed!
					// Opportunity to get a gift...
					if ($someoneThere < 1){
						echo "<br><font color=red>Quote: Alone, alone, all all alone, alone on the wide wide sea...";
						echo "<br>Be the first to text us title and author - win something big!";
						echo "<br><br></font><font color=blue>Text to: 34546</font>";
						}
					}
					//$dbObject->DBClose();
					?>
				</div>
			</div>

			<h3><hr></h3>

      <div class="row">
    </div>
	</div>

		<div class="large-4 columns">
			<h4>Getting Started</h4>
			<div id="rightSide">We think this is a great idea... and we hope you do to! When you are standing someplace, waiting and there are others around who are also waiting... 
			maybe you can meet someone else! Check out if they logged onto our site... the logon matches day of the week and time of day and location - so 
			anyone who was waiting in about the same place, about the same time... if they signed in here you will see them!</div>
			<p>
			<div id="rightSide">If you find each other, tell us and get points! 248 points gets you a free meal at a locally participating restaurant!</div>
			</p>
			<h4>How are you doing?</h4>
			<div id="rightSide">
			<?php
				$userID = $_SESSION['user'];
				echo $userID . " Point Tally: ";
				$uNum = $dbObject->getPersonID($userID);
				//echo "NUMBER: " . $uNum;
				$dbObject->getPoints($uNum);
				echo $dbObject->getHold('Points');
			?>
			</div>
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
/**
 * displaying a row of our table
 * @param an array from mysqli
 **/
function display_record($r){
	if($_SESSION['user']  == $r['NAME']){
		echo "<tr><td><div class=itsYou>";
		echo $r['NAME'] . "</div></td><td><div class=description> " . 
		$r['DESCRIPTION'] . "</div></td><td></td><td></td></tr>";
		}
	else {
		echo "<tr><td><div class=name>";
		echo $r['NAME'] . "</div></td><td><div class=description> " . 
		$r['DESCRIPTION'] . "</div></td><td><div class=boxtag>I found them <input type='checkbox' name=I" . 
		$r['NAME'] . " value=True></div></td><td><div class=boxtag>They found me <input type='checkbox' name=U" . 
		$r['NAME'] . " value=True></div></td></tr>";		
		}
}


/**
 * comparing tabled time on accessed record to
 * real system time.... in MinArr and MaxArr
 * arrays are {Hours, Minutes}
 * @param $val - datetime from the database return
 * @return - true - time is within our 40 minute period 
 *		   - false - time is not within...
 * @affect - loads two global arrays with minimum/maximum times
 **/

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

/**
 * test harness for time_is_right...
 **/
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