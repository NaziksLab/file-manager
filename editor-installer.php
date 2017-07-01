<html>
<?php
//YOU MUSTN'T CHANGE THE CODE BELOW
//NAZIKS(c) ALL RIGHTS RESERVED
?>
<head>
<title>Installing File Namager by Nazik</title>
<meta name=viewport content="width=device-width user-scalable=no">
<meta charset=utf-8>
</head>
<body>
<?php
if(!file_exists('editor.php')){
echo '<h1>Installing Editor</h1>';
echo '<hr><br><div class=txt-status><b>Status</b>: Downloaded</div><div class=status>';
echo rand(1,10).'% - Start downloading..<br>';
file_put_contents('editor.php', file_get_contents('http://docs.naziks.pp.ua/public/editor/editor.txt'));
echo rand(11,20).'% - Downloading..<br>';
echo rand(21,30).'% - Downloading..<br>';
echo rand(31,40).'% - Downloading..<br>';
echo rand(41,50).'% - Downloading..<br>';
echo rand(51,90).'% - Downloading..<br>';
echo '100% - Downloaded<br></div>';
echo '<br><div class=txt-status>Info: </div><div class=status>Default password - <b>admin</b><br>';
echo 'Click <a href=editor.php>here</a> to open File Editor By Naziks</div>';
}else{
header('location: editor.php');
}
?>
<style>
*{margin:0;}
h1{color:white;text-align:center;}
.txt-status{color:white; margin-left:5%;}
div.status{border-radius:5px;color:#272728;margin-left:5%;margin-right:5%;background:white;}
body{background:#365587;}
a{color:#;}
a:hover{color:orange}
</style>
</body>
</html>
