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
"DROP TABLE BConnection",
"DROP TABLE BContact",
 
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
PHONE varchar(128),
PRIMARY KEY(ID))",

"CREATE TABLE BTime 
(ID smallint NOT NULL AUTO_INCREMENT,
WEEK_DAY varchar(9),
DATE_TIME datetime,
TIME_OF_DAY varchar(8),
PRIMARY KEY (ID))",

"CREATE TABLE BPersonStationTime  
(ID smallint NOT NULL AUTO_INCREMENT,
PERSON_ID smallint NOT NULL,
STATION_ID smallint NOT NULL,
TIME_ID smallint NOT NULL,
PRIMARY KEY (ID))",

"CREATE TABLE BConnection  
(PERSON_ID smallint NOT NULL,
OTHER_PERSON_ID smallint NOT NULL,
REASON_NUMBER smallint NOT NULL,
POINTS smallint NOT NULL,
TIME_ID smallint NOT NULL,
STATION_ID smallint NOT NULL)",

"CREATE TABLE BContact
(ID smallint NOT NULL AUTO_INCREMENT,
PERSON_ID smallint NOT NULL,
POINTS_LAST_SENT smallint NOT NULL,
TIME_LAST_SENT datetime,
PRIMARY KEY (ID))"

);

include_once '../DB2.php';

$dbObject = DBFactory::getFactory()->getDB("Test");

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