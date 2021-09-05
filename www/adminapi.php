<?php 
header("Access-Control-Allow-Methods:POST, GET");
header("Access-Control-Max-Age:3600");
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Headers:Origin,Accept, X-Requested-With, Authorization, Content-Type, Accept-Language");

session_start();
error_reporting(E_CORE_ERROR);
extract($_GET);
require "admin-include.php";

$json[error]=false;
if($action=='ais-trial'){
	
}
if($action=='today'){
	if(!$date) $date=date("Y-m-d");
	$q="select count(id) 'views',count(distinct(user)) 'users' from user_views where date='$date'";
	$dr=qdr($q);
	extract($dr);
//	if(!$mo) $mo=date("m");
//	if(!$yr) $yr=date("Y");
	//$q="select count(id) 'mviews',count(distinct(uuid)) 'musers' from user_view where month(date)='$mo' and year(date)='$yr' ";
	//$dr=qdr($q);
	//extract($dr);
	$q="select count(id) 'nusers' from user where date(registered)='$date' and type='guest' ";
	$dr=qdr($q); //print $q;
	extract($dr);

	$dl=qval("select balance from user_downloads where date<'$date' order by date desc limit 1 ");
	$json[yddl]=$dl;
	$todaydl=qval("select count(id) from user where registered>'$date' and type='guest' ");
	$json[todaydl]=$todaydl;
	$dl+=$todaydl;
	$json[dl]=$dl;
	$downloads=$dl;

//	$q="select count(id) 'downloads' from user where registered>'2017-12-15' and type='guest' ";
//	$dr=qdr($q);
//	extract($dr);
	
	$vlist=array('views','users','nusers','downloads');
	while(list(,$v)=each($vlist)){
		$json[$v]=number_format(${$v},0);
	}
	$q="select t2.name_en 'name',count(t1.id) views from user_views as t1,title as t2 where t2.id=t1.title 
	and t1.date='$date'
	group by t1.title
	order by count(t1.id) desc
	limit 10
	";
	$json[topviews]=qdt($q);
	//$q="select t2.name_en 'name',count(t1.id) views from user_view as t1,title as t2 where t2.id=t1.title and t1.date='$date' group by t1.title order by t1.datetime desc limit 10 	";
	//$json[lastviews]=qdt($q);
	$q="select count(id) from user_platform where status='trial' and date(start_datetime)='$date' ";
	$json[aisq]=$q;
	$json[aistrial]=qval($q);
	$q2="select count(id) from user_platform where status in ('trial','activate') and date(start_datetime)<='$date' ";
	//$json[aisq]=$q2;
	$json[aistrials]=qval($q2);
	$json[flixeraccounts]=qval("select count(id) from user where email<>'' ");
}
if($json){
	http_response_code(200);
	unset($json[response]);
	
	header('Content-Type:application/json;charset=UTF-8');
	//if($json[response]) http_response_code(200);
	//else http_response_code(500);
	print json_encode($json);
}
?>