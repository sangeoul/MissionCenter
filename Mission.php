<?php

include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";
session_start();
dbset();

if(isset($_SESSION["shrimp_userid"]) ) {
    logincheck();
    $portmenu="<table><tr><td><img class=\"mainportrait\" src=\"https://images.evetech.net/characters/".$_SESSION["shrimp_userid"]."/portrait?size=128\"></td>\n";
    $portmenu.="<td><span class=\"mainusername\">".$_SESSION["shrimp_username"]."</span><br><a href=\"../CorpESI/shrimp/logout.php?redirect=".$serveraddr."/MissionCenter/Mission.php\" >Logout</a></td></tr></table>\n";

}
else{
    $_SESSION["shrimp_login_failed_location"]=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $portmenu="<a href=\"../CorpESI/shrimp/login.php\" class=\"mainlogin\">Login</a>";
}

?>
<html>
    <head>
    <title>Mission Center</title>
    <link rel="stylesheet" type="text/css" href="main_style.php">
    </head>
<body>
<table>
    <tr>
        <td class="mainportrait"><?=$portmenu?></td>
        <td class="mainmenu1"><a href="javascript:access_list(1);">Agents List</a></td>
        <td class="mainmenu2"><a href="javascript:access_list(2);">My Missions</a></td>
        <td class="mainmenu3"><a href="javascript:access_list(3);">History</a></td>
        <?php
            $qr="select username from Shrimp_agents where userid=".$_SESSION["shrimp_userid"].";";
            $result=$dbcon->query($qr);
            if($result->num_rows>0){
                echo("<td class=\"mainmenu4\"><a href=\"javascript:access_list(4);\">Agent Menu</a></td>\n");
            }
        
        ?>
</tr>
</table>
<div id=iframediv>
<iframe class="agentslist" src="agents_list.php" id="agentslist">
</iframe>
<iframe class="missionslist" src="missions_list.php" id="missionslist">
</iframe>
</div>

</body>
</html>
<script language="javascript">

function access_list(filter){
    var agentslist=document.getElementById("agentslist");
    var missionslist=document.getElementById("missionslist");

    agentslist.src="./agents_list.php?filter="+filter;
    missionslist.src="./missions_list.php?agent_id=0&filter="+filter;


}

</script>