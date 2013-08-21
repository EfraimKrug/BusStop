<?php
include 'DB2.php';

// based on now - get minumum and maximum times
// for a 40 minute period
$dtMin = new DateTime(date('Y-m-d H:i:s'));
$dtMax = new DateTime(date('Y-m-d H:i:s'));

$dtMax->add(new DateInterval('P0000-00-00T00:20:00'));
$dtMin->sub(new DateInterval('P0000-00-00T00:20:00'));

//keep the times in two arrays 
$timeMinArr = explode(":", $dtMin->format('H:i'));
$timeMaxArr = explode(":", $dtMax->format('H:i'));

//what day is it?
$wd = date('l');

//analyze geo coordinates from the form -
//parse minumum and maximum
$arrLon = explode('.', $_POST['Lon2']);
$lonMin = (int)substr($arrLon[1],0,5);
$lonMin -= 8;
$lonMax =  $lonMin + 16;
$arrLat = explode('.', $_POST['Lat2']);
$latMin = (int)substr($arrLat[1],0,5);
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
		S.LONG_DEC < $lonMax and
		S.LONG_DEC > $lonMin and
		S.LAT_DEC < $latMax and
		S.LAT_DEC > $latMin and
		T.WEEK_DAY = '$wd'";

	$dbObject = DBFactory::getFactory()->getDB("Local");
	$someoneThere = 0;
	if ($dbObject->selectBX($where))
	{
		while($row = $dbObject->getNextRecord())
		{
		foreach ($row as $idx => $val){
			if(!is_int($idx)){	
				if($idx == "DATE_TIME"){
					if(time_is_right($val)){
						$someoneThere = 1;
						display_record($row);
						}
					}
				}
			}
		}
	if ($someoneThere < 1){
			echo "<br><font color=red>Quote: Alone, alone, all all alone, alone on the wide wide sea...";
			echo "<br>Be the first to text us title and author - win $17";
			echo "<br>Text to: 34546</font>";
			}
	}
}

$dbObject->DBClose();
echo "</body></html>";

function display_record($r){
echo "<br>" . $r['NAME'] . ": " . $r['DESCRIPTION'];
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

function test_time_is_right(){
global $dtMin, $dtMax;
global $timeMinArr, $timeMaxArr;

$tableFind = new DateTime(date('2013-07-17 12:14:25'));

$dtMin = new DateTime(date('2013-07-17 12:35:25'));
$dtMax = new DateTime(date('2013-07-17 12:35:25'));

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