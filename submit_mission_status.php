<?php
  include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";
    dbset();
    session_start();
    /*
    $_GET["mission_index"];
    $_GET["userid"];
    $_GET["clientid"];
    $_GET["status"];
    */
  
    // 0 : 수락가능 , 1: 진행중 , -1:Client 로부터 완료신청 , 3: Agent 로부터 완료승인 , 4: 완료된 미션 , 404: 실패한 미션
    //미션 수락하기.
    if($_GET["status"]==1 && $_GET["clientid"]==$_SESSION["shrimp_userid"]){

        //이미 받은 미션을 중복으로 눌러서 받는 것이 아닌지 체크한다.
        $qr="select * from Shrimp_missions 
        where status=1 and indexx=".$_GET["mission_index"]." and clientid=".$_GET["clientid"].";";
        $checkresult= $dbcon->query($qr);


        //중복이 아니면 미션을받는다.
        if($checkresult->num_rows==0){
            $resultqr="update Shrimp_missions 
            set status=1,clientid=".$_GET["clientid"].",clientname=\"".$_SESSION["shrimp_username"]."\" ,accepttime=UTC_TIMESTAMP() 
            where status=0 and indexx=".$_GET["mission_index"]." and userid=".$_GET["userid"]." limit 1;";
        }
    }
    //미션 완료 신청하기 (Client)
    else if($_GET["status"]==-1 && $_GET["clientid"]==$_SESSION["shrimp_userid"]){
        $resultqr="update Shrimp_missions 
        set status=-1
        where status=1 and indexx=".$_GET["mission_index"]." and userid=".$_GET["userid"]." and clientid=".$_GET["clientid"]."
        and ADDTIME(accepttime,expiretime)>UTC_DATE();";
    }
    //미션 최종 완료하기 (Client)
    else if($_GET["status"]==4 && $_GET["clientid"]==$_SESSION["shrimp_userid"]){
        $resultqr="update Shrimp_missions 
        set status=4
        where status=3 and indexx=".$_GET["mission_index"]." and userid=".$_GET["userid"]." and clientid=".$_GET["clientid"].";";
    }
    //미션 완료 신청하기 (Agent)
    else if($_GET["status"]==3 && $_GET["userid"]==$_SESSION["shrimp_userid"]){
        $resultqr="update Shrimp_missions 
        set status=3
        where status=1 and indexx=".$_GET["mission_index"]." and userid=".$_GET["userid"]." and clientid=".$_GET["clientid"].";";
    }
    //미션 최종 완료하기 (Agent)
    else if($_GET["status"]==4 && $_GET["userid"]==$_SESSION["shrimp_userid"]){
        $resultqr="update Shrimp_missions 
        set status=4
        where status=-1 and indexx=".$_GET["mission_index"]." and userid=".$_GET["userid"]." and clientid=".$_GET["clientid"].";";
    }
    //미션 실패 처리하기 (Agent)
    else if($_GET["status"]==404 && $_GET["userid"]==$_SESSION["shrimp_userid"]){
        $resultqr="update Shrimp_missions 
        set status=404
        where status=-1 and indexx=".$_GET["mission_index"]." and userid=".$_GET["userid"]." and clientid=".$_GET["clientid"].";";
    }
    //미션 삭제하기
    else if($_GET["status"]==403 &&$_GET["userid"]==$_SESSION["shrimp_userid"] && $_GET["clientid"]==0)
    {
        $resultqr="delete from Shrimp_missions
        where status=0 and indexx=".$_GET["mission_index"]." and userid=".$_GET["userid"]." and clientid=0;";
    }
    
    $result=$dbcon->query($resultqr);
    if($result){
       // echo("SUCCESS<br>\n".$resultqr);

    }
    else{
       // echo("NAH<br>\n".$resultqr);
    }

?>
