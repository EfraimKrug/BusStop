<?php
//NO-CHANGE // note for auto-migrate
// June 16: introduced DB Access factory & error handling
/*
 * Database access object- 
 * @return - record set - either 1 row, many rows, or empty
 * @param -
 * For this entire file, it is the client's responsibility to
 * make sure he has already connected to the database...
 */
interface DBInterface
{
	public function DBClose();
	public function dumpTable($table);
	public function getNextRecord();
	public function runRawSQL($sql);
	public function loadUser();
	public function getCurrentUser($UserName, $PassWord);
	public function deleteRecord($table, $index);
	public function deleteRecordByField($table, $field, $index);
}


class DBAccess implements DBInterface
{
	private static $singleDB = False;
	/*
	 * Open the database connection and sign in...
	 */
		var $LastKey = "";
		var $Error = 0;
		var $ErrorMessage = "";
		var $Level = ""; // Prod,Test,Local
		var $resultSet = "";
		var $Row = "";
		var $Connection = "";
		var $RowCount = 0; // only after select...
		var $HoldTag = ""; // HoldTag labels whatever is in Hold...
		var $Hold = "";

	 function DBAccess($level)
	{
		global $singleDB;
		$this->Level = $level;
		if(!$singleDB){
				
			$this->Connection = $this->getConnected();
			$singleDB = True;
			}
	}
	
	public function getHold($tag)
	{
	
		if($this->HoldTag == $tag){
			return $this->Hold;
			}
		return "__NOTHING_HERE__";
	}
	
	private function setHold($tag, $val)
	{
		$this->HoldTag = $tag;
		$this->Hold = $val;
	}
	public function getRowCount()
	{
		return $this->RowCount;
	}
	
	private function setRowCount($rs)
	{
		 $this->RowCount = mysqli_num_rows($rs);
	}
	
	public function getErrorNumber()
	{ 
		return $this->Error;
	}
	
	public function getErrorMessage()
	{
		return $this->ErrorMessage;
	}
	
	/*
	 * getConnected is responsible for connecting to the database - 
	 * including doing whatever is necessary before executing queries
	 * $this->Level - is either: "Prod", "Test", "Local"
	 * getConnected also selects the database... fine and good!
	 */
	private function getConnected()
	{
		if($this->Level == "Prod"){
				try 
				{	
				$DBConn = new mysqli("efraimmkrugcom.domaincommysql.com","busstop","stop_01");
				if(mysqli_connect_errno()){
					$this->ErrorMessage = 'Database connection problems in Production. ';
					throw new DataBaseException();
					}
				if(!$DBConn->select_db("busstop")){
					$this->ErrorMessage = 'Database selection problems in Production: ';
					throw new DataBaseException();
					}
				} 
				catch(DataBaseException $e)
				{
					$this->ErrorMessage .= mysqli_connect_errno();
					$this->Error = 79;					
					echo $e->errorMessage($this->ErrorMessage, $this->Error);
				}
			return $DBConn;

			}

		if($this->Level == "Test"){
				try 
				{	
				$DBConn = new mysqli("localhost","root","");
				if(mysqli_connect_errno()){
					$this->ErrorMessage = 'Database connection problems in Local. ';
					throw new DataBaseException();
					}
				if(!$DBConn->select_db("busstop")){
					$this->ErrorMessage = 'Database selection problems in Local: ';
					throw new DataBaseException();
					}
				} 
				catch(DataBaseException $e)
				{
					$this->ErrorMessage .= mysqli_connect_errno();
					$this->Error = 79;
					echo $e->errorMessage($this->ErrorMessage, $this->Error);
				}
			return $DBConn;
			}
		
		if($this->Level == "Local"){
				try 
				{	
				$DBConn = new mysqli("localhost","root","");
				if(mysqli_connect_errno()){
					$this->ErrorMessage = 'Database connection problems in Local. ';
					throw new DataBaseException();
					}
				if(!$DBConn->select_db("busstop")){
					$this->ErrorMessage = 'Database selection problems in Local: ';
					throw new DataBaseException();
					}
				} 
				catch(DataBaseException $e)
				{
					$this->ErrorMessage .= mysqli_connect_errno();
					$this->Error = 79;
					echo $e->errorMessage($this->ErrorMessage, $this->Error);
				}
			return $DBConn;

		}
	}
	
	public function setLastInsertKey()
	{
		$this->LastKey = mysqli_insert_id($this->Connection);
	}
	
	public function getLastKey()
	{
		return  $this->LastKey;
	}
	/*
	 * Close the database - really! no kidding!
	 */
	public function DBClose()
	{
		try 
		{
			if($this->Connection){
				if(!$this->Connection->close()){
					$this->ErrorMessage = 'Database Connection was NULL... ';
					throw new DataBaseException();
					}
				}
			return; // for whatever reason - sometimes the connection is null.... forget about it!			
		}
		catch(DataBaseException $e)
		{
				$this->ErrorMessage .= 'Could not close the database: ';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 2;	
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
		}
	}

	/*
	 *  There are a number of tables that have a one-piece primary key
	 *  dumpTable can select all the records in those tables and hand 
	 *  them back in a record set.
	 * 
	 *  Client is responsible for connected the database before dumping
	 */
	public function dumpTable($table)
	{
	$tableNames = array("BPerson",  "BStation",  "BTime",  "BPersonStationTime", "BConnection", "BContact"); 
						
	if(in_array($table,$tableNames))
		{
		$sql = "SELECT * FROM $table";
		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not dump the database table $table: ';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 99;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}
		}
	else {
		$this->ErrorMessage = "$table is not a listed table for the 'dump' routine";
		$this->Error = 23;
		}
		
	$this->LastKey = 0;
	}

	
	/*
	 *  Insert Station Record...
	 *  NOTE: in test mode we might make up our geo position
	 *  @param longitude = (base . "." . dec) and latitude = (base . "." . dec) eg: base = 42, dec = 3213443 => longitude = 42.3213443)
	 *  @return insert result
	 */
	public function insertStation($longBase, $longDec, $latBase, $latDec, $fmtAddress)
	{
		//if($this->getStationID($longBase, $longDec, $latBase, $latDec) > 0){
//		echo "<br>in insertStation";
		if($this->getStationID($fmtAddress) > 0){
//			echo "<br>past getStationID(" . $fmtAddress . ")";
			$this->resultSet = null;
			$this->Error = -1;
			$this->ErrorMessage = "This station has already been posted to the database!";
			return $this->resultSet;
			}

		$sql = "INSERT INTO BStation (LONG_BASE, LONG_DEC, LAT_BASE, LAT_DEC, FORMATTED_ADDRESS)
		VALUES (" . (int)$longBase . ", " . (int)$longDec . ", " . (int)$latBase  . ", " . (int)$latDec . ", '" . $fmtAddress . "')";

		//echo $sql;
		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}

		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not insert into the BStation Table';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 99;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}		
		$this->setLastInsertKey();
		return $this->resultSet;
	}

	
	

	/*
	 *  Find Station Record...
	 *  @param $name - data input
	 *  @return 1 row of person
	 */
	//public function getStationID($longBase, $longDec, $latBase, $latDec)
	public function getStationID($fmtAddress)
	{
//		echo "<br>inside getStationID(" . $fmtAddress . ")";
		$sql = "SELECT ID FROM BStation WHERE FORMATTED_ADDRESS = '" . $fmtAddress . "'";
//		$sql = "SELECT ID FROM BStation WHERE LONG_BASE = " . $longBase
//		. " AND LONG_DEC = " . 
//		$longDec . " AND LAT_BASE = " .
//		$latBase . " AND LAT_DEC = " . $latDec;

		//echo $sql;
		//exit;
		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}
			$row = $this->getNextRecord();
			$this->setHold("StationID", $row['ID']);
			return $row['ID'];
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not insert into the BStation Table';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 98;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}		
		return -1;
	}

	/*
	 *  Insert Person Record...
	 *  @param $name/$description - data input
	 *  @return insert result
	 */
	public function insertPerson($name, $description, $phone)
	{
		if($this->getPersonID($name) > 0){
			$this->resultSet = null;
			$this->Error = -1;
			$this->ErrorMessage = $name . " has already been posted to the database!";
			return $this->resultSet;
			}
			
		$sql = "INSERT INTO BPerson (NAME, DESCRIPTION, PHONE)
		VALUES ('" . $name . "', '" . $description . "', '" . $phone . "')";		

		//echo $sql;
		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not insert into the BPerson Table';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 98;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}		
		$this->setLastInsertKey();
		return $this->resultSet;
	}

	

	/*
	 *  Find Person Record...
	 *  @param $name - data input
	 *  @return 1 row of person
	 */
	public function getPersonID($name)
	{
		$sql = "SELECT ID FROM BPerson WHERE NAME = '" . $name . "'";
		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}
			
			$row = $this->getNextRecord();
			$this->setHold("PersonID", $row['ID']);
			return $row['ID'];
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not select from the BPerson Table';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 98;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}		
		return -1;
	}

	/*
	 *  Insert Current Time Record...
	 *  @param 
	 *  @return insert result
	 */
	public function insertTime()
	{
	date_default_timezone_set("UTC");
	$TimeZone = new DateTimeZone('America/New_York');
	$DtTime = new DateTime('now', $TimeZone);

	$dt = $DtTime->format('Y-m-d H:i:s');
	$tod = $DtTime->format('H:i:s');
	$wd = $DtTime->format('l');
	//echo "$dt, $tod, $wd";
	//exit;
		if($this->getTimeID($wd, $dt) > 0){
			$this->resultSet = null;
			$this->Error = -1;
			$this->ErrorMessage = $wd . " @ " . $tod . " has already been posted to the database!";
			return $this->resultSet;
			}

	$sql = "INSERT INTO BTime (WEEK_DAY, DATE_TIME, TIME_OF_DAY)
			VALUES ('" . $wd . "', '" . $dt . "', '" . $tod . "')";		

		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not insert into the BTime Table';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 97;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}		
		$this->setLastInsertKey();
		return $this->resultSet;
	}
	

	/*
	 *  Find Time Record...
	 *  @param $name - data input
	 *  @return 1 row of person
	 */
	public function getTimeID($wd, $tod)
	{
	date_default_timezone_set("UTC");
	$TimeZone = new DateTimeZone('America/New_York');
	//$DtTime = new DateTime('now', $TimeZone);
	$dtMin = new DateTime('now', $TimeZone);
	$dtMax = new DateTime('now', $TimeZone);

	$dI = new DateInterval('P0000-00-00T00:02:00');
	$dtMax->add($dI);
	$dtMin->sub($dI);

	$dtMinF = $dtMin->format('H:i:s');
	$dtMaxF = $dtMax->format('H:i:s');

		$sql = "SELECT ID FROM BTime WHERE WEEK_DAY = '" . $wd . "' AND TIME_OF_DAY <= '" . $dtMaxF . "' AND TIME_OF_DAY >= '" . $dtMinF . "'";
		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}
			$row = $this->getNextRecord();
			$this->setHold("TimeID", $row['ID']);
			return $row['ID'];
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not insert into the BTime Table';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 98;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}		
		return -1;
	}
	

	/*
	 *  Insert PST - ie PersonStationTime...
	 *  @param 
	 *  @return insert result
	 */
	public function insertPST($Person, $Station, $Time)
	{
	$sql = "INSERT INTO BPersonStationTime (PERSON_ID, STATION_ID, TIME_ID)
			VALUES (" . $Person . "," . $Station . "," . $Time . ")";		

		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not insert into the BPersonStationTime Table';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 96;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}		
		$this->setLastInsertKey();
		return $this->resultSet;
	}
	
	/*
	 *  Find PST Record...
	 *  @param $name - data input
	 *  @return 1 row of person
	 */
	public function getPSTID($personID, $stationID, $timeID)
	{

		$sql = "SELECT ID FROM BPersonStationTime WHERE PERSON_ID=" . $personID .
				" AND STATION_ID=" . $stationID . 
				" AND TIME_ID=" . $timeID;
		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}
			$row = $this->getNextRecord();
			$this->setHold("PSTID", $row['ID']);
			return $row['ID'];
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not select from the BPersonStationTime Table';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 98;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}		
		return -1;
	}
	

	/*
	 *  Insert BConnection - ie Tally station!
	 *  @param 
	 *  @return insert result
	 */
	public function insertConnection($personID, $otherPersonID, $reasonNumber, $reasonPoints, $timeID,$stationID)
	{
	$sql = "INSERT INTO BConnection (PERSON_ID, OTHER_PERSON_ID, REASON_NUMBER, POINTS, TIME_ID, STATION_ID) ";
	$sql .= " VALUES ($personID, $otherPersonID, $reasonNumber, $reasonPoints, $timeID, $stationID)";
	//echo $sql;
	//exit;
		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}
			echo "Successful store";
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not insert into the BConnection Table';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 96;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}		
		$this->setLastInsertKey();
		return $this->resultSet;
	}
	

	
	/*
	 *  Get points ...
	 *  @param $name - data input
	 *  @return int: number of points tallied
	 */
	public function getPoints($personID)
	{

		$sql = "SELECT SUM(POINTS) FROM BConnection WHERE PERSON_ID=" . $personID;
		//echo "INSIDE getPoints";
		//echo $sql;
		//exit;
		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}
			$this->setHold("Points", 0);
			$row = $this->getNextRecord();
			//foreach ($row as $e=>$v){
			//	echo "<br>{" . $e . "}//" . $v;
			//	}
			//exit;
			if(isset($row['SUM(POINTS)'])){
				$this->setHold("Points", $row['SUM(POINTS)']);
				//echo "HERE: " . $row['SUM(POINTS)'];
				//exit;
				}
			return True;
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not select from the BConnection Table';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 98;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}		
		return False;
	}
	
	
	/*
	 *  Distinct categories within the program
	 *  @param $Program - int from database lookup
	 *  @return result set list of category records
	 */
	public function selectBX($where="")
	{
	
		$sql = "SELECT * FROM BPerson as P, BStation as S, BTime as T, BPersonStationTime as X" . $where;
		//$sql = "SELECT DISTINCT NAME, DESCRIPTION, FORMATTED_ADDRESS, DATE_TIME FROM BPerson as P, BStation as S, BTime as T, BPersonStationTime as X" . $where;
		//echo $sql;
		//exit;
		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not dump the BPerson Table';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 99;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}		
		$this->setRowCount($this->resultSet);
		return $this->resultSet;
	}
	
	/*
	 * get all the user records - used primarily to load user/password to $_SESSION
	 */
	public function loadUser()
	{
		$sql = "SELECT USER_NAME, PASSWORD FROM User";
		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not select the User table $table: ';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 101;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}
	}

	/*
	 * Gets exactly one user record. If the username/password pair are not valid - we return null
	 */
	public function getCurrentUser($UserName, $PassWord)
	{
		$sql = "SELECT * FROM User WHERE USER_NAME = '" . $UserName . "' AND PASSWORD = '" . $PassWord . "'";
		try 
		{
			if(!$this->resultSet = $this->Connection->query($sql)){
				throw new DataBaseException();
			}
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage = 'Could not get Current User $UserName ';
				$this->ErrorMessage .= mysql_error();
				$this->Error = 103;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}
	}

	
	/*
	 * The client is responsible for writing valid SQL
	 */
	public function runRawSQL($sql)
	{
		try 
		{
			mysqli_query($this->Connection, $sql);
			if(mysqli_error($this->Connection)){
				throw new DataBaseException();
				}
			$this->LastKey = mysqli_insert_id($this->Connection);
			return True;
		} 
		catch(DataBaseException $e)
		{
			$this->Row = False;
			$this->resultSet = False;
			$this->ErrorMessage = 'Could not run your sql:<br> ';
			$this->ErrorMessage .= mysql_error();
			$this->ErrorMessage .= "<br>SQL: " . $sql . "**";
			$this->Error = 107;
			echo $e->errorMessage($this->ErrorMessage, $this->Error);
			return False;
		}
	}


	
	/*
	 * This can only delete fields from the connection tables given one key (index)
	 * the field must be Course, Category, or Instructor keys 
	 */
	public function deleteRecordByField($table, $field, $index){
	$tableNames = array("CourseCategory" => "ID", "InstructorCourse" => "ID");
						
	if(!$this->Connection){
			$this->getConnected();
			}

	if(isset($tableNames[$table]))
		{
		$sql = "DELETE FROM " . $table . " WHERE " . $field . " = " . $index;
		try 
		{
				mysqli_query($this->Connection, $sql);
				if(mysqli_error($this->Connection)){
					throw new DataBaseException();
					}
			
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage .= 'Could not delete from table $table: ';
				$this->ErrorMessage .= mysqli_error($this->Connection);
				$this->Error = 109;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}
		}	
	}

	/*
	 * Allowing delete on primary key only
	 */
	public function deleteRecord($table, $index){
	$tableNames = array("Course" => "ID",  "Program" => "ID", "ProgramType" => "TYPE", 
						"Category" => "ID", "Instructor" => "ID", "Institution" => "ID");
						
	if(!$this->Connection){
			$this->getConnected();
			}
	if(isset($tableNames[$table]))
		{
		$sql = "DELETE FROM " . $table . " WHERE " . $tableNames[$table] . " = " . $index;
		try 
		{
				mysqli_query($this->Connection, $sql);
				if(mysqli_error($this->Connection)){
					throw new DataBaseException();
					}
			
		} 
		catch(DataBaseException $e)
			{
				$this->ErrorMessage .= 'Could not delete record from $table: ';
				$this->ErrorMessage .= mysqli_error($this->Connection);
				$this->Error = 99;
				echo $e->errorMessage($this->ErrorMessage, $this->Error);
			}
		}	
	}
	
	/*
	 * Returning next row from the table - as an array... there are
	 * actually two arrays - one indexed and one keyed - notice there
	 * are twice as many fields as one would ever expect
	 */
	public function getNextRecord()
	{
		try 
		{
			$this->Row = $this->resultSet->fetch_array(MYSQLI_BOTH);
			if(mysql_errno() > 0){
				throw new DataBaseException();
				}
			return $this->Row;
		} 
		catch(DataBaseException $e)
		{
			$this->Row = False;
			$this->ErrorMessage = 'Could not get next record ';
			$this->ErrorMessage .= mysql_error();
			//$this->ErrorMessage .= "{{$sql}}";
			$this->Error = 101;
			echo $e->errorMessage($this->ErrorMessage, $this->Error);
		}	
			
	}
	
	public function displayError(){	
		echo "Error Message: " . $this->ErrorMessage;
		echo "Error Code: " . $this->Error;
	}
}
/*
 * tying down the try-catch routine
 */
class DataBaseException extends Exception
{
	public function errorMessage($msg = "")
	{
		
		$errorMsg = 'Error on line '.$this->getLine().' in '.$this->getFile()
		.': <b>'.$this->getMessage();
		$errorMsg .= "//////" . $msg;
		return $errorMsg;
	}
}

/*
 * At some point we may have to scale - this factory is here
 * to make the code a bit more flexible
 */
class DBFactory
{
    private static $factory;
    public static function getFactory()
    {
        if (!self::$factory)
            self::$factory = new DBFactory();
        return self::$factory;
    }

    private $db = null;

    public function getDB($level) {
	global $db;
        if (!$db){
            $db = new DBAccess($level);
			}
        return $db;
    }
}

?>

