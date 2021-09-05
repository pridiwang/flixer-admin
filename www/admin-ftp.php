<?php 
//extract($_GET);
//require "admin-include.php";
if($action=='ftp'){
	$ftp = ftp_connect($ftpserver) or die("Could not connect to $ftp_server");
	$login = ftp_login($ftp, $ftpuser, $ftppwd);

	//print "ftp $ftp <br>";
	//print "Current directory is now: " . ftp_pwd($ftp) . "<br>";
	if(!$code) $code='555';
	$dir='/home/dex/'.$code;
	$ls=ftp_nlist($ftp,$dir);
	print "<h3>$code</h3><ul>";
	while(list(,$file)=each($ls)){
		$f=str_replace($dir.'/','',$file);
		print "<li class='col-md-4'>$f</li>";
	}
	print "</ul>";
	ftp_close($ftp);
}
?>