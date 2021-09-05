<?php
function yearrange(){
	global $yr1,$yr2 ;
	extract($_POST);
	if(!$yr1) $yr1=2017;
	if(!$yr2) $yr2=date('Y');
	$o= " Year 
	<input type=text id=yr1 class=year name=yr1 value=$yr1 > - 
	<input type=text id=yr2 class=year name=yr2 value=$yr2 >
	";
	return $o;

}
function yearnav(){
	global $year;
	extract($_POST);
	if(!$year) $year=date('Y');
	$o= "<div class='col-sm-2'><i class='fa fa-2x fa-chevron-circle-left' onclick=yearchange(-1);></i>
	<input type=text id=year class=year name=year value=$year >
	<i class='fa fa-2x fa-chevron-circle-right' onclick=yearchange(1);></i>
	</div>";
	return $o;
}
function datepick(){
	global $date;
	extract($_POST);
	if(!$date) $date=date('Y-m-d');
	$o= "<div class='col-sm-4' ><i class='fa fa-2x fa-chevron-circle-left' onclick=datechange(-1);></i>
	<input type=text id=date class=date name=date value=$date >
	<i class='fa fa-2x fa-chevron-circle-right' onclick=datechange(1);></i>
	</div>";
	return $o;
}
function daterange(){
	global $date1,$date2;
	extract($_POST);
	if(!$date1) $date1=date('Y-m-01');
	if(!$date2) $date2=date('Y-m-d',mktime(0,0,0,date('m')+1,0,date('Y')));
	$o= "<div class='col-md-3'><i class='fa fa-chevron-circle-left' onclick=rangemonth(-1);></i>
	<input type=text id=date1 class=date name=date1 value=$date1 >
	<input type=text id=date2 class=date name=date2 value=$date2 >
	<i class='fa fa-chevron-circle-right' onclick=rangemonth(1);></i>
	</div>";
	return $o;
	
}
function datenav(){
	global $date;
	extract($_POST);
	if(!$date) $date1=date('Y-m-d');
	
	$o= "<div class='col-sm-4'><i class='fa fa-2x fa-chevron-circle-left' onclick=datechange(-1);></i>
	<input type=text id=date class=date name=date value=$date >
	<i class='fa fa-2x fa-chevron-circle-right' onclick=datechange(1);></i>
	</div>";
	return $o;
	
}
function monthnav(){
	global $moyr,$mo,$yr,$action,$report,$type;
	
	if($_POST[moyr]){
		extract($_POST);
		$mo=substr($moyr,0,2);
		$yr=substr($moyr,3,4);
	}
	if(!$yr)$yr=date('Y');
	if(!$mo)$mo=date('m');
	print "<form action=?action=$action&report=$report&type=$type method=post id=monthform> 
	 Month 
	<i class='fa fa-chevron-circle-left text-primary' onclick=navmonth(-1);></i>
	<input type=text size=5 class=monthpicker id=monthpicker name=moyr value=$mo-$yr onchange=this.form.submit();>
	<i class='fa fa-chevron-circle-right text-primary' onclick=navmonth(1);></i>
	<button type=submit class='btn btn-primary'>Report</button>
	</form><script>
	$('#monthpicker').datepicker({
		format:'mm-yyyy', startView:'months', minViewMode: 'months'
	});
	function navmonth(i){
		moyr=$('#monthpicker').val();
		mo=moyr.substring(0,2);
		yr=moyr.substring(3,7);
		console.log(' yr '+yr+' mo '+mo);
		mo=parseInt(mo)+i;
		if(mo>12){yr++;mo=1;}
		if(mo<1){yr--;mo=12;}
		if(mo<10) mo='0'+mo;
		console.log(' yr '+yr+' mo '+mo);
		moyr=mo+'-'+yr;
		$('#monthpicker').val(moyr);
		$('#monthform').submit();
	}
	</script>";
}
function ftype($fld){
	global $intflds,$realflds,$amountflds;
	$o='string';
	if(strpos($fld,'date')) $o='date';
	if($fld=='date') $o='date';
	if(in_array($fld,$intflds)) $o='int';
	if(in_array($fld,$realflds)) $o='real';
	if(in_array($fld,$amountflds)) $o='amount';
	
	return $o;
}
function stdchart($q,$type='line'){
	global $db,$toreport,$tosum,$intflds;
	if(!$type) $type='line';
	$dt=qdt($q);
	$sum=array();
	$chartskip=array('action');
	while(list($i,$dr)=each($dt)){
		$j=0;
		while(list($fld,$val)=each($dr)){
			if($fld=='id') continue;
			if(in_array($fld,$chartskip)) continue;
			if($fld=='bal'){
				if($i==0) $bal=$val;
				else $bal+=$dr[all];
				$val=$bal;
			}
			$ftype=ftype($fld);
			if($fld=='date') $val=substr($val,8,2);
			if(strlen($d[$j])>1) $d[$j].=",";
			$l[$j]=$fld;
			if($j==0) $d[0].="'".str_replace("'","",$val)."'";
			else $d[$j].=$val;
			$j++;
		}
	}
	$colorlist=array('','rgba(255,60,60,1)','rgba(60,60,255,1)','rgba(60,255,60,1)','rgba(200,80,80,1)','rgba(80,200,80,1)');
	for($i=1;$i<count($d);$i++){
		if($i>1){ $datasets.=","; $yaxes.=",";}
		$datasets.="{label:'$l[$i]',borderColor:'$colorlist[$i]', data:[ $d[$i] ], yAxisID:'y$i' }";
		if($i % 2  == 0) $p='left';
		else $p='right';
		$yaxes.="{id:'y$i',position:'$p' }";
	}
	print "<canvas id=chart2 width=600 height=300></canvas><script>
	$(function(){
		var lineChartData={
			labels: [ $d[0] ],
			datasets: [
			$datasets
			]
		};	
		var ctx=document.getElementById('chart2').getContext('2d');
		window.myLine= new Chart(ctx,{
			type: '$type',
			data:lineChartData,
			options:{
				scales:{ yAxes:[
					$yaxes 
				]}
			}
		});
	});
	</script>";
}
function stdexport($q){
	$dt=qdt($q);
	if(!$dt) return false;
	while(list($i,$dr)=each($dt)){
		$k=$i+1;
		$csvb.=$k;
		while(list($fld,$val)=each($dr)){
			if($i==0) $csvh.=",$fld";
			$csvb.=",".$val;
		}
		$csvb.="\n";
	}
	$csv=$csvh."\n".$csvb;
	$file="csv/export.csv";
	file_put_contents($file,$csv);
	$rnd=rand(0,9999);
	//print "<pre>$csv</pre>";
	$count=count($dt);
	print "<br>$count exported<br><a href=$file?$rnd target=_blank> Excel CSV</a> ";
}

function stdreport($q,$isdt=true){
	global $db,$toreport,$tosum,$intflds,$realflds,$amountflds,$monthflds,$prmt,$hideflds, $linkflds,$csv;
	$dt=qdt($q);
	if(!$dt) return false;
	//if(!$dt){  print $db->error.' '.$q; }
	if(count($dt)==0) print $q;
	$sum=array();
	array_push($intflds,$tosum);
	while(list($i,$dr)=each($dt)){
		$k=$i+1;
		if($i==0){
			while(list($fld,$val)=each($dr)){
				if(in_array($fld,$hideflds)) continue;
				$ftype=ftype($fld);
				if($ftype=='int') $ftype='inthead';
				
				$th.="<td class='$fld $ftype' >$fld</td>";
				if($fld!='action') $ch.=",".$fld;
			} 
			
			reset($dr);
		
		}
		
		list($fld,$val)=each($dr);
		$link="?action=report&report=$toreport&$val&$prmt&id=$val&fld=$fld&val=$val";
		$tb.="<tr  ><td class='int'>$k</td>";
		
		reset($dr);
		$j=0;
		$cb.=$k;
		$total=0;
		while(list($fld,$val)=each($dr)){
			if(in_array($fld,$hideflds)) continue;
			$ftype=ftype($fld);
			if(($fld=='total')&&($val=='')) {
				
				$val=$total;
				$total=0;
			}
			if($fld=='bal') {
				$bal+=$dr[all];
				$val=$bal;
			}
			if(($fld=='balance')&&($val=='bal')){
				if($dr[status]=='not paid'){ $bal+=$dr[revenue]+$dr[premium_revenue];}
				$val=$bal;
			}
			
			if(($j==0)||(in_array($fld,$linkflds)))$tb.="<td class='$fld $ftype' ><a href=$link>$val</a></td>";
			else $tb.="<td class='$fld $ftype' value='$val' >$val</td>";
			
			if($fld!='action') $cb.=",".strip_tags(str_replace(',','',$val));
			if(in_array($fld,$tosum)) $sum[$fld]+=$val;
			if(in_array($fld,$monthflds)){
				$total+=$val;				
			} 
			
			if($fld=='date') $val=substr($val,8,2);
			if(strlen($d[$j])>1) $d[$j].=",";
			$l[$j]=$fld;
			$d[$j].=$val;
			$j++;
		}
		$tb.="</tr>";
		$cb.="\n";
		
	}
	reset($dt);
	list(,$dr)=each($dt);
	while(list($fld,$val)=each($dr)){
		if(in_array($fld,$hideflds)) continue;
		if(in_array($fld,$tosum)){ $val=$sum[$fld]; $ftype='int';}
		else {$val=''; $ftype='';}
		$ftype=ftype($fld);
		if((in_array($fld,$intflds))&&($val)) $val=number_format($val,0);
		if((in_array($fld,$amountflds))&&($val)) $val=number_format($val,2);
		$ftype='real';
		if($val=='') $ftype='';
		$tf.="<td class='sum $ftype'>$val</td>";
	}
	
	print "<table class='table table-border rpt'>
	<thead><tr><td class=no>No.</td>$th</tr></thead>
	<tbody>$tb</tbody>
	<tfoot><td ></td>$tf</tr></tfoot>
	</table>";
	
	$csv.="$ch\n$cb";
	$file="csv/report.csv";
	file_put_contents($file,$csv);

	print "<br><a href=$file?$rnd target=_blank> Excel CSV</a> ";
	
	

	
	
} 
if($action=='report'){
	$rptlist=array('today'=>'Today','downloads'=>'Downloads','view-all'=>'Total View','view-top20'=>'Top20','user-new'=>'New User','feedback'=>'Feedback','view-titles'=>'Titles','title-daily'=>'Daily','view-licensor-year'=>'Licensors','share-licensors-year'=>'Lic.Summary','view-licensor-title'=>'Lic.Title ','view-licensor-years'=>'Lic.Years ','title-years'=>'By Title','titles-year'=>'T2018','cdn'=>'CDN','coupon-redeemed'=>'Coupon','ais'=>'AIS','user-active'=>'Active Users','rental'=>'Rental');
//'chart-newuser'=>'Chart New User','view-month'=>'Views','chart-views'=>'Chart Views');
	$rpticon=array('today'=>'calendar','downloads'=>'download','view-all'=>'eye','user-new'=>'user','feedback'=>'comment','view-titles'=>'film','view-licensor-year'=>'handshake-o');
	if($_SESSION[admin_type]=='licensor') $rptlist=array('view-licensor-year'=>'Year','view-licensor-title'=>'Title');
	if($_SESSION[admin_type]=='licensors') $rptlist=array('view-licensor-year'=>'Licensor');
	if($_SESSION[admin_type]=='marketing') $rptlist=array('today'=>'Today','downloads'=>'Downloads','title-views-top50'=>'Top Views','title-views'=>'Views by Title','title-daily'=>'Daily','ais'=>'AIS','rental'=>'Rental');
	if($_SESSION[admin_type]=='mkt') $rptlist=array('today'=>'Today','downloads'=>'Downloads','view-all'=>'Total View','user-new'=>'New View','feedback'=>'Feedback','view-titles'=>'Titles','title-daily'=>'Daily','ais'=>'AIS');
	if($_SESSION[admin_type]=='support') $rptlist=array('today'=>'Today','downloads'=>'Downloads','title-views-top50'=>'Top Views','title-views'=>'Views by Title','title-daily'=>'Daily','ais'=>'AIS','rental'=>'Rental');
	if($_SESSION[admin_type]=='report-view') $rptlist=array('today'=>'Today','downloads'=>'Downloads','title-views-top50'=>'Top Views','title-views'=>'Views by Title','title-daily'=>'Daily');
	if($_SESSION[admin_type]=='service') $rptlist=array('today'=>'Today','downloads'=>'Downloads','view-all'=>'Total Views','view-top20'=>'Top 20','user-new'=>'New User','view-titles'=>'Titles','title-daily'=>'Daily','coupon-redeemed'=>'Coupon','ais'=>'AIS','user-active'=>'Active Users','rental'=>'Rental');
	print "Report : ";
	while(list($a,$t)=each($rptlist)) print "<a href=?action=report&report=$a><i class='fa fa-$rpticon[$a]'></i>$t</a> | ";
}
$tosum=array('views','users','share','revenue','premium_views','premium_revenue','premium_users','total_views');
function aroptions($ar,$val){
	while(list($i,$v)=each($ar)){
		$sl='';
		if($val==$i) $sl='selected';
		$o.="<option  $sl value=$i>$v</option>";
	}
	return $o;
}
function getvr($title,$y,$m){

	$share_percent=qval("select share_percent from title where id='$title' ");
	$q="select share,premium_share from income where year(date)='$y' and month(date)='$m' ";
	$dr=qdr($q); 
	//print_r($dr);
	extract($dr);
	$q="select sum(views) as 'v',sum(p_views) as 'vp',sum(r_views) as 'vr' from user_views_daily where title='$title' and date_format(date,'%Y-%c')='$y-$m'";
	$dr2=qdr($q);
	extract($dr2);
	//print_r($dr2);
	return array('v'=>$v,'r'=>$v*$share*$share_percent/100,'vp'=>$vp,'rp'=>$vp*$premium_share*$share_percent/100,'vr'=>$vr);

}
if($report=='title-daily'){
	extract($_POST);
	print "<h3>Daily Views</h3><form action=?action=$action&report=$report method=post class='' >".daterange()."<select onchange=this.form.submit(); class='col' name=title><option>".qoptions("select id,concat(name_en,' - ',id) 'name' from title order by name_en ",$title)."</select><button>report</button></form>";
	if($title){
		
		$q="select name_en,name_th,tags,if(premium=1,'Yes','-') as 'premium' 
		,if(rental=1,'Yes','-') as rental, status from title where id='$title' ";
		stdreport2($q);
		
		$q=" select t1.date 'id',t1.date,sum(t1.views) as 'views',sum(t1.p_views) 'premium_views',sum(t1.r_views) 'rental_views', sum(t1.views)+sum(t1.p_views)+sum(t1.r_views) as 'total_views' from user_views_daily as t1 where t1.title='$title' and t1.date between '$date1' and '$date2' group by t1.title,t1.date ";
		//print $q;
		$csv="Name EN,$tt[name_en]\nName TH,$tt[name_th]\nTags,\"$tt[tags]\"\nDate,$date1,$date2\n";
		stdreport($q);
	}

}
if($report=='rental-user'){
	$email=qval("select email from user where id='$id' ");

	print "<h3>Rental : $email</h3><form action=?action=$action&report=$report method=post><button>report</button></form>";
	$q="select t2.id,t2.name_en ,t1.purchase_datetime,t1.status,t1.watch_start from user_rental as t1 ,title as t2 where t2.id=t1.title and t1.user='$id' ";
	stdreport($q); 
}
if($report=='rental-title'){
	print "<h3>Rental</h3><form action=?action=$action&report=$report method=post>".daterange()."<select name=title>".qoptions("select id,name_en 'name' from title ",$id)."</select><button>report</button></form>";
	$q="select t2.id,t2.email 'user_email',t1.purchase_datetime,t1.status,t1.watch_start from user_rental as t1,user as t2 where t2.id=t1.user and t1.purchase_datetime between '$date1' and '$date2 23:59:59' and t1.title='$id' ";
	$toreport="rental-user";
	stdreport($q);
}
if($report=='rental'){
	print "<h3>Rental</h3><form action=?action=$action&report=$report method=post>".daterange()."<button>report</button></form>";
	$q="select t1.title,t2.name_en,count(t1.id) 'rented' from user_rental as t1,title as t2 where t2.id=t1.title and t1.purchase_datetime between '$date1' and '$date2 23:59:59' group by t1.title ";
	$toreport="rental-title&date1=$date1&date2=$date2";
	stdreport($q);
}
if($report=='user-active'){
	extract($_POST);
	print "<form action=?action=$action&report=$report method=post>".yearnav()."<button class='btn btn-primary'>Report</button></form>";
	$q="select date_format(date,'%b %Y') month, count(distinct(user)) 'users' from user_views where year(date)='$year' group by date_format(date,'%Y%m') ";
	print "<h3>Monthly Active User</h3>";
	//stdreport($q); //print "<br>$q";
	
	$q="select date_format(date,'%b %Y') month, count(distinct(user)) 'users' from user_views where year(date)='$year' and premium=1 group by date_format(date,'%Y%m') ";
	//print "<h3>Monthly Active Premium User 👑</h3>";
	//stdreport($q); //print "<br>$q";
	$q="select date_format(date,'%b %Y') month, users, premium_users, views, p_views 'premium_views' from user_views_monthly where year(date)='$year' order by date ";
	stdreport($q); //print "<br>$q";

}
if($report=='premium-users'){
	print "<form action=?action=$action&report=$report method=post>".monthnav()."<button>Report</button></form>";
	$q=" select sum(if(platform='android',1,0)) 'android',sum(if(platform='ios',1,0)) 'ios' from user_premium where receipt_data<>'' and month(validated)='$mo' and year(validated)='$yr' and valid=1 and expired=0 group by date_format(validated,'%Y%m') ";
	$tosum=array('android','ios');
	stdreport($q); print $q;
}
if($report=='ais-user'){
	if($val) $platform_id=$val;
	extract($_POST);
	$platform_id=str_replace('-','',$platform_id);
	$platform_id=str_replace(' ','',$platform_id);
	if(substr($platform_id,0,1)==0) $platform_id='66'.substr($platform_id,1,9);
	print "<form action=?action=$action&report=$report method=post><input type=name name=platform_id minlength=10 maxlength=15 placeholder='mobile' value='$platform_id' ><button>Report</button></form>";

	if(strlen($platform_id)==11){
		$q="select user,status,valid,expire,platform_id,platform_private_id from user_platform where platform_id='$platform_id' ";
		$q="select user,platform_id,status,expire_datetime,payment_pending,last_charge,logs from user_platform where platform_id='$platform_id' ";
		stdreport2($q);
		$dr3=qdr($q);
		if($dr3[expire_datetime]<date('Y-m-d H:i:s')) print "<span class='btn btn-primary' onclick=aisCharge('$platform_id'); >Charge $platform_id </span>";
		if($dr3[status]=='verified') print "<span class='btn btn-primary' onclick=aisTrial('$platform_id'); > Start Trial $platform_id </span>
		";
	}
}
function stdreport2($q){
	$dr=qdr($q); //print $q;
	print "<table class='table table-bordered rpt'><thead><tr><td>Name</td><td>Value</td></tr></thead><tbody>";
	while(list($fld,$val)=each($dr)){
		if($fld=='logs') $val="<pre>$val</pre>";
		if($fld=='user') $val="<a href=?action=user-check&user_id=$val>$val</a>";
		print "<tr><td>$fld</td><td class='$fld'>$val</td>";
	}
	print "</tbody></table>";
}
if($report=='ais-uncharge'){
	$q="select platform_id,user,status,expire_datetime from user_platform where status in ('trial','activate') and expire_datetime<now() ";
	$toreport='ais-user';
	stdreport($q,false);
}
if($report=='ais'){
	print "<form action=?action=$action&report=$report method=post>".monthnav()."<button>Report</button><a href=?action=$action&report=ais-user> AIS User </a> </form>";
	$tosum=array('users');
	$q="select status,count(id) 'users' from user_platform where platform='ais' group by status ";
	print "<h3>Summary AIS</h3>";
	stdreport($q,false);
	print "New = trial Today , Acc. = (trial+activate), verified= OTP success but no pack/use, activate= charged  ";
	print "<div class='row'><div class='col-md-3'>";
	$q="select date(datetime) 'date',count(id) 'users' from platform_data where month(datetime)='$mo' and year(datetime)='$yr' and platform_private_id<>'' and action='activate'  group by date(datetime) ";
	print "<h3>Subscribe at AIS</h3>";
	stdreport($q,false);
	print "</div><div class='col-md-3'>";
	$q="select date(start_datetime) 'date',count(id) 'users' from user_platform where status in ('trial','activate') and month(start_datetime)='$mo' and year(start_datetime)='$yr' group by date(start_datetime) ";
	print "<h3>Start Trial as Flixer</h3>";
	stdreport($q,false);
	print "</div><div class='col-md-3'>";
	$q="select date(datetime) 'date',count(id) 'users' from platform_data where month(datetime)='$mo' and year(datetime)='$yr' and platform_private_id<>'' and action in( 'terminate','unsubscribe')  group by date(datetime) ";
	print "<h3>Terminate at AIS</h3>";
	stdreport($q,false);
	print "</div><div class='col-md-3'>";
	$q="select date(datetime) 'date',count(id) 'users',count(id)*79 'amount' from user_receipt where month(datetime)='$mo' and year(datetime)='$yr' and platform_private_id<>''  group by date(datetime) ";
	print "<h3>Charged To AIS </h3>";
	$tosum=array('amount');
	stdreport($q,false);
	print "</div></div>";
}
if($report=='email-list'){
	extract($_POST);
	if($premium) $pchk=' checked';
	print "<form action=?action=$action&report=$report method=post>".daterange()."<input type=text name=titles placeholder='titles' value='$titles' > <input $pchk type=checkbox value=1 name=premium><button>export</button></form>";
	if($premium) $cond.=" and t1.is_trial_pass=1 ";
	else $cond.=" and t2.premium=0 ";
	$q="select
	t2.email from user_premium as t1, user as t2 where t2.id=t1.user and t2.email<>'' 
	and t2.email REGEXP '^[a-zA-Z0-9][a-zA-Z0-9._-]*[a-zA-Z0-9._-]@[a-zA-Z0-9][a-zA-Z0-9._-]*[a-zA-Z0-9]\\.[a-zA-Z]{2,63}$'
	and	t2.last_login>'$date1' $cond ";
	
	if($titles){
		$cond.=" and t1.title in (select id from title where tags like '%%$titles%%' ) ";
		$q="select
	t2.email from user_views as t1, user as t2 where t2.id=t1.user and t2.email<>'' 
	and t2.email REGEXP '^[a-zA-Z0-9][a-zA-Z0-9._-]*[a-zA-Z0-9._-]@[a-zA-Z0-9][a-zA-Z0-9._-]*[a-zA-Z0-9]\\.[a-zA-Z]{2,63}$'
	and	t2.last_login>'$date1' $cond ";
	}
	print $q;
	stdexport($q);
}
$upstlist=array('new'=>'Request','verified'=>'Verified','trial'=>'Trial','activate'=>'Activate','terminate'=>'Terminate');
if($report=='platform-date'){
	$date=$id;
	print "<form action=?action=$action&report=$report method=post>".datenav()."<button>report</button></form>";
	$q="select t1.id,t1.user,t2.email 'user_email',t1.platform_id,t1.start_datetime,t1.expire_datetime,t1.status from user_platform as t1,user as t2 where t2.id=t1.user and date(t1.start_datetime)='$date' ";
	stdreport($q);
}
if($report=='platform-subs'){
	extract($_POST);
	if(!$status) $status='trial';
	$stoptions=aroptions($upstlist,$status);
	print "<form action=?action=$action&report=$report method=post>".daterange()."<select name=status>$stoptions</select><button>report</button></form>";
	$q="select date(t1.start_datetime) 'date',count(t1.id) 'qty',status from user_platform as t1 where t1.start_datetime between '$date1' and '$date2' and status='$status' group by date(t1.start_datetime) ";
	$toreport='platform-date';	
	stdreport($q); //print "<br>$q";

}
if($report=='titles-year'){
	extract($_POST);
	if(!$yr) $yr=date("Y");
	for($i=2018;$i<=date('Y');$i++){
		$sl='';
		if($i==$yr) $sl='selected ';
		$yoptions.="<option $sl >$i";
	}
	print "<table class=' '><form action=?action=$action&report=$report method=post><tbody>
	<tr><td>Year</td><td><select onchange=this.form.submit(); name=yr>$yoptions</select>
	<button class='btn btn-sm btn-primary' >Report</button></td></tr></tbody></form></table>"; 
	$mtext=" Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec";
	$mnts=explode(' ',$mtext);
	$thead="<tr><td rowspan=2>No</td><td rowspan=2>Title</td><td rowspan=2>Name</td>";
	$csvh.=",title,name";
	for($m=1;$m<=12;$m++){
		$thead.="<td colspan=4>$mnts[$m]</td>";
		$csvh.=",$mnts[$m],,,";
	}
	$thead.="</tr><tr>";
	$csvh.="\n,,";
	for($m=1;$m<=12;$m++){
		$thead.="<td>Views</td><td>Revenue</td><td>Views(P)</td><td>Revenue(P)</td>";//<td>Views(T)</td>";
		$csvh.=",Views,Revenue,Views(P),Revenue(P)";
	}
	$thead.="</tr>";

	print "<table class='table table-bordered dataTable' ><thead>$thead</thead><tbody>";
	$q="select id 'title',name_en 'name' from title where lc_2018=1 order by name ";
	$dt=qdt($q);
	while(list($i,$dr)=each($dt)){
		$k=$i+1;
		extract($dr);
		print "<tr><td>$k</td><td>$title</td><td>$name</td>";
		$csvb.="$k,$title,$name";
		for($m=1;$m<=12;$m++){
			$info=getvr($title,$yr,$m);
			extract($info);
			print "<td class=int>$v</td><td class=real>$r</td><td class=int>$vp</td><td class=real>$rp</td>";//<td>$vr</td>";
			$csvb.=",$v,$r,$vp,$rp";
		}
		print "</tr>";
		$csvb.="\n";
	}
	print "</tbody></table>";
	$csv="$csvh\n$csvb";
	//print "<pre>$csv</pre>";
	$file="csv/report.csv";
	file_put_contents($file,$csv);
	print "<a href=$file?$rnd target=_blank> Excel CSV</a> ";

}
if($report=='title-years'){
	extract($_POST);
	print "<table class='table table-bordered '><form action=?action=$action&report=$report&viewby=$viewby method=post><tbody>
	<tr><td>Title</td><td><select name=title onchange=this.form.submit() ><option>".qoptions("select id,name_en 'name' from title order by name_en ",$title)."</select>
	<button class='btn btn-sm btn-primary' >Report</button></td></tr>
	
	</tbody></form></table>";  
}
if(($report=='title-years')&&($title)){
	if(!$viewby) $viewby='month';
	print "
	<a class='btn btn-default' href=?action=$action&report=$report&title=$title&viewby=month>	View and Revenue by Month	</a>
	<a class='btn btn-default' href=?action=$action&report=$report&title=$title&viewby=episode>	View by Episode	</a>
	";
	$tt=qdr("select * from title where id='$title' ");
	$lc=qdr("select * from licensor where id='$tt[licensor]' ");
	
	print "<div>Licensor : $lc[name]  $tt[share_percent] </div> ";
	$share_percent=$tt[share_percent];
	$csvh.="Report by Title
Title:,\"$tt[name_en]\"
Licensor:,\"$lc[name]\"
";
}
if(($report=='title-years')&&($title)&&($viewby=='episode')){
	
	print "<form action=?action=$action&report=$report&title=$title&viewby=$viewby method=post>".yearnav()."<button>report</button></form>";
	$q="select episode,sum(if(premium=0,1,0)) as 'AVOD views',sum(if(premium=1,1,0)) as 'Premium Views' from user_views where title='$title' and year(date)='$year' and date<date(now()) group by episode ";
	$tosum=array('AVOD views','Premium Views');
	stdreport($q); //print $q;
}
if(($report=='title-years')&&($title)&&($viewby=='month')){
	
	$fyr='2018';
	$lyr=date("Y");
	for($y=$fyr;$y<=$lyr;$y++) $yrs[]=$y;
	
	$o="<table class='table table-bordered dataTable' ><thead><tr><td rowspan=2>Month/Year</td>";
	$csvh.="Month/Year";
	while(list(,$y)=each($yrs)){
		$o.="<td colspan=4>$y</td>";
		$csvh.=",$y,,,";
	} 
	$csvh.="\n";
	$o.="</tr><tr>";
	reset($yrs);
	while(list(,$y)=each($yrs)){
		$o.="<td>Views</td><td>Revenue</td><td>Views(P)</td><td>Revenue(P)</td>";//<td>Views(T)</td>";
		$csvh.=",Views,Revenue,Views(P),Revenue(P)";
	} 
	$o.="</tr></thead><tbody>";
	$csvh.="\n";
	$mtext=" Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec";
	$mnts=explode(' ',$mtext);
	
	for($m=1;$m<=12;$m++){

		$o.="<tr><td>$mnts[$m]</td>";
		$csv.=$mnts[$m];
		reset($yrs);
		
		while(list(,$y)=each($yrs)){
			$dr=getvr($title,$y,$m);
			extract($dr);
			$sum[v][$y]+=$v;
			$sum[r][$y]+=$r;
			$sum[vp][$y]+=$vp;
			$sum[rp][$y]+=$rp;
			$o.="<td class=int>$v</td><td class=real>$r</td><td class=int>$vp</td><td class=real>$rp</td>";//<td class=int>$vr</td>";
			$csv.=",$v,$r,$vp,$rp";
		}
		$csv.="\n";		
		$o.="</tr>";
	}
	
	$o.="</tbody><tfoot><tr><td>Total</td>";
	$csv.="Total";
	reset($yrs);
	while(list(,$y)=each($yrs)){
		$o.="
		<td class=int>".$sum[v][$y]."</td>
		<td class=int>".$sum[r][$y]."</td>
		<td class=int>".$sum[vp][$y]."</td>
		<td class=int>".$sum[rp][$y]."</td>";
		$csv.=",".$sum[v][$y].",".$sum[r][$y].",".$sum[vp][$y].",".$sum[rp][$y];

	}
	$o.="</tr></tfoot></table><style>thead td{text-align:center;}</style>";
	$csv.="\n";
	print $o;
	$csv=$csvh.$csv;
	//print "<pre>$csv</pre>";
	$file="csv/report.csv";
	file_put_contents($file,$csv);
	print "<a href=$file?$rnd target=_blank> Excel CSV</a> ";
	
}
if($report=='tags-viewer'){
	$q="select distinct(user) from user_views where title in (select id from title where tags like '%%rider%%') ";
}
if($report=='coupon-redeemed'){
	$q="select t2.name,count(t1.id) as 'redeemed' from coupon_redeem as t1, coupon_set as t2 where t2.id=t1.coupon_set group by t1.coupon_set ";
	//print $q;
	stdreport($q); 
	
}
if($report=='title-views-top50'){
	$tlist=array(10=>'Top10',50=>'Top50',100=>'Top100');

	print "<form method=post action=?action=$action&report=$report>".daterange()."<button type=submit>go</button></form>";

	$q="select t1.title 'id',t2.name_en,sum(t1.views) views from user_views_daily as t1,title as t2 where t2.id=t1.title and t1.date between '$date1' and '$date2' group by t1.title order by sum(t1.views) desc limit 50 ";
	$toreport="title-views&date1=$date1&date2=$date2&title=";
	$cmprmt="&date1=$date1&date2=$date2&title=";

	stdreport($q); //print $q;
	stdchart($q,'horizontalBar');
}

if($report=='title-views'){
	extract($_POST);
	print "<form method=post action=?action=$action&report=$report>".daterange()."<select name=title onchange=this.form.submit();><option>all".qoptions("select id,concat(name_en,'-',name_th) 'name' from title ",$title)."</select><button type=submit>go</button></form>";
	if($title) $cond.=" and title='$title' ";
	$q="select date,sum(views) views from user_views_daily where date between '$date1' and '$date2' group by date order by date ";
	stdchart($q);
	stdreport($q);
}
if($report=='cdn'){
	extract($_POST);
	if(!$date1) $date1=date('Y-m-d');
	if(!$date2) $date2=date('Y-m-d');
	print "<form method=post action=?action=$action&report=$report>".daterange()."<select name=ip onchange=this.form.submit();><option>".qoptions("select ip 'id',concat(name,'-',ip) 'name' from cdn ",$ip)."</select><button type=submit>go</button></form>";
		
	$q="select date_format(datetime,'%Y-%m%-%d %H:%i') 'date/time'";
	$dt=qdt("select ip 'i',name 't' from cdn ");
	while(list(,$dr)=each($dt)){
		extract($dr);
		$q.=",sum(if(ip='$i',conn,0)) '$t' ";	
	}
	
	$q.=" from flixer_log.cdn_log where datetime between '$date1 00:00:00' and '$date2 23:59:59' group by date_format(datetime,'%Y-%m%-%d %H:%i') ";
	//print $q;
	stdreport($q); 
	stdchart($q);
	
}
if($report=='downloads'){
	print "<div><form action=?action=$action&report=$report class='form-inline' method=post >".daterange()."
	<button type=submit>Report</button></form></div>";
	$q="select date,downloads,balance from user_downloads where date between '$date1' and '$date2' ";
	//print $q;
	//stdchart($q); //print $q;
	stdreport($q);
}
if($report=='today0'){
	print "<div><form action=?action=$action&report=$report class='form-inline' method=post >".datepick()."
	<button type=submit>Report</button></form></div>";
	$q="select count(id) 'views',count(distinct(uuid)) 'users' from user_views where date='$date'";
	$dr=qdr($q);
	extract($dr);
	$yr=substr($date,0,4);
	$mo=substr($date,5,2);
	
	$q="select count(id) 'mviews',count(distinct(uuid)) 'musers' from user_views where month(date)='$mo' and year(date)='$yr' ";
	$dr=qdr($q);
	extract($dr);
	$q="select count(id) 'nusers' from user where date(registered)='$date' and type='guest' ";
	$dr=qdr($q); //print $q;
	extract($dr);
	$q="select count(id) 'downloads' from user where registered>'2017-12-15' and type='guest' ";
	$dr=qdr($q);
	extract($dr);
	print "
	<div class=rptcard><div class='rptno '>$views </div> <span>Today Views</span></div>
	<div class=rptcard><div class='rptno '>$users </div> <span>Today View Users</span></div>
	<div class=rptcard><div class='rptno '>$nusers </div> <span>New Users Today</span></div>
	<div class=rptcard><div class='rptno '>$downloads </div> <span>Total Downloads</span></div>
	";
	$q="select hour(datetime) 'hour',count(id) 'views',count(distinct(uuid)) 'users' from user_views where date='$date' group by hour(datetime)";
	stdchart($q);
}
if($report=='today'){
	print "<p><center>
	<div class='rptcard'><div class='views  rptno'>$views </div> <span>Today Views</span></div>
	<div class=rptcard><div class='users rptno'>$users </div> <span>Today View Users</span></div>
	<div class=rptcard><div class='nusers  rptno'>$nusers </div> <span>New Users Today</span></div>
	<div class=rptcard><div class='downloads rptno'>$downloads </div> <span>Total Downloads</span></div>
	<div class=rptcard><div class='aistrial ais rptno'>$aistrial </div> <span>AIS today</span></div>
	<div class=rptcard><div class='aistrials ais rptno'>$aistrials </div> <span>AIS Acc.</span></div>
	</center>
	<table id=topviews class='table table-xs table-striped'>
	<thead><tr><td>Today Top 10 $date </td><td>Views</td></tr></thead>
	<tbody>
	<tr><td class=t0></td><td class='int v0'></td></tr>
	<tr><td class=t1></td><td class='int v1'></td></tr>
	<tr><td class=t2></td><td class='int v2'></td></tr>
	<tr><td class=t3></td><td class='int v3'></td></tr>
	<tr><td class=t4></td><td class='int v4'></td></tr>
	<tr><td class=t5></td><td class='int v5'></td></tr>
	<tr><td class=t6></td><td class='int v6'></td></tr>
	<tr><td class=t7></td><td class='int v7'></td></tr>
	<tr><td class=t8></td><td class='int v8'></td></tr>
	<tr><td class=t9></td><td class='int v9'></td></tr>
	</tbody></table>
	Total Flixer Accounts: <span class='int flixeraccounts'></span>
	<style>
	#topviews td{padding:0px 5px;}
	</style>
	<script>
	
$(function(){
	today();
	window.setInterval(function(){ today();		}, 10000);
});
function today(){
	url='adminapi.php?action=today';
	$.getJSON(url,function(r){
		$('.views').text(r.views);
		$('.users').text(r.users);
		$('.nusers').text(r.nusers);
		$('.downloads').text(r.downloads);
		$('.aistrial').text(r.aistrial);
		$('.aistrials').text(r.aistrials);
		$('.rpt').number(true,0);
		$.each(r.topviews,function(i,d){
			$('.t'+i).text(d.name);
			$('.v'+i).text(d.views);
			
		});
		$('.flixeraccounts').text(r.flixeraccounts);
		$('.int').number(true,0);
	});
	
}
</script>";

}
if($report=='view-day-title'){
	if($_GET[id]){ $date1=$_GET[id];$date2=$_GET[id];}
	if($_GET[date]){ $date1=$_GET[date];$date2=$_GET[date];}
	
	print "<div><form action=?action=$action&report=$report class='form-inline' method=post >".daterange()."
	<button type=submit>Report</button></form></div>";
	$q="select t1.title 'id',t2.name_en 'title',t2.name_th,t2.tags,count(t1.id) as 'views',count(distinct(t1.user)) 'users' from user_views as t1, title as t2 
	where t2.id=t1.title and t1.date between '$date1' and '$date2' group by t1.title order by count(t1.id) desc ";
//	print $q;
	stdreport($q); 
}
if($report=='view-day-hour'){
	
	print "<form action=?action=$action&report=$report method=post>".datepick()."
	<button class='btn btn-primary'>report</button>
	</form> $date $hour :00:00";
	$q="select 
	minute(t1.datetime) as  'minute'
	,count(t1.id) 'views'
	,count(distinct(t1.uuid)) 'users'
	from user_views as t1
	where t1.date='$date'
	and uuid<>'16944b51-5f6f-4b3b-a24c-5c5fdc0533fd'
	and t1.date='$date'
	and hour(t1.datetime)='$hour'
	group by minute(t1.datetime)
	order by minute(t1.datetime) 
	";
	$toreport='view-month';
	$prmt="&date1=$date1&date2=$date2";
	stdreport($q);
	stdchart($q);
}

if($report=='view-day-time'){
	
	print "<form action=?action=$action&report=$report method=post>".datepick()."
	<button class='btn btn-primary'>report</button>
	</form>";
	$q="select 
	hour(t1.datetime) as  'hour'
	,count(t1.id) 'views'
	,count(distinct(t1.uuid)) 'users'
	from user_view as t1
	where t1.date='$date'
	and uuid<>'16944b51-5f6f-4b3b-a24c-5c5fdc0533fd'
	group by hour(t1.datetime)
	order by hour(t1.datetime) 
	";
	$toreport='view-month';
	$prmt="&date1=$date1&date2=$date2";
	stdreport($q); //print $q;
	stdchart($q);
}
if($report=='view-top20'){
	
	print "<form action=?action=$action&report=$report method=post>".daterange()."
	<button class='btn btn-primary'>report</button>
	</form>";
	$q=" select t2.name_en,sum(t1.views) 'views' from user_views_daily as t1,title as t2 where t2.id=t1.title 
	and t1.date between '$date1' and '$date2' group by t1.title order by sum(t1.views) desc limit 20 
	";
	stdreport($q); //print $q;
}
if($report=='view-all'){
	
	print "<form action=?action=$action&report=$report method=post>".daterange()."
	<button class='btn btn-primary'>report</button>
	</form>";
	$q="select 
	t1.date as  'date'
	,count(t1.id) 'views'
	,count(distinct(t1.user)) 'users'
	,concat('<a href=?action=report&report=view-day-time&date=',t1.date,'>by time</a> | <a href=?action=report&report=view-day-title&date=',t1.date,'>by title</a>') 'action'
	from user_views as t1
	where t1.date between '$date1' and '$date2' and user>0 
	
	group by t1.date
	order by t1.date 
	";
	$toreport='view-day';
	$prmt="&date1=$date1&date2=$date2";
	stdreport($q);
	//stdchart($q);
}
if($report=='feedback'){
	print "<form action=?action=$action&report=$report method=post>
	".daterange()."
	<button type=submit>report</button></form>";
	$q="select datetime,feedback,platform,os_version,device_model,app_version from user_feedback where date(datetime) between '$date1' and '$date2' ";
	stdreport($q);
}
if($report=='view-titles'){
	print "<form action=?action=$action&report=$report method=post>".daterange()."
	<button type=submit class='btn btn-primary'>Report</button></form>";
	$q="select t1.title,t2.name_en,t2.name_th,t2.tags,count(t1.id) 'views',count(distinct(uuid)) 'users' 
	from user_views as t1,title as t2 where t2.id=t1.title 
	and t1.date between '$date1' and '$date2'
	group by t1.title 
	order by count(t1.id) desc
	 ";
	 $q="select t1.title,t2.name_en,t2.name_th,t2.tags,sum(t1.views) 'views',sum(t1.p_views) 'premium_views',sum(t1.views+t1.p_views) 'total'
	 from  user_views_daily as t1, title as t2 where t2.id=t1.title and t1.date between '$date1' and '$date2' group by t1.title order by sum(t1.views+t1.p_views) desc ";
	//print $q;
	$tosum=array('views','premium_views','total');
	 $toreport='view-ep&title=';
	$prmt="date1=$date1&date2=$date2";
	stdreport($q);
}
if($report=='view-ep'){
	if($_GET[fld]) ${$fld}=$val;
	extract($_POST);
	print "<form action=?action=$action&report=$report method=post>".daterange()."
	<select name=title><option>".qoptions("select id,name_en as 'name' from title where 1 order by name_en ",$title)."</select>
	<button type=submit class='btn btn-primary'>Report</button></form>";
	$q="select t2.name_en,t1.episode 
	, sum(if(t1.premium=0,1,0)) 'views'
	, sum(if(t1.premium=1,1,0)) 'premium_views'
	,count(t1.id) 'total'
	from user_views as t1,title as t2 where t2.id=t1.title 
	and t1.date between '$date1' and '$date2' and t1.title='$title' 
	group by t1.episode
	order by t1.episode	
	 ";
	//print $q;,count(distinct(user)) 'users' 
	 $toreport='view-ep&ev=';
	 $tosum=array('views','premium_views','total');
	$prmt="date1=$date1&date2=$date2&licensor=$licensor&title=$title";
	stdreport($q);
}

if($report=='view-title'){
	if($_GET[id]) $title=$id;
	extract($_POST);
	if($_SESSION[licensor]){ $lcond.=" and id='$_SESSION[licensor]' "; $licensor=$_SESSION[licensor];}
	print "<form action=?action=$action&report=$report method=post>".daterange()."
	<select name=licensor>".qoptions("select id,name as 'name' from licensor where 1 $lcond order by name ",$licensor)."</select>

	<select name=title><option>".qoptions("select id,name_en as 'name' from title where licensor='$licensor' order by name_en ",$title)."</select>
	
	<button type=submit class='btn btn-primary'>Report</button></form>";
	
	$q="select t1.episode,count(t1.id) 'views',count(distinct(uuid)) 'users' 
	from user_views as t1,title as t2 where t2.id=t1.title 
	and t1.date between '$date1' and '$date2' and t1.title='$title'
	group by t1.episode 
	order by t1.episode ";
	//print $q;
	//$tosum=array('views','u);
	$toreport='view-detail';
	$prmt="date1=$date1&date2=$date2&licensor=$licensor&title=$title";
	stdreport($q);

}
if($report=='view-detail'){
	if($id) $ep=$id;
	if($file=='title') $title=$val;
	extract($_POST);
	if($title) $eps=qval("select total_episodes from title where id='$title' ");
	for($i=1;$i<=$eps;$i++){
		$sl='';
		if($ep==$i) $sl='selected';
		$epoptions.="<option $sl >$i";
	}

	
	
	print "<form action=?action=$action&report=$report method=post>".daterange()."
	<select name=licensor><option>".qoptions("select id,name as 'name' from licensor where 1 $lcond order by name ",$licensor)."</select>

	<select name=title><option>".qoptions("select id,name_en as 'name' from title where licensor='$licensor' order by name_en ",$title)."</select>
	<select name=ep><option value=''>all $epoptions </select>
	<button type=submit class='btn btn-primary'>Report</button></form>";
	$cond.=" and t1.title='$title'";
	if($ep) $cond.=" and t1.episode='$ep' ";
	$cond.=" and t1.date between '$date1' and '$date2'";
	$q="select t3.name 'licensor',t2.name_en 'title',t1.episode,t1.datetime 
	from user_views as t1,title as t2 ,licensor as t3
	where t2.id=t1.title and t3.id=t2.licensor
	
	$cond 
	order by t1.datetime
	";
	$prmt="date1=$date1&date2=$date2&licensor=$licensor&title=$title";
	//print $q;
	stdreport($q);
	
}
if($report=='view-title-days'){
	if($_GET[id]) $title=$id;
	extract($_POST);
	print "<form action=?action=$action&report=$report method=post>".daterange()."
	<select name=title>".qoptions("select id,name_en as 'name' from title order by name_en ",$title)."</select>
	<button type=submit class='btn btn-primary'>Report</button></form>";
	
	$q="select t1.date 'date',count(t1.id) 'views',count(distinct(uuid)) 'users' 
	from user_views as t1,title as t2 where t2.id=t1.title 
	and t1.date between '$date1' and '$date2' and t1.title='$title'
	group by t1.date 
	order by t1.date ";
	//print $q;
	//$tosum=array('views','u);
	stdreport($q);
	stdchart($q);
}

if($report=='view-licensors'){
	extract($_POST);
	if($_GET[id]) {$date1=$id;$date2=$id;}
	
	print "<form id=rptform action=?action=$action&report=$report&date1=$date1&date2=$date2 method=post class='inline-form'>
	".yearnav()."<input type=submit value=go></form>";
	$total_views=qval("select count(id) from user_views where date(datetime) between '$date1' and '$date2' ");
	$total_income=qval("select sum(amount)/2 from income where date between '$date1' and '$date2' ");
	print "<h4>Total ".number_format($total_views,0)." views - Total income ".number_format($total_income,2)." ฿ </h4>";
	if($licensor) $cond.=" and t2.licensor='$licensor' ";
$q="select t3.id,t3.name 'licensor',count(t1.id) 'views'
,count(t1.id)/$total_views*100 'percent'
,count(t1.id)/$total_views*$total_income 'revenue'
,count(distinct(t1.uuid)) 'users' 
from user_views as t1,title as t2,licensor as t3 where t2.id=t1.title and t3.id=t2.licensor
	and t1.datetime between '$date1 00:00:00' and '$date2 23:59:59' and t1.datetime>'2017-11-17' $cond
	group by t2.licensor order by count(t1.id) desc ";
	$tosum=array('views','percent','revenue');
	$toreport='view-licensor';
	$prmt="date1=$date1&date2=$date2";
	stdreport($q);

}
if($report=='view-licensor-title-month'){
	$title=$id;
	extract($_POST);
	/*
	print "<table><form id=rptform action=?action=$action&report=$report&licensor=$licensor method=post class='inline-form'><tr><td colspan=2>".yearnav()."</td></tr>";
	
	if($_SESSION[admin_type]=='licensor'){
		$licensor=$_SESSION[licensor];
		print "<h2>$_SESSION[user]</h2>";
	}else{
		print "<tr><td>Licensor: </td><td><select name=licensor onchange=this.form.submit(); ><option>.".qoptions("select id,name from licensor where 1 $lcond order by name ",$licensor)."</select></td></tr>";
	}
	$q1="select id,name_en 'name' from title where licensor='$licensor' ";
	
	print "<tr><td>Title: </td><td><select name=title onchange=this.form.submit();><option >".qoptions($q1,$title)."</select>";
	print "<input type=submit value=go></td></tr></form></table>";
	print $q1;
	$dr=qdr("select share_percent 'percent' from licensor where id='$licensor' ");
	extract($dr);
	if($title) $cond.=" and t1.title='$title' ";
	
	$q="select '$year' as 'year',date_format(t1.date,'%M') 'Month'
	,count(t1.id) as 'Views'
	,count(t1.id)*(select t9.share from income as t9 where year(t9.date) ='$year' and month(t9.date)=month(t1.date))*t2.share_percent/100 as 'revenue'
	from user_views as t1, title as t2 
	where t2.id=t1.title and year(t1.date)='$year' and month(t1.date)='$mo' $cond
	group by year(t1.date),month(t1.date)
	order by month(t1.date)
	";
	*/
	$tt=qdr("select name_en,name_th from title where id='$title' ");
	extract($tt);
	print "<h2>$name_en - $mo / $year </h2>";
	$q="select t1.episode,t1.episode,count(t1.id ) 'views'
	from user_views  as t1 where title='$title' and date_format(t1.date,'%Y-%m')='$year-$mo' group by episode order by episode ";
	//print $q;
	stdreport($q);
	
}
if($report=='view-licensor-month'){
	$yr=$year;
	$mo=$id;
	$yrmo="$yr-$mo";
	extract($_GET);
	$dr=qdr("select share,premium_share from income where date_format(date,'%Y-%m')='$yr-$mo' ");
	extract($dr);
	$q="select name 'name',share_percent 'percent' from licensor where id='$licensor' ";
	$dr=qdr($q); //print $q;
	extract($dr);
	$q=" select t2.id,t2.name_en as 'title',t2.total_episodes as 'eps'
	,sum(t1.views) as 'views'
	,sum(t1.views)*$share*t2.share_percent/100 as 'revenue'
	,sum(t1.p_views) as 'premium_views'
	,sum(t1.p_views)*$premium_share*t2.share_percent/100 as 'premium_revenue'
	from user_views_daily as t1,title as t2 
	where t2.id=t1.title
	and t2.licensor='$licensor'
	and date_format(t1.date,'%Y-%m')='$yrmo'
	group by t1.title
	";
	//print $q;
	$tosum=array('views','percent','revenue','premium_views','premium_revenue');
	$toreport="view-licensor-title-month&year=$year&licensor=$licensor&mo=$mo";
	$toreport2="title-years&year=$year&licensor=$licensor&mo=$mo&title=";

	$today=date("Y-m-d");
	print "<h2>$name $mo / $yr  </h2><a href=?action=$action&report=view-licensor-years&licensor=$licensor>View Years</a> ";
	stdreport($q);

}
if($report=='view-licensor-years'){
	extract($_GET);
	extract($_POST);
	print "<form action=?action=$action&report=$report&licensor=$licensor method=post>
	Licensor <select name=licensor ><option>".qoptions("select id,name from licensor",$licensor)."</select>
	 ".yearrange()."<button >report</button></form>";
	if(!$licensor) exit;
	//$dr=qdr("select share,premium_share from income where date_format(date,'%Y-%m')='$yr-$mo' ");
	//extract($dr);
	$q="select name 'name',share_percent 'percent' from licensor where id='$licensor' ";
	$dr=qdr($q); //print $q;
	extract($dr);
	$q=" select t2.id,t2.name_en as 'title',t2.total_episodes as 'eps'
	,sum(t1.views) as 'views'
	,sum(t1.views*t3.share*t2.share_percent/100) as 'revenue'
	,sum(t1.p_views) as 'premium_views'
	,sum(t1.p_views*t3.premium_share*t2.share_percent/100) as 'premium_revenue'
	from user_views_daily as t1,title as t2 ,income as t3
	where t2.id=t1.title and date_format(t3.date,'%Y-%m')=date_format(t1.date,'%Y-%m')
	and t2.licensor='$licensor'
	and year(t1.date) between '$yr1' and '$yr2' 
	group by t1.title
	";
	//print $q;
	$tosum=array('views','percent','revenue','premium_views','premium_revenue');
	$toreport="view-licensor-title-month&year=$year&licensor=$licensor&mo=$mo";
	$toreport2="title-years&year=$year&licensor=$licensor&mo=$mo&title=";

	$today=date("Y-m-d");
	print "<h2>$name  $yr1 - $yr2  </h2>";
	stdreport($q);

}

if($report=='view-licensor-title'){
	$licensor=$_SESSION[licensor];
	extract($_GET);
	extract($_POST);
	
	print "<form id=rptform action=?action=$action&report=$report&licensor=$licensor method=post class='inline-form'>
	".yearnav();
	if($_SESSION[admin_type]=='flixer'){
		print "Licensor <select name=licensor onchange=this.form.submit();><option>".qoptions("select id,name from licensor order by name ",$licensor)."</select>";
	}
	print " Title <select name=title onchange=this.form.submit();><option value=''>".qoptions("select code 'id',name_en 'name' from title where licensor='$licensor' group by code order by name_en",$title)."</select>
	<button type=submit>Report</button>
	</form>";
	$rate=qval("select share from income where date='$yr-$mo-01' ");
	if(!$rate) $rate=0;
	$q="select name 'name',share_percent 'percent' from licensor where id='$licensor' ";
	$dr=qdr($q); //print $q;
	extract($dr);
	if($title) $cond.="and t2.code='$title' ";
	$q=" select t2.id,date_format(t1.date,'%Y-%m') 'month',t2.name_en as 'title',t2.total_episodes as 'eps'
	,sum(t1.views) as 'views'
	,sum(t1.views)*t3.share*t2.share_percent/100 as 'revenue'
	,sum(t1.p_views) as 'premium_views'
	,sum(t1.p_views)*t3.premium_share*t2.share_percent/100 as 'premium_revenue'
	from user_views_daily as t1,title as t2 ,income as t3
	where t2.id=t1.title and date_format(t3.date,'%Y-%m')=date_format(t1.date,'%Y-%m')
	and t2.licensor='$licensor' and year(t1.date)='$year' 
	$cond
	group by t2.code,date_format(t1.date,'%Y-%m')
	order by t2.name_en,date_format(t1.date,'%Y-%m')	
	";
	
	$tosum=array('views','percent','revenue','premium_views','premium_revenue');
	$toreport="view-licensor-title-month&year=$year&licensor=$licensor&mo=$mo";
	$today=date("Y-m-d");
	print "<h2>$_SESSION[user]</h2>";
	stdreport($q);

}
if($report=='share-licensors-year'){
	//print_r($_SESSION);
	extract($_POST);
	if($_GET[id]) {$licensor=$id;}
	if($admin_type) $lcond.=" and id='$licensor' ";
	print "<form id=rptform action=?action=$action&report=$report&licensor=$licensor method=post class='inline-form'>
	".yearnav()."</form>";
	
	$q=" select t3.id,t3.name 'licensor' ";
	
	for($m=1;$m<=12;$m++){
		$q.=",sum(if(month(t1.date)=$m,t1.views*t2.share_percent/100,0)) * (select share from income where year(date)='$year' and month(date)='$m')  as '$m' ";
		$mp=$m.'p';
		$q.=",sum(if(month(t1.date)=$m,t1.p_views*t2.share_percent/100,0)) * (select premium_share from income where year(date)='$year' and month(date)='$m')  as '$mp' ";
	}
	$q.=",'' as 'total'
from user_views_daily as t1,title as t2,licensor as t3 where t2.id=t1.title and t3.id=t2.licensor and year(t1.date)='$year' group by t2.licensor ";
	$tosum=array_merge($monthflds,array('total'));
	$toreport="view-licensor-year&year=$year";
	$linkflds=array('licensor');
	stdreport($q);
	//print $q;
	
	
}
if($report=='view-licensor-year'){
	//print_r($_SESSION);
	extract($_POST);
	if($_GET[id]) {$licensor=$id;}
	if($_SESSION[admin_type]=='licensor'){
		$licensor=$_SESSION[licensor];
	}

	if($_SESSION[admin_type]=='licensor') $lcond.=" and id='$licensor' ";
	print "<form id=rptform action=?action=$action&report=$report&licensor=$licensor method=post class='inline-form'>
	".yearnav();
	if($_SESSION[admin_type]=='licensor'){
		$licensor=$_SESSION[licensor];
		print "<h2>$_SESSION[user]</h2>";
	}else{
		print "<select name=licensor onchange=this.form.submit(); ><option>.".qoptions("select id,name from licensor where 1 $lcond order by name ",$licensor)."</select><input type=submit value=go>";
		//unset($licensor);
	}

	print "</form>";
	if($licensor){
		$percent=qval("select share_percent from licensor where id='$licensor' ");
		$cond.=" and t1.licensor='$licensor' ";
	}else{
		exit;
	} 
	//,(select t9.share from income as t9 where year(t9.date)=year(t1.date) and month(t9.date)=month(t1.date)) as '฿/view'
	$q="select date_format(t1.date,'%m') 'id', date_format(t1.date,'%M') 'for_month'
	,count(t1.id) 'views'
	
	,count(t1.id)* (select t9.share from income as t9 where year(t9.date)=year(t1.date) and month(t9.date)=month(t1.date))*t1.percent/100 as 'revenue'
	,(select t8.paid from licensor_revenue as t8 where t8.licensor='$licensor' and year(t8.date)='$year' and month(t8.date)=month(t1.date)) as 'status' 
	,'bal' as balance
	from user_views as t1 where
	year(t1.date)='$year' $cond
	group by year(t1.date),month(t1.date) order by month(t1.date) ";
	
	$q=" select date_format(t1.date,'%m') 'id', t2.licensor 'code', date_format(t1.date,'%Y-%m') 'month', date_format(t1.date,'%M %Y') as 'for_month'
	,sum(t1.views) 'views' 
	
	,sum(t1.views*t2.share_percent)*(select t9.share from income as t9 where date_format(t9.date,'%Y-%m')=date_format(t1.date,'%Y-%m') )/100 as 'revenue'
	,sum(t1.p_views) 'premium_views'
	,sum(t1.p_views*t2.share_percent)*(select t10.premium_share from income as t10 where year(t10.date)=year(t1.date) and month(t10.date)=month(t1.date))/100 as  'premium_revenue'
	,(select t8.paid from licensor_revenue as t8 where t8.licensor='$licensor' and year(t8.date)='$year' and month(t8.date)=month(t1.date)) as 'status' 
	,'bal' as 'balance'
	,'<button class=pay>Pay</button>' as 'pay'
	from user_views_daily as t1,title as t2 where t2.id=t1.title and t2.licensor='$licensor'
	and year(t1.date)='$year' group by date_format(t1.date,'%Y-%m')
	";
	if($_SESSION[admin_type]=='licensor') $hideflds=array('pay');
		$dt=qdt($q);
		while(list(,$dr)=each($dt)){
			extract($dr);
			
			$date="$year-$id-01";
			$q1="insert into licensor_revenue (licensor,views,date) values ('$licensor','$view','$date') ";
			@qexe($q1); //print $q1.'<br>';
			$q1="update licensor_revenue set revenue='$revenue',views='$views' where licensor='$licensor' and date='$date' ";
			qexe($q1);	//print $q1.'<br>';
			
		}
		array_push($tosum,'revenue','premium_views');

	$toreport="view-licensor-month&licensor=$licensor&year=$year";
	$linkflds=array('month');
	
	stdreport($q); //print "<br>$q";
	$today=date("Y-m-d");
	if(in_array($_SESSION[admin_type],array('licensors','flixer'))) print "<form action=?action=licensor-paid&yr=$yr&licensor=$licensor&year=$year method=post style=background:#eee; >
	<h3>Licensor Pay Record </h3>
	<div class='form-group col-sm-3'>
	<label>Paid Date </label>
	<input type=text name=paid_date class='form-control date paid_date' value='$today'>
	</div>
	<div class='form-group col-sm-3'>
	<label>Paid Info - cheque no / inv / date  </label>
	<input type=text name=paid_info class='form-control paid_info' >
	</div>
	<div class='form-group col-sm-3'>
	<label>Paid Amount </label>
	<input type=text name=paid_amount class='form-control paid_amount' value='0'>
	</div>

	<div class='form-group col-sm-3'>
	<label>Paid For </label>
	<textarea name=paid_for class='form-control paid_for' rows=2></textarea>
	</div>
	<div class='form-group col-sm-3'>
	
	<button type=reset class=' btn btn-default' onclick=$('button.pay').show();$('.paid_for').text('');>Reset</button>
	<button type=submit class=' btn btn-primary'>Proceed</button>
	</div>
	</form><script>
	$(function(){
		$('button.pay').each(function(){
			var s=$(this).closest('tr').find('td.status').text();
			if(s=='paid'){
				$(this).hide();
			}
		});
	});
	$('button.pay').on('click',function(){
		var m=$(this).closest('tr').find('td.month').text();
		var a=parseFloat($(this).closest('tr').find('td.revenue').attr('value'));
		am=parseFloat($('.paid_amount').val());
		am=am+a;

		$('.paid_amount').val(am);
		pf=$('.paid_for').text();
		pf=pf+m+', ';
		
		$('.paid_for').text(pf);
		$(this).hide();
	});
	</script>";
	
}
if($report=='view-licensor'){
	//monthnav();
	
	extract($_POST);
	if($_GET[id]) {$licensor=$id;}
	if($_SESSION[admin_type]=='licensor'){
		$lcond.=" and id='$_SESSION[licensor]' ";
		$licensor=$_SESSION[licensor];
	} 
	print "<form id=rptform action=?action=$action&report=$report&date1=$date1&date2=$date2 method=post class='inline-form col-sm-4'>
	<div class='col-sm-6'>".daterange()."</div><div class=col-sm-6>
	<label>Licensor</label><select name=licensor onchange=this.form.submit(); ><option value=''>all".qoptions("select id,name from licensor where 1 $lcond ",$licensor)."<input type=submit value=go></form></div>";
	if($licensor) $cond.=" and t2.licensor='$licensor' ";
	$q="select t2.id,t2.name_en 'name',count(t1.id) 'views',count(distinct(t1.uuid)) 'users' from user_views as t1,title as t2 where t2.id=t1.title
	and t1.datetime between '$date1 00:00:00' and '$date2 23:59:59' and t1.datetime>'2017-11-17' $cond
	group by t1.title order by count(t1.id) desc ";
	//print $q;
	$toreport='view-title';
	$prmt="date1=$date1&date2=$date2&licensor=$licensor";
	$tosum=array('views');
	stdreport($q);
}
if($report=='user-day'){
	$date=$id;
	$q="select id,type,fbid,email,name,registered,last_login from user where date_format(registered,'%Y-%m-%d')='$date' and t1.registered>'2017-11-17' ";
	print "<h3>New User : $date</h3>";
	stdreport($q);
}
if($report=='user-new'){
	print "<form action=?action=$action&report=$report method=post>".daterange()."
	<button >Report</button>
	</form>";
	$q="select id from user where registered<'$date1' ";
	$bal=count(qdt($q));
	$cond .=" and registered between '$date1' and '$date2 23:59:59' ";
	//if($type) $cond.=" and type='$type' ";
	$cond.=" and registered>'2017-11-18' ";
	$q="select date(registered) 'date'
	,sum(if(type='guest',1,0)) as 'guest'
	,sum(if(type='fb',1,0)) as 'fb'
	,sum(if(type='email',1,0)) as 'email'
	, count(id) 'all' 
	, $bal as 'bal'
	from user where 1 $cond group by date(registered) order by date(registered) ";
	$toreport='user-day';
	$tosum=array('guest','fb','email','all');
	stdreport($q);
	stdchart($q);
}

if($report=='chart-views'){
	monthnav();
	$q="select t2.name_en 'fld',count(t1.id) 'val' from user_views as t1,title as t2 where t2.id=t1.title
	and date_format(t1.datetime,'%Y-%m')='$yr-$mo' and t1.title='$title'
	group by t1.title order by count(t1.id) desc ";
	$dt=qdt($q);
	
	while(list($i,$dr)=each($dt)){
		if($i>0){ $lbl.=" , "; $dat.=" , "; }
		while(list($fld,$val)=each($dr)){
			$lbl.=$fld;
			$dat.=$val;
		}
	}
	print "<canvas id=chart1 width=600 height=400 style=background:'#ffffff'> lbl $lbl dat $dat </canvas>";
	print "<script>
	$(function(){
		
		var ctx=document.getElementById('chart1').getContext('2d');
		var thischart=new Chart(ctx,{
			type:'doughnut',
			data:{
			labels: [ $lbl ] ,
			datasets: data : [ $dat ]
			},
			options:{
				responsive:true,
				legend:{position:'top',},
				title:{display:true,text:'Views $mo / $yr '}
			}
		}
		)
	});
	</script>";
}

if($report=='chart-newuser'){
	monthnav();
	
	$cond .=" and date_format(registered,'%Y-%m')='$yr-$mo' ";
	$cond .=" and registered>'2017-11-17' ";
	$q="select date_format(registered,'%d') 'date'
	,sum(if(type='guest',1,0)) as 'guest'
	,sum(if(type='fb',1,0)) as 'fb'
	,sum(if(type='email',1,0)) as 'email'
	, count(id) 'all' 
	from user where 1  $cond group by date_format(registered,'%Y-%m-%d') order by date_format(registered,'%d') ";
	$dt=qdt($q); //print $q;
	while(list($i,$dr)=each($dt)){
		while(list($fld,$val)=each($dr)){
			//print "$fld $val <br>";
			if(strlen($d[$fld])>1) $d[$fld].=", ";
			//if($fld=='date') $d[$fld].="'$val'";
			//else
				$d[$fld].=$val;
		}
	}
	//print_r($d);
	$labels=$d[date];
	//{label:'Guest',type:'line',fill:true,border:true,borderColor:'rgba(255,0,0,1)', data: [ $d[guest] ] }
	$datasets="[
	{ label:'Guest',borderColor:'rgba(255,100,100,0.5)', data: [ $d[guest] ] }
	,{ label:'FB',borderColor:'rgba(100,255,100,0.5)', data: [ $d[fb] ] }
	,{ label:'Email',borderColor:'rgba(255,180,0,0.5)', data: [ $d[email] ] }
	,{ label:'All',borderColor:'rgba(100,100,255,0.5)', data: [ $d[all] ] }
	]";
	
	print "<canvas id=chart1 width=600 height=400 style=background:'#ffffff'></canvas><script>
	
	$(function(){
		var ctx=document.getElementById('chart1').getContext('2d');
		var thischart=new Chart(ctx,{
			type:'line',
			data:{
			labels: [ $labels ] ,
			datasets: $datasets
			},
			options:{
				responsive:true,
				legend:{position:'top',},
				title:{display:true,text:'New User $mo / $yr '}
			}
		}
		)
	});;
	</script>";
}
if($action=='report'){
	print "<script>$(function(){
		$('.rpt').dataTable({paginate:false});
	});
	
	</script>";
}
?>
