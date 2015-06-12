<?php
$CFG=Array("ORIGIN"=>"h.lohray.com.",
			"TTL"=>6,
			"NS"=>"ns",
			"email"=>"bharath.lohray.com.",
			"refresh"=>30,
			"retry"=>20,
			"expiry"=>3600,
			"nx"=>6,
			"zoneFile"=>"/var/cache/bind/h.lohray.com.zone"
	);

if ( !empty($_REQUEST["SIG"]) && !empty($_REQUEST["HOST"]) && !empty($_REQUEST["TS"]) ){
	if ( verifySig( $_REQUEST["SIG"], $_REQUEST["HOST"], $_REQUEST["TS"] ) ) {
		updateHostIPFile($_REQUEST["HOST"]);
		echo $_SERVER["REMOTE_ADDR"];
	}else{
		echo "Verification Failed\n";
	}
}else{
	echo "Nothing received";
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
	$CFG=$GLOBALS["CFG"];
	$zoneFileHead="\$ORIGIN ".$CFG["ORIGIN"]."\n\$".
	"TTL ".$CFG["TTL"]."s\n".
	"@\tIN\tSOA\t".$CFG["NS"].".".$CFG["ORIGIN"]."\t".$CFG["email"]."(\n".
	"\t".time()."\n".
	"\t".$CFG["refresh"]."\n".
	"\t".$CFG["retry"]."\n".
	"\t".$CFG["expiry"]."\n".
	"\t".$CFG["nx"]."\n".
	")\n".
	"@\tIN NS ".$CFG["NS"]."\n";
	$zoneFileBody='';
	foreach($IP as $hostName=>$record){
		$zoneFileBody.=$hostName." IN A ".$record["ip"]."\n";
	}
	foreach($IP as $hostName=>$record){
		$zoneFileBody.="\$ORIGIN ".$hostName.".".$CFG["ORIGIN"]."\n";
		$zoneFileBody.=" IN MX 10 ".$hostName."\n";
	}
	file_put_contents($CFG["zoneFile"], $zoneFileHead."\n".$zoneFileBody);
	exec("/usr/bin/sudo /usr/sbin/service bind9 reload",$outputS,$output);
}

function updateHostIPFile($hostName,$timestamp){
	$IPFile=file_get_contents('hostIP.json');
	if (!$IPFile){													// IP file does not exist.
		$IPFile='{"'.$hostName.'":{"ip":"","timestamp":"0"}}';		// Generate a blank file.
	}
	$IPDB=json_decode($IPFile,TRUE);
	if(!array_key_exists($hostName,$IPDB) ){				// if hostname does not exist create it.
		$IPDB[$hostName]["ip"]="";
		$IPDB[$hostName]["timestamp"]=0;
	}
	if ($IPDB[$hostName]["ip"]!=$_SERVER["REMOTE_ADDR"]){	// if the IP Address has changed,
		$IPDB[$hostName]["ip"]=$_SERVER["REMOTE_ADDR"];		// update it.
		updateZoneFile($IPDB);								// Update the zone file;
	}
	$IPDB[$hostName]["timestamp"]=time();					// update time stamp irrespective of ip change to detect stale ip addresses.
	$IPFile=json_encode($IPDB);
	file_put_contents('hostIP.json', $IPFile);
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
