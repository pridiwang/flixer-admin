<?php 
// sendgrid API-key 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

function adminmail($to,$subject,$body){
    $sgkey="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";
    require "sendgrid-php/sendgrid-php.php";
    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("no-reply@flixerapp.com", "Flixer");
    $email->setSubject($subject);
    $email->addTo($to, "");
    $email->addContent("text/html", $body);
    $sendgrid = new \SendGrid($sgkey);
    try {
        $response = $sendgrid->send($email);
        /*print "<pre>";
        print $response->statusCode() . "\n";
        print_r($response->headers());
        print $response->body() . "\n";
        print "</pre>";
        */
        return true;
    } catch (Exception $e) {
        //echo 'Caught exception: ',  $e->getMessage(), "\n";
        return false;
    }
}
if($action=='admin-mail'){
    
    $subject="Flixer Premium Coupon";
    $q="select id,email,code from flixer_data.android_user where sent=0 limit 50 ";
    $dt=qdt($q);
    while(list(,$dr)=each($dt)){
        extract($dr);
        //print "<br>$email $code ";
        //$email="preedeew@gmail.com";
        //$code="TEXT-XXXXX-XXXXX";
$txt="
เรียน ท่านผู้ใช้บริการ FLIXER

ขอขอบคุณท่านที่ใช้บริการแอปพลิเคชั่น FLIXER มาโดยตลอด
​
จากเหตุการณ์ปัญหาการใช้งานในช่วงเดือนธันวาคมที่ผ่านมา ส่งผลให้ผู้ใช้งานบนระบบ Android บางท่านพบปัญหาในส่วนของการรับชมคอนเทนต์ต่าง ๆ และการต่ออายุสมาชิก Premium 
 
ทางทีมงาน FLIXER ได้ทำการตรวจสอบและแก้ไขในทันทีจนสามารถกลับมาใช้งานได้ตามปกติแล้ว 

อย่างไรก็ตามทางทีมงานได้ทำการส่ง E-mail ฉบับนี้เพื่อเป็นการขออภัยในความผิดพลาดที่เกิดขึ้น และทางทีมงานได้จัดส่งรหัสสำหรับสมัครสมาชิก Premium ฟรี เป็นระยะเวลา  2สัปดาห์ โดยมีเงื่อนไขดังต่อไปนี้

1. FLIXER ID ที่ต้องการใช้งานรหัสสมาชิก Premium จะต้องไม่เคยเป็นสมาชิก หรือทดลองใช้ Premium 3 วันมาก่อน (สามารถสมัครใหม่ได้)

2. สามารถกรอกรหัสสมาชิก Premium ได้ตั้งแต่ วันที่ 24 ธันวาคม 2562 จนถึงวันที่ 31 มกราคม 2563 หากพ้นจากวันที่ที่กำหนดไป จะไม่ทำการใช้งานรหัสสมาชิก Premium นี้ได้
 
*หมายเหตุ เงื่อนไขเป็นไปตามที่บริษัทกำหนด
 
 
รหัสสำหรับสมัครสมาชิก Premium
|code|
 
ทางทีมงานขอขอบคุณลูกค้า FLIXER ทุกท่าน และขออภัยในความผิดพลาดอีกครั้ง  และขอนำข้อผิดพลาดรวมถึงคำแนะนำจากผู้ใช้งานไปปรับปรุงเพื่อให้ประสบการณ์ในการรับชม FLIXER มีประสิทธิภาพต่อไป 
 

ขอแสดงความนับถือ
ทีมงาน FLIXER
";


        $body="<img src=https://img.flixerapp.com/app_icon.png>";
        $html=nl2br($txt);
        $html=str_replace('|code|',$code,$html);
        $body.=$html;
        $body.="<div style=text-align:center;background:#cccccc;color:#ffffff;padding:20px;margin-top:20px;>Flixer</div>";
        adminmail($email,$subject,$body);
        $q="update flixer_data.android_user set sent=1 where email='$email' ";
        qexe($q);
        print "<br>$id $email $code";
    }
}
?>
