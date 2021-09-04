$(function(){
	
});
function dirlist(dir){
	sv='http://img.flixerapp.com';
	url='http://img.flixerapp.com/?action=dirlist&dir='+dir;
	
	$.getJSON(url,function(r){
		$.each(r.files,function(,f){
			i='<div class=imgthumb><a href='+sv+'/'+dir+'/'+f+' target=_blank><img src='+sv+'/'+dir+'/'+f+' height=80px; ></a><br>'+f+'<a href='+sv+'/?action=img-delete&file='+dir+'/'+f+' class=\'fa fa-trash pull-right danger\'></a></div>';
			$('.imgs').append(i);
		});
	});
}