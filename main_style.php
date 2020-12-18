<?php
header("Content-type: text/css");

$file = fopen("main_style.css","r");
echo(fread($file,filesize("main_style.css")));
fclose($file);
?>
