document.domain = 'flixerapp.com';
$(function(){
	$('input.date').datepicker({format:'yyyy-mm-dd'});
	$('.int, .rptno, tbody td.views').number(true,0);
	$('tbody tr td.amount, tbody td.real , tfoot td.real').number(true,2);
	$('.btn').addClass('btn-sm');
	$('.title-tags').click(function(e){
		var id=$(this).attr('tid');
		console.log('opening tags edit '+id);
		e.stopPropagation();
		
		window.open('admin-popup.php?action=title-tags-edit&id='+id,'_blank','toolbar=0,location=0,menubar=0,width=600,height=400,top=100,left=100');
		return false;
	});
	//$('.datetime').datetimepicker({	format:'yyyy-mm-dd hh:ii'});
});
function aisCharge(platform_id){
	url = '/ais/ais-daily.php?action=directCharge&mobile=' + platform_id;
	console.log(url);
	$.getJSON(url, function (r) {
		if (r.result == 'failed') {
			alert('failed to charge');	
		}
		if (r.result == 'SUCCESS') {
			alert('charged OK');
			window.location.href='?action=ais-user&platform_id='+platform_id;
		}
	});
}
function aisTrial(platform_id){
	url = 'admin.php?action=ais-trial&mobile=' + platform_id;
	console.log(url);
	$.getJSON(url, function (r) {
		if (r.result == 'failed') {
			alert('failed to charge');	
		}
	});
}

function yearchange(i){
	d1=$('#year').val();
	d2=parseInt(d1)+i;
	$('#year').val(d2);
	$('#rptform').submit();
}
function datechange(i){
	d1=$('#date').val();
	mo=d1.substring(5,7);
	yr=d1.substring(0,4);
	dd=d1.substring(8,10);
	console.log(' yr '+yr+' mo '+mo+ 'dd '+dd);
	dd= parseInt(dd)+i;
	d2=new Date(yr,mo-1,dd);
	d22=formatDate(d2);
	$('#date').val(d22);
	$('#rptform').submit();
}
function rangemonth(i){
	d1=$('#date1').val();
	mo=d1.substring(5,7);
	yr=d1.substring(0,4);
	console.log(' yr '+yr+' mo '+mo);
	mo=parseInt(mo)+i;
	if(mo>12){yr++;mo=1;}
	if(mo<1){yr--;mo=12;}
	if(mo<10) mo='0'+mo;
	console.log(' yr '+yr+' mo '+mo);
	d11=yr+'-'+mo+'-01';
	d2=new Date(yr,mo,0);
	d22=formatDate(d2);
	$('#date1').val(d11);
	$('#date2').val(d22);
	$('#rptform').submit();
}
function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [year, month, day].join('-');
}