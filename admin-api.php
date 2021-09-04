<?php 
require "admin-include.php";
$json[response]=true;

if($action=='getwpcontent'){
	// countries='TH' and
	if($search) $cond.=" and (code like '%%$search%%' or name_en like '%%$search%%' or name_th like '%%$search%%' ) ";
	if($lastid) $cond.=" and id>$lastid ";
$q="select id,code,name_th 'name',name_en 'slugs',description_th as 'desc',concat(tags,',new') as tags,total_episodes as 'episodes'
,concat('https://img.flixerapp.com/dex/',code,'/poster.png') as 'poster_url'
 from title where status='publish' $cond order by id  ";
	$dt=qdt($q);	
	$json[result][data]=$dt;
	$json[result][count]=count($dt);
	$q2="select id,code,name_th 'name',name_en 'slugs' from title where status<>'publish' ";
	$dt2=qdt($q2);
	$json[result][down]=$dt2;
	$json[result][downs]=count($dt2);
	//$json[q]=$q;
}
if($json){
	http_response_code(200);
	header('Content-Type:application/json;charset=UTF-8');
	print json_encode($json);
}
?>