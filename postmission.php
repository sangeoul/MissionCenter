<style>

table,td,tr{
    border:1px solid black;
    border-collapse:collapse;
}
textarea{
    width:300px;
    height:140px;
}
input{
    width:260px;
}
</style>

<?php

include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";
dbset();
logincheck();

$fixed_phrase=array(
"_shrimp"=>"./images/shrimp.jpg",
"_TRC"=>"./images/TRC.jpg"
);

$qr="select  * from Shrimp_agents where userid=".$_SESSION["shrimp_userid"].";";
$permissionresult=$dbcon->query($qr);

//현재 올라가 있는 미션 수를 체크한다.
$qr="select count(index_number) as n where userid=".$_SESSION["shrimp_userid"]." group by indexx";
$limitresult=$dbcon->query($qr);
if($limitresult->num_rows==0){
    $limitresult=false;
}
else{
    $limitresult=$limitresult->fetch_row();
    if($limitresult[0]<$MAX_MISSIONS_PER_AGENT){
        $limitresult=false;
    }
    else{
        $limitresult=true;
    }
}

if($permissionresult->num_rows==0){

    errorclose("미션 제시 권한이 없습니다.");
}
else if($limitresult){
    errorclose("미션 제시 숫자 한계입니다.(".$MAX_MISSIONS_PER_AGENT.")");
}

else if(isset($_POST["title"])){
    
    $erroroccured=0;

    $_POST["title"]=str_replace("\\","",htmlentities($_POST["title"],ENT_QUOTES));
    $_POST["objective"]=str_replace("\\","",htmlentities($_POST["objective"],ENT_QUOTES));
    $_POST["granted"]=str_replace("\\","",htmlentities($_POST["granted"],ENT_QUOTES));
    $_POST["reward"]=str_replace("\\","",htmlentities($_POST["reward"],ENT_QUOTES));
    $_POST["bonus"]=str_replace("\\","",htmlentities($_POST["bonus"],ENT_QUOTES));
    $_POST["note_title"]=str_replace("\\","",htmlentities($_POST["note_title"],ENT_QUOTES));
    $_POST["note"]=str_replace("\\","",htmlentities($_POST["note"],ENT_QUOTES));

    $_POST["title"]=preg_replace("/\r\n|\r|\n/","",nl2br($_POST["title"],false));
    $_POST["objective"]=preg_replace("/\r\n|\r|\n/","",nl2br($_POST["objective"],false));
    $_POST["granted"]=preg_replace("/\r\n|\r|\n/","",nl2br($_POST["granted"],false));
    $_POST["reward"]=preg_replace("/\r\n|\r|\n/","",nl2br($_POST["reward"],false));
    $_POST["bonus"]=preg_replace("/\r\n|\r|\n/","",nl2br($_POST["bonus"],false));
    $_POST["note_title"]=preg_replace("/\r\n|\r|\n/","",nl2br($_POST["note_title"],false));
    $_POST["note"]=preg_replace("/\r\n|\r|\n/","",nl2br($_POST["note"],false));



    $_POST["objective_icon"]=getIconUri($_POST["objective_icon"]);
 
    $_POST["granted_icon"]=getIconUri($_POST["granted_icon"]);
    $_POST["reward_icon"]=getIconUri($_POST["reward_icon"]);
    $_POST["bonus_icon"]=getIconUri($_POST["bonus_icon"]);

    $_POST["rounds"]=intval($_POST["rounds"]);
    
    if(strpos($_POST["objective_icon"],"error")!==false){
        errordebug("Objective icon ".$_POST["objective_icon"]);
        $erroroccured++;
    }
    else if(strpos($_POST["granted_icon"],"error")!==false){
        errordebug("Granted item icon ".$_POST["granted_icon"]);
        $erroroccured++;
    }
    else if(strpos($_POST["reward_icon"],"error")!==false){
        errordebug("Reward icon ".$_POST["reward_icon"]);
        $erroroccured++;
    }
    else if(strpos($_POST["bonus_icon"],"error")!==false){
        errordebug("Bonus Reward icon ".$_POST["bonus_icon"]);
        $erroroccured++;
    }
    else if($_POST["objective_icon"]==""){
        errordebug("You must set objective");
        $erroroccured++;       
    }
    else if($_POST["reward_icon"]==""){
        errordebug("You must set reward");
        $erroroccured++;  
    }

    if($_POST["granted_icon"]==""){
        $_POST["granted"]=" ";
    }
    if($_POST["bonus_icon"]==""){
        $_POST["bonus"]=" ";
    }
    if($_POST["note_title"]==""){
        $POST_["note"]=" ";
    }
    /*
    echo(strpos($_POST["objective_icon"],"error")."<br>\n");
    echo(strpos($_POST["granted_icon"],"error")."<br>\n");
    echo(strpos($_POST["reward_icon"],"error")."<br>\n");
    echo(strpos($_POST["bonus_icon"],"error")."<br>\n");
    */ 
    if($erroroccured==0){
        //indexx 값을 가져와야한다.
        $qr="select indexx from Shrimp_missions order by indexx desc limit 1";
        $temp_indexx=$dbcon->query($qr)->fetch_row();
        $temp_indexx=$temp_indexx[0]+1;
        $qr="insert into Shrimp_missions (indexx,title,username,userid,
        objective_icon,objective,granted_icon,granted,
        reward_icon,reward,bonus_icon,bonus,note_title,note,offertime,expiretime) values (
        ".$temp_indexx.",\"".$_POST["title"]."\",\"".$_SESSION["shrimp_username"]."\",
        ".$_SESSION["shrimp_userid"].",
        \"".$_POST["objective_icon"]."\",\"".$_POST["objective"]."\",
        \"".$_POST["granted_icon"]."\",\"".$_POST["granted"]."\",
        \"".$_POST["reward_icon"]."\",\"".$_POST["reward"]."\",
        \"".$_POST["bonus_icon"]."\",\"".$_POST["bonus"]."\",
        \"".$_POST["note_title"]."\",\"".$_POST["note"]."\",
        UTC_TIMESTAMP ,
        \"".$_POST["limithours"].":00:00\"
        );";
        $result=$dbcon->query($qr);
        if($result){
            $dbcon->query("update Shrimp_agents set offer=offer+".$_POST["rounds"]." where userid=".$_SESSION["shrimp_userid"]);
            for($i=1;$i<$_POST["rounds"];$i++){
                $dbcon->query($qr);
            }
            errordebug("미션이 등록되었습니다.");
            
        }
        else{
            errordebug("DB 에러 발생");

        }
    }

}


function getIconUri($_keyword){
    global $fixed_phrase;
    global $dbcon;
    if($_keyword==""){
        return "";
        
    }

    else if(isset($fixed_phrase[$_keyword])){
        
        return $fixed_phrase[$_keyword];
    }
    else{
        
        $qr="select typeid from EVEDB_Item where itemname=\"".$_keyword."\"";
        $keywordresult=$dbcon->query($qr);
        if($keywordresult->num_rows>0){
            
            $type_id=$keywordresult->fetch_row();
            $type_id=$type_id[0];
            
        }
        else{

            //strict= true 로 검색해서 type_id 를 찾는다.
            $apiurl="https://esi.evetech.net/latest/search/?search=".urlencode($_keyword)."&categories=inventory_type&datasource=tranquility&language=en-us&strict=true";
            $iconcurl= curl_init();
            curl_setopt($iconcurl, CURLOPT_SSL_VERIFYPEER, $SSLauth); 
            curl_setopt($iconcurl,CURLOPT_HTTPGET,true);
            curl_setopt($iconcurl,CURLOPT_URL,$apiurl);
            curl_setopt($iconcurl,CURLOPT_RETURNTRANSFER,true);
        
            $curl_response=curl_exec($iconcurl);
            curl_close($iconcurl);
        
            $item_data=json_decode($curl_response,true);
            //검색 결과가 없으면 strict=false 로 한 번 더 검색
            if(sizeof($item_data["inventory_type"])==0){
                $apiurl="https://esi.evetech.net/latest/search/?search=".urlencode($_keyword)."&categories=inventory_type&datasource=tranquility&language=en-us&strict=false";
                $iconcurl= curl_init();
                curl_setopt($iconcurl, CURLOPT_SSL_VERIFYPEER, $SSLauth); 
                curl_setopt($iconcurl,CURLOPT_HTTPGET,true);
                curl_setopt($iconcurl,CURLOPT_URL,$apiurl);
                curl_setopt($iconcurl,CURLOPT_RETURNTRANSFER,true);
            
                $curl_response=curl_exec($iconcurl);
                curl_close($iconcurl);
            
                $item_data=json_decode($curl_response,true);
                //그래도 없으면 "no_uri" 를 리턴.
                if(sizeof($item_data["inventory_type"])==0){
                    return "error:no_result";
                }         
            }
            $type_id=$item_data["inventory_type"][0];
        }

        $iconuri="https://images.evetech.net/types/".$type_id."/icon";

        $checkcurl = curl_init();

        curl_setopt_array($checkcurl, array(    
            CURLOPT_URL => $iconuri,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => true));
        
        $header = explode("\n", curl_exec($checkcurl));
        curl_close($checkcurl);
        if(strpos($header[1],"image")!==false){
            return $iconuri;
        }
        else if(strpos($header[0],"Not Found")!==false){
            return "error:not_found";
        }
        else if(strpos($header[0],"Bad Request")!==false){
            return "error:bad_request(You must use another item)";
        }
        else{
            return "error:unknown";
        }

    }

}

?>
<html>
<head>
<title> 미션 제시하기 </title>
</head>
<body onload="javascript:bodyload();">
※입력 방법:
Title / Objective / Reward 는 필수항목<br><br>

그 외에는 선택항목이며, 사용하지 않을 경우 아이콘 칸을 비우면 된다.<br><br>
아이콘으로 쓰고 싶은 아이템 이름을 정확하게 입력하면 아이콘이 입력된다<br><br>
아이콘에 블루프린트 아이콘은 사용할 수 없으며, 그 외에도 사용하지 못하는 아이콘들이 존재한다.<br><Br>
<form method="POST" action="./postmission.php">
<table>
<tr>
<td>Mission Title</td><td><input type=text id="title" name="title" maxlength=60></td>
</tr>
<tr>
<td>Mission objective 아이콘<br>(아이템 이름을 정확하게 입력)</td><td><input type=text value="Proof of Discovery: Anomalies" id="objective_icon" name="objective_icon" maxlength=80></td>
</tr>
<tr>
<td>Mission Objective</td><td><textarea id="objective" name="objective" maxlength=1500>미션 목표 설명</textarea></td>
</tr>
<tr>
<td>Granted Item 아이콘<br>(아이템 이름을 정확하게 입력)</td><td><input type=text value="" id="granted_icon" name="granted_icon" maxlength=80></td>
</tr>
<tr>
<td>Granted Item</td><td><textarea id="granted" name="granted" maxlength=1500>주어지는 물품에 대한 설명</textarea></td>
</tr>
<tr>
<td>Reward 아이콘<br>(아이템 이름을 정확하게 입력)</td><td><input type=text value="A lot of money" id="reward_icon" name="reward_icon" maxlength=80></td>
</tr>
<tr>
<td>Reward</td><td><textarea id="reward" name="reward" maxlength=1500>보상에 대한 설명</textarea></td>
</tr>
<tr>
<td>Bonus Reward 아이콘<br>(아이템 이름을 정확하게 입력)</td><td><input type=text id="bonus_icon" name="bonus_icon" value="" maxlength=80></td>
</tr>
<tr>
<td>Bonus Reward</td><td><textarea id="bonus" name="bonus" maxlength=1500>추가 보상에 대한 설명</textarea></td>
</tr>
<tr>
<td>Additional Note Title<br>미션에 덧붙일 문구 제목</td><td><input type=text id="note_title" name="note_title" value="" maxlength=80></td>
</tr>
<tr>
<td>Additional Note<br>미션에 덧붙일 문구 내용</td><td><textarea id="note" name="note" maxlength=1500>메모 및 비고</textarea></td>
</tr>
<tr>
    <td>Time Limit<br>(시간단위로 가능.)</td><td><input type=number id="limithours" name="limithours" max=336 value=24></td>
</tr>
<tr>
    <td>Round<br>(같은 미션을 여러개 건다)</td><td><input type=number id="rounds" name="rounds" max=50 value=1></td>
</tr>
<tr>
<td colspan=2><input type=submit value="확인" style="width:300px;height:50px;font-size:25px;"></td>
</tr>
</table>


</body>
</html>
<script>

function bodyload(){


    if(<?=isset($_POST["title"])?>){
        document.getElementById("title").value="<?=html_entity_decode(str_replace("<br>","\\n",$_POST["title"]))?>";
    }
    if(<?=isset($_POST["objective"])?>){
        document.getElementById("objective").value="<?=html_entity_decode(str_replace("<br>","\\n",$_POST["objective"]))?>";
    }
    if(<?=isset($_POST["granted"])?>){
        document.getElementById("granted").value="<?=html_entity_decode(str_replace("<br>","\\n",$_POST["granted"]))?>";
    }
    if(<?=isset($_POST["reward"])?>){
        document.getElementById("reward").value="<?=html_entity_decode(str_replace("<br>","\\n",$_POST["reward"]))?>";
    }
    if(<?=isset($_POST["bonus"])?>){
        document.getElementById("bonus").value="<?=html_entity_decode(str_replace("<br>","\\n",$_POST["bonus"]))?>";
    }
    if(<?=isset($_POST["note_title"])?>){
        document.getElementById("note_title").value="<?=html_entity_decode(str_replace("<br>","\\n",$_POST["note_title"]))?>";
    }
    if(<?=isset($_POST["note"])?>){
        document.getElementById("note").value="<?=html_entity_decode(str_replace("<br>","\\n",$_POST["note"]))?>";
    }
    if(<?=isset($_POST["limithours"])?>){
        document.getElementById("limithours").value="<?=$_POST["limithours"]?>";
    }
    if(<?=isset($_POST["rounds"])?>){
        document.getElementById("rounds").value="<?=$_POST["rounds"]?>";
    }
}
</script>