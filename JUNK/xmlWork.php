<?php
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
				//ho "<br>$xml->name";
				$current = 1;
				$arr['reason'] = "";
				}
			if($xml->name == "points"){
				//ho "<br>$xml->name";
				$current = 2;
				$arr['points'] = "";
				}
        }
       
        if($xml->nodeType == XMLReader::TEXT)
        {
		// $level = 6 is the name of the 'friend', 
		// $level = 4 is our level
		if($level < 5){
			if($current == 1){
				//ho "$xml->value";
				$arr['reason'] = $xml->value ;
				$current = 0;
				}
			if($current == 2){
				$arr['points'] = $xml->value;
				$bigArray[] = $arr;
				$current = 0;
				}
			}
        }
    }
     
    return $bigArray;
}

// function getAddresses($xml)
// @param - XMLReader object - file is open...
// @return - array of associated arrays - each assoc array has key/value pairs - keys: number/street/city/state
// xml: <people><person><fname>...</fname><lname>...</lname>...<stats><address><number>...</number><street>...</state></address>...</stats></person></people>
// I am going to build an array of associated arrays - 
// each associated array with all address info - as is in the xml file
// and that is it.
//
function getAddresses($xml)
{
	$arr = array();
	$bigArray = array();
	$inAddress = 0;
	$currentElt = "";
	
    while($xml->read())
    {   
		if($xml->nodeType == XMLReader::END_ELEMENT)
		{
			// when we get to </address>
			if($xml->name == "address"){
				$inAddress = 0;
				$bigArray[] = $arr;
				$arr = null;
				}
		}
		
        if($xml->nodeType == XMLReader::ELEMENT)
        {
			if($xml->name == "address"){
				$inAddress = 1;
				$arr = array();
				}
			if($inAddress > 0){
				$currentElt = $xml->name;
				}
        }
       
        if($xml->nodeType == XMLReader::TEXT)
        {
			if($inAddress > 0){
				$arr[$currentElt] = $xml->value;
				}
        }
    }
     
    return $bigArray;
}


// function getContactInfo($xml)
// @param - XMLReader object - file is open...
// @return - array of associated arrays - each assoc array has 2 key/value pairs - keys: firstname/lastname
// xml: <people><person><fname>...</fname><lname>...</lname>...<contact>...</contact></person></people>
// I am going to build an array of associated arrays - 
// each associated array has a['email'] and a['phone']
// and that is it.
//
// Note: This also accesses attributes on 'phone'
//
function getContactInfo($xml)
{
	$arr = array();			// collect each line
	$bigArray = array();	// collect everything
	$inContact = 0;			// switch - there are a number of elements inside of <contact>...</contact>
	$currentElt = "";		// holds the name of the element
	
    while($xml->read())
    {   
		if($xml->nodeType == XMLReader::END_ELEMENT)
		{
			// when we get to </contact>
			if($xml->name == "contact"){
				$inContact = 0;
				$bigArray[] = $arr;
				$arr = null;
				}
		}
		
        if($xml->nodeType == XMLReader::ELEMENT)
        {
			if($xml->name == "contact"){	// found contact node!
				$inContact = 1;
				$arr = array();
				}
			if($inContact > 0){
				$currentElt = $xml->name;	// save the tag to associate with ::TEXT
				}
       
	        if($xml->hasAttributes)
            {
                $attributes = array();
                while($xml->moveToNextAttribute())
                {
                    //print("Adding attr " . $xml->name ." = " . $xml->value . "<br>");
                    $attributes[$xml->name] = $xml->value;
                }
                $arr[] = $attributes;
			}
	    }
		
        if($xml->nodeType == XMLReader::TEXT)
        {
			if($inContact > 0){				// $xml->name == text is not so helpful... get tag from above
				$arr[$currentElt] = $xml->value;
				}
        }
    }
     
    return $bigArray;
}



// function getNameContact($xml)
// @param - XMLReader object - file is open...
// @return - array of associated arrays - each assoc array has 2 key/value pairs - keys: firstname/lastname
// xml: <people><person><fname>...</fname><lname>...</lname>...<contact>...</contact></person></people>
// I am going to build an array of associated arrays - 
// each associated array has a['email'] and a['phoen']
// and that is it.
//
function getNameContact($xml)
{
/*
 * So here is the problem:
 * our xml file allows a person to have a friend,
 * and that friend can also have name/contact info...
 * makes our process a little more difficult because
 * now we can not just go through the file until we 
 * find name/contact... we have to make sure it is not
 * a friend!
 */
 
	$arr = array();			// collect each line
	$bigArray = array();	// collect everything
	$inContact = 0;			// switch - there are a number of elements inside of <contact>...</contact>
	$inFriend = 0;
	$inName = 0;
	$currentElt = "";		// holds the name of the element
	
    while($xml->read())
    {   
		if($xml->nodeType == XMLReader::END_ELEMENT)
		{
			if($xml->name == "name"){
				$inName = 0;
				}
			// when we get to </contact>
			if($xml->name == "contact"){
				$inContact = 0;
				if($inFriend < 1){
					$bigArray[] = $arr;
					$arr = null;
					}
				}
			if($xml->name == "friend"){
				$inFriend = 0;
				}
		}
		
        if($xml->nodeType == XMLReader::ELEMENT)
        {
			if($xml->name == "friend"){
				$inFriend = 1;
				}
			
			// This is starting to get klutzy... let's get functional
			if($inFriend < 1){
				if($xml->name == "name"){	// found contact node!
					$inName = 1;
					$arr = array();
					}
				if($inName > 0){
					$currentElt = $xml->name;	// save the tag to associate with ::TEXT
					}

				if($xml->name == "contact"){	// found contact node!
					$inContact = 1;
					}
				if($inContact > 0){
					$currentElt = $xml->name;	// save the tag to associate with ::TEXT
					}
				}
        }
       
        if($xml->nodeType == XMLReader::TEXT)
        {
			if($inFriend < 1){
				if(($inContact > 0) || ($inName > 0)){				// $xml->name == text is not so helpful... get tag from above
					$arr[$currentElt] = $xml->value;
					}
				}
        }
    }
     
    return $bigArray;
}

//
// Now, the beauty of xml, is that it should be easily recursive...
// let's see if I can get that to work:
//

function getRNames($xml)
{
// keeping everything as simple as possible... 
$arr = array();
$bigArr = array();
$current = "";
    while($xml->read()){
		if($xml->nodeType == XMLReader::END_ELEMENT)
		{
			//echo "END: " . $xml->name;
			$bigArr[] = $arr;
		}
		
        if($xml->nodeType == XMLReader::ELEMENT)
        {
			$arr = array();
			echo $xml->name . "//" . $xml->value;
			$current = $xml->name;
			$arr[] = getRNames($xml);

			//$arr[$xml->name] = getRNames($xml);
			//if(!$xml->isEmptyElement){
			//	$arr[$xml->name] = getRNames($xml);
			//	}
        }
		$bigArr[] = $arr;
		
        if($xml->nodeType == XMLReader::TEXT)
        {
			$arr = array();
			echo "<br>TEXT: " . $xml->value;
			$arr[$current] = $xml->value;
        }		
	}
	return $bigArr;
}


function xPlore($xml)
{
// keeping everything as simple as possible... 
$wholeDoc = array();
$arr = array();
//$bigArr = array();
//$current = "";
    while($xml->read()){
		if($xml->nodeType == XMLReader::END_ELEMENT)
		{
			echo "<br>END: " . $xml->name;
			return $arr;
			//$arr[] = "end" . $xml->name;
			//$bigArr[] = $arr;
		}
		
        if($xml->nodeType == XMLReader::ELEMENT)
        {
			//$arr = array();
			echo "<br>ELEMENT: " . $xml->name . "//" . $xml->value;
			
			$x = "ELT: " . $xml->name;
			$arr[] = $x;
			//$current = $xml->name;
			//$arr[] = getRNames($xml);

			//$arr[$xml->name] = getRNames($xml);
			//if(!$xml->isEmptyElement){
			//	$arr[$xml->name] = getRNames($xml);
			//	}
        }
		//$bigArr[] = $arr;
		
        if($xml->nodeType == XMLReader::TEXT)
        {
			//$arr = array();
			echo "<br>TEXT: " . $xml->value;
			$arr[] = "<ul>" . $xml->value;
			//$arr[$current] = $xml->value;
        }		
	}
	return $arr;
}

//
// recursive print function...
//
function printArray($arr){
foreach($arr as $k=>$v){
	if(is_array($v)){
		printArray($v);
		}
	else{
		echo "<br>$v";
		}
	}
}

$xml = new XMLReader();
$xml->open('../../REASONS.XML');
$finalArray = getReasons($xml);
//$finalArray = getAddresses($xml);
//$finalArray = getContactInfo($xml);
//$finalArray = getRNames($xml);
//$finalArray = xPlore($xml);
echo "<BR>---------------------";
printarray($finalArray);
//print_r($finalArray);
// corrects for friend...
//$finalArray = getRNames($xml);
$xml->close();
?>
