<?php 
require "admin-include.php";
$yrmo=date('Ym',mktime(0,0,0,date('m')-1,1,date('Y')));
if($_GET) extract($_GET);
$q=" replace into user_views_monthly (date,users,views,a_views,p_views)
select date_format(date,'%Y-%m-01'), count(distinct(user)),count(id)
,sum(if(premium=0,1,0))
,sum(if(premium=1,1,0))
from user_views where date_format(date,'%Y%m')='$yrmo' group by date_format(date,'%Y%m')";
//qexe($q);
print "<br>$q";
$q="select count(distinct(user)) 'users' from user_views where date_format(date,'%Y%m')='$yrmo' and premium=1 ";
$pusers=qval($q);
print "<br>$q";
$q="update user_views_monthly set premium_users='$pusers' where date_format(date,'%Y%m')='$yrmo' ";
qexe($q); 
print "<br>$q";

?>