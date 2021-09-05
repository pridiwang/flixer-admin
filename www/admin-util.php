<?php 
//print "<br>util $action ";
if($action=='util-set-rviews'){
    if(!$date) $date=date('Y-m-d',mktime(0,0,0,date('m'),date('d')-1,date('Y')));
    $ydate=date('Y-m-d',strtotime("$date -1 day "));
    $tdate=date('Y-m-d',strtotime("$date +1 day "));
    print "
    <a href=?action=$action&date=$ydate> < </a> date $date
    <a href=?action=$action&date=$tdate> > </a>
    ";
    $q="select title,count(id) 'rviews' from user_views where date='$date' and rental=1 and episode<>99 group by title ";
    $dt=qdt($q);
    while(list(,$dr)=each($dt)){
        extract($dr);
        print "<br> $title $rviews ";
        $cid=qval("select id from user_views_daily where date='$date' and title='$title' ");
        if($cid) $q="update user_views_daily set r_views='$rviews' where id='$cid' ";
        else $q=" insert into user_views_daily (date,title,r_views) values ('$date','$title','$rviews') ";
        print " -  $q ";
        qexe($q);
    }
    if(!$dt){
        $q=" update user_views_daily set r_views=0 where date='$date' ";
        qexe($q); print "<br> 0 - $q ";
    }
}
if($action=='util-checkais'){
    $q="select *,logs 'log' from  user_platform where status='activate' and expire_datetime<now() order by expire_datetime ";
    $dt=qdt($q);
    print "<table class='table table-sm table-bordered'>";
    foreach($dt as $i=>$dr){
        extract($dr);
        $k=$i+1;
        print "<tr><td>$k</td><td><a target=_blank href=https://api.flixerapp.com/ais/ais-daily.php?action=directCharge&mobile=$platform_id>$platform_id</a></td><td>$status</td><td>$expire_datetime</td><td>$payment_pending</td><td><pre class='hide'>$log</pre></td></tr>";
    }
    print "</table>";
}


if($action=='util-set-dur'){
    $q=" SELECT t1.title,t2.code,t1.episode 'ep'  FROM `episode` as t1, title as t2  WHERE t2.id=t1.title and t1.duration = 2400 and t1.status='publish' group by t1.title order by t1.title desc limit 20 ";
    $dt=qdt($q); print $q;
    while(list(,$dr)=each($dt)){
        extract($dr);
        print "<br>$title $code $ep ";
        $url="https://i2.kudson.net/api.php?key=e24R6VDBwDBy8EaCSisG97r6xiTx0C&action=duration&code=$code&ep=$ep";
        print "<br><iframe src=admin.php?action=edit&tb=title&id=$title></iframe>";

    }
}
if($action=='util-setup-devdb'){
    $emptytbs=array('user','user_platform','user_premium','user_token','user_view','user_views','user','user_views_daily','user_watch','user_watching','title','episode','shelf');
    while(list(,$tb)=each($emptytbs)){
        $q=" truncate table flixer_dev.$tb ;";
        print "<br>$q";
    }
    $dumptbs=array('title','episode','shelf');
    
    while(list(,$tb)=each($dumptbs)){
        $q=" truncate table flixer_dev.$tb; ";
        print "<br>$q";
        $q=" insert into flixer_dev.$tb (select * from flixer_vod.$tb) ";
        print "<br>$q";

    }
}
if($action=='util-ais-clean'){
    $q="select id,platform_private_id from platform_data where platform_private_id<>'' order by id ";
    $dt=qdt($q);
    while(list(,$dr)=each($dt)){
        extract($dr);
        
        $q="delete from platform_data where platform_private_id='$platform_private_id' and id>'$id' ";
        print "<br>$id $platform_private_id $q ";
        qexe($q);

    }
}

if($action=='util-draft-eps'){
    $q="select id 'title',status from title where status<>'publish' ";
    $dt=qdt($q);
    while(list(,$dr)=each($dt)){
        extract($dr);
        $q="update episode set status='$status' where title='$title' ";
        print "<br> $title $status $q ";
        qexe($q);
    }
}
if($action=='util-checkfirstview'){
    $tlist=array(304,305,139,322,320,230,231,229,227);
    while(list(,$title)=each($tlist)){
        $q="select datetime from user_view where title='$title' order by datetime limit 1 ";
        $dt=qval($q);
        print "<br>$title $dt  ";
    }
}
/*
if($action=='util-viewers'){
    if($tags){
        $tar=explode(',',$tags);
        $ar=array();
        
        while(list(,$tag)=each($tar)){
            $q="select id from title where tags like '%%$tag%%' or name_en like '%%$tag%%' or code like '%%$tag%%' ";
            $rs=qar($q); //print $q;
            if(count($rs)>0) $ar=array_merge($ar,$rs);
        }
        //print_r($ar);
        while(list($i,$id)=each($ar)){
            if($i>0) $ids.=',';
            $ids.=$id;
        }
    }
    $q="delete from flixer_data.viewer_ ";
    qexe($q); print $q;
    print " ids $ids ";
    $cond.=" and date>'2019-12-01' ";
    $cond.=" and premium=0 ";
    $q="select distinct(user) 'user' from flixer_vod.user_views where title in ($ids) $cond  ";
    print $q;
    $q=" insert into flixer_data.viewer_ (user) $q ";
    print "<br>$q ";
    qexe($q);
    print " <br>total ".$db->affected_rows." users ";
    
    $q="select * from flixer_data.viewer_ ";
    $dt=qdt($q);
    
    while(list($i,$dr)=each($dt)){
        extract($dr);
        $email=qval("select email from user where id='$dr[user]' ");
        if(filter_var($email,FILTER_VALIDATE_EMAIL)){
            //$q="replace into flixer_data.viewer_ (user,email) values ('$dr[user]','$email') ";
            $q="update flixer_data.viewer_ set email='$email' where user='$user' ";
            qexe($q); //print "<br>$q";
        }else{
            $q="delete from flixer_data.viewer_ where user='$user'";
            qexe($q)  //print "<br>$q";
        }
        print "<br>$user $email ";
        $k++;
    }
    print "total $k ";
    $q=" delete from flixer_data.viewer_ where email='' ";
    qexe($q);


}
if($action=='non-premium'){
    $q="
    SELECT id,email FROM `user` WHERE email<>'' and email REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$' and last_login > now()-interval 3 month order by id desc 
    ";
}
if($action=='util-mac-cal'){
    $q="select * from coupon_mac ";
    $dt=qdt($q); print $q;
    while(list(,$dr)=each($dt)){
        extract($dr);
        $hex1=str_replace(':','',$start_mac);
        $dec1=hexdec($hex1);
        $hex2=str_replace(':','',$end_mac);
        $dec2=hexdec($hex2);
        
        $qty=$dec2-$dec1;
        print "<br>$start_mac $hex1 $dec1 - $end_mac $hex2 $dec2 = $qty ";
        $q="update coupon_mac set qty=$qty where id='$id' ";
        print $q;
        qexe($q); 
        print '<br>'.$q;
    }
}
if($action=='util-ultraman-emails'){
    print $action;
    $q="select user,email from flixer_data.viewer_onepiece where email NOT REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$' limit 1000 ";
    //$q="select user,email from flixer_data.viewer_onepiece where email='' limit 2000 ";
    $dt=qdt($q);
    while(list(,$dr)=each($dt)){
        extract($dr);
        //$email=qval("select email from flixer_vod.user where id='$user' ");
        //$q="update flixer_data.viewer_onepiece set email='$email' where user='$user' ";
        $q="";
        //if(!strpos($email,'@')){}
        $q="delete from flixer_data.viewer_onepiece where user='$user' ";
        qexe($q);
        
        
        print "<br>$user $email $q";
        

    }
}
if($action=='util-rider-emails'){
    print $action;
    $dt=qdt("select user,email from flixer_data.viewer_rider where email NOT REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$' limit 10000 ");
    while(list(,$dr)=each($dt)){
        extract($dr);
        //$q="select email from flixer_vod.user where id='$user' ";
        //$email=qval($q);
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            $q="update flixer_data.viewer_rider set email='$email' where user='$user' ";
            //qexe($q);
            //print "<br>$user $email ";
        }else{
            $q="delete from flixer_data.viewer_rider  where user='$user' ";
            qexe($q);
            print "<br>clean $user $email ";

        }
    }
    
}
if($action=='util-android-users'){
    $q="select id,user,email from flixer_data.android_user ";
    $dt=qdt($q);
    while(list(,$dr)=each($dt)){
        extract($dr);
        $code=qval("select code from flixer_vod.coupon_code where id=$id+33071 ");
        //$email=qval("select email from flixer_vod.user where id='$user' ");
        //$q="update flixer_data.android_user set email='$email' where user='$user' ";
        //qexe($q);
        $q="update flixer_data.android_user set code='$code' where user='$user' ";
        qexe($q);
        print "<br>$user $email $code ";
        
    }
}
*/
?>