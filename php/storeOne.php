<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> 
<html lang="en" > <!--<![endif]-->
<?php
session_start();
include 'DB2.php';
//include './XML/xmlManipulation.php';
//foreach ($_POST as $k=>$v){
//	echo "<br>" . $k . "- - - >" . $v;
//	}
//exit;
$ENVIRONMENT = "Test";

/**
 * we get the 'found/was found' information... 
 * form - what happened / get phone number
 **/
 
/* only one reason is processed at a time */
$reason = array();
$reason = explode("@", $_POST['reason']);
$reasonNumber = $reason[2];
$reasonPoints = $reason[1];

/* active (I)/passive partners (U) */
$Uname = array();
$Iname = array();

foreach ($_POST as $k => $v){
	if(preg_match("/^UNAME/", $k)){
		$Uname[] = $v;
		}
	if(preg_match("/^INAME/", $k)){
		$Iname[] = $v;
		}
	}
	
$dbObject = DBFactory::getFactory()->getDB($ENVIRONMENT);
$userID = $dbObject->getPersonID($_POST['name']);
$timeID = $_POST['TimeID'];
$stationID = $_POST['StationID'];
//$PSTID = $dbObject->getPSTID($userID, $stationID, $timeID);

$UnameNumbers = array();
$InameNumbers = array();
foreach ($Uname as $u){
	$UnameNumbers[$u] = $dbObject->getPersonID($u);
	}
foreach ($Iname as $i){
	$InameNumbers[$i] = $dbObject->getPersonID($i);
	}

$insertArray = array();
//$timeID = getTimeID();
//$pstID = getPST_ID();

foreach ($UnameNumbers as $e){
	//echo "<br>Storing: $e";
	$dbObject->insertConnection($userID, $e, $reasonNumber, $reasonPoints, $timeID, $stationID);
	//$xml = "INSERT INTO BConnection (PERSON_ID, PST_ID, OTHER_PERSON_ID, REASON_NUMBER, POINTS, TIME_ID, STATION_ID) ";
	//$xml .= " VALUES ($userID, $PSTID, $e, $reasonNumber, $reasonPoints, $timeID, $stationID)";
		//$dbObject = DBFactory::getFactory()->getDB($ENVIRONMENT);
	//	$dbObject->runRawSQL($xml);
	}

foreach ($InameNumbers as $e){
	//echo "<br>Storing: $e";
	$dbObject->insertConnection($userID, $e, $reasonNumber, $reasonPoints, $timeID, $stationID);
		//$dbObject = DBFactory::getFactory()->getDB($ENVIRONMENT);
		//$dbObject->runRawSQL($xml);
	}

header("Location: ../index.php?problem=nothing");		
/**
 * "CREATE TABLE BConnection  
 * (ID smallint NOT NULL AUTO_INCREMENT,
 * PERSON_ID smallint NOT NULL,
 * PST_ID smallint NOT NULL,
 * OTHER_PERSON_ID smallint NOT NULL,
 * REASON_NUMBER smallint NOT NULL,
 * POINTS smallint NOT NULL,
 * TIME_ID smallint NOT NULL,
 **/
?>
<html>
<head>
<meta charset="utf-8" />
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

			<!-- Database access: find other people for the same times/geoCodes/Days -->
			<div class="row">

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
			<h4>Phone Number?</h4>
			<div id="rightSide">When you get your points... we need to be able to get back in touch with you! Alright, it is true,  we will post winners
			on our website... that's fine... so if you want to give us a fake phone number and then check back at our website - that's also good. However,
			the winnings are only going to be good for a little while - then we give them to someone else!</div>

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