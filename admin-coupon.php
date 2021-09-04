<?php 
if($action=='coupon-generate'){
	$dr=qdr("select * from coupon_set where id='$coupon_set' ");
	//print_r($dr);
	extract($dr);
	$issued=qval("select count(id) from coupon_code where coupon_set='$coupon_set' ");
	$qty-=$issued;
	if($qty<=0) {
		print "<br> $issued coupon_code already generated ";
		$action='';
	}
}
if($action=='coupon-generate'){
	require "coupon.php";
	$no_of_coupons=$qty;
	$length=1;
	$prefix='';
	$suffix='';
	$numbers=true;
	$letters=false;
	$symbols=false;
	$random_register=true;
	
	if(!$mask) $mask='TEST-XXXX-XXXX';
	
	//$coupons = coupon::generate_coupons($no_of_coupons, $length, $prefix, $suffix, $numbers, $letters, $symbols, $random_register, $mask);
	/*$coupons = coupon::generate_coupons($qty,array('mask'=>'TEST-XXXX-XXXX','numbers'=>true));
	while(list($i ,$code)=each($coupons)){
		print "<br> $i $code";
		$q="insert into coupon_code (coupon_set,code) values ('$coupon_set','$code') ";
		qexe($q);
	}
	*/
	$cp=new coupon();
	$csv="coupon_code";
	for($i=0;$i<$qty;$i++){
		$code=$cp->generate(array('mask'=>$mask,'numbers'=>true));
		$q="insert into coupon_code (coupon_set,code,redeem_limit) values ('$coupon_set','$code','$redeem_limit') ";
		print "<br>$code ";//$q ";
		
		$rs=qexe($q);
		if($rs) $c++;
		$csv.="\n$code";
	}
	print "<br>$c coupon code generated ";
	if($csv) {
		$file="csv/coupon-$coupon_set.csv";
		file_put_contents($file,$csv);
		print "<a href=$file>$file</a>";
	}
}
?>