<?php
if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on")
{
    //Tell the browser to redirect to the HTTPS URL.
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], true, 301);
    //Prevent the rest of the script from executing.
    exit;
}
header("Access-Control-Allow-Origin:*"); 
session_start();
ini_set('display_errors','On');
ini_set('memory_limit','-1');

error_reporting(E_ERROR);
?>
<html><head>
<title>Admin</title>
<!-- meta http-equiv="refresh" content="0;url=https://api.flixerapp.com/admin.php" /-->
<meta charset=UTF-8 />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="apple-touch-icon" sizes="120x120" href="apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
<link rel="manifest" href="/manifest.json">
<link rel="mask-icon" href="safari-pinned-tab.svg" color="#5bbad5">
<meta name="theme-color" content="#ffffff">
<link rel=stylesheet href=css/bootstrap.min.css />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" SameSite="Lax" Secure >
<link rel=stylesheet href=css/jquery.tagsinput.min.css />
<link rel=stylesheet href=css/bootstrap-datepicker.min.css />
<!-- script src="js/fullcalendar.min.js"></script>
<link rel="stylesheet" href="css/fullcalendar.min.css">
<link rel="stylesheet" media='print' href="css/fullcalendar.print.css" -->
<link rel="stylesheet" href="css/bootstrap-datetimepicker.min.css"/>
<link href="css/bootstrap-toggle.min.css" rel="stylesheet">

<script src=js/jquery-1.12.4.min.js ></script>
<script src=js/bootstrap.min.js ></script>
<script src=js/jquery.dataTables.min.js ></script>
<script src=js/jquery.tagsinput.min.js ></script>
<script src=js/bootstrap-datepicker.min.js ></script>
<script src="js/Chart.min.js"></script>
<script src="js/Chart.bundle.min.js"></script>
<script src="js/moment.min.js"></script>

<script src="js/jquery.number.min.js"></script>
<script src="js/bootstrap-toggle.min.js"></script>
<script src="js/bootstrap-datetimepicker.min.js"></script>

</head>
<body><div class='container ' >

<?php
$path = '/data/dex';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
print "";

//error_reporting(E_ALL);
$rnd=rand(0,9999);
print "
<link rel=stylesheet href=admin.css?$rnd />
<script src=admin.js?$rnd ></script>
";
extract($_GET);
require "admin-include.php";
if($action=='log-out'){
	session_destroy();
	unset($_SESSION);
}
if($action=='log-in'){
	extract($_POST);
	$q="select email 'name',type 'admin_type',licensor from admin_user where email='$email' and password=password('$password') ";
	$dr=qdr($q); //print $q;
	if(!$dr){
		$q="select name,admin_type,licensor from user where email='$email' and password=password('$password') and type='admin' ";
		//print "<div style=color:#dddddd>$q</div>";
		$dr=qdr($q); //print $q;
	}
	if($dr){
		//print_r($dr);
		$_SESSION[user]=$dr[name];
		$_SESSION[admin_type]=$dr[admin_type];
		$_SESSION[licensor]=$dr[licensor];
		$action='report';$report='today';
		if($_SESSION[admin_type]=='editor') {$action='';$tb='title';}
		if($dr[admin_type]=='support') {$action='user-check';$report='';$email=''; $_POST[email]='';}
		if($dr[admin_type]=='licensor'){
			$_SESSION[all_licensors]=1;
			$report='licensors';
			$_SESSION[admin_type]='licensors';
		}
		//print_r($_SESSION);
	}else{
		$msg=" user / password not correct";
	}
	if(!$_SESSION[user]){
		$q="select id,name from licensor where email='$email' and password=password('$password') ";
		$dr=qdr($q);
		if($dr){
			$_SESSION[user]=$dr[name];
			$_SESSION[admin_type]='licensor';
			$_SESSION[licensor]=$dr[id];
		}
	}
	//print "<div style=color:#dddddd>$q</div>";
}
if($action=='logaslicensor'){
	$q="select id,name from licensor where id='$id' ";
	$dr=qdr($q);
	if($dr){
		$_SESSION[user]=$dr[name];
		$_SESSION[admin_type]='licensor';
		$_SESSION[licensor]=$dr[id];
	}
}
if($action=='setdur'){
	$title=qval("select id from title where code='$code' ");
	$q="update episode set duration='$dur' where title='$title' and episode='$ep' ";
	qexe($q); //print $q;
	//$action='edit';$id=$title;$tb='title';
}
if(!$_SESSION[user]){
	print "<div class='row text-center'><form action=?action=log-in method=post class='col-md-4 col-sm-12 text-center' >
	<center><img src=img/logo_flixer235x92.png><h3>Admin Login</h3></center>
	$msg
	<input type=email name=email placeholder=email class=form-control required >
	<br><input type=password name=password placeholder=password class=form-control required>
	<br>
	<a class='btn btn-default pull-left' href=?user-forgot >Forgot Password?</a>
	<button class='btn btn-primary pull-right'>Login</button><br><br>
	</form></div></div></body><style>
	body{height:100%;text-align:center;}
	form{background:#fff;padding:5px;margin:5% auto !important;float:none !important;}
	.row{text-align:center;padding:auto;}
	input{text-align:left;}
	</style></html>";
	exit;
}

//,'user_token'=>'Token','user_log'=>'Log','user'=>'User'
$alist=array('title'=>'Title','title_rental'=>'Rental','feature_banner'=>'Banner','category'=>'Category','income'=>'Income','licensor'=>'Licensor','cdn'=>'CDN','ads_inventory'=>'Ads','ads_banner'=>'AdsBanner','shelf'=>'Shelf');

if($_SESSION[admin_type]=='editor') $alist=array('title'=>'Title','banner'=>'Banner');
if($_SESSION[admin_type]=='licensor') $alist=array();
if($_SESSION[admin_type]=='licensors') $alist=array('licensor'=>'Licensor List');

if($_SESSION[admin_type]=='marketing') $alist=array();
if($_SESSION[admin_type]=='mkt') $alist=array();
if($_SESSION[admin_type]=='support') $alist=array('');
if($_SESSION[admin_type]=='report-view') $alist=array('');
if($_SESSION[admin_type]=='service') $alist=array('title'=>'Title');

print "<i class='fa fa-2x fa-bars togglebars' onclick=$('.menu').toggle();></i><div class=menu><img src=https://img.flixerapp.com/app_icon.png height=30> Flixer Admin : ";
$rptusers=array('flixer','dex','licensor','marketing','licensors','mkt','admin');
if(in_array($_SESSION[admin_type],$rptusers)) print "<a href=?action=report><i class='fa fa-list'></i> Report </a> | ";
while(list($a,$t)=each($alist)){ print "<a href=?action=browse&tb=$a>$t</a> | ";}

$aclist=array();
if($_SESSION[admin_type]=='admin') $aclist=array('user-check'=>'User Check');
if($_SESSION[admin_type]=='flixer') $aclist=array('user-check'=>'User Check');
if($_SESSION[admin_type]=='support') $aclist=array('user-check'=>'User Check');
if($_SESSION[admin_type]=='report-view') $aclist=array('report'=>'Report');
if($_SESSION[admin_type]=='service') $aclist=array('report'=>'Report','user-check'=>'User Check','user-receipt-check'=>'Receipt Check','ais-user'=>'AIS User Check');

while(list($a,$t)=each($aclist)){ print "<a href=?action=$a >$t</a> | ";}
print "<span class='pull-right' ><i class='fa fa-user'></i> $_SESSION[admin_type]: $_SESSION[user] <a href=?action=log-out>Log-out <i class='fa fa-sign-out'></i></a></span> </div>";
require "admin-mail.php";
require "admin-img.php";
require "admin-licensor.php";
//require "admin-title.php";


if($action=='img-upload'){
	if(!file_exists($dir)) mkdir($dir);
	print "uploading ";
	//print_r($_FILES);
	if($_FILES){
		extract($_POST);
		
			
		
		$sfile=$_FILES[file][name];
		$ext=substr($sfile,strlen($sfile)-3,3);
		
		if($fname) $tfile=$dir.'/'.$fname.'.png';
		
		if($dir=='../img/banner'){
			$tfile=$dir.'/'.$sfile;
		}
		print $tfile ;
		$rs=move_uploaded_file($_FILES[file][tmp_name],$tfile);
		if($rs) print " ok ";
		else print " failed ";
		
		
	}
	$action='browse';$tb='title';
	if($dir=='../img/banner') $action='img';
}
if($action=='img-delete'){
	print "delete $file ";
	$rs=unlink($file);
	if($rs) print "ok";
	else print "failed";
	$action='img';
}
if($action=='img0'){
	if(!file_exists($dir)) mkdir($dir);
	print "<h3>$dir</h3>";
	$dh=opendir($dir);
	while($file=readdir($dh)){
		if($file=='.') continue;
		if($file=='..') continue;
		$files[]=$file;
	}
	$rnd=rand(0,9999);
	sort($files);
	while(list(,$file)=each($files)){
		print "<span class='col-md-2'><a href=$dir/$file?$rnd target=_blank> $file </a>";
		
		$floc="$dir/$file";
		//print "<a href=?action=img-delete&dir=$dir&file=$dir/$file onclick=\"return confirm('Confirm Delete $file ?');\" style=color:#dddddd> x </a>";
		print "</span>";
		//if(file_exists($floc)) print "+";
		//else print "-";
	}
	closedir($dh);
	
	for($i=1;$i<=60;$i++){
		$j=$i;
		if($i<10) $j='0'.$i;
		$es.="<option value=$j>EP.$j ";
	}
	print "<p><hr><form action=?action=img-upload&dir=$dir method=post enctype=multipart/form-data class='form form-inline'>
	select image type : <select name=fname><option>banner<option>poster $es
	</select>
	<input type=file name=file onchange=this.form.submit();><button type=submit class='btn btn-primary' ><i class='fa fa-upload'></i> upload </button>
	</form>";
}


//gen ep data and smil file
if($action=='genTimelineThumbnail'){
	$q="select t2.code,t1.episode from episode as t1,title as t2 where t2.id=t1.title and t1.title='$title' ";
	$dt=qdt($q);print $q;
	
	while(list(,$dr)=each($dt)){
		$sdir="/data/dex/".$dr[code];
		$tdir="/home/flixer/public_html/api/dex/".$dr[code];
		if(!file_exists($tdir)) mkdir($tdir);
		$tdir="/home/flixer/public_html/api/dex/$dr[code]/thumb";
		if(!file_exists($tdir)){
			if(mkdir($tdir)) print " $tdir mkdir ";
			else print "$tdir failed mkdir ".error_get_last()[message];
		} 
		$tdir.="/".$dr[episode];
		if(!file_exists($tdir)) mkdir($tdir);
		print "<br>sdir $sdir > tdir  $tdir \n";
		$ex="/usr/bin/ffmpeg -i ".$sdir.'/'.$dr[code]."_".$dr[episode]."_480_jp.mp4 -vf fps=1,scale=160:120 $tdir/thumb%04d.jpg";
		shell_exec($ex);
		print "<br> $ex \n";
	
		
	}

	
}
if($action=='checkep'){
	$dt=qdt("select code,episodes,audio from title where status='draft' and subtitle_type='embed' ");
	while(list($i,$dr)=each($dt)){
		print "<br>$dr[code] ";
		
	}
}
if($action=='getep'){
	$title=qdr("select code,episodes,audio from title where id='$id' ");
	for($i=1;$i<=$title[total_episodes];$i++){
		$q=" insert into episode (title,episode,rank) values ('$id',$i,$i) ";
		qexe($q);
	}
	$q="select episode from episode where title='$id' order by episode ";
	$eps=qdt($q);
	//print_r($title);
	$dir="/data/dex/".$title[code];
	if(strpos($title[code],'2')){
		print " this season2 ".$title[code];
		ep2rename($title[code]);
	} 
	mp4rename($dir);
	$tmpsmil='/data/dex/jpth.smil';
	if($title[audio]=='jp') $tmpsmil='/data/dex/jp.smil';
	if($title[audio]=='th') $tmpsmil='/data/dex/th.smil';
	$smil=file_get_contents($tmpsmil);
	print "<br>using smil $tmpsmil \n";
	while(list(,$dr)=each($eps)){
		$ep=$dr[episode];
		if(($title[episodes]>99)&&($dr[episode]<100)){
			$ep='0'.$dr[episode];
		}
		$smil_file="$dir/".$title[code]."_".$dr[episode].".smil";
		$src_th=$title[code]."_".$ep."_480_th.mp4";
		$src_jp=$title[code]."_".$ep."_480_jp.mp4";
		$smil1=$smil;
		$code_ep=$title[code].'_'.$dr[episode];
		$smil1=str_replace('|code_ep|',$code_ep,$smil1);
		$smil1=str_replace('|src_jp|',$src_jp,$smil1);
		$smil1=str_replace('|src_th|',$src_th,$smil1);
		file_put_contents($smil_file,$smil1);
		//print "<br>tmpsmil $tmpsmil ep $dr[episode] src_jp $src_jp src_th $src_th smilfile $smil_file \n";
		//print "<br> gen $smil_file \n";
	}
	$q="update title set status='publish',publish_time=now(),logs=concat(now(),' published by $_SESSION[user]\n',logs) where id='$id' ";
	qexe($q); //print $q;
	$action='browse';$tb='title';
	
}

if($action=='setstatus'){
	$q="update $tb set status='$status',logs=concat(now(),' $status by $_SESSION[user]\n',logs) where id='$id' ";
	qexe($q);
	$action='browse';
	if($tb=='episode'){
		$id=qval("select title from $tb where id='$id' ");
		$action='edit';$tb='title';
	}
	if(($tb=='title')&&($status<>'publish')){
		$q="update episode set status='$status' where title='$id' ";
		qexe($q);
	}
	catecount();
}
if($action=='banner-upload'){
}
if($action=='poster-upload'){
	//print_r($_FILES);
	//print "uplaoding ".$_FILES[userfile][tmp_name]." > $_GET[file] ";
	$rs=move_uploaded_file($_FILES[userfile][tmp_name],$_GET[file]);
	if($rs) print "$_GET[file] uploaded ";
	$action='edit';
}
if($action=='premium-all'){
	$q="update episode set premium='$premium' where title='$title' ";
	qexe($q); print $q;
	$action='edit';$tb='title';$id=$title;
	print "action $acton tb $tb id $id  ";
}
if($action=='status-all'){
	$q="update episode set status='$status',publish_time='',hold_time='' where title='$title' ";
	qexe($q);
	$action='edit';$tb='title';$id=$title;
}
if($action=='edit'){
	
	$q="select * from $tb ";
	if($tb=='title'){
		epsetstatus($id);
		$q="select id,episodes,code,name_en,name_th,description_en,description_th,audio,audio_type,subtitles,subtitle_type,pinned,tags,licensor,total_episodes, publish_time,hold_time,ais_publish,premium_publish,premium,premium_unpublish,premium_hd,premium_jp,premium_simulcast,premium_th,status,ads_tags,share_percent,last_ep,countries,premium_cover,ais_expire,tester,viewer_rating,rental,rental_datetime from $tb ";
		if($_SESSION[admin_type]=='editor')$q="select id,name_en,name_th,description_en,description_th,total_episodes,status,premium,ads_tags from $tb ";
	}
	if($tb=='episode'){
		
		$q="select id,title,episode,duration,name_en,name_th,publish_time,hold_time,status,rank,premium,premium_publish,premium_unpublish,ais_publish from $tb ";
		if($_SESSION[admin_type]=='editor')$q="select id,title,episode,name_en,name_th,status from $tb  ";
	} 
	
	if($id) $q .=" where id='$id' ";
	else $q .=" limit 1 ";
	
	$dr=qdr($q); //print $q;
	if(($tb=='episode')&&($id)) $code=qval("select code from $tb as t1,title as t2 where t2.id=t1.title and t1.id='$dr[id]' ");
	$col=1;$cols=2;$allcols=$cols*2;
	print "<form action=?action=update&tb=$tb&id=$id method=post ><div class='row'>";
	$txtflds=array('description_en','description_th','tags','pinned');
	$hiddenflds=array('title');
	if($tb=='title_rental'){
		$controlflds=array('id','logs');
		$hiddenflds=array();
	}
	
	$chk=array('','checked');
	$chk0=array('checked','');
	while(list($fld,$val)=each($dr)){
		if(in_array($fld,$controlflds)) continue;
		if(in_array($fld,$hiddenflds)){ print "<input type=hidden name=$fld value=$val>"; continue;}
		if(!$id){
			$val='';
			if($fld=='episodes') $val=1;
			if($fld=='total_episodes') $val=1;
			if($fld=='audio') $val='jp';
			if($fld=='audio_type') $val='embed';
			if($fld=='subtitles') $val='th';
			if($fld=='subtitle_type') $val='srt';
			if($fld=='tags') $val='hero';
		} 
		if($val=='0000-00-00 00:00:00') $val='';
		if($fld=='password')$val='';
		if(strpos($fld,'_time')) $ftype='datetime';
		
		$input="<input type=text class='form-control $ftype' name=$fld value=\"$val\" >";
		if(in_array($fld,$txtflds)) $input="<textarea class='form-control $fld' name=$fld  rows=4>$val</textarea>";
		if(($fld=='status')&&(in_array($tb,array('title','episode')))){
			$input="<br><span style=white-space:nowrap;>$val 
			<a href=?action=setstatus&status=draft&tb=$tb&id=$id class='btn btn-default'>Draft </a>
			<a href=?action=setstatus&status=publish&tb=$tb&id=$id class='btn btn-success'>Publish </a>
			<a href=?action=setstatus&status=hold&tb=$tb&id=$id class='btn btn-danger'> Hold </a>
			</span>";
		}
		if($fld=='active'){
			
			$input="<input type=radio name=$fld value=1 $chk[$val] > ON / <input type=radio name=$fld value=0 $chk0[$val] > Off ";
		}
		if(($fld=='tags')||($fld=='pinned')){
			$dt2=qdt("select name_en as 't' from category ");
			$ts='';
			while(list(,$dr2)=each($dt2)){
				extract($dr2);
				$ts.="<span class=tgs onclick=$('.$fld').addTag('$t');>$t</span> ";
			}
			$ts.="
			<span class=tgs onclick=$('.$fld').addTag('jpsound');>jpsound</span>
			<span class=tgs onclick=$('.$fld').addTag('thsound');>thsound</span>
			";
			$input.="$ts";
		}
		if(in_array($fld,$tbflds)){
			$input="<select class='form-control' name=$fld id=$fld><option>".qoptions("select id,name from $fld order by name ",$val)."</select>";
		}
		if(in_array($fld,$toggleflds)){
			$chk1=''; $chk0='';
			if($val==1) $chk1='checked';
			else $chk0='checked';
			$input="<br>
			<span class='label label-success'> <input type=radio name=$fld value=1 $chk1 arial-label='Yes'> Yes </span> / 
			<span class='label label-danger'> <input type=radio name=$fld value=0 $chk0 arial-label='No' > No </span>
			";
		}
		if(array_key_exists($fld,$radioflds)){
			$rlist=$radioflds[$fld];
			$input="<br>";
			while(list(,$t)=each($rlist)){
				$chk='';
				if($val==$t) $chk='checked';
				$input.="<input $chk type=radio name=$fld $id=$fld-$t value=$t > $t ";
			}
		}
		print "<div class='form-group col-sm-6'><label for=#$fld>$fld</label>$input</div>";
		//if($col==$cols) { print "</tr><tr>"; $col=1;}
		//else {$col++;}
	}
	print "</div><button class='btn btn-primary pull-right' type=submit ><i class='fa fa-floppy-o'></i> Save</button></form>";
	if(($tb=='title')&&($id)){
		$code=qval("select code from $tb where id='$id' ");
		print "
		<a href=?action=img&dir=dex/$dr[code]&eps=$dr[total_episodes]&title=$id&code=$dr[code] class='btn btn-success'><i class='fa fa-picture-o '></i> Img </a> 
		<a href=?action=title-media&id=$id&code=$dr[code] class='btn btn-success'><i class='fa fa-film'></i> Media </a> 
		<i class='fa fa-hastag></i>
		";
		//print "<table><tr><td><h3>Poster</h3><img src=poster/$id.jpg?id=$rnd height=200><form action=?action=poster-upload&tb=$tb&id=$id&file=poster/$id.jpg method=post enctype=multipart/form-data><input type=file name=userfile onchange=this.form.submit(); ><input type=submit value=Upload></form>";
		//print "</td><td><h3>Banner</h3><img src=banner/$id.jpg?id=$rnd height=200><form action=?action=poster-upload&tb=$tb&id=$id&file=banner/$id.jpg method=post enctype=multipart/form-data><input type=file name=userfile onchange=this.form.submit();><input type=submit value=Upload></form></td></tr></table>";
	}

	if($tb=='episode'){
		
		$dir="../img/dex/$code";
		
		if(!file_exists($dir)) mkdir($dir);
		$tfile=$dir."/".$dr[episode].".png";
		print "<table><tr><td><h3>Screenshot</h3>dir $dir file $tfile<br><img src=$tfile?$rnd height=200><form action=?action=poster-upload&tb=$tb&id=$id&file=$tfile method=post enctype=multipart/form-data><input type=file name=userfile onchange=this.form.submit(); ><input type=submit value=Upload></td></tr></form></table>";
		$tt=qdr("select * from title where id='$dr[title]'");
		print $tt[code].' '.$dr[episode];
		$dir=$code;
		
		
		$mp4dir="/data/dex/$tt[code]";
		$url="http://$_SERVER[SERVER_NAME]:8081/vod/dex/$tt[code]";
		
		if($tt[subtitle_type]=='embed'){
			$mp4=$mp4dir."/$tt[code]_$dr[episode]_480_$tt[audio].mp4";
			$mp4url=$url."/$tt[code]_$dr[episode]_480_$tt[audio].mp4";
		}
		$mp4url=$url."/$tt[code]_$dr[episode]_480_$tt[audio].mp4/playlist.m3u8";
		if($tt[subtitle_type]=='srt'){
			$smilurl=$url."/$tt[code]_$dr[episode].smil/playlist.m3u8";
			$srturl=$url."/$tt[code]_$dr[episode]_th.srt/playlist.m3u8";
		}

		
		print "<br><a target=_blank href=$url>$url</a>";
		print "<br>checking $mp4url ";
		$hdrs=get_headers($mp4url); 
		//print "headers ";		print_r($hdrs);
		if(strpos($hdrs[0],'200')){ print " ready ";}
		else print " not found ";
		print "<br>checking substitle $srturl ";
		$hdrs=get_headers($srturl); 
		if(strpos($hdrs[0],'200')){ print " ready ";}
		else print " not found ";
		
	}
	if(($tb=='title')&&($id)) print "<a class='btn btn-danger' href=?action=ep&title=$id><i class='fa fa-list-ul'></i> Episodes  </a> <script>
	$(function(){
	$('.tags, .pinned').tagsInput();		
	});

	</script>";
	//print "<a class='btn ' onclick=\"return confirm('Confirm delete?');\" href=?action=delete&tb=$tb&id=$id> <i class='fa fa-trash'></i> delete </a> ";
	if(($tb=='title')&&($id)) {print "api_url http://flixerapp.com/api/title/$id "; $title=$id;$action='ep';}
	if($tb=='ep'){
		
		
	}
	if($tb=='licensor') print "<a href=?action=licensor-mail&id=$id> Gen pass and send mail to $dr[email] </a> ";
}
if($action=='ep'){
	$tt=qdr("select name_en 'title_name',total_episodes 'eps' from title where id='$title' ");
	extract($tt);
	$found=0;
	$found=qcount("select id from episode where title='$title' ");
	//print "found $found eps $eps ";
	if($found<$eps){
		for($i=1;$i<=$eps;$i++){	
			$q="select id from episode where title='$title' and episode='$i' ";
			$epid=qval($q);
			if(!$epid){
				$q="insert into episode (title,episode,rank) values ('$title','$i','$i') ";
				qexe($q);
			}
		}
	}
	//$q="select id,episode,name_en,name_description,status from episode where title='$title' order by episode ";
	$action='browse';$tb='episode';
	print "<h3>Title: $title_name $action $eb / $code</h3><input id=code value=$code type=hidden>";
	

}

if($action=='browse'){
	
//	$q="update title set status='publish' where status='draft' and publish_time between '2000-01-01' and now() ";
//	qexe($q);
//	$q="update title set status='draft' where status='publish' and hold_time between '2000-01-01' and now() ";
//	qexe($q);
	extract($_POST);
	print "<form action=?action=$action&tb=$tb method=post>
	<input type=search name=search value='$search'><button class='btn btn-primary' type=submit><i class='fa fa-search'></i></button>
	</form>";
	while(list($f,$v)=each($_GET)){
		if(in_array($f,array('action','tb','id'))) continue;
		$cond.=" and $f='$v' ";
	}
	$q="select * from $tb where 1 $cond order by id desc ";
	
	if($tb=='licensor') $q="select id,code,name,email from $tb order by name ";
	if($tb=='title'){
		if($search) $cond.=" and (name_en like '%%$search%%' or name_th like '%%$search%%' or tags like '%%$search%%' ) ";
		$q=" select id,id as 'title_id',code, name_en as 'name',name_th as 'name_th',total_episodes as 'eps',audio,subtitle_type as 'sub' ,if(status='publish','publish','') as 'publish' ,if(status='draft','draft','') as 'draft' ,tags,licensor,share_percent 'percent',premium,premium_hd 'hd' from title where 1 $cond order by id desc  ";
		//$q="select id,id as 'title',code,name_en,name_th from $tb where 1 $cond order by id desc limit 100 ";
		 
	} 
	if($tb=='episode') $q="select id,episode,name_th,duration,publish_time,hold_time
	,status 'ep_status'
	,if(status='publish',status,'') 'publish'
	,if(status='draft',status,'') 'draft'
	,concat('f_chkimg-',title,'-',episode) 'ready',rank,premium
	,premium_publish,premium_unpublish
	from $tb where title='$title' order by episode ";
	if($tb=='user') $q="select id,id 'user',uuid,type,email,email_verify,fbid,name,mobile,registered,premium from $tb where 1 $cond order by registered desc ";
	if($tb=='user_log') $q.=" limit 100 ";
	if($tb=='user') $q.=" limit 100 ";
	print "<a href=?action=edit&tb=$tb class='btn btn-success'><i class='fa fa-plus-circle'></i> New $tb</a> ";
	if($tb=='feature_banner')print " <a href=?action=img&dir=banner class='btn btn-success'><i class='fa fa-picture-o'></i> Upload Banner</a>";
	$sumflds=array('eps');
	$countflds=array('publish','draft');
	if($_SESSION[admin_type]=='editor'){
		if($tb=='title') $q="select id,code,name_th,description_th,total_episodes 'eps',status from $tb order by id desc ";
	}
	if($tb=='user-premium'){
 		$q="select t1.user,t1.valid,t1.expired,t1.expire_gmt + interval 7 hour as 'expires',t1.validated,t2.email,t1.platform from user_premium as t1, user as t2 where t2.id=t1.user order by t1.validated desc limit 100 ";
		print $q;
	}
	//print $q;
	print qbrowse($q,$tb);
	
	print "<script>$(document).ready(function(){
		$('.table').dataTable({pageLength:50,lengthMenu:[50,100,200,500],stateSave:true});
	});
	</script>";
	if($tb=='episode'){
		print "
		<a href=?action=status-all&status=draft&title=$title class='btn btn-danger'> Draft All EPs </a>
		<a href=?action=status-all&status=publish&title=$title class='btn btn-success'> Publish All EPs </a>
		<a href=?action=premium-all&premium=1&title=$title class='btn btn-success'> Premium All EPs </a>
		<a href=?action=premium-all&premium=0&title=$title class='btn btn-danger'> Not Premium All EPs </a>
	<script>
	$('.episode-duration').each(function(){
		code=$(this).attr('code');
		ep=$(this).attr('ep');
		url='https://i2.kudson.net/api.php?key=e24R6VDBwDBy8EaCSisG97r6xiTx0C&action=duration&code='+code+'&ep='+ep;
		console.log(url);
		$.getJSON(url,function(r){
			//console.log(r);
			if(r.dur === undefined) return;

			//console.log(' - dur: '+r.dur);
			dur=parseInt(r.dur);
			//console.log(' - dur: '+r.dur+ ' durInt '+dur);
			if( (parseInt(r.dur)!='0') &&(r.dur!='undefined')){
				url2='/util.php?action=setdur&code='+code+'&ep='+r.ep+'&dur='+r.dur;
				console.log(url2);
				$.getJSON(url2,function(r2){
					$(this).html(r.dur);
				});
				
			}
			
		});
		
	});
	</script>	
		";
	}
}


require "admin-report.php";
require "admin-media.php";
require "admin-ftp.php";
require "admin-user.php";
require "admin-coupon.php";
require "admin-ais.php";
if($action=='dup'){
	if($tb=='title'){
		$q=" insert into $tb 
(licensor,code,name_th,name_en,description_th,description_en,audio,audio_type,subtitles,subtitle_type,total_episodes,category,tags)
select 
licensor,code,name_th,name_en,description_th,description_en,audio,audio_type,subtitles,subtitle_type,total_episodes,category,tags
from $tb where id='$id' 
";
		

	}
	qexe($q); print $q;
}

//require "util.php";
require "admin-util.php";

?>


</div></body>
</html>
