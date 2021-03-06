<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> 
<html lang="en" > <!--<![endif]-->
<?php
/**
 * for testing:
 *
 *foreach ($_POST as $k => $v){
 *	echo "<br>" . $k . " - - >" . $v;
 *	}
 *exit;
 **/
// end testing...

/*************************************************************
 * index.php:
 * 1) starts the session
 * 2) checks parameter for returning with errors...
 *		- if we got further along with no 'user name'
 * 3) retrieves cookies for returning visitor
 * 4) presents 2 forms
 * 		- 1 entry for user
 *		- 2 just to see who is here - you can get a list 
 *			without giving any info at all
 *************************************************************
 *** 1) starts the session   */
session_start();
include 'DB2.php';
include './XML/xmlManipulation.php';
$ENVIRONMENT = "Test";
/**
 * we get the 'found/was found' information... 
 * form - what happened / get phone number
 * remember - anything on most is the record of the other participant...
 **/
$message = "";
if($_POST['name'] == $_SESSION['user']){
	$message = "Self enlightenment is a wonderful thing...";
	}
	
$stationID = 0;
$timeID = 0;
if(isset($_POST['StationID'])){
	$stationID = $_POST['StationID'];
	}
if(isset($_POST['TimeID'])){
	$timeID = $_POST['TimeID'];
	}

/******************************************************************
 * there are two types of possible connections
 * one - our person (user) was active (I)
 * two - our person (user) was passive (U)
 * and each person he/she connects to also can be either
 ******************************************************************/
$IotherName = array();
$UotherName = array();
foreach ($_POST as $e => $v){
	if(preg_match("/^I/", $e)){
		$IotherName[] = preg_replace("/^I/", "", $e);
		}
	if(preg_match("/^U/", $e)){
		$UotherName[] = preg_replace("/^U/", "", $e);
		}
	}
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
		<?php 
			if(isset($_SESSION['user']))
			{
				echo "<i>Remember, everyone checks in for themselves!</i>";
				echo "<h4> What did we do? (Metaphorically) speaking!</h4>";
			} 
			/**
			 * only one action with any number of people can be logged at a time
			 * action: from select
			 * people: U (I was the passive recipient) or I (I was the active) people...
			 * eg: 2 people find me... U: (me) and I: (the two people)
			 * or: Me and someone else find another: I and someone else are Inames, another is Uname.
			 **/
			$arr = getWholeArray();
			echo "<table width=365>";
			echo "<form action='./storeOne.php' method='post'><tr><td>What did you do? <select name=reason>";
				
			foreach ($arr as $x => $y){
				//$hold = $y['reason'] . "@" . $y['points'] . "@" . $y['number'];
				//echo "<option name='" . $y['reason'] . "@" . $y['points'] . "@" . $y['number']  . "'>" . $y['reason'] . "[" . $hold . "]" ;
				echo "<option value='" . $y['reason'] . "@" . $y['points'] . "@" . $y['number']  . "'>" . $y['reason'];
				}
			echo "</select></td></tr>";
			if(isset($_SESSION['phone'])){
				echo "<tr><td>Phone Number: <input type='text' name='phone' size='10' value='" . $_SESSION['phone'] . "'></tr></td>";
				}
			else {
				echo "<tr><td>Phone Number: <input type='text' name='phone' size='10' placeholder='to identify you for gifts!'></tr></td>";
				}
			echo "<tr><td><input type='submit' name='submit' value='I did that!'></tr></td>";
			echo "<input type=hidden name=lat value=" . $_SESSION['latitude'] . ">";
			echo "<input type=hidden name=lon value=" . $_SESSION['longitude'] . ">";
			echo "<input type=hidden name=name value=" . $_SESSION['user'] . ">";
			echo "<input type=hidden name=StationID value=" . $stationID . ">";
			echo "<input type=hidden name=TimeID value=" . $timeID . ">";

			$i = 0;
			foreach ($IotherName as $n){
				echo "<input type=hidden name=INAME" . $i++ . " value=" . $n . ">";
				}
			$i = 0;
			foreach ($UotherName as $n){
				echo "<input type=hidden name=UNAME" . $i++ . " value=" . $n . ">";
				}

			echo "</form></table>";
		?>

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