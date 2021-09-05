<?php 
if($action=='ais-user'){
	if($val) $platform_id=$val;
	extract($_POST);
	$platform_id=str_replace('-','',$platform_id);
	$platform_id=str_replace(' ','',$platform_id);
	if(($platform_id)&&(substr($platform_id,0,1)==0)) $platform_id='66'.substr($platform_id,1,9);
	print "<form action=?action=$action&report=$report method=post><input type=name name=platform_id minlength=10 maxlength=15 placeholder='mobile' value='$platform_id' ><button>Check</button></form>";

	if(strlen($platform_id)==11){
		$q="select user,status,valid,expire,platform_id,platform_private_id from user_platform where platform_id='$platform_id' ";
		$q="select user,platform_id,status,expire_datetime,payment_pending,last_charge,logs from user_platform where platform_id='$platform_id' ";
		stdreport3($q);
		$dr3=qdr($q);
		if($dr3[expire_datetime]<date('Y-m-d H:i:s')) print "<span class='btn btn-primary' onclick=aisCharge('$platform_id'); >Charge $platform_id </span>";
		if($dr3[status]=='verified') print "<span class='btn btn-primary' onclick=aisTrial('$platform_id'); > Start Trial $platform_id </span>
		";
	}
}

if($action=='user-ais'){
	extract($_POST);
	if(substr($mobile,0,1)=='0') $mobile='66'.substr($mobile,1,9);
	print "<form action=?action=$action method=post>Mobile NO. <input placeholder=mobile type=text name=mobile value=$mobile> 66xxxxxx <button class='btn btn-primary'>check</button></form>";
	if($mobile){
		$q="select id,user,platform_id,status,start_datetime,expire_datetime,logs from user_platform where platform_id='$mobile' ";
		$dr=qdr($q);
		$dr2=qdr("select email,mobile,premium,premium_expire from user where id='$dr[user]' ");

		print "<table class='table table-bordered'><tbody>
		<tr><td>user</td><td>$dr[user] $dr2[email] <a href=?action=report&report=ais-user&platform_id=$dr[platform_id]>$dr[platform_id]</a></td></tr>
		<tr><td>status</td><td>$dr[status] start $dr[start_datetime] - $dr[expire_datetime]</td></tr>	
		<tr><td>Logs</td><td><pre>$dr[logs]</pre></td></tr>
		</tbody></table>";
		$action='report';$report='ais-user';
		$platform_id=$dr[platform_id];
	}
}
function iosValidate($receipt_data,$end_point){
	global $json;
	$ios_secret="6aca35a881714df6b762a5c2ba6ebe21";
	if($end_point=='sandbox') $endpoint='https://sandbox.itunes.apple.com/verifyReceipt';
	if($end_point=='production') $endpoint='https://buy.itunes.apple.com/verifyReceipt';
	$json[endpoint]=$endpoint;
	if(!$receipt_data) $receipt_data=$test_receipt;
	//$json[debug][receipt_data]=$receipt_data;
	$post=json_encode(array('receipt-data' => $receipt_data, 'password' => $ios_secret));
	$ch = curl_init($endpoint);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

	$response = curl_exec($ch);
	$errno    = curl_errno($ch);
	$errmsg   = curl_error($ch);
	$info = curl_getinfo($ch);
	$json[server_response_time_ms]=$info[total_time];
	
	curl_close($ch);

	if ($errno != 0) {
		throw new Exception($errmsg, $errno);
	}
	return $response;
	//$json[dbname]=$dbname;
	
}
if($action=='user-reset-premium'){
	$q="update user set premium=0 where id='$user' ";
	qexe($q); print $q;
	$q=" delete from coupon_redeem where user='$user' ";
	qexe($q); print $q;
	$q="delete from user_premium where user='$user' ";
	qexe($q);
	$q="delete from user_premium_dup where dup_user='$user' ";
	qexe($q);
	$action='user-check';
}
if($action=='user-change-password'){
        $dr=qdr("select email,last_token as 'token' from user where id='$user' ");
        if($dr){
                extract($dr);
//                error(105);
                $q="update user set token_expire=now() + interval 1 hour where id='$user' ";
                qexe($q);
                $rs=mail($email,"Flixer: Request to change password ","Please click to reset your password
https://api.flixerapp.com/changepwd/$token
Please change password before expired in 60 minutes.
                ","from:members@flixerapp.com");
                //$json[api_key]=$token;
			if($rs) print "mail sent to $email ";
			$changelink="https://api.flixerapp.com/changepwd/$token";
        }
		$action='user-check';
		
}


if($action=='userpremium-clear'){
	$q="delete from user_premium where user='$dupuser' ";
	qexe($q);
	$q="delete from user_premium_dup where dup_user='$dupuser' ";
	qexe($q);
	$action='user-check';
}
if($action=='user-token-expire'){
	$q=" update user_token set expire=now() where user='$user' and expire>now() ";
	qexe($q);print $q;
	$q=" delete from user_token  where user='$user' and expire>now()-interval 1 day  ";
	$q=" delete from user_token  where user='$user'  ";
	qexe($q); //print $q;
	$q=" delete from user_watching  where user='$user'  ";
	qexe($q); //print $q;
	$action='user-check';
}
if($action=='user-receipt-check'){
	extract($_POST);
	print "<form action=?action=$action method=post><input type=text name=transaction placeholder='purchase_no' value=$transaction ><button type=submit>Check</button></form>";
	
	if($transaction){
	$q="select t1.transaction,t1.org_transaction,t1.user,t2.email,t2.type from user_premium as t1 ,user as t2 where t2.id=t1.user and t1.transaction like '$transaction%%' ";//or org_transaction like '%%$transaction%%' ";
	//print $q;
	$dt=qdt($q);
	dt2tb3($dt);
	}
}
if($action=='user-changepwd'){
	$q="update user set password=password('$_POST[pass]') where id='$user' ";
	//print $q.'<br>';
	qexe($q);
	$action='user-check';
}
if($action=='user-clear-watching'){
	$q=" delete from user_watching where user='$user' ";
	qexe($q);
	$action='user-check';
}
if($action=='user-check'){
	if($_GET[user_id]) $email=qval("select email from user where id='$_GET[user_id]' ");
}
if($action=='user-check'){

	if($_POST[email]) $email=$_POST[email];
	$email=strtolower(trim($email));
	print "<form action=?action=$action method=post>Check User <input name=email type=text placeholder=email value=$email ><button type=submit>check</button>  |
	<a href=?action=user-receipt-check>Receipt Check</a> |
	<a href=?action=ais-user>AIS User Check</a> | 
	</form>";
	if($changelink) print "or send below link to user <br>  $changelink <br>";
	if(($email)||($_GET[user_id])){
		$action='user-token';
	}
	
}
if($action=='user-last100-views'){

	$q="select t1.date,t2.name_en 'title' from user_views as t1 ,title as t2 where t2.id=t1.title and t1.user='$user' order by t1.date desc limit 100 ";
	stdreport($q);
}

function dt2tb3($dt){
	print "<table>";
	while(list($i,$dr)=each($dt)){
		$k=$i+1;
		$tbody.= "<tr><td colspan=1>$k</td>";
		while(list($fld,$val)=each($dr)){
			if($i==0) $thead.="<td>$fld</td>";
			$tbody.="<td>$val</td>";
		}
		$tbody.="</tr>";
	}
	print "<table><thead><tr><td>no</td>$thead</tr>
	<tbody>$tbody</tbody>
	</table>";
}

function dt2tb2($dt){
	print "<table>";
	while(list($i,$dr)=each($dt)){
		$k=$i+1;
		print "<tr><td colspan=2>$k</td></tr>";
		while(list($fld,$val)=each($dr)){
			
			print "<tr><td>$fld</td><td>$val</td></tr>";
		}
		print "<tr><td onclick=$('#logs').toggle();>Logs</td><td class=logs ><pre id=logs style=display:none;>$dr[logs]</pre></td></tr>";
	}
	print "</table>";
}
if($action=='user-premium'){
	$dt=qdt("select * from user_premium where user='$user' ");
	dt2tb2($dt);
	//print "<pre>";print_r($dt);print "</pre>";
}

if($action=='user-token'){


	$email=strtolower(trim($email));
	//print "<pre>";print_r($dr);print "</pre>";
	extract($_GET);
    $q="select id,type,email 'flixer_account',if(fbid<>'','Facebook','Email') 'register_by',last_login,premium as 'premium_status',premium_expire from user where  email='$email' or id='$_GET[user_id]' ";// mobile='$email' ";
    $dr=qdr($q);
	if($dr){
		$user=$dr[id];
		$email=$dr[flixer_account];
	}
	if($user){
		print "
<a class='btn btn-primary' onclick=\"return confirm('Reset all User Login?');\" href=?action=user-token-expire&user=$user&email=$email>Logout All Devices</a> <a href=?action=edit&tb=user&id=$user>.</a>
<a class='btn btn-primary' onclick=\"return confirm('Send Change Password ?');\" href=?action=user-change-password&user=$user&email=$email>Send Change Password for $email</a>  
<a class='btn btn-primary' onclick=\"return confirm('Reset all Premium ?');\" href=?action=user-reset-premium&user=$user&email=$email>Reset all Premium $email</a> 

";
		//print "<pre>User $dr[name] \n";print_r($dr);print "</pre>";
		print "<table class='table table-bordered table-sm'>";
		while(list($fld,$val)=each($dr)){
			if($fld=='id') continue;
			print "<tr><td class='fld '>".str_replace('_',' ',$fld)."</td><td class='col ' >$val</td></tr>";
		} 
		print "<tr><td colspan=2><h4>User Logins</h4></td></tr>";
		$q="select token,datetime,expire from user_token where user='$user' order by id desc limit 4  ";
		$dt=qdt($q);
		while(list(,$dr)=each($dt)){
			print "<tr ><td class='fld '>Last Login</td><td >$dr[datetime] - $dr[expire] $dr[token] </td></tr>";
		}
		
		//print "<pre>User Token\n";print_r($dt);print "</pre>";
		
		$q="select validated
		, if(valid=1,'Yes','') 'receipt_is_valid'
		, if(is_trial_pass=1,'Yes','') 'finish_trial_period' ,receipt_data,platform,product_id,logs from user_premium where user='$user' order by validated desc limit 1  ";
		
		$dr=qdr($q); //print $q;
		if($dr){
			print "<tr><td colspan=2><h4>Subscription Info</h4></td></tr>";
			
			while(list($fld,$val)=each($dr)){
				if($fld=='logs') continue;
				if($fld=='receipt_data') continue;
				print "<tr><td class='fld'>".str_replace('_',' ',$fld)."</td><td >$val</td></tr>";
			} 
			if($dr[receipt_data]<>''){
				/*
				$q="select user from user_premium where receipt_data='$dr[receipt_data]' and user<>'$user' ";
				$dr3=qdr($q); 
				if($dr3){ 
					$email2=qval("select email from user where id='$dr3[user]' ");
					print "<div class='row'><div class='col-md-4 fld text-danger'> Duplicate Receipt with user </div><div clas='col-md-8'>$email2 <a href=?action=userpremium-clear&dupuser=$dr3[user]&email=$email>clear this</a></div></div> "; 
				}
				$q="select dup_user,dup_email from user_premium_dup where user='$user' ";
				$dt3=qdt($q); 
				if($dt3){ 
					while(list(,$dr3)=each($dt3)){
						extract($dr3);
						print "<tr><td class='fld text-danger'> Duplicate Receipt with user </td><td >$dup_email <a href=?action=userpremium-clear&dupuser=$dup_user&email=$dup_email>clear this</a></td></tr> "; 
					}
				}
				*/
			}
			if($dr[platform]=='ios'){
				//$response=iosValidate($dr[receipt_data],'production');
				//$rs=json_decode($response);	
			}
		}else{
			print "<tr><td>Subscription</td><td>No Receipt Data</td></tr>";
		}
		
		$q="select * from user_platform where user='$user' ";
		$dr4=qdr($q);
		if($dr4){
			print "<tr><td>$dr4[platform]</td><td ><a href=?action=report&report=ais-user&platform_id=$dr4[platform_id]>$dr4[platform_id]</a> $dr4[status] $dr4[start_datetime] - $dr4[expire_datetime]  <span onclick=$('.uplogs').toggle();>.</span><pre style=display:none class='uplogs' >$dr4[platform_private_id]\n $dr4[logs]</pre> </td></tr>";
			
			$dt5=qdt("select * from platform_data where platform_id='$dr4[platform_id]' or platform_private_id='$dr4[platform_private_id]' ");
			while(list(,$dr5)=each($dt5)){
				print "<tr><td>$dr5[datetime]</td><td>$dr5[action] $dr5[status] </td></tr>";
			}
			
		}
		
		$q="select * from coupon_redeem where user='$user' and now() < expire_datetime ";
		$dr6=qdr($q);
		
		if($dr6){
			print "<tr><td colspan=2><h4>Coupon</h4></td></tr>
			<tr><td>Coupon Pack / Code </td><td>$dr6[package] / $dr6[code] </td></tr>
			<tr><td>Coupon Expire</td><td>$dr6[expire_datetime] </td></tr>
			";
		}else{
			//print $q;
		}
		
		$q="select * from user_rental where user='$user' order by start_datetime desc ";
		$dt6=qdt($q);
		
		if($dt6){
			print "<tr><td colspan=2>Rental</td></tr>";
			while(list(,$dr6)=each($dt6)){
				print "<tr><td>Title $dr6[title] / $dr6[status] </td><td>start $dr6[start_datetime] - $dr6[end_datetime] | watch $dr6[watch_start] - $dr6[watch_end] </td></tr>";
			}
		}else{
			//print $q;
		}

		$q="select * from user_premium_dup where user='$user' ";
		//print "<br>q7 $q";
		$dt7=qdt($q);
		
		if($dt7){
			while(list(,$dr7)=each($dt7)){
				extract($dr7);
				print "<tr><td>Receipt Duplicate</td><td><a href=?action=user-check&user_id=$dup_user>$dup_email</a><br>$logs</td></tr>";
			}
		}else{
			//print $q;
		}
		$q="select * from user_watching where user='$user' ";
		$dt8=qdt($q);
		if($dt8){
			while(list(,$dr8)=each($dt8)){
				extract($dr8);
				print "<tr><td>Watching</td><td>$token $datetime <a href=?action=user-clear-watching&user=$user&email=$email>[Clear]</a></td></tr>";
			}
		}

		print "</table>";
		//if($dr[register_by]=='Facebook')
		print "<form action=?action=user-changepwd&user=$user&email=$email method=post><input type=text placeholder=new_pass name=pass><button >change pass</button></form>";
		print "<span onclick=$('.logs').toggle();>Logs</span><pre class='logs' style=display:none;>$dr[logs]</pre>
		<span onclick=$('.rc_data').toggle();>..</span><div class='rc_data' style=display:none;max-width:100%;><textarea style=width:100%;height:100px;>$dr[receipt_data]</textarea>
		<pre>$response</pre>
		</div>
		
		<style>
		div.row {padding:2px;}
		td{padding:2px 4px !important}
		h4{margin:0;}
		.table {background:#fff;}
		.fld {text-transform:capitalize;}
		.rc_data{max-width:100%;}
		</style>
		";
		//print "<pre>$dr[logs]</pre>";
		
		
	}else{
		print "$email not found ";
		$e2=substr($email,0,6);
		$q="select id,email from user where email like '%$e2%' ";
		$dt=qdt($q);
		while(list(,$dr)=each($dt)){
			extract($dr);
			print "<li> $id $email ";
		}
		
	}
}
?>
