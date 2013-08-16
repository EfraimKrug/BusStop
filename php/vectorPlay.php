<?php
$myVector = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
$leftVector = array();
$rightVector = array();

function printVector($v){
	echo "<br>";
	foreach ($v as $e){
		echo "(($e))";
		}
	}

function getArrayLength($v){
	return count($v);
	}
	
function reverseVector($v){
	$j=  getArrayLength($v) - 1;
	for($i=0;$i<getArrayLength($v)/2;$i++,$j--){
		$temp = $v[$i];
		$v[$i] = $v[$j];
		$v[$j] = $temp;
		}
	return $v;
	}

function splitArray($i, $v){
	$k=0;
	for($j=$i;$j>=0;$j--,$k++){
		$leftVector[$k] = $v[$j];
		}
	$k=getArrayLength($v)-1;
	for($j=0;$j<getArrayLength($v)-$i;$j++,$k--){
		$rightVector[$j] = $v[$k];
		}
	}
	
printVector($myVector);
$myVector = reverseVector($myVector);
printVector($myVector);

?>