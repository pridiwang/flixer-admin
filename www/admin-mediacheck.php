<?php 
ini_set('display_errors','On');
error_reporting(E_ERROR);
require "admin-include.php";
function mcheck($url){
	
}
$q="select id,code,total_episodes 'eps',audio,subtitle_type 'stype',premium_hd 'hd' from title where status='publish' order by code  ";
$dt=qdt($q);
$sv="i2.kudson.net";$port=8081;
$sv="cdn1.dexclub.com";$port=80;
$ch = curl_init ();
curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
$logs=" start ".date("Y-m-d H:i:s")."\n";
while(list(,$dr)=each($dt)){
	extract($dr);
	print "<br>$code $eps ";
	for($i=1;$i<=$eps;$i++){
		$status=qval("select status from episode where title='$id' and episode='$i' ");
		if($status!='publish') continue;
		$ep=$i;
		if($i<10) $ep='0'.$i;
		$f=$code."_".$ep."_480_".$audio.".mp4";
		if($stype=='srt')$f=$code."_".$ep.".smil";
		if($hd=='1')$f=$code."_".$ep."hd.smil";
		$url="http://$sv:$port/vod/dex/$code/$f/playlist.m3u8";
		print "<br> $url ";
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//$logs.="$f";
		if($http_code!==200){
			//print "  <font color=red>fail</font> ";
			$logs.="$f  * * fail * * \n";
			$fail++;
		}else{
			//print " ok ";
			$ok++;
		}
		//$logs.= "\n";
		$count++;
	}
}
curl_close($ch);
$logs.=" OK $ok fail $fail total $count \n";
$logs.=" end ".date("Y-m-d H:i:s")."\n";
print "
-----------------------
$logs
-----------------------";
mail("preedeew@gmail.com","Flixer Media Check Fail list",$logs,"from:admin@flixerapp.com");

?>