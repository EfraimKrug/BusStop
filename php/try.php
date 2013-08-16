<?php
function getGeoData($ip) 
    {   
	$curl = curl_init("http://api.hostip.info/get_html.php?ip=" . $ip . "&postion=true"); 
	curl_setopt($curl, CURLOPT_FAILONERROR, true); 
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);   
	$result = curl_exec($curl); 
//echo $result; 
        //$Content    =       CurlGet("http://api.hostip.info/get_html.php?ip=" . $ip . "&position=true");
        //$ContentArr =   explode("\n", $Content);
		$ContentArr =   explode("\n", $result);

        $Stack      =   array();
        $Ctr        =   0;

        foreach($ContentArr as $Item)
        {
            if($Ctr == 2) 
            {
                $Ctr++;
                continue;
            }

            if($Ctr == 6)
                break;
            $SingleItemArr  =   explode(":", $Item);
            array_push($Stack, $SingleItemArr[1]);  
            $Ctr++;
        }

        $MappedStr      =   array("country" => $Stack[0], "city" => $Stack[1], "latitude" => $Stack[2], "longitude" => $Stack[3], "ip" => $Stack[4]);
		print_r ($MappedStr);
        $JsonEncoded    =   json_encode($MappedStr);

        return $JsonEncoded;
    }

function getIP(){
return $_SERVER['REMOTE_ADDR'];
}

echo "hello";
$ip = getIP();
$j = getGeoData($ip);
foreach($j as $k => $val){
	echo "<br>$k" . "--))" . $val;
	}
?>