<?php 
ini_set('display_errors','On');
error_reporting(E_ERROR);
require "/var/www/html/api/admin-include.php";
$date=date("Y-m-d",mktime(0,0,0,date('m'),date('d')-1,date('Y')));
if($_GET[date]) $date=$_GET[date];
if($_GET[action]=='fix'){
	extract($_GET);
	if(!$yr) $yr=2019;
	for($d=1;$d<=31;$d++){
		$dd=$d;
		if($d<10) $dd='0'.$d;
		$date="$yr-$mo-$dd";
		if(!checkdate($mo,$dd,$yr)) continue;
		$ckid=qval("select id from user_downloads where date='$date' ");
		if(!$ckid){
			print "<br>$date";

			gendl($date);
		}
	}
	exit;
}
$ldate=qval("select date from user_views_daily order by date desc limit 1");
print "ldate $ldate date $date <br>\n";
$q="insert into user_views_daily (date,title,views) select date,title,count(id) from user_views where date>'$ldate' and date <= '$date' group by date,title ";


$q=" 
insert into user_views_daily (date,title,views,p_views,r_views)
select date,title,sum(if(premium=0 && rental=0 ,1,0)),sum(if(premium=1,1,0)),sum(if(rental=1 and episode<>99,1,0)) from user_views where date>'$ldate' and date<='$date'  group by date,title ";

//print $q;
qexe($q);
//print "ldate $ldate $q ".$db->affected_rows;

gendl($date);

$rs=mail('preedeew@gmail.com','admin-daily '.$date,"$q","from:daily@api.flixerapp.com");
print " mail sent $rs";
function gendl($ydate){
	$dl=qval("select count(id) from user where type='guest' and date(registered)='$ydate' ");
	$bal=qval("select sum(downloads) from user_downloads where date<='$ydate' ");
	$bal+=$dl;
	//print "ydate $ydate dl $dl bal $bal newbal ";
	$q="delete from user_download where date='$ydate'";
	qexe($q);
	$q="insert into user_downloads (date,downloads,balance,logs) values ('$ydate','$dl','$bal',concat(now(),' cron updated') ) ";
	$rs=qexe($q); 
	print $q."<br>\n";print $db->affected_rows;print $rs;
}
function genuv($date){

}
/*
$q="";
$q="select date,downloads,balance from user_downloads where balance=0 order by date ";
$dt=qdt($q);
while(list(,$dr)=each($dt)){
	extract($dr);
	$bal=qval("select sum(downloads) from user_downloads where date<='$date' ");
	print "<br>$date $downloads $balance $bal";
	$q="update user_downloads set balance='$bal' where date='$date' ";
	qexe($q);
}
*/

?>
