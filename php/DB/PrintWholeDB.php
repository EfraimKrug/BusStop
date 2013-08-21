<?php
include '../DB2.php';
/*
 * A general record dump of the database
 * Now working with DB2.php
 * 
 */
function dumpTable($dbObj, $table){
echo "<br>================= " . $table . " ============================";
$dbObj->dumpTable($table);
while($row = $dbObj->getNextRecord())
	{
	echo "<br>";
	foreach ($row as $idx => $val){
		if(!is_int($idx)){
			echo $idx . ": {" . $val . "}";
			}
		}
	}
}

echo "<br>================ D U M P I N G === T H E ===  <U>B U S - S T O P</U> ===  D A T A B A S E  ==========================<br>";
$dbObject = DBFactory::getFactory()->getDB("Local");
$dbObject->dumpTable("BPerson");
dumpTable($dbObject, "BPerson");
$dbObject->dumpTable("BStation");
dumpTable($dbObject, "BStation");
$dbObject->dumpTable("BTime");
dumpTable($dbObject, "BTime");
$dbObject->dumpTable("BPersonStationTime");
dumpTable($dbObject, "BPersonStationTime");
$dbObject->dumpTable("BConnection");
dumpTable($dbObject, "BConnection");


//$dbObject->DBClose();
?>