<?php
if ( !empty($_REQUEST["SIG"]) && !empty($_REQUEST["HOST"]) && !empty($_REQUEST["TS"]) ){
	if ( verifySig( $_REQUEST["SIG"], $_REQUEST["HOST"], $_REQUEST["TS"] ) ) {
		updateHostIPFile($_REQUEST["HOST"]);
		echo $_SERVER["REMOTE_ADDR"];
	}else{
		echo "Verification Failed\n";
	}
}

function verifySig($sig, $host, $timeStamp){
	$currenTS=time();
	// Window of opportunity	
	if (abs($timeStamp-$currenTS)<9999999999999999){
		$sharedKey=getSharedKey($host);
		if ($sharedKey!=FALSE){
			$verificationString=$host.$timeStamp.$sharedKey;	
			$computedHash=hash("sha256",$verificationString);
			if ($computedHash==$sig){
				return TRUE;
			}else{
				echo "hash failed\n";
				echo $verificationString."\n";
				echo $computedHash."\n";
				return FALSE;
			}
		}else{
			echo "Key not found\n";
			return FALSE;
		}
	}else{
		echo "Replay\n";
		return FALSE;
	}
}

function updateZoneFile($IP){

}

function updateHostIPFile($hostName,$timestamp){
	$IPFile=file_get_contents('hostIP.json');
	if (!$IPFile){
		echo "IP File not found\n";
		return FALSE;
	}else{
		$IPDB==json_decode($IPFile,TRUE);
		if ($IPDB[$hostName]["ip"]==$_SERVER["REMOTE_ADDR"]){
		}else{
			$IPDB[$hostName]=$_SERVER["REMOTE_ADDR"];
			updateZoneFile($IPDB);
		}
		$IPDB[$hostName]["timestamp"]=time();
		dbg(json_encode($IPDB));
	}
}

function getSharedKey($hostName){
	// Read file, Find host name, Return shared key or false
	$keyFile=file_get_contents('keyFile.json');
	if (!$keyFile){
		echo "Key File Missing\n";
		return FALSE;
	}else{
		$keyDB=json_decode($keyFile,TRUE);
		if (array_key_exists($hostName, $keyDB)){
			return $keyDB[$hostName];
		}else{
			echo "host not authorized\n";
			return FALSE;
		}
	}
}

function dbg($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}
?>
