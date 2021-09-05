<html><head><title></title>
<link rel=stylesheet href=https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.0/css/bootstrap.min.css />

<script src=https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.0/js/bootstrap.min.js></script>



<link rel=stylesheet href=css/jquery.tagsinput.min.css />
<script src=js/jquery.tagsinput.min.js ></script>
</head><body >
<?php 
session_start();
extract($_GET);
if(!$_SESSION[user]) exit;
require "admin-include.php";
if($action=='title-tags-update'){
    $q="update title set tags='$_POST[tags]' where id='$id' ";
    $rs=qexe($q); //print $q;
    $action='title-tags-edit';
    print "<div class='alert alert-success'>updated </div><i class='fa fa-close close' onclick=window.close();>close</i>
    <script>
    document.domain='flixerapp.com';
    $(function(){
    $('#title-tags-$id',opener.document).html('$_POST[tags]');
    window.close();    
});
    </script>

    ";

}
if($action=='title-tags-edit'){
    $q="select name_en from category ";
    $dt=qdt($q);
    while(list(,$dr)=each($dt)){
        extract($dr);
        $tagslist.="<span class='tgs' >$name_en</span> ";
    }
    $q="select id,name_en,tags from title where id='$id' ";
    $dr=qdr($q);
    extract($dr);
    print "<form action=?action=title-tags-update&id=$id method=post class='form w-100' style=max-width:500px >
    <h3>$name_en </h3>
    <textarea name=tags class='form-control tags'>$tags</textarea>$tagslist 
    <br><button class='float-right'>Update Tags</button>
    </form>";
}
?>
<script>
$(function(){
	$('.tags').tagsInput();		
    $('.tgs').on('click',function(){
       $('.tags').addTag($(this).text());
    });
});
</script>
<style>
  div.tagsinput{width:600px!important;}
    .tgs{background:#afa;padding:0 2px;border:1px solid #aaa;}
</style>
</body></html>