<?php
/*************************************************************
 * post48.php:
 * 1) starts the session
 * 2) retrieves the values from previous form
 * 3) loads values into $_SESSION variables
 * 4) post the station record (identified by geo coordinates)
 * 5) post the new user
 * 6) post the new time
 * 7) post the connection record
 *************************************************************
 *** 1) starts the session   */
session_start();
include 'DB2.php';
$ENVIRONMENT = "Test";

//foreach ($_POST as $k => $v){
//	echo "<br>" . $k . "//" . $v;
//	}
//exit;

echo "<html><head></head><body>";
/* 2) retrieves the values from previous form */
/* 3) loads values into $_SESSION variables */

$Lon2 = $_POST['Lon'];
$Lat2 = $_POST['Lat'];
$Place2 = $_POST['Place1'];
$formatAddress = $_POST['Place1'];
$phone = $_POST['phone'];

$_SESSION['longitude'] = $Lon2;
$_SESSION['latitude'] = $Lat2;
$_SESSION['place'] = $Place2;
$_SESSION['address'] = $formatAddress;
$_SESSION['phone'] = $phone;

if($_POST['name'] == ""){
	if(isset($_COOKIE['user'])){
		$_SESSION['user'] = $_COOKIE['user'];
		}
	}
$_SESSION['user'] = $_POST['name'];

if($_SESSION['user'] == ""){
	header("Location: ../index.php?problem=noname");
	exit;
	}
	
$arrLon = explode('.', $_POST['Lon']);
$arrLat = explode('.', $_POST['Lat']);

/*if($_SERVER["REQUEST_METHOD"] == "POST"){*/
/* 4) post the station record (identified by geo coordinates) */
$lonDec = 0;
$latDec = 0;
if(isset($arrLon[1])){
	$lonDec = (int)substr($arrLon[1],0,7);
	}
if(isset($arrLat[1])){
	$latDec = (int)substr($arrLat[1],0,7);
	}
	$dbObject = DBFactory::getFactory()->getDB($ENVIRONMENT);
	if($dbObject->insertStation($arrLon[0], $lonDec, $arrLat[0], $latDec, $formatAddress))
	{
		echo "<br> Successful Insert<br>";
		$Station = $dbObject->getLastKey();
	}
	else
	{
		if($dbObject->getErrorNumber() < 0)
		{
			$Station = $dbObject->getHold("StationID");
		}
		else 
		{
			echo "<br>Database Error: " . $dbObject->displayError();
		}
	}

/* 5) post the new user */

	$_POST['name'] = preg_replace("/'/", "&apos;", $_POST['name']);
	$_POST['desc'] = preg_replace("/'/", "\'", $_POST['desc']);
	// check if e-mail or phone...
	if(!preg_match("/\@/", $_POST['phone'])){
		$_POST['phone'] = preg_replace("/\D/", "", $_POST['phone']);
		}
	
	if ($dbObject->insertPerson(htmlspecialchars($_POST['name']), htmlspecialchars($_POST['desc']), htmlspecialchars($_POST['phone'])))
	{
		//echo "<br>Successful BPerson Insert";
		$Person = $dbObject->getLastKey();
		$expire=time()+60*60*24*30;
		setcookie("user", $_POST['name'], $expire);
		//echo "HERE";
		//exit;
	}
	else
	{
		if($dbObject->getErrorNumber() < 0)
		{
			$Person = $dbObject->getHold("PersonID");
		}
		else 
		{
			echo "<br>Database Error: " . $dbObject->displayError();
		}
	}

	
	
/* 6) post the new time */
	if ($dbObject->insertTime())
	{
		//echo "<br>Successful BTime insert<br>";
		$Time = $dbObject->getLastKey();
		//echo "<br>" . stristr($sql,"(", true) . " Successful<br>";
	}
	else
	{
		if($dbObject->getErrorNumber() < 0)
		{
			$Time = $dbObject->getHold("TimeID");
		}
		else 
		{
			echo "<br>Database Error: " . $dbObject->displayError();
		}
	}

/**
 * 7) post the connection record 
 * 		- everything is good - show the list of 
 *		everyone here...
 **/
	if($dbObject->insertPST($Person,  $Station, $Time))
	{	
		$HeaderLocation = "WhoIsHere2.php?Lon2=" . $Lon2 . "&Lat2=" . $Lat2 . "&Place2=" . $Place2 . "&name=" . $_POST['name'];
		header("Location: $HeaderLocation");
	}
	else
	{
		echo "<br>Database Error: " . $dbObject->displayError();
	}
/*} */


$dbObject->DBClose();
echo "</body></html>";
?>