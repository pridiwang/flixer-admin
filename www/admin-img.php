<?php 
if($action=='img'){
	if(strpos($code,'_')){ $code=explode('_',$code)[0]; $dir='dex/'.$code;}
	
	if(!$dir){
		if($code) $dir='dex/'.$code; 
	}
	$sv='https://img.flixerapp.com';
	$url='https://img.flixerapp.com/?action=dirlist&dir='.$dir;
	print "<h3><a href=?action=edit&tb=title&id=$title class='fa fa-arrow-left'></a> image for $code </h3><div class=imgs></div>";
	
	$json=file_get_contents($url);
	//print "<pre>$json</pre>";
	$data=json_decode($json);
	//print_r($data);
	$rnd=rand(0,999999);
	while(list(,$file)=each($data->files)){
		print "<div class=imgthumb>
		<a href=$sv/$dir/$file target=_blank><img src=$sv/$dir/$file?$rnd height=80px; ></a>
		<br>$file</div>";
//		<a href=$sv/?action=img-delete&file=$dir/$file class='fa fa-trash pull-right danger' target=tmp onclick=\"return confirm('confirm delete $dir/$file ?');\"></a></div> ";
	}
	if(!$eps) $eps=60;
	
	for($i=1;$i<=$eps;$i++){
		$j=$i;
		if($i<10) $j='0'.$i;
		$es.="<option value=$j>EP.$j ";
	}
	print "<p><hr><form action=$sv/?action=img-upload&dir=$dir method=post enctype=multipart/form-data class='form form-inline row' target=tmp >
	Upload Image / select type : <select class=form-control name=fname><option>
	<option>banner<option>poster
	<option>banner-premium<option>poster-premium
	$es
	</select>
	<input class='form-control' type=file name=file > <button type=submit class='btn btn-primary form-control' ><i class='fa fa-upload'></i> upload </button>
	</form>";
	print "<iframe id=tmp name=tmp style=height:20px;></iframe>";
	
}
if($action=='img'){
	print "
	<link href=https://hayageek.github.io/jQuery-Upload-File/4.0.11/uploadfile.css rel=stylesheet>
<script src=https://hayageek.github.io/jQuery-Upload-File/4.0.11/jquery.uploadfile.min.js></script>
	<div>Drop file(s) below to upload
	<div id=fileuploader></div>
	<button onclick=location.reload(); class='btn btn-success'>Refresh</button>
	</div>
<script>
$(document).ready(function()
{
	$('#fileuploader').uploadFile({
	url:'https://img.flixerapp.com/?action=img-upload2&dir=$dir',
	fileName:'file',
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
?>

<style>
.imgthumb{padding:5px;margin:3;border:1px solid 1px #ddd;display:inline-block;font-size:8pt;}
.imgthumb img{height:80px;}
iframe.tmp{width:0;height:0;}
</style>