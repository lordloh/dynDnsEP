<?php
function dbg($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}

function ago($time){
    $currentTS=time();
    $diffSec=$currentTS-$time;
    
    $days=intval($diffSec/86400);
    $resH=$diffSec%86400;
    $hours=intval($resH/3600);
    $resM=$resH%3600;
    $minutes=intval($resM/60);
    $seconds=$resM%60;
    
    $agoStr="";
    $dayStr=" day".(($days>1)?"s ":" ");
    $hourStr=" hour".(($hours>1)?"s ":" ");
    $minStr=" minute".(($minutes>1)?"s ":" ");
    $secStr=" seconds ";
    
    $largerMeasure=false;
    if ($days!=0){
        $agoStr.=$days." ".$dayStr;
        $largerMeasure=true;
    }
    if (!($largerMeasure==false && $hours==0)){
        $agoStr.=$hours.$hourStr;
        $largerMeasure=true;
    }
    if (!($largerMeasure==false && $minutes==0)){
        $agoStr.=$minutes.$minStr;
        $largerMeasure=true;
    }
    if (!($largerMeasure==false && $seconds==0)){
        $agoStr.=$seconds.$secStr." ago";
        $largerMeasure=true;
    }
    if ($largerMeasure==false){
        $agoStr="now";
    }
    return $agoStr;
}

$hideIP=0;
$IPFile=file_get_contents('hostIP.json');
$IPDB=json_decode($IPFile,TRUE);
echo "<table style='width:60%'><tr><th>Hostname</th><th>IP</th><th>Last Update</th></tr>";
foreach ($IPDB as $host=>$record){
    if (@$hideIP || !isset($hideIP)){
        $IPAddress="0.0.0.0";
    }else{
        $IPAddress=$record["ip"];
    }
    echo "<tr><td>".$host."</td><td>".$IPAddress."</td><td>".ago($record["timestamp"])." </td></tr>";
}
echo "</table>";
?>