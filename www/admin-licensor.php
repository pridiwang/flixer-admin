<?php 
if($action=='licensor-mail'){
	$pwd=rand(10000000,999999999);
	$q="update licensor set password=password('$pwd'),note='$pwd' where id='$id' ";
	//if($newpwd) 
	qexe($q); //print $q;
	$dr=qdr("select email,password,note as 'pwd' from licensor where id='$id' ");
	extract($dr);
	$msg="
	Dear Licensors
	
	According to your partnership with Dream Express [DEX] Co., Ltd. for the mobile application named \"FLIXER\" for Thailand territory as the VOD, firstly we would like to thank you for your trust in our VOD platform, FLIXER. 
	
	Herewith, we would like to inform you for the username and password in order to check for the number of view and the revenue sharing of each title providing by you as the licensor of those contents.
	
	Kindly be informed the username and password as below.
	
	USENAME:  $email
	PASSWORD: $pwd
	
	Portal: https://api.flixerapp.com/admin.php
	
	
	Please go to the portal and put your username and password for the data.
	More information, please feel free to ask to your contact person at Dream Express [DEX] Co., Ltd.
	
	
	Best regards,
	FLIXER admin team
	
	PS. For any queries, please feel free to contact Nirada at nirada@dex.co.th
			
";
	print "<pre>$msg</pre>";
	$cc='';
	$bcc="nirada@dex.co.th,pw@kudson.com";
	//$email='preedeew@gmail.com';$bcc='';
	//$email='pw@kudson.com';/$bcc='';
	//$bcc='pw@kudson.com';
	
	print " to $email bcc $bcc ";
	$rs=mail($email,"Licensor Report access information ",$msg,"from:admin@flixerapp.com\ncc:\n$cc\nbcc:$bcc");
	//$rs=mail($email,"Licensor Report access information ",$msg,"from:admin@flixerapp.com/nbcc:$bcc");
	if($rs) print " sent ";


}
if($action=='licensors-list'){
	$q="update licensor set passwrod='',note='' ";
	qexe($q);
	$q="select * from licensor where email<>'' and id=70 ";
	$dt=qdt($q);
	while(list(,$dr)=each($dt)){
		extract($dr);
		$pwd=rand(10000000,99999999);
		//$note=$pwd;
		print "<br> email $email pwd $note ";
		//$q="update licensor set password=password('$pwd'), note='$pwd' where id='$id' ";
		//qexe($q);
		$msg="
Dear Licensors

According to your partnership with Dream Express [DEX] Co., Ltd. for the mobile application named \"FLIXER\" for Thailand territory as the VOD, firstly we would like to thank you for your trust in our VOD platform, FLIXER. 

Herewith, we would like to inform you for the username and password in order to check for the number of view and the revenue sharing of each title providing by you as the licensor of those contents.

Kindly be informed the username and password as below.

USENAME:  $email
PASSWORD: $note

Portal: http://flixerapp.com/api/admin.php


Please go to the portal and put your username and password for the data.
More information, please feel free to ask to your contact person at Dream Express [DEX] Co., Ltd.


Best regards,
FLIXER admin team

PS. For any queries, please feel free to contact Nirada at nirada@dex.co.th
		
		";
		print "<pre>$msg</pre>";
		//$email='preedeew@gmail.com';
		//$bcc='pw@kudson.com';
		$bcc="nirada@dex.co.th,preedeew@gmail.com";
		//mail($email,"Licensor Report access information ",$msg,"from:admin@flixerapp.com\nbcc:$bcc");
	}
}
?>