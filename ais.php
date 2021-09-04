<?php 
error_reporting(E_ALL);
require "include.php";
qexe("set names 'tis620' ");

$q=" select t2.id,'/VDO Packages/Dex' as 'catpath'
,'TH_G' as 'rating'
,'10034' as 'cp_id'
,'DEX CARTOON' as 'cp_name'
,'1' as 'price_id'
,concat('EP.',t1.episode) as 'title'
,t1.name_th 'title_th'
,t2.name_en 'series_name',t2.name_th as 'series_name_th'
,concat('Season ',t2.season) as 'season_name'
,concat(' ',t2.season) as 'season_name_th'
,t1.episode as 'episode_no',t2.genres as 'genres','' as 'genres_th'
,'' as 'cast', '' as 'cast_th'
,'' as 'director', '' as 'director_th'
,t2.description_en as 'synopsis', t2.description_th as 'synopsis_th'
,t2.release_year as 'release_year'
,round(t1.duration/60) as 'duration'
,date_format(t2.ais_publish,'%Y-%m-%dT%H:%i:%s') as 'publish_date'
,date_format(t2.ais_expire,'%Y-%m-%dT%H:%i:%s') as 'expirey_date'
,t2.audio as 'audio_language'
,'' as 'subtitle_lang'
,concat('http://m9.dexclub.com:8081/vod/dex/',t2.code,'/',t2.code,'_',t1.episode,'_480_',t2.audio,'.mp4/playlist.m3u8') as 'vdo_file_name'
,'' as 'trailer'
,concat(t2.code,'_',t1.episode,'.smil') as 'mp4'
,concat('http://media.dexclub.com/img/ais/poster/',t2.code,'.jpg') as 'poster_url'
,concat('http://media.dexclub.com/img/ais/backdrop/',t2.code,'.jpg') as 'backdrop_url'

,t2.code,t1.episode 'ep'
from episode as t1,title as t2 where t2.id=t1.title
and now() between t2.ais_publish and t2.ais_expire
order by t2.ais_publish,t2.code,t1.episode ";
  
 

$dt=qdt($q); //print $q;
$skipflds=array('id','code','ep','mp4');
while(list($i,$dr)=each($dt)){
	//print " $dr[mp4] <br>";
/*	$thfile="/data/dex/".$dr[code]."/".$dr[code]."_".$dr[ep]."_480_th.mp4";
	$jpfile="/data/dex/".$dr[code]."/".$dr[code]."_".$dr[ep]."_480_jp.mp4";
	if(file_exists($thfile)){
		$dr[vdo_file_name]="http://27.254.144.132:8081/vod/dex/".$dr[code]."/".$dr[code]."_".$dr[ep]."_480_th.mp4/playlist.m3u8";
		$pimg="img/ais/".$dr[code]."_".$dr[ep]."_480_th.mp4.poster.jpg";	
		$bimg="img/ais/".$dr[code]."_".$dr[ep]."_480_th.mp4.backdrop.jpg";	
	}elseif(file_exists($jpfile)){
		$dr[vdo_file_name]="http://27.254.144.132:8081/vod/dex/".$dr[code]."/".$dr[code]."_".$dr[ep]."_480_jp.mp4/playlist.m3u8";
		$pimg="img/ais/".$dr[code]."_".$dr[ep]."_480_jp.mp4.poster.jpg";	
		$bimg="img/ais/".$dr[code]."_".$dr[ep]."_480_jp.mp4.backdrop.jpg";	
	}
	$mp4=$dr[vdo_file_name];
	$mp4=str_replace('http://27.254.144.132:8081/vod/dex/','',$mp4);
	$mp4=str_replace('/playlist.m3u8','',$mp4);
	$mp4=str_replace($dr[code].'/','',$mp4);
	
	
	//print '-'.$mp4.'<br>';
	$pimg="img/ais/".$mp4.".poster.jpg";	
	$bimg="img/ais/".$mp4.".backdrop.jpg";	
	$psrc="img/poster/".$dr[id].".jpg";
	$bsrc="img/backdrop/".$dr[id].".jpg";
	if(!file_exists($psrc)) $psrc="img/DEXCHANNEL-poster.png";
	if(!file_exists($bsrc)) $bsrc="img/DEXCHANNEL-poster.png";
	copy($psrc,$pimg);
	copy($bsrc,$bimg);
	$dr[pimg]=$pimg; $dr[bimg]=$bimg;
	*/
	while(list($fld,$val)=each($dr)){
		if(in_array($fld,$skipflds)) continue;
		if($i==0) $hd.="$fld,";
		if($fld!='catpath') $csv.=",";
		$val=str_replace("\""," ",$val);
		$val=str_replace("\r"," ",$val);
		$val=str_replace("\r\n"," ",$val);
		$val=str_replace("\n"," ",$val);
		$csv.="\"$val\"";
	}
	//$csv.=",\"http://media.dexclub.com/$pimg\"";
	//$csv.=",\"http://media.dexclub.com/$bimg\"";
	$csv.="\n";
	
	
}

if($csv){
	
	$file="csv/ais.csv";
	$tis=iconv("UTF-8","TIS-620",$csv);
	file_put_contents($file,$csv);
	print "<meta charset='TIS-620'/><a href=$file>download</a>";
	//print "<pre>$csv</pre>";
	
}
?>
