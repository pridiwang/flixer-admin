<?php 
if($action=='ais-trial'){
    $q=" update user_platform set status='trial' where platform_id='$mobile' ";
    qexe($q);
    $action='report';$report='ais-user';
}
if($action=='ais-check'){
    extract($_POST);
    if(substr($mobile,0,2)=='66') $mobile='0'.substr($mobile,2,9);
    print "<form action=?action=ais-check method=post>
    <input type=number name=mobile placeholder=mobile value=$mobile ><button>search</button>
    </form>";
    if($mobile){
        $platform_id='66'.substr($mobile,1,9);
        print "platform_id $platform_id ";
        $q="select * from user_platform where platform_id='$platform_id' ";
        $up=qdr($q); 
        $u=qdr("select * from user where id='$up[user]' ");
        extract($u);
        print "<table class='table table-bordered table-stripped table-sm' >
        <tr><td>Type</td><td>$u[id] $u[type] $u[fbid] <a href=?action=edit&tb=user&id=$u[id]>.</a></td></tr>
        <tr><td>Email</td><td>$u[email]</td></tr>
        <tr><td>Premium</td><td>$u[premium] expired $u[premium_expire] </td></tr>
        <tr><td>AIS</td><td><a href=?action=edit&tb=user_platform&id=$up[id]>.</a> $up[status] $up[platform_private_id] <br>$up[start_datetime] - $up[expire_datetime] </td></tr>
        
        </table>
        <span onclick=$('.logs').toggle();>Logs</span>
        <pre class='logs' style=display:none; >$up[logs] </pre>";
        $q="select id,datetime,action from platform_data where platform_private_id='$up[platform_private_id]' or platform_id='$platform_id'  order by datetime desc ";
        print "<div class='logs' style=display:none;>".qbrowse($q,'')."</div>";

        
    }

}
?>