<?php 

require "admin-config.php";
//$db=mysqli_connect($dbserver,$dbuser,$dbpwd,$dbname) or die ('can not connect $dbserver > $dbname ');
//$db->query('set names utf8');

$db=mysqli_connect($dbserver,$dbuser,$dbpwd,$dbuser) or die ('can not connect $dbserver > $dbuser ');
$db->query('set names utf8');
global $db,$json;
extract($_GET);
if($action=='licensor-paid'){
	extract($_POST);
	
	$mos=explode(',',$paid_for);
	while(list(,$yrmo)=each($mos)){
		$yrmo=trim($yrmo);
		if(!$yrmo)continue;
		$logs.="<li>$yrmo ";
		$q="update licensor_revenue set paid='paid',paid_date='$paid_date',paid_info='$paid_info' where licensor='$licensor',logs=concat(now(),' paid by $_SESSION[user]\n',logs) and date_format(date,'%Y-%m')='$yrmo' ";
		qexe($q);
		
	}
	$action='report';
	$report='view-licensor-year';
	
}
if($action=='delete'){
	$q="delete from  $tb where id='$id' ";
	qexe($q);
	$action='browse';
}
if($action=='update'){
	if($tb=='feature_banner'){
		$info=explode('/',$_POST[api_url]);
		//print_r($info);
		$_POST[title]=$info[5];
	}
	if($id){
		$q="update $tb set logs=concat(now(), ' updated by $_SESSON[user]\n',logs) ";
		while(list($fld,$val)=each($_POST)){
			$val=addslashes($val);
			
			if(($fld=='password')&&($val)) $q.=", $fld=password('$val') ";
			else $q.=", $fld='$val' ";
		}
		$q.=" where id='$id' ";
	}else{
		$vars=" logs ";
		$vals=" concat(now(),' added by $_SESSION[user]') ";
		while(list($fld,$val)=each($_POST)){
			$val=addslashes($val);
			$vars.=" ,$fld ";
			if(($fld=='password')&&($val)) $vals.=" ,password('$val') ";
			else $vals.=", '$val' ";
		}
		$q="insert into $tb ( $vars ) values ( $vals ) ";
	}
	qexe($q); //print $q;
	if(!$id) $id=$db->insert_id;
	$action='browse';
	$title=$_POST[title];
	if($tb=='title') qexe("update $tb set updated=now() where id='$id' ");
	if($tb=='episode'){
		$id=qval("select title from $tb where id='$id' ");
		$action='edit';$tb='title'; $id=$title;
	}
	if($tb=='income'){
		$yrmo=substr($_POST[date],0,7);
		$q="select count(id) from user_view where date_format(date,'%Y-%m')='$yrmo' ";
		$views=qval($q); //print $q;
		//print "$q $views";
		$q="update $tb set views='$views',share=amount/$views where id='$id' ";
		qexe($q);
		$q="select sum(p_views) from user_views_daily where date_format(date,'%Y-%m')='$yrmo' ";
		$pviews=qval($q); //print $q;
		$q="update $tb set premium_views='$pviews',premium_share=premium_amount/$pviews where id='$id' ";
		qexe($q); print $q;
		
	}
}
function stdreport3($q){
	$dr=qdr($q); //print $q;
	print "<table class='table table-bordered rpt'><thead><tr><td>Name</td><td>Value</td></tr></thead><tbody>";
	while(list($fld,$val)=each($dr)){
		if($fld=='logs') $val="<pre>$val</pre>";
		if($fld=='user') $val="<a href=?action=user-check&user_id=$val>$val</a>";
		print "<tr><td>$fld</td><td class='$fld'>$val</td>";
	}
	print "</tbody></table>";
}
function mail2($to,$subject,$body,$from,$cc,$bcc){
	/*$Host='localhost';
	$Username='sender@flixerapp.com';
	$Password='u4Tx0qAf';
	*/
	$Host='smtp.gmail.com';
	$Username='pridi@flixerapp.com';
	$Password='5291pee9144';
	
//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
	date_default_timezone_set('Etc/UTC');
	require 'vendor/autoload.php';
	//Create a new PHPMailer instance
	$mail = new PHPMailer\PHPMailer\PHPMailer();
	//Tell PHPMailer to use SMTP
	$mail->isSMTP();
	//Enable SMTP debugging
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
	$mail->SMTPDebug = 2;
	//Set the hostname of the mail server
	$mail->Host = $Host;
	//Set the SMTP port number - likely to be 25, 465 or 587
	$mail->Port = 587;
	$mail->SMTPSecure='tls';
	//Whether to use SMTP authentication
	$mail->SMTPAuth = true;
	//Username to use for SMTP authentication
	$mail->Username = $Username;
	//Password to use for SMTP authentication
	$mail->Password = $Password;
	//Set who the message is to be sent from
	$mail->setFrom($from, 'Flixer Admin');
	//Set an alternative reply-to address
	//Set who the message is to be sent to
	$mail->addAddress($to,'');
	//Set the subject line
	$mail->Subject = $subject;
	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	$mail->msgHTML($body);
	//Replace the plain text body with one created manually
	$mail->AltBody = $body;
	//Attach an image file
	//$mail->addAttachment('images/phpmailer_mini.png');
	//send the message, check for errors
	$rs=$mail->send();
	if(!$rs)  echo 'Mailer Error: ' . $mail->ErrorInfo;
	return $rs;
}
function catecount(){
	$dt=qdt("select id,name_en 'cat' from category ");
	while(list(,$dr)=each($dt)){
		extract($dr);
		$q="select count(id) from title where status='publish' and tags like '%%$cat%%' ";
		if($cat=='all')$q="select count(id) from title where status='publish' ";
		$t=qval($q);
		$q="update category set total='$t' where id='$id' ";
		qexe($q);
		
	}
}
function epsetstatus($id){
	$q="update episode set status='publish' where title='$id' and status='draft' and publish_time between '2018-01-01' and now()  ";
	qexe($q);
	$q="update episode set status='draft' where title='$id' and status='publish' and hold_time between '2018-01-01' and now()  ";
	qexe($q);
	$q="update episode set status='draft' where title='$id' and status='publish' and publish_time > now()  ";
	qexe($q);
	//$published=qcount("select id from episode where title='$id' and status='publish' ");
	//if($published==0) qexe(" update title set status='draft' where id='$id' ");
	
}

function error($errcode){
	global $json,$lang;
	$tfld='title_'.$lang;
	$mfld='message_'.$lang;
	$bfld='button_'.$lang;
	$dr=qdr("select $tfld as 'title',$mfld as 'message',$bfld as 'button' from error_code where code='$errcode' ");
	$json[message]=$dr;
	
	
	
}
function ep2rename($code){
	$code1=str_replace('2','',$code);
	$dir="/data/dex/$code";
	//$code1='SAINT';
	print" rename ep2 $dir from $code1 -> $code ";
	$dh2=opendir($dir);
	while($file=readdir($dh2)){
		if($file=='.') continue;
		if($file=='..') continue;
		$file2=$file;
		$file2=str_replace($code1.'_',$code.'_',$file2);
		//print "<br> rename $dir/$file -.$dir/$file2 \n";
		$rs=rename("$dir/$file","$dir/$file2");
	}
	closedir($dh2);
}
function mp4rename($d2){
	print "dir $d2 <br>\n";
	$dh2=opendir($d2);
	while($file=readdir($dh2)){
		if($file=='.') continue;
		if($file=='..') continue;
		$file2=$file;
		$file2=str_replace('_00','_0',$file2);		
		$file2=str_replace('_010_','_10_',$file2);		
		$file2=str_replace('_011_','_11_',$file2);		
		$file2=str_replace('_ED_','_',$file2);
		$file2=str_replace('_EP01_','_',$file2);
		$file2=str_replace('_EP02_','_',$file2);
		$file2=str_replace('_LC02_','_',$file2);
		$file2=str_replace('_TH1000','_480_th',$file2);
		$file2=str_replace('_JP1000','_480_jp',$file2);
		$file2=str_replace('_EP','_',$file2);
		//print " - $d2/$file -> $d2/$file2 <br>\n";
		$rs=rename("$d2/$file","$d2/$file2");
		//print " -- renamed ";
	}
	closedir($dh2);
}
function txt2array($dr){
	
	global 	$vwrating;
	if(array_key_exists('rating',$dr)){
		$vr=$dr['rating'];
		$dr['rating']= $vwrating[$vr];
	} 
	if(array_key_exists('score',$dr)){
	//	$dr['score']=rand(0,10)/2;
		//$dr['score']=5;
		
	}
	//;
	

	$flds=array('audio'=>'language','subtitle'=>'language','subtitles'=>'language','resolutions'=>'quality','tags'=>'word');
	while(list($k,$v)=each($flds)){
		if(array_key_exists($k,$dr)){
			$arr=explode(',',$dr[$k]);
			unset($dr[$k]);
			while(list(,$val)=each($arr)){
				//print "val $val<br>";
				$dr[$k][]=array($v=>$val);
			}
		}
	}
	
	return $dr;
}

function fnameclean($in){
	$out=$in;
	$out=str_replace(' ','_',$out);
	$out=str_replace('(','',$out);
	$out=str_replace(')','',$out);
	return $out;
}
function sendFCM($mess) {
	global $json;
	$id="f8oMeZExGJM:APA91bF8jn-GE3RL2Bjdb41bwXuFvo7IlVgynSTGUPOqgLfDPG_hUbrKPHsSFIWdgBLw7i2hzEC5uCqUEPIQjQSE2qQJdpoL0Asb2NDcLgIVzqiO6Mc9fSXv_FXgk-0cPS-1ogeJipuv";
	$url = 'https://fcm.googleapis.com/fcm/send';
	$fields = array (
			'to' => $id,
			'notification' => array (
					"body" => $mess,
					"title" => "บทความใหม่"
			)
	);
	$fields = json_encode ( $fields );
	$headers = array (
			'Authorization:key=' . "AIzaSyBg81u7F8HU33bjUnmb-PgExAV0xzCJ1SQ",
			'Content-Type:application/json'
	);

	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_POST, true );
	curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );

	$result = curl_exec ( $ch );
	$json[error]=curl_error($ch);
	curl_close ( $ch );
	$json[result]=$result;
	
}

function qdata($q){
	global $json;
	$dt=array();
	$dt=qdt($q);
	if(count($dt)==0){
		$dt=array();
	}
	while(list($i,$dr)=each($dt)){
	}
	$json[response]=true;
	$json[result]=$dt;	
}
function qdata0($q){
	global $json;
	$dt=array();
	$dt=qdt($q);
	if(count($dt)==0){
		$dt=array();
	}
	while(list($i,$dr)=each($dt)){
		
		$pid=$dr[id];
		$q="select t2.name 'name',t2.term_id 'id' from wp_term_relationships as t1,wp_terms as t2 where t2.term_id=t1.term_taxonomy_id and t1.object_id='$pid' and term_order=0 ";
		//$dt[$i][q]=$q;
		$cat=qdr($q);		
		$dt[$i][category]=$cat[name];
		$dt[$i][cat_id]=$cat[id];
		
		$imgid=qval("select id from wp_posts where post_parent='$pid' and post_mime_type in ('image/jpeg','image/png') ");
		$metajson=qval("select meta_value from wp_postmeta where post_id='$imgid' and meta_key='_wp_attachment_metadata' ");
		//print $metajson;
		$meta=unserialize($metajson);
		$img='http://cafe.itban.com/wp-content/uploads/'.$dr[yrmo].'/'.$meta[sizes][medium][file];
		$dt[$i][img]=$img;
		$ct=$dr[content];
		$dt[$i][eb]='';
		$dt[$i][yt]='';
		$dt[$i][mp4]='';
		$dt[$i][vdo]='';
		if(strpos($ct,'[/embed]')){
			$e1=strpos($ct,'[embed]');
			if(!$e1) $e1=0;
			$e2=strpos($ct,'[/embed]');
			$eb=substr($ct,$e1+7,$e2-$e1-7);
			
			$yt=str_replace('https://www.youtube.com/watch?v=','',$eb);
			if(strpos($eb,'youtu.be')) $yt=str_replace('https://youtu.be/','',$eb);
			
			
			$dt[$i][eb]=$eb;
			$dt[$i][yt]=$yt;
			if(strpos($eb,'.mp4')){
				$dt[$i][mp4]=$eb;
				$dt[$i][yt]='';
			}
			if((!$img)&&($yt)){
				$dt[$i][img]="https://img.youtube.com/vi/$yt/0.jpg";
			}
			$vdo="<iframe style=margin:10px; width=100% src=https://www.youtube.com/embed/$yt?rel=0&amp;controls=0&amp;showinfo=0 frameborder=0 allowfullscreen></iframe>";
			$dt[$i][vdo]=$vdo;
			
			//$dt[$i][content]=str_replace("[embed]","<embed>",$$dt[$i][content]);
			//$dt[$i][content]=str_replace("[/embed]","</embed>",$$dt[$i][content]);
			//$dt[$i][content].=$vdo;
		
		}
		if($dr2[img]){
			$ext=substr($dr2[img],strlen($dr2[img])-3,3);
			$img2="media/$dr[id].$ext";
			$dt[$i][img2]=$img2;
			copy($dr2[img],$img2);
		}
		$dr2=$dt[$i];
		$q="insert into posts (id,category,date,title,content,image,vdo,youtube) values 
		('$dr2[id]','$dr2[cat_id]','$dr2[date]','$dr2[title]','$dr2[content]','$img2','$dr2[mp4]','$dr2[yt]' ) ";
		//$db2->query($q);
	}
	$json[response]=true;
	$json[result]=$dt;	
}

function medialib(){
	$dir="media";
	$dh=opendir($dir);
	$q="select name 'file',type 'ext' from media order by datetime desc ";
	$dt=qdt($q);
	while(list($i,$dr)=each($dt)){
		extract($dr);
		$url='http://'.$_SERVER[SERVER_NAME]."/$dir/".$file;
		$mview="<img src=$url height=90  alt=$url title=$url>";
		if($ext=='mp4') {$mtype='vdo'; $mview='<img src=../img/clip.jpg height=90  alt=$file title=$file>';}
		else $mtype='image';
		$item="<div class='media-item $mtype' url=$url >$mview<div class=media-file>$file</div></div>";
		$out.=$item;
	}
	return $out;
}
function qdt($q){
	global $db,$json;
	$ck=$db->query($q);
	if(!$ck){
		$json[error]=$db->error.$q;
		return false;
	}
	while($dr=$ck->fetch_assoc()){
		$dt[]=$dr;
	}
	return $dt;
}
function qar($q){
	global $db;
	$ck=$db->query($q);
	while($dr=$ck->fetch_array(MYSQLI_BOTH)){
		$o[]=$dr[0];
	}
	return $o;
}
function qdr($q){
	global $db,$json;
	$ck=$db->query($q);
	if(!$ck){
		$json[error]=$db->error;
		return false;
	}
	$dr=$ck->fetch_assoc();
	return $dr;
}
function qcount($q){
	global $db,$json;
	$ck=$db->query($q);
	if(!$ck){
		$json[error]=$db->error;
		return 0;
	}
	
	$o=$ck->num_rows;
	return $o;
}
function qval($q){
	global $db,$json;
	$ck=$db->query($q);
	if(!$ck){
		$json[error]=$db->error;
		return false;
	}
	$dr=$ck->fetch_array();
	return $dr[0];
}
function qexe($q){
	global $db,$json;
	$q=trim($q);
	$ck=$db->query($q);
	if(!$ck){
		//$json[error]=$db->error;
		//print $db->error;
		return false;
	}
	if(substr($q,0,6)=='delete') return $db->affected_rows;
	return $ck;
}
function qoptions($q,$val){
	$dt=qdt($q);
	
	while(list(,$dr)=each($dt)){
		if($dr[id]==$val) $out.="<option selected value=$dr[id]>$dr[name]";
		else $out.="<option value=$dr[id]>$dr[name]";
	}
	return $out;
}
function qbrowse($q,$tb){
	global $controlflds,$sumflds,$countflds, $tbflds,$code;
	$maxlen=200;
	$dt=qdt($q); //print $q;
	if(!$dt){
		return $q;
		//return '';
	}
	if(count($dt)==0) print $q;
	$rnd=rand(0,9999);
	while(list($i,$dr)=each($dt)){
		$k=$i+1;
		$tbody.= "<tr onclick=window.location.href='?action=edit&tb=$tb&id=$dr[id]'><td>$k</td>";
		while(list($fld,$val)=each($dr)){
			if(in_array($fld,$controlflds)) continue;
			if(in_array($fld,$sumflds))$sum[$fld]+=$val;
			if(in_array($fld,$countflds)&&($val<>'')) $sum[$fld]+=1;
			if($i==0) $thead.="<td>$fld</td>";
			if($val=='0000-00-00') $val='';
			if($val=='0000-00-00 00:00:00') $val='';
			if($fld=='tags') $val=str_replace(',',', ',$val);
			
			if($val=='publish') $val="<i class='btn btn-success'></i>";
			if($val=='draft') $val="<i class='btn btn-danger'></i>";
			if($fld=='link') $maxlen=60;
			if(strlen($val)>$maxlen) $val=substr($val,0,$maxlen).'..';
			if(substr($val,0,5)=='f_chk') $val=f_val($val);
			if(in_array($fld,$tbflds)) $val=qval("select name from $fld where id='$val' ");
			$tbody.= "<td class='$fld $tb-$fld' id='$tb-$fld-$dr[id]' tid=$dr[id] code='$code' ep='$dr[episode]' tname='$tb' >$val</td>";
		}
		//$link="<a href=?action=edit&tb=$tb&id=$dr[id]>Edit</a> | ";
		//if($tb=='title') $link="<a href=?action=ep&title=$dr[id]>EP</a> | <a href=?action=img&dir=img/dex/$dr[code]>Img</a> 		<a href=img/dex/$dr[code]/poster.png?$rnd>P</a> | 		<a href=img/dex/$dr[code]/banner.png?$rnd>B</a>		";
		if($tb=='title') $link="<a href=?action=img&dir=dex/$dr[code]&eps=$dr[eps]><i class='fa fa-picture-o'></i></a>";
		$tbody.= "<td>$link</td></tr>";
	}
	
	reset($dt);
	list(,$dr)=each($dt);
	while(list($fld,)=each($dr)){
		if(in_array($fld,$controlflds)) continue;
		$val='';
		if(in_array($fld,$sumflds)) $val=$sum[$fld];
		if(in_array($fld,$countflds)) $val=$sum[$fld];
		$tfoot.="<td class='$fld'>$val</td>";
	}
	$out="<table class='table table-bordered'>
	<thead><tr><td>No.</td>$thead<td></td></tr></thead>
	<tbody>$tbody</tbody>
	<tfoot><tr><td></td>$tfoot<td></td></tr></tfoot>
	</table>";
	return $out;
}
function f_val($in){
	//return '';
	$d=explode('-',$in);
	$o='';
	//$o=$d[0];
	if($d[0]=='f_chkimg'){
		$title=$d[1];
		$dr=qdr("select code,audio,subtitle_type,premium_hd from title where id='$title' ");
		extract($dr);
		$ep=$d[2];
		//$audio=$d[3];
		//$stype=$d[4];
		$imgurl="https://img.flixerapp.com/dex/$code/$ep.png";
		$img="../img/dex/$code/$ep.png";
		//$o=$img;
		$signkey = "SZ12Cwo8SyCX444vl57u"; //enter your key here
		$validminutes = 10;
		$ip = $_SERVER['REMOTE_ADDR'];
		$today = gmdate("n/j/Y g:i:s A");
		$str2hash = $ip . $signkey . $today . $validminutes;
		$md5raw = md5($str2hash, true);
		$base64hash = base64_encode($md5raw);
		$urlsignature = "server_time=" . $today ."&hash_value=" . $base64hash. "&validminutes=$validminutes";
		$base64urlsignature = base64_encode($urlsignature);
		$json[test][base64urlsignature]=$base64urlsignature;
		$playlist="$base_url?wmsAuthSign=$base64urlsignature";

		if($subtitle_type=='embed')$mp4="http://i2.kudson.net:8081/vod/dex/".$code."/".$code."_".$ep."_480_".$audio.".mp4/playlist.m3u8??wmsAuthSign=$base64urlsignature";
		if($subtitle_type=='srt') $mp4="http://i2.kudson.net:8081/vod/dex/".$code."/".$code."_".$ep.".smil/playlist.m3u8?wmsAuthSign=$base64urlsignature";
		if($premium_hd=='1') $mp4="http://i2.kudson.net:8081/vod/dex/".$code."/".$code."_".$ep."hd.smil/playlist.m3u8?wmsAuthSign=$base64urlsignature";

		if(url_exists($imgurl)) $o="<i class='fa fa-check-circle' data-img=''></i><a target=_blank href=$mp4>.</a>";
		else $o="<span data-mp4='$mp4' data-img='$img'></span>";
		
	}
	if($d[0]=='f_chkmp4'){
		$title=$d[1];
		$ep=$d[2];
		$tt=qdr("select code,audio from title where id='$title' ");
		$mp4="/data/dex/".$tt[code]."/".$tt[code]."_".$ep."_480_th.mp4";
		
		if(file_exists($mp4)) $o="<i class='fa fa-check-circle'></i>";
		else $o=$mp4;
	}
	return $o;
	
}
function url_exists($url){
	$file_headers = @get_headers($url);
	//print "<pre>$url\n";print_r($file_headers);Print "</pre>";
	if($file_headers[0]=='HTTP/1.1 200 OK') return true;
	return false;
}
function encrypt_string($string = '', $salt 		= '8638FD63E6CC16872ACDED6CE49E5A270ECDE1B3B938B590E547138BB7F120EA') {
	$key = pack('H*', $salt);    
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $string, MCRYPT_MODE_CBC, $iv);
    return base64_encode($iv . $ciphertext);
}

function decrypt_string($encodedText = '', $salt	= '8638FD63E6CC16872ACDED6CE49E5A270ECDE1B3B938B590E547138BB7F120EA') {
	$key = pack('H*', $salt);
    $ciphertext_dec = base64_decode($encodedText);
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $iv_dec = substr($ciphertext_dec, 0, $iv_size);
    $ciphertext_dec = substr($ciphertext_dec, $iv_size);
    return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
}
function encrypt($data){
	global $encryptkey;
	require 'vendor/autoload.php';
	$cryptor = new \RNCryptor\Encryptor();
	$encrypted = $cryptor->encrypt($data, $encryptkey);
	return $encrypted;
}
function decrypt($data){
	global $encryptkey;
	require 'vendor/autoload.php';
	$decryptor = new \RNCryptor\Decryptor();
	$decrypted = $decryptor->decrypt($data, $encryptkey);
	if(!$decrypted) return '';
	return $decrypted;
}

?>
