<?php 
ini_set('display_errors',Off);
require "/var/www/html/api/admin-include.php";
print date("Y-m-d H:i:s")."\n";
$mexpire=qval("select min(expire)+interval 1 day from user_token ");
$q="delete from user_token where user=0 and expire<(now() - interval 1 day)";
qexe($q);
$q="delete from user_token where  expire < '$mexpire' and expire < (now()-interval 1 month) ";
qexe($q);
$rows=$db->affected_rows;
print "<br> ok < $mexpire  rows $rows $q \n";
print date("Y-m-d H:i:s")."\n";

$gid=qval("select min(id) from user where type='guest' ");
$gnid=$gid+5000;
$q="delete from user type='guest' and email='' and registered < ( now() - interval 1 day) ";
qexe($q);
$rows=$db->affected_rows;
print "<br> ok gid $gid gnid=$gnid  rows $rows $q \n";
print date("Y-m-d H:i:s")."\n";

?>
