<?php
include 'DB2.php';

echo "<html><head></head><body>";

if($_SERVER["REQUEST_METHOD"] == "POST"){
//post the BStation
foreach ($_POST as $e => $v){
	echo "<br>:$e: :$v:";
	}
exit;
$lonDec = 0;
$latDec = 0;
if(isset($arrLon[1])){
	$lonDec = (int)substr($arrLon[1],0,5);
	}
if(isset($arrLat[1])){
	$latDec = (int)substr($arrLat[1],0,5);
	}
	$dbObject = DBFactory::getFactory()->getDB("Local");
	if($dbObject->insertStation($arrLon[0], $lonDec, $arrLat[0], $latDec))
	{
		echo "<br> Successful Insert<br>";
	}
	else
	{
		echo "<br>Database Error: " . $dbObject->displayError();
		echo "{{ $sql }}";
	}
	
	$Station = $dbObject->getLastKey();

//post the BPerson
	$_POST['name'] = preg_replace("/'/", "&apos;", $_POST['name']);
	$_POST['desc'] = preg_replace("/'/", "\'", $_POST['desc']);
	if ($dbObject->insertPerson(htmlspecialchars($_POST['name']), htmlspecialchars($_POST['desc'])))
	{
		echo "<br>Successful BPerson Insert";
		//echo "<br>" . stristr($sql,"(", true) . " Successful<br>";
	}
	else
	{
		echo "<br>Database Error: " . $dbObject->displayError();
		echo "{{ $sql }}";
	}

	$Person = $dbObject->getLastKey();
	
//post the BTime
	if ($dbObject->insertTime())
	{
		echo "<br>Successful BTime insert<br>";
		//echo "<br>" . stristr($sql,"(", true) . " Successful<br>";
	}
	else
	{
		echo "<br>Database Error: " . $dbObject->displayError();
		//echo "{{ $sql }}";
	}
	$Time = $dbObject->getLastKey();

	if($dbObject->insertPST($Person,  $Station, $Time))
	{
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