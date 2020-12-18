<html>
    <head>
    <link rel="stylesheet" type="text/css" href="./main_style.php">
    <?php

        include "../CorpESI/shrimp/phplib.php";
        dbset();
        session_start();
        if(!isset($_SESSION["Shrimp_userid"])){
            $_SESSION["Shrimp_userid"]=0;
        }

    ?>
    </head>



    <body onload="javascript:bodyload()" style="background-color:rgb(2,2,2);" id="docbody">
    <div class="mdetail_content">
    <img id="closewindow" src="./images/closewindow.png" align=right onmouseover="javascript:activeclose(1)" onmouseout="javascript:activeclose(0)" onclick="javascript:closethis();">
    <br>
    <?php
        //미션 status 에 따른 분류.
        // 0 : 수락가능 , 1: 진행중 , -1:Client 로부터 완료신청 , 3: Agent 로부터 완료승인 , 4: 완료된 미션 , 404: 실패한 미션
        
        $qr="select * from Shrimp_missions where indexx=".$_GET["mission_number"]." and (clientid=0 or clientid=".$_SESSION["shrimp_userid"].") order by clientid desc";
        
        //자신이 건 미션을 받아올때는 다른 쿼리를 써야한다.
        if($_GET["filter"]==4){
            $qr="select * from Shrimp_missions where indexx=".$_GET["mission_number"]." and clientid=".$_GET["client_id"]." order by clientid desc";
        }
        $result=$dbcon->query($qr);
        //errordebug($qr);
        if($result->num_rows==0){
            
            echo("<div class=\"no_available_found\">No Data</div>");
        }
        else{
            $mdata=$result->fetch_array();
            
            if($mdata["objective_icon"]==""){
                $mdata["objective_icon"]="https://images.evetech.net/types/32365/icon?size=32";
            }
            echo("<table><tr>\n");
            echo("<td class=\"mdetail_title\" colspan=2>".$mdata["title"]." Objectives</td></tr>\n");
            echo("</tr><td class=\"mdetail_content\" colspan=2>Agent : <a href=\"javascript:namecopy('".str_replace("'","",$mdata["username"])."')\">".$mdata["username"]."</a><br><br>The follwing objectives must be completed to finish the mission:</td></tr>\n");
            echo("<tr>\n");
            echo("<td class=\"mdetail_objective_icon\"><img src=\"".$mdata["objective_icon"]."\" class=\"mdetail_objective_icon\"></td>\n");
            echo("<td class=\"mdetail_objective\">".$mdata["objective"]."</td>");
            echo("</tr>\n");
            echo("<tr>\n");
            echo("<td class=\"mdetail_timelimit\" colspan=2><span class=\"mdetail_timelimit\">Time Limit : ".$mdata["expiretime"]."</span></td>\n");
            echo("</tr>\n");
            if($mdata["granted_icon"]!=""){
                echo("<tr><td colspan=2 class=\"mdetail_granted_title\">Granted Items</td></tr>");
                echo("<tr>");
                echo("<td class=\"mdetail_granted_icon\"><img src=\"".$mdata["granted_icon"]."\" class=\"mdetail_granted_icon\"></td>\n");
                echo("<td class=\"mdetail_granted\">".$mdata["granted"]."</td>");
                echo("</tr>");
            }
            if($mdata["reward_icon"]!=""){
                echo("<tr><td colspan=2 class=\"mdetail_reward_title\">Rewards</td></tr>");
                echo("<tr>");
                echo("<td class=\"mdetail_reward_icon\"><img src=\"".$mdata["reward_icon"]."\" class=\"mdetail_reward_icon\"></td>\n");
                echo("<td class=\"mdetail_reward\">".$mdata["reward"]."</td>");
                echo("</tr>");
            }
            if($mdata["bonus_icon"]!=""){
                echo("<tr><td colspan=2 class=\"mdetail_bonus_title\">Bonus Rewards</td></tr>");
                echo("<tr>");
                echo("<td class=\"mdetail_bonus_icon\"<img src=\"".$mdata["bonus_icon"]."\" class=\"mdetail_bonus_icon\"></td>\n");
                echo("<td class=\"mdetail_bonus\">".$mdata["bonus"]."</td>");
                echo("</tr>");
            }
            if($mdata["note_title"]!=""){
                echo("<tr><td colspan=2 class=\"mdetail_note_title\">".$mdata["note_title"]."</td></tr>");
                echo("<tr>");
                echo("<td colspan=2 class=\"mdetail_note\">".$mdata["note"]."</td>");
                echo("</tr>");
            }
            echo("</table>");
        }
    ?>

    </div>
    <div class=mdetail_decision>
    <?php
    // 0 : 수락가능 , 1: 진행중 , -1:Client 로부터 완료신청 , 3: Agent 로부터 완료승인 , 4: 완료된 미션 , 404: 실패한 미션
        if($mdata["status"]==0 && $mdata["userid"]!=$_SESSION["shrimp_userid"]){
            echo("<span class=\"mdetail_accept\" onclick=\"javscript:changethis(1);\">Accept</span>");
        }
        else if($mdata["status"]==1  && $mdata["clientid"]==$_SESSION["shrimp_userid"]){
            echo("<span class=\"mdetail_accept\" onclick=\"javscript:changethis(-1);\">Claim</span>");
        }
        else if($mdata["status"]==3 && $mdata["clientid"]==$_SESSION["shrimp_userid"]){
            echo("<span class=\"mdetail_accept\" onclick=\"javscript:changethis(4);\">Complete</span>");
        }
        else if($mdata["status"]==1 && $mdata["userid"]==$_SESSION["shrimp_userid"]){
            echo("<span class=\"mdetail_accept\" onclick=\"javscript:changethis(3);\">Confirm</span>");
        }
        else if($mdata["status"]==-1 && $mdata["userid"]==$_SESSION["shrimp_userid"]){
            echo("<span class=\"mdetail_accept\" onclick=\"javscript:changethis(4);\">Confirm</span>");
            echo("<span class=\"mdetail_accept\" onclick=\"javscript:changethis(404);\">Rescission</span>");
        }

        echo("<span class=\"mdetail_close\" onclick=\"javascript:closethis();\">Close</span>");

        if($mdata["status"]==0 && $mdata["userid"]==$_SESSION["shrimp_userid"]){
            echo("<span class=\"mdetail_accept\" onclick=\"javscript:changethis(403);\" style=\"margin-left:20px;\">Delete</span>");
        }
    ?>
    </div>
    </body>

    <script>
    function activeclose(button_active){
        var bimg=document.getElementById("closewindow");
        if(button_active==1){
            bimg.src="./images/closewindow_hl.png";
        }
        else{
            bimg.src="./images/closewindow.png";
        }
    }
    function closethis(){
        var iam=parent.document.getElementById("iframe_mission_detail");
        iam.remove();
        
       
    }
    function namecopy(copyingstr){
        
        var copyarea=document.createElement("textarea");
        copyarea.style.width="600";
        copyarea.style.height="600";
        copyarea.style.position = 'absolute';
        copyarea.style.left = '-9999px';
        copyarea.value=copyingstr;
        document.getElementById("docbody").appendChild(copyarea);
        copyarea.select();
        copyarea.setSelectionRange(0, 99999);
        document.execCommand("cut");
        //alert(copyingstr);
        document.getElementById("docbody").removeChild(copyarea);
        
    }
  
    function changethis(var_stat){
        var ESIdata=new XMLHttpRequest();
        var esiurl="";
        if(<?=( $mdata["userid"] == $_SESSION["shrimp_userid"])?1:0 ?>){
            esiurl="./submit_mission_status.php?mission_index=<?=$mdata["indexx"]?>&userid=<?=$mdata["userid"]?>&clientid=<?=$mdata["clientid"]?>&status="+var_stat;
        }
        else if(<?=($mdata["clientid"]==0 || $mdata["clientid"]==$_SESSION["shrimp_userid"])?1:0?>){
            esiurl="./submit_mission_status.php?mission_index=<?=$mdata["indexx"]?>&userid=<?=$mdata["userid"]?>&clientid=<?=$_SESSION["shrimp_userid"]?>&status="+var_stat;
        }
        ESIdata.onreadystatechange=function(){

            if (this.readyState == XMLHttpRequest.DONE && this.status==200){
                
                location.reload();

            }   
        }
        
        ESIdata.open("GET",esiurl,false);
        ESIdata.send();     

    }
    </script>

</html>
