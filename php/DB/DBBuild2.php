<!DOCTYPE html>
<html>
<body>
<?php
$sql = array(	
"DROP TABLE BPersonPerson",  
 
"CREATE TABLE BPersonPerson
(ID smallint NOT NULL AUTO_INCREMENT,
ID_01 int NOT NULL,
ID_02 int NOT NULL,
REWARD varchar(128),
POINTS smallint,
PRIMARY KEY(ID))",
);

include_once '../DB2.php';

$dbObject = DBFactory::getFactory()->getDB("Local");

//for($i=0;$i<2;$i++)
for($i=0;$i<count($sql);$i++)
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