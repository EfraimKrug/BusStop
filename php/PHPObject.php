<?php
//study in php standard functions:

//ArrWorkBegin
interface ArrInterface
{
	public function openFile();
	public function readLine();
	public function setFileName();
	public function getFileName();
}

//ArrObject Class
class ArrObject implements ArrInterface
{
		var $Arr = array();

	 function ArrObject($fname = "PHPObject.php")
	{
		$this->Arr = array("this", "that", "the", "other" ,"and","also","the","final");
	}

//setArr
function setArr($arr){
	$this->Arr = $arr;
	}

//prtArr	
function prtArr(){
	foreach($this->Arr as $e){
		echo "<br>$e";
		}
	}

function getArr(){
	return $this->Arr;
	}
	
function sortArr($direction){
	if($direction == "f"){
		return sort($this->Arr);
		}
	if($direction == "b"){
		return rsort($this->Arr);
		}
	}	
//end array object
}

function testArray(){
	$aO = new ArrObject;
	foreach ($aO->sortArr("f") as $e){
		echo "<br>$e";
		}
	}
//FileWorkBegin
interface FileInterface
{
	public function openFile();
	public function readLine();
	public function setFileName();
	public function getFileName();
}

//FileObject Class
class FileObject implements FileInterface
{
		var $FileName = "";
		var $FileHandle = 0;

	 function FileObject($fname = "PHPObject.php")
	{
		global $singleFile;
		$this->FileName = $fname;
		
		$this->openFile();
	}

//openFile
function openFile(){
	$this->FileHandle = fopen($this->FileName, "r") or exit("WHAT? You have GOT to be kidding!");
	return $this->FileHandle;
	}

//closeFile
function closeFile(){
	fclose($this->FileHandle);
	$this->FileHandle = 0;
	}
	
//readLine
function readLine(){
	if(!feof($this->FileHandle)){
		return fgets($this->FileHandle);
		}
	else {
		return False;
		}
	}

//setFileName
function setFileName($file = "PHPObject.php"){
	$this->FileName = $file;
	}
//getFileName
function getFileName(){
	return $this->FileName;
	}
	
//FileObject End Class
}



//DateInterface
interface DateInterface
{
	public function getToday();
	public function getJulian();
}

//DateObject Class
class DateObject implements DateInterface
{
	private static $singleDate = False;
	/*
	 * Open...
	 */
		var $DtTime = "";
		var $FormattedDate = "";
		var $JulianDay = 0;
		var $JewishDay = "";
		var $TimeZone = "";

	 function DateObject($Zone = "UTC")
	{
		global $singleDate;
		date_default_timezone_set($Zone);
		$this->TimeZone = new DateTimeZone('America/New_York');
		
		/* only need to initialize one time... */
		if(!$singleDate){
			$singleDate = True;
			$this->setToday();
			$this->setJulian();
			$this->setJewish();
			}
	}

//setToday
function setToday(){
	$this->DtTime = new DateTime('now', $this->TimeZone);
	$this->FormattedDate = $this->DtTime->format('Y-m-d H:i:s');
	}
	
//getToday
function getToday(){
	return $this->FormattedDate;
	}

//setJulian
function setJulian(){
	$dtArray = explode('-', $this->DtTime->format('m-d-Y'));
	$this->JulianDay = gregoriantojd($dtArray[0], $dtArray[1], $dtArray[2]); 
	}

//getJulian
function getJulian(){
	if($this->JulianDay == 0){
		setJulian();
		}
	return $this->JulianDay;
	}
//setJewish
function setJewish(){
	$dtArray = array();
	
	if($this->JulianDay == 0){
		setJulian();
		}
		
	$dtArray = explode("/", jdToJewish($this->JulianDay));
	$monthName = jdMonthName($this->JulianDay, 4);
	$this->JewishDay = $monthName . " " . $dtArray[1] . ", " . $dtArray[2];
	}
	
//getJewish
function getJewish(){
	if($this->JewishDay == ""){
		setJewish();
		}
	return $this->JewishDay;
	}	
//DateObject End Class
}

//StringObjectInterface
interface StringObjectInterface {
	public function getPosition();
	public function getTarget();
	public function setTarget($t);
	public function setString($s);
	public function getString();
	public function getLength();	
}

//StringObject Class
class StringObject implements StringObjectInterface {
	var $SO_String = "";
	var $SO_Target = "";
	
	function StringObject($target = "", $str = ""){
		$this->SO_Target = $target;
		$this->SO_String = $str;
	}
//setTarget	
	function setTarget($target){
		$this->SO_Target = $target;
	}
//getTarget	
	function getTarget(){
		return $this->SO_Target;
		}
//setString		
	function setString($str){
		$this->SO_String = $str;
		}
//getString	
	function getString(){
		return $this->SO_String;
		}
//getLength		
	function getLength(){
		return strlen($this->SO_String);
		}
//getPosition	
	function getPosition(){
		return strpos($this->SO_String, $this->SO_Target);
		}
//getLastPosition
	function getLastPosition(){
		return strrpos($this->SO_String, $this->SO_Target);
		}
//renderUpperCase
	function renderUpperCase(){
		return strToUpper($this->SO_String);
		}
//renderLowerCase
	function renderLowerCase(){
		return strToLower($this->SO_String);
		}
//renderCapitalizeWords
	function renderCapitalizeWords(){
		return UCWords($this->SO_String);
		}
//replaceTarget
	function replaceTarget($target, $replacement){
		return strtr($this->SO_String, $target, $replacement);
		}
//replaceTargetList
	function replaceTargetList($targetList){
		return strtr($this->SO_String, $targetList);
		}
//match
	function match($pattern){
		return preg_match($pattern, $this->SO_String);
		}
		
//change - the art of regex!
	function change($pattern, $newStuff){
		return preg_replace($pattern, $newStuff, $this->SO_String);
		}

//StringObject end Class
}

//dateObjectTest:
function dateObjectTest(){
	echo "<br>====================================================================================";
	echo "<br>=============== D A T E   -   O B J E C T   -    T E S T ===========================";
	echo "<br>====================================================================================";

	printFunction("DateObject");
	$pO = new DateObject;
	printFunction("setToday");
	printFunction("getToday");
	echo "<font color=red>" . $pO->getToday() . "</font>";
	printFunction("setJulian");
	printFunction("getJulian");
	echo "<br>Notice - this is a number of days since January 1, 4714 BCE: {<font color=red>" . $pO->getJulian() . "</font>}";
	echo "<br>Evidently, this was the last time the 19-year lunar cycle, and the 28-year solar cycle, and the 15-year roman tax cycle ";
	echo "<br> - right, go figure! - all coincided. This is called a 'Julian Period' by some guy named Joseph Justus Scaliger - born in ";
	echo "<br>France in the late 1500's. Anyway - the period is 19 * 28 * 15 = 7980 years long. Now years can be 365 or 366 days long,";
	echo "<br>so it gets a little more complicated.";
	printFunction("setJewish");
	printFunction("getJewish");
	echo "<br>Jewish Date: <font color=red>" . $pO->getJewish() . "</font>";
	echo "<br>===========================<br>";
}

//stringObjectTest
function stringObjectTest(){
	echo "<br>===================================================================================";
	echo "<br>================= S T R I N G  -  O B J E C T  -  T E S T =========================";
	echo "<br>===================================================================================";

	printFunction("StringObject");
	$sO = new StringObject;
	printFunction("setString");
	$sO->setString("This is the string, and these are the people!");
	printFunction("setTarget");
	$sO->setTarget("t");

	printFunction("getLength");
	
	echo "<br>String Length: " . $sO->getLength();
	printFunction("getPosition");
	echo "<br>String Position: ", $sO->getPosition();
	printFunction("getLastPosition");
	echo "<br>String Position: ", $sO->getLastPosition();
	printFunction("renderUpperCase");
	echo "<br>Upper Case: ", $sO->renderUpperCase();
	printFunction("renderLowerCase");
	echo "<br>Lower Case: ", $sO->renderLowerCase();
	printFunction("renderCapitalizeWords");
	echo "<br>Each word is capitalized: ", $sO->renderCapitalizeWords();
	printFunction("replaceTarget");
	echo "<br>Replacing parts: ", $sO->replaceTarget("aeiou", "zyxwv");
	$targetList = array("This" => "That", "these" => "those", "people" => "animals");
	printFunction("replaceTargetList");
	echo "<br>Replacing target list: ", $sO->replaceTargetList($targetList);
	echo "<br><br>Regular Expressions, matching is true/false (1/0)";
	printFunction("match");
	echo "<br>Matching 'ring'?: " . $sO->match("/ring/");
	echo "<br>Matching Beginning of line: '^is'?: " . $sO->match("/^is/");
	echo "<br>Matching End of line: 'people$'?: " . $sO->match("/people$/");
	echo "<br>Matching Beginning of line: '^This'?: " . $sO->match("/^This/");
	echo "<br>Matching Beginning of not case specific: '/^this/i'?: " . $sO->match("/^this/i");
	echo "<br>Matching End of line: 'people!$'?: " . $sO->match("/people!$/");
	echo "<br>Matching 'thought'?: " . $sO->match("/thought/");
	printFunction("change");
	echo "<br>The line we are working on is: \"" . $sO->getString() . "\"";
	echo "<br><font color=blue\sO->change(\"/string/\",\"rubberband\")</font>";
	echo "<br>Change a word: <font color=red>" . $sO->change("/string/","rubberband") . "</font>";
	echo "<br><font color=blue>\$sO->change(\"/\st\S*/\",\" T-WORD\")</font>";
	echo "<br>Change every word that starts with 't': <font color=red>" . $sO->change("/\st\S*/"," T-WORD") . "</font>";
	echo "<br><font color=blue>\$sO->change(\"/,.*/\",\" and that is that.\")</font>";
	echo "<br>Change everything after the comma: <font color=red>" . $sO->change("/,.*/"," and that is that.") . "</font>";
	echo "<br><font color=blue>\$sO->change(\"/\s(a\S*)/\",\" NOT$1\")</font>";
	echo "<br>Add 'NOT' to every word beginning with 'a': <font color=red>" . $sO->change("/\s(a\S*)/"," NOT$1") . "</font>";
	echo "<br><font color=blue>\$sO->change(\"/e(\W)/\", \"X$1\")</font>";
	echo "<br>If 'e' is the last letter of a word - change it to 'X': <font color=red>" . $sO->change("/e(\W)/", "X$1") . "</font>";
	echo "<br><font color=blue>\$sO->change(\"/T.*\st/\", \"***\")</font>";
	echo "<br>Is this regex greedy? <font color=red>" . $sO->change("/T.*\st/", "***") . "</font> yes, i think so...";



	}

//fileObjectTest
function fileObjectTest(){
	echo "<br>====================================================================================";
	echo "<br>============== F I L E   -   O B J E C T    -     T E S T ==========================";
	echo "<br>====================================================================================";

	printFunction("FileObject");
	$fO = new FileObject;
	$sO = new StringObject;
	$targetList = array("<br>" => "{BR}");
	printFunction("openFile");
	printFunction("readLine");
	printFunction("closeFile");
	while($line = $fO->readLine()){
		$sO->setString($line);
		$line = $sO->replaceTargetList($targetList);
		//echo "<br>" . $line;
		}
	$fO->closeFile();
	}

//printFunction
function printFunction($functionName){
	$fO = new FileObject;
	$sO = new StringObject;
	$fName = "\/\/" . $functionName;
	$printSwitch = 0;
	echo "<table>";
	while ($line = $fO->readLine()){
		$sO->setString($line);
		if($sO->match("/\/\//")){
			$printSwitch = 0;
			}
		if($sO->match("/^" . $fName . "/")){
			$printSwitch = 1;
			}
		if($printSwitch > 0){
			echo "<TR><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><TD><font color=green>" . $line . "</font></TD></TR>";
			}
		}
	echo "</table>";
	$fO->closeFile();
	$fO = null;
	}
	
// begin 
dateObjectTest();
stringObjectTest();
fileObjectTest();
?>