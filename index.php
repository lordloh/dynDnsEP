<?php
if ( !empty($_REQUEST["SIG"]) && !empty($_REQUEST["HOST"]) && !empty($_REQUEST["TS"]) ){
	if (verifySig($_REQUEST["SIG"],$_REQUEST["HOST"],$_REQUEST["TS"])){
		// Implementation Pending
	}else{
		echo "Verification Failed";
	}
}

function verifySig($sig, $host, $timeStamp){
	$sharedKey=getSharedKey($host);
	if ($sharedKey!=FALSE){
		// Implementation Pending
		$verificationString='';	
		$computedHash=sha1($verificationString);
		if ($computedHash==$sig){
			// Implementation Pending
			return TRUE;
		}else{
			return FALSE;
		}
	}else{
		echo "bad";
		return FALSE;
	}
}
function getSharedKey($hostName){
	// Implementation Pending
	return FALSE;
}
?>