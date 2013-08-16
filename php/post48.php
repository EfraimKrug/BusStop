<?php
session_start();
include 'DB2.php';
//if(isset($_COOKIE['user'])){
//	echo $_COOKIE['user'];
//	}

if(!isset($_SESSION['user']))
{
	$_SESSION['user'] = 'USER';
}
//foreach ($_POST as $e => $v){
//	echo "<br>[$e] -- >>$v<< --";
//	}
//exit;
echo "<html><head></head><body>";
$arrLon = explode('.', $_POST['Lon']);
$arrLat = explode('.', $_POST['Lat']);
$formatAddress = $_POST['Place1'];

if($_SERVER["REQUEST_METHOD"] == "POST"){
//post the BStation
$lonDec = 0;
$latDec = 0;
if(isset($arrLon[1])){
	$lonDec = (int)substr($arrLon[1],0,7);
	}
if(isset($arrLat[1])){
	$latDec = (int)substr($arrLat[1],0,7);
	}
	$dbObject = DBFactory::getFactory()->getDB("Local");
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

//post the BPerson
	$_POST['name'] = preg_replace("/'/", "&apos;", $_POST['name']);
	$_POST['desc'] = preg_replace("/'/", "\'", $_POST['desc']);
	if ($dbObject->insertPerson(htmlspecialchars($_POST['name']), htmlspecialchars($_POST['desc'])))
	{
		echo "<br>Successful BPerson Insert";
		$Person = $dbObject->getLastKey();
		$expire=time()+60*60*24*30;
		setcookie("user", $_POST['name'], $expire);
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

	
	
//post the BTime
	if ($dbObject->insertTime())
	{
		echo "<br>Successful BTime insert<br>";
		$Time = $dbObject->getLastKey();
		//echo "<br>" . stristr($sql,"(", true) . " Successful<br>";
	}
	else
	{
		echo "<br>Database Error: " . $dbObject->displayError();
		//echo "{{ $sql }}";
	}


	if($dbObject->insertPST($Person,  $Station, $Time))
	{	
		echo "<br>$Person, $Station, $Time";
		echo "<br>Successful BPersonStationTime Insert<br>";
	}
	else
	{
		echo "<br>Database Error: " . $dbObject->displayError();
	}
}


$dbObject->DBClose();
echo "</body></html>";
?>