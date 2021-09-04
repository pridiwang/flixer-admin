<?php 
if($action=='title-media'){
	$tt=qdr("select code,name_en,name_th,audio,subtitle_type,status,premium,premium_hd,total_episodes from title where id='$id' ");
	extract($tt);
	//print_r($tt);
	/*print "$name_en $code $total_episodes EPs <table class='table table-border table-sm'><thead>tr><td>EP</td>";
	
	$sflist=array('_480_jp.mp4','_480_th.mp4','_720_jp.mp4','_1080_jp.mp4','_th.srt','.mp4','.smil','hd.smil');
	while(list(,$s)=each($sflist)) print "<td>$s</td>";
	print "</tr></thead><tbody>";
	//$total_episodes=5;
	
	for($e=1;$e<=$total_episodes;$e++){
		$ep=$e;
		if($e<10) $ep='0'.$e;
		print "<tr><td>$ep</td>";
		reset($sflist);
		
		while(list(,$s)=each($sflist)){
			$url="http://i2.kudson.net:8081/vod/dex/$code/$code"."_$ep$s/playlist.m3u8";
			if($s=='_th.srt')$url="http://i2.kudson.net:8081/vod/dex/$code/$code"."_$ep$s/subtitle.m3u8";
			$c='';
			//print "<li>$url";
			if(check200($url)) $c='ok';
			print "<td><a href=$url>$c</a></td>";
		}
	
		print "</tr>";
	}
	print "</table>";
	*/
	$url="http://i2.kudson.net/api.php?action=dirlist&code=$code";
	//print "url $url";
	$rs=getjson($url);
	
	$result=$rs->result;
	//print "<pre>";print_r($result);print "</pre>";
	while(list(,$f)=each($result)){
		$files[]=$f->name; 
		//$sizes[]=$f->size;
		//$updates[]=$f->updated;
	}
	sort($files);
	print "<div class='row'><h3>media / $code</h3>";
	while(list(,$f)=each($files)){
		print "<div class='col-md-2 media-file'><a href=admin-player.php?path=http://i2.kudson.net:8081/vod/dex/$code/$f/playlist.m3u8 target=_blank>$f</a> <a class='pull-right' style=display:none;>.</a></div>";
	}
	print "</div>";
	$key="e24R6VDBwDBy8EaCSisG97r6xiTx0C";
		print "
	<link href=http://hayageek.github.io/jQuery-Upload-File/4.0.11/uploadfile.css rel=stylesheet>
<script src=http://hayageek.github.io/jQuery-Upload-File/4.0.11/jquery.uploadfile.min.js></script>
	<div>Drop file(s) below to upload
	<div id=fileuploader></div>
	<button onclick=location.reload(); class='btn btn-success'>Refresh</button>
	</div>
<script>
$(document).ready(function()
{
	$('#fileuploader').uploadFile({
	url:'http://m92.flixerapp.com/api.php?action=upload&code=$code&key=$key',
	fileName:'userfile',
	multiple:true,
	dragDrop:true,
	onSuccess:function(files,data,xhr,pd){
		//files: list of files
		//data: response from server
		//xhr : jquer xhr object
		location.reload();
	}
	});
});
</script>	
	";
}
if($action=='media-test'){
	$q="select ip,port,name from cdn where 1 ";//app='flixer' ";
	$dt=qdt($q);
	$path="/vod/dex/$code/$code"."_$ep$hd.smil/playlist.m3u8";
	while(list(,$dr)=each($dt)){
		extract($dr);
		$fpath="http://$ip:$port$path";
		$epath="chrome-extension://emnphkkblegpebimobpbekeedfgemhof/player.html#$fpath";
		print "<div style=display:inline-block;>
		$name<br><iframe frameborder=0 border=0 src=admin-player.php?muted=muted&path=$fpath style=display:inline-block></iframe>
		</div>";
	}
	
}
function getjson($url){
	$auth="Authorization: e24R6VDBwDBy8EaCSisG97r6xiTx0C";
	$ch = curl_init ($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $auth ));
	 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	 $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result);
 
}
function check200($url){
	$ch = curl_init ($url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_TIMEOUT,10);
	$output = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if($httpcode==200) return true;
	else return false;

}
?>