<?php 
ini_set('display_errors','On');
error_reporting(E_ERROR);
require "/var/www/html/api/admin-include.php";
//$slist=array('i8'=>'203.154.126.188','i9'=>'203.154.126.189','i2'=>'203.154.126.192','k6'=>'203.121.143.26','k49'=>'122.155.12.149', 'k0'=>'27.254.33.100','k32'=>'27.254.144.132');
//$slist=array('k6'=>'203.121.143.26','k49'=>'122.155.12.149');

/*print "<table><thead><tr><td>Node</td><td>IP</td><td>Connections</td><td>Bandwidth</td><td>CPU</td><td>CPU Load</td><td>RAM</td><td>Free RAM</td><td>HDD</td><td>HD Free</td></tr></thead><tbody>";*/

$q="select id,name 't',ip,mgt_port 'port' from cdn where 1 ";
$dt=qdt($q);

$key='knGfBjKpd3s2tCF';
print "<pre>";
while(list(,$dr)=each($dt)) {
	extract($dr);
	if(substr($ip,0,4)=='cdn1') continue;
	$Connections=0;$OutRate=0;$fpms=0;$fsss=0;
	$ip2=$ip;
	if($ip=='203.154.126.189') $ip2='172.10.1.189';
	if($ip=='203.154.126.188') $ip2='172.10.1.188';
	if($ip=='203.154.126.187') $ip2='127.0.0.1';
	if($ip=='203.154.126.192') $ip2='172.10.1.192';
	$salt= rand(0, 1000000);
	$str2hash = $salt . "/". $key;
	$md5raw = md5($str2hash, true);
	$base64hash = base64_encode($md5raw);
	$url = "http://$ip2:$port/manage/server_status?salt=$salt&hash=$base64hash";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,3);
	//curl_setopt($ch, CURLOPT_URL,$url);
	$result=curl_exec($ch);
	$info=curl_getinfo($ch);
	//print_r($info);
	curl_close($ch);
	$msg.="$ip\n$url\n$result\n";
	//print "<pre>$result</pre>";
	$rs=json_decode($result, true);
	extract($rs);
	extract($SysInfo);
	$q="update cdn set conn='$Connections',bandwidth=$OutRate/1024000,free_ram=$fpms/1024000,free_swap=$fsss/1024000,cpu_load='$scl', updated=now() where ip='$ip' ";
	qexe($q);
	$msg.="\n$q\n";
	$q="insert into flixer_log.cdn_log (ip,datetime,conn,bandwidth,free_ram,free_swap,cpu_load) values ('$ip',now(),'$Connections',$OutRate/1024000,$fpms/1024000,$fsss/1024000,$scl)  ";
	qexe($q); //print "\n$q\n";
	$msg.="\n$q\n";
	/*print "<tr>
			<td>$t</td><td>$ip</td>			
			<td>$rs[Connections]</td>
			<td>".round($rs[OutRate]/1024000,2)."</td>
			<td>$SysInfo[ap]</td>
			<td>$SysInfo[scl]</td>
			<td>".number_format($SysInfo[tpms]/1024000,2)."</td>
			<td>".number_format($SysInfo[fpms]/1024000,2)."</td>
			<td>".number_format($SysInfo[tsss]/1024000,2)."</td>
			<td>".number_format($SysInfo[fsss]/1024000,2)."</td>
			</tr>";*/
			
}

//print "</tbody></table>";
$rs=mail("preedeew@gmail.com","admin cdn on ".date("Y-m-d H:i"),"$msg $q","from:admin@flixerapp.com");
print "\n $msg mail sent $rs";
print "</pre> ";
?>
