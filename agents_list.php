<html>
    <head>
    <link rel="stylesheet" type="text/css" href="./main_style.php">
    <?php

        include "../CorpESI/shrimp/phplib.php";
        dbset();
        session_start();
        if(!isset($_GET["filter"])){
            $_GET["filter"]=1;
        }
    ?>
    </head>
<body class="agents_list" onload="javascript:bodyload()">
<div class="agent_list_head" id="titlediv">
<?=($_GET["filter"]==4?"Client List":"Agent List")?>
</div>
<div id="agent_list" class="agent_list">

</div>


</body>

<script>

var agents=new Array();



function bodyload(){

    var listdiv=document.getElementById("agent_list");

    <?php
    if($_GET["filter"]==1){
        //수락할 수 있는 미션들을 다 보여준다
        $qr="select username,userid,agent_type from Shrimp_agents where userid!=".$_SESSION["shrimp_userid"]." order by offer desc, username asc";
        $result=$dbcon->query($qr);
        for($i=0;$i<$result->num_rows;$i++){
            $agentdata=$result->fetch_row();
            echo("listdiv.appendChild(agent_slot(\"".$agentdata[0]."\",".$agentdata[1].",\"".$agentdata[2]."\"));\n");
            echo("agents[".$i."]=".$agentdata[1].";\n\n");
        }
    }
    else if($_GET["filter"]==2){

        
        echo("listdiv.appendChild(agent_slot(\"All\",0,\"\"));\n");
        echo("agents[0]=0;\n\n");

        //내가 진행중인 미션들을 받아온다.
        $qr="select userid from Shrimp_missions 
        where clientid=".$_SESSION["shrimp_userid"]." and userid!=".$_SESSION["shrimp_userid"]." 
        and (status<4 and status!=0) 
        group by userid,username order by username asc";
        //echo("console.log('".$qr."');");
        $result=$dbcon->query($qr);
        for($i=1;$i<=$result->num_rows;$i++){
            $agent_id_data=$result->fetch_row();
            $qr="select username,userid,agent_type from Shrimp_agents where userid=".$agent_id_data[0];
            $agent_result=$dbcon->query($qr);
            $agentdata=$agent_result->fetch_row();
            echo("listdiv.appendChild(agent_slot(\"".$agentdata[0]."\",".$agentdata[1].",\"".$agentdata[2]."\"));\n");
            echo("agents[".$i."]=".$agentdata[1].";\n\n");
        }

    }
    else if($_GET["filter"]==3){

        echo("listdiv.appendChild(agent_slot(\"All\",0,\"\"));\n");
        echo("agents[0]=0;\n\n");

        //내가 완료한 미션들을 받아온다.
        $qr="select userid from Shrimp_missions 
        where clientid=".$_SESSION["shrimp_userid"]." and status>3 
        group by userid,username,offertime order by offertime desc, username asc";
        $result=$dbcon->query($qr);
        for($i=1;$i<=$result->num_rows;$i++){
            $agent_id_data=$result->fetch_row();
            $qr="select username,userid,agent_type from Shrimp_agents where userid=".$agent_id_data[0];
            $agent_result=$dbcon->query($qr);
            $agentdata=$agent_result->fetch_row();
            echo("listdiv.appendChild(agent_slot(\"".$agentdata[0]."\",".$agentdata[1].",\"".$agentdata[2]."\"));\n");
            echo("agents[".$i."]=".$agentdata[1].";\n\n");
        }
        

    }
    else if($_GET["filter"]==4){

       
        echo("listdiv.appendChild(agent_slot(\"Not Accepted\",0,\"\"));\n");
        echo("agents[0]=0;\n\n");

        //내가 걸어놓은 미션들을 받아온다
        $qr="select clientid from Shrimp_missions 
        where userid=".$_SESSION["shrimp_userid"]." and clientid!=0 
        group by clientid,username order by username asc";
        //echo("console.log('".$qr."');");
        $result=$dbcon->query($qr);
        for($i=1;$i<=$result->num_rows;$i++){
            $agent_id_data=$result->fetch_row();
            $qr="select username,userid from Shrimp_accounts where userid=".$agent_id_data[0];
            $agent_result=$dbcon->query($qr);
            $agentdata=$agent_result->fetch_row();
            
            echo("listdiv.appendChild(agent_slot(\"".$agentdata[0]."\",".$agentdata[1].",\"\"));\n");
            echo("agents[".$i."]=".$agentdata[1].";\n\n");
            
        }

    }
    $result=$dbcon->query($qr);



    ?>
}


function select_agent(agentid){
    for(var i=0;i<agents.length;i++){
        document.getElementById("agent_slot"+agents[i]).className="agent_slot";
    }
    document.getElementById("agent_slot"+agentid).className="agent_slot_selected";
    parent.document.getElementById("missionslist").src="./missions_list.php?agent_id="+agentid+"&filter=<?=$_GET["filter"]?>";

}


function agent_slot(agent_name,agent_id,agent_type){

    var table_return=document.createElement("table");
    var tr_agentlist=new Array(document.createElement("tr"),document.createElement("tr"),document.createElement("tr"));
    var td_portrait=document.createElement("td");
    var td_name=document.createElement("td");
    var td_type=document.createElement("td");
    var td_conv=document.createElement("td");
    var img_portrait=document.createElement("img");
    var img_conv=document.createElement("img");

    table_return.className="agent_slot";
    table_return.setAttribute("ondblclick","javascript:select_agent("+agent_id+");");
    table_return.id="agent_slot"+agent_id;
    td_portrait.setAttribute("rowspan","3");
    td_portrait.className="agent_portrait";
    img_portrait.setAttribute("src","https://images.evetech.net/characters/"+agent_id+"/portrait?size=64");
    img_portrait.className="agent_portrait";
    
    if(agent_id==0 && (agent_name=="All" || agent_name=="Not Accepted")){
        img_portrait.setAttribute("src","./images/aura64.png");
    }

    td_name.className="agent_name";
    td_type.className="agent_type";

    td_conv.className="agent_conv";
    img_conv.id="conv_icon"+agent_id;
    img_conv.setAttribute("src","./images/conv.png");
    img_conv.setAttribute("onclick","javascript:select_agent("+agent_id+");");
    img_conv.setAttribute("onmouseover","javascript:convhover("+agent_id+",1);");
    img_conv.setAttribute("onmouseout","javascript:convhover("+agent_id+",0);");
    img_conv.className="agent_conv";

    td_portrait.appendChild(img_portrait);
    td_conv.appendChild(img_conv);
    tr_agentlist[0].appendChild(td_portrait);
    tr_agentlist[0].appendChild(td_name);
    tr_agentlist[1].appendChild(td_type);
    tr_agentlist[2].appendChild(td_conv);
    for(var i=0;i<tr_agentlist.length;i++){
        table_return.appendChild(tr_agentlist[i]);
    }
    
    td_name.innerHTML=""+agent_name;
    td_type.innerHTML=""+agent_type;

    return table_return;
}

function convhover(agent_id,bool_hover){
    if(bool_hover==1){
        document.getElementById("conv_icon"+agent_id).src="./images/conv_hl.png";
    }
    else if(bool_hover==0){
        document.getElementById("conv_icon"+agent_id).src="./images/conv.png";
    }
}
</script>
</html>

