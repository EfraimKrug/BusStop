<?php
/*************************************************************
 * xmlManipulation.php:
 * 1) this handles one particular xml file and returns an
 *		associated array: array[tag-name] = data-value
 *************************************************************
 
/**
 * getReasons($xml)
 * @param - an opened XMLReader object
 * @return - an array of arrays - each inside array = (name,value)
 **/
function getReasons($xml)
{
	$arr = array();
	$bigArray = array();
	$current = 0;
	$level = 0;
	
    while($xml->read())
    {
		if($xml->nodeType == XMLReader::END_ELEMENT)
		{
			$level--;
		}
		
        if($xml->nodeType == XMLReader::ELEMENT)
        {
			$level++;
			if($xml->name == "reason"){
				$current = 1;
				$arr['reason'] = "";
				}
			if($xml->name == "points"){
				$current = 2;
				$arr['points'] = "";
				}
			if($xml->name == "number"){
				$current = 3;
				$arr['number'] = "";
				}
        }
       
        if($xml->nodeType == XMLReader::TEXT)
        {
		if($level < 5){
			if($current == 1){
				$arr['reason'] = $xml->value ;
				$current = 0;
				}
			if($current == 2){
				$arr['points'] = $xml->value;
				$bigArray[] = $arr;
				$current = 0;
				}
			if($current == 3){
				$arr['number'] = $xml->value;
				$current = 0;
				}
			}
        }
    }
     
    return $bigArray;
}


/**
 * recursive print function...
 * for testing
 **/

function printArray($arr){
	foreach ($arr as $a => $b){
		if(is_array($b)){
			foreach ($b as $c => $d){
				echo "<br>///$c -- >> $d";
				}
			}
		else {
			echo "<br>\\\[$a] -- >> [$b]";
			}
		}
	}

/**
 * getWholeArray
 * opens the XML file and returns the array of arrays
 **/
function getWholeArray(){
$xml = new XMLReader();
$xml->open('./XML/REASONS.XML');
$finalArray = getReasons($xml);
$xml->close();
return $finalArray;
}

//getWholeArray();
?>
