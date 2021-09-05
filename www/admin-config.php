<?php 
$dbserver='localhost';
$dbserver="172.10.1.188";
if(substr($_SERVER['SERVER_ADDR'],0,7)!='172.10.')$dbserver="203.154.126.188";
$dbuser=$_ENV['FDBU'];
$dbpwd=$_ENV['FDBP'];
$salt=$_ENV['FSLT'];
$encryptkey=$_ENV['FECK'];

$dbname="flixer_vod";
$controlflds=array('id','logs');



$intflds=array('views','users','guest','fb','email','all','balance','bal','hour','premium_views','premium_users','total_views','rental_views');
$realflds=array('rate','share','total',1,2,3,4,5,6,7,8,9,10,11,12,'premium_amount');
$monthflds=array(1,2,3,4,5,6,7,8,9,10,11,12);
$amountflds=array('revenue','percent','premium_amount','premium_revenue');
$tbflds=array('licensor');
$toggleflds=array('premium_hd','premium_jp','premium','premium_th','premium_simulcast','premium_cover','tester','rental');
$radioflds=array(
	 'audio'=>array('jp','th','en','jpth','')
	,'subtitles'=>array('jp','th','en','cn','')
	,'audio_type'=>array('embed','audio_track')
	,'subtitle_type'=>array('embed','srt')
);
$ftpserver='203.154.126.192';
$ftpuser='dex';
$ftppwd="d8GtugTqH8";

$cdn_access_key_id="950m9rJ44ghq6V1w";
$cdn_access_key_secret="921p0m670760405kOw0HR61zzN0R483E";
?>
