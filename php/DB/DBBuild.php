<!DOCTYPE html>
<html>
<body>
<?php
$sql = array(	
"CREATE DATABASE busstop",

"DROP TABLE BStation",  
"DROP TABLE BPerson",
"DROP TABLE BTime",
"DROP TABLE BPersonStationTime",
 
"CREATE TABLE BStation
(ID int NOT NULL AUTO_INCREMENT,
LONG_BASE smallint,
LONG_DEC bigint,
LAT_BASE smallint,
LAT_DEC bigint,
FORMATTED_ADDRESS varchar(128),
PRIMARY KEY(ID))",

"CREATE TABLE BPerson  
(ID smallint NOT NULL AUTO_INCREMENT,
NAME varchar(35),
DESCRIPTION varchar(48),
PRIMARY KEY(ID))",

"CREATE TABLE BTime 
(ID smallint NOT NULL AUTO_INCREMENT,
WEEK_DAY varchar(9),
DATE_TIME datetime,
TIME_OF_DAY varchar(8),
PRIMARY KEY (ID))",

"CREATE TABLE BPersonStationTime  
(PERSON_ID smallint NOT NULL,
STATION_ID smallint NOT NULL,
TIME_ID smallint NOT NULL)",
);

include_once '../DB2.php';

$dbObject = DBFactory::getFactory()->getDB("Local");

//for($i=0;$i<6;$i++)
for($i=1;$i<count($sql);$i++)
{
if ($dbObject->runRawSQL($sql[$i]))
  {
  echo "<br>" . stristr($sql[$i],"(", true) . " Successful<br>";
  }
else
  {
  echo "<br>Database Error: " . $dbObject->displayError();
  echo "<br>{{ $sql[$i] }}";
  }
}
$dbObject->DBClose();
?> 

</body>
</html>