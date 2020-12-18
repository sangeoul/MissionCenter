<html>
    <head>
    <link rel="stylesheet" type="text/css" href="./main_style.php">
    <?php

include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";
        dbset();
        session_start();
        if(!isset($_SESSION["shrimp_userid"])){
            $_SESSION["shrimp_userid"]=0;
        }
        if(!isset($_GET["filter"])){
            $_GET["filter"]=1;
        }

    ?>
    </head>
<body class="missions_list" onload="javascript:bodyload()" id="missions_list_body">
<div id="missions_list">
</div>


</body>

<script>
var missions=new Array();
function bodyload(){

    var mlistdiv=document.getElementById("missions_list");

    <?php
        //미션 status 에 따른 분류.
        // 0 : 수락가능 , 1: 진행중 , -1:Client 로부터 완료신청 , 3: Agent 로부터 완료승인 , 4: 완료된 미션 , 404: 실패한 미션
        if($_GET["filter"]==1){
            //수락할 수 있는 미션들을 받아온다
            $qr="select distinct title,indexx, objective_icon,objective,status,clientid,userid from Shrimp_missions 
            where (clientid=0 or clientid=".$_SESSION["shrimp_userid"].") 
            and userid=".$_GET["agent_id"]." and userid!=".$_SESSION["shrimp_userid"]."
            and status<4
            order by clientid desc limit ".$MAX_MISSIONS_PER_AGENT;
            //echo("alert('DEBUG : ".$qr."');\n");
        }
        else if ($_GET["filter"]==2){
            //내가 진행중인 미션들을 받아온다.
            
            $qr="select distinct title,indexx, objective_icon,objective,status,clientid,userid  from Shrimp_missions 
            where clientid=".$_SESSION["shrimp_userid"]." 
            and (".($_GET["agent_id"]==0?"true":"false")." or userid=".$_GET["agent_id"].") 
            and (status<4 and status!=0) 
            order by status desc limit ".$MAX_MISSIONS_PER_CLIENT;
            //var_dump($qr);
            //echo("alert('DEBUG : ".$qr."');\n");
        }
        else if($_GET["filter"]==3){
            //내가 완료한 미션들을 받아온다.
            $qr="select distinct title,indexx, objective_icon,objective,status,clientid,userid  from Shrimp_missions 
            where clientid=".$_SESSION["shrimp_userid"]." 
            and (".($_GET["agent_id"]==0?"true":"false")." or userid=".$_GET["agent_id"].") 
            and status>3  
            order by status desc limit ".$MAX_MISSIONS_COMPLETED;
        }
        else if($_GET["filter"]==4){
            //내가 건 미션들을 받아온다.
            $qr="select distinct title,indexx, objective_icon,objective,status,clientid,userid from Shrimp_missions 
            where userid=".$_SESSION["shrimp_userid"]." 
            and clientid=".$_GET["agent_id"]."
            and 1  
            order by status desc limit ".$MAX_MISSIONS_PER_AGENT;
        }
        
        $result=$dbcon->query($qr);
        
        for($i=0;$i<$result->num_rows;$i++){
            $missiondata=$result->fetch_row();
            
            $missiondata[3]=explode("<br>",$missiondata[3]);
            if(!isset($missiondata[3][1])){
                $missiondata[3][1]=" ";
            }
            
            $missiondata[3]=str_replace("\"","\\\"",$missiondata[3][0]."<br>".$missiondata[3][1]);
            
            echo("mlistdiv.appendChild(mission_slot(\"".$missiondata[0]."\",".$missiondata[1].",\"".$missiondata[2]."\",\"".$missiondata[3]."\",".$missiondata[4].",".$missiondata[5].",".$missiondata[6]."));\n");
            echo("missions[".$i."]=".$missiondata[1].";");

        }
        if($result->num_rows==0){
            echo("mlistdiv.className=\"no_available_found\";\n");
            echo("mlistdiv.innerHTML=\"No Available Missions Found\";\n");
        }
    ?>   
}


function mission_slot(mission_title,indexx,objective_icon,mission_objective,status,clientid,userid){

    
    var table_return=document.createElement("table");
    var tr_missionlist=new Array(document.createElement("tr"),document.createElement("tr"));
    var td_objective_icon=document.createElement("td");
    var td_title=document.createElement("td");
    var td_detail=document.createElement("td");

    var a_title=document.createElement("a");
    var span_status=document.createElement("span");
    var img_objective_icon=document.createElement("img");
    var span_detail=document.createElement("span");

    table_return.className="mission_slot";
    table_return.setAttribute("ondblclick","javascript:select_mission("+indexx+","+clientid+");");
    table_return.id="mission_slot"+indexx;

    td_objective_icon.className="mission_objective_icon";
    td_objective_icon.setAttribute("rowspan","2");
    img_objective_icon.className="mission_objective_icon";
    img_objective_icon.setAttribute("src",objective_icon);
    


    td_title.className="mission_title";
    a_title.className=" ";
    a_title.setAttribute("href","javascript:select_mission("+indexx+","+clientid+");");
    a_title.innerHTML=mission_title;
    
    switch(status){
        case 1:
            span_status.className="mission_status1";
            span_status.innerHTML="(Accepted)";
        break;
        case -1:
            span_status.className="mission_status_1";
            span_status.innerHTML="(Claimd)";
        break;
        case 3:
            span_status.className="mission_status3";
            span_status.innerHTML="(Confirmed)";
        break;
        case 4:
            span_status.className="mission_status4";
            span_status.innerHTML="(Complete)";
        break;
        default:
            span_status.className="mission_status1";
            span_status.innerHTML="";        
    }

    td_detail.className="mission_detail";
    span_detail.innerHTML=mission_objective;
    span_detail.className="mission_detail";

    td_objective_icon.appendChild(img_objective_icon);
    td_title.appendChild(a_title);
    td_title.appendChild(document.createTextNode (" "));
    td_title.appendChild(span_status);
    td_detail.appendChild(span_detail);
    tr_missionlist[0].appendChild(td_objective_icon);
    tr_missionlist[0].appendChild(td_title);
    tr_missionlist[1].appendChild(td_detail);
    for(var i=0;i<tr_missionlist.length;i++){
        table_return.appendChild(tr_missionlist[i]);
    }

    return table_return;
}

function select_mission(mission_indexx,client_id){
    
    var iframe_detail=document.createElement("iframe");
    
    iframe_detail.setAttribute("id","iframe_mission_detail");
    
    iframe_detail.className="mission_detail";
    
    iframe_detail.src="https://"+location.hostname+"/MissionCenter/mission_detail.php?mission_number="+mission_indexx+"&filter=<?=$_GET["filter"]?>&client_id="+client_id;
            
    document.getElementById("missions_list_body").appendChild(iframe_detail);
    
}
</script>

</html>

