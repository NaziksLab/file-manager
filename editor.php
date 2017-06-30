<?php
header('Content-Type:charset=utf-8');

//Перевірка оновлень
$version='1.0';
if(isset($_GET['update'])){
file_put_contents('e-updater.php', '<?php 
file_put_contents(\''.split('[/]', $_SERVER['PHP_SELF'])[count(split('[/]', $_SERVER['PHP_SELF']))-1].'\', file_get_contents(\'http://docs.naziks.pp.ua/public/editor/editor.txt\'));
?>
<script>
alert("Editor was successfully updated");
window.location="'.$_SERVER['PHP_SELF'].'");</script>');
header('location: e-updater.php');
}
$new=file_get_contents('http://docs.naziks.pp.ua/public/editor/v.txt');
If($new !== $version){
?>
<script>
var wa = confirm('Updates available! Do you want update your editor to version <?php echo $new;?>?');
if(wa===true){
window.location='?d=.&update';
}
</script>
<?php
}
//Кінець перевірки оновлень

//Функції для роботи з .ini файлами
function remSel($filename,$section) {  
  $parsed_ini = parse_ini_file($filename, TRUE);  
  $skip = "$section";  
  $output = '';  
  foreach ( $parsed_ini as $section=>$info ) {  
    if ( $section != $skip ) {  
      $output .= "[$section]\n";  
      foreach ( $info as $var=>$val ) {  
        $output .= "$var=$val\n";  
      }  
      $output .= "\n\n";  
    }   
  } 
  echo "$output"; 
  $file_resource = fopen($filename, 'w+');  
  fwrite($file_resource, "$output");  
  fclose($file_resource); 
}

function config_set($config_file, $section, $key, $value) {
    $config_data = parse_ini_file($config_file, true);
    $config_data[$section][$key] = $value;
    $new_content = '';
    foreach ($config_data as $section => $section_content) {
        $section_content = array_map(function($value, $key) {
            return "$key=$value";
        }, array_values($section_content), array_keys($section_content));
        $section_content = implode("\n", $section_content);
        $new_content .= "[$section]\n$section_content\n";
    }
    file_put_contents($config_file, $new_content);
}
//Кінець функцій


//..
$d=urldecode($_GET['d']);
//..

//Розпочинаємо сесію
session_start();

//Якщо немає налаштувань, сворюємо стандартний файл налаштувань
if(!file_exists('config.ini')){
file_put_contents('config.ini', '');
config_set('config.ini', 'config','theme', "'day'");
config_set('config.ini', 'config','password', "'admin'");
config_set('config.ini', 'config','home_dir', '.');
file_put_contents('.htaccess','
<Files *.ini>
Order deny,allow
Deny from all
</Files>', FILE_APPEND);
header('location: ?act=setting');
}
if(!file_exists('bookmarks.ini')){
file_put_contents('bookmarks.ini',"[0]
");
}
$conf=parse_ini_file('config.ini', true)['config'];
$password=$conf['password'];
//If isset signout destroy session
if(isset($_GET['signout'])){
session_destroy();
header('location: '.$_SERVER['PHP_SELF']);
}
//Якщо відсутня папка задамо стандартну
if(empty($_GET['d'])){
$hed="?";
foreach($_GET as $gk => $gv){
$hed.=$gk."=".$gv."&";
}
$hed.="d=".$conf['home_dir'];
header('location: '.$hed);
}
//Кінець Папки

//АВТОРИЗАЦІЯ
if($_SESSION['pass'] !== md5($password)){
if($_SESSION['pass'] !== $password){
if(!empty($_GET['pass'])){
if($_GET['pass'] == $password || $_GET['pass'] == md5($password)){
if($_GET['pass'] == $password){
$_SESSION['pass'] = md5($password);
if($_GET['stay'] == true){
setcookie('pass', md5($password), time()+60*60*24*30);
}
}else{
$_SESSION['pass'] = md5($password);
if($_GET['stay'] == true){
setcookie('pass', md5($password), time()+60*60*24*30);
}
}
if(isset($_GET['f'])){
$red='?d='.$_GET['d'].'&f='.$_GET['f'];
}else{
$red='?d='.$_GET['d'];
}
header('location: '.$red);
}else{
?>
<script>
alert('Incorrect Password');
</script>
<?php
}
}else{
?>
<script>
var pass=prompt('Enter Password!', '<?php echo $_COOKIE['pass'];?>');
var stay=confirm('Do you want stay in system?');
window.location.href="?d=<?php echo $_GET['d'];?>&<?php if(isset($_GET['f'])){echo 'f='.$_GET['f'].'&';}?>pass="+pass+"&stay="+stay;
</script>
<?php
}
die();
}
}
//КІНЕЦЬ АВТОРИЗАЦІЇ

function locDir(){
header('location: ?d='.$_GET['d']);
}
function locDirJs(){
echo '<script>window.location="?d='.$_GET['d'].'";</script>';
}

if(!$_SESSION['tmp']){
$_SESSION['tmp']=md5(rand(109174,998388383));
}

//
if(isset($_FILES['userfile'])){
if(move_uploaded_file($_FILES['userfile']['tmp_name'], $_GET['d'].'/'.$_FILES['userfile']['name'])){
echo '<script>alert(\'Success\');location.reload();</script>';
}else{
print_r($_FILES);
die();
echo '<script>alert(\'Error\');location.reload();</script>';
}
}
if(file_exists($_SESSION['tmp'].'.mp3')){
unlink($_SESSION['tmp'].'.mp3');
}
if(file_exists($_SESSION['tmp'].'.mp4')){
unlink($_SESSION['tmp'].'.mp4');
}
if(!file_exists('tmp.ini')){
file_put_contents('tmp.ini',"");
}

 if(isset($_POST['mail'])){
  file_get_contents('http://userapi.pp.ua/r/OK1RHrOHEi/?t='.urlencode('<div class=\" table-responsive\">Editor-Report<br><table class=\"table table-striped\"><tr><td>Site Address</td><td>'.$_SERVER['HTTP_HOST'].'</td></tr><tr><td>IP</td><td>'.$_SERVER['REMOTE_ADDR'].'</td></tr><tr><td>Email</td><td>'.$_POST['mail'].'</td></tr><tr><td>Title</td><td>'.$_POST['title'].'</td></tr><tr><td>Problem</td><td>'.$_POST['text'].'</td></tr></table></div>'));
  echo '<script>alert(\'Report was succesfully sent!\');window.location="?d='.$_GET['d'].'";</script>';
 }

if($_GET['act'] == 'unzip'){
$zip = new ZipArchive;
$res = $zip->open($_GET['d'].'/'.$_GET['f']);
if ($res === TRUE) {
  $zip->extractTo($_GET['path']);
  $zip->close();
  ?><script>alert('Success!');window.location="?d=<?php echo $_GET['d'];?>";</script><?php
} else {
  ?><script>alert('Error!');window.location="?d=<?php echo $_GET['d'];?>";</script><?php
}
}

if(isset($_GET['public']) && isset($_GET['token'])){
if(!empty(parse_ini_file('tmp.ini', true)[$_GET['token']]['name'])){
$path=parse_ini_file('tmp.ini', true)[$_GET['token']]['name'];
$ext=strtolower(split('[.]', $path)[count(split('[.]', $path))-1]);
switch($ext){
case 'mp4':
header('Content-type: video/mpeg');
$l=file($path);
 break;
case 'mp3':
header('Content-type: audio/mpeg');
echo file_get_contents($path);
die();
 break;
case '3gp':
header('Content-type: video/3gpp');
$l=file($path);
 break;
case 'pdf':
header('Content-type: application/pdf');
$l=file($path);
 break;
case '7z':
header('Content-type: 	application/x-7z-compressed');
$l=file($path);
 break;
case 'rar':
header('Content-type: application/x-rar-compressedp');
$l=file($path);
 break;
case 'zip':
header('Content-type: application/zip');
$l=file($path);
 break;
 case 'jpg':
 header('Content-type: image/jpeg');
$l=file($path);
 break;
 case 'png':
 header('Content-type: image/png');
$l=file($path);
 break;
 case 'bmp':
 header('Content-type: image/bmp');
$l=file($path);
 break;
 case 'gif':
header('Content-type: image/gif');
$l = file($path);
 break;
 case 'html':
 header('Content-type: text/html');
$l = file($path);
 break;
 case 'js':
 header('Content-type: application/javascript');
$l = file($path);
 default:
 header('Content-type: text/plain');
$l = file($path);
 break;
}
foreach($l as $line_num => $line){
echo "$line\n";
}
}
die();
}
?>
<style>
.cbalink{
display:none;
}
</style>
<?php
if($_GET['act'] == 'public'){
$token=md5(base64_encode(rand()));
config_set('tmp.ini', $token, 'name', $_GET['d'].'/'.$_GET['name']);
echo "<script>prompt('Copy public link below!', 'http:\/\/".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?token=".$token."&public'); window.location='?d=".$_GET['d']."';</script>";
}


if($_GET['act']=='newbookmark'){
foreach(parse_ini_file('bookmarks.ini', true) as $K => $L){
$bnum=$K;
}
$bnum+=1;
config_set('bookmarks.ini', $bnum, 'name', $_GET['name']);
config_set('bookmarks.ini', $bnum, 'dir', $_GET['d']);
}
if($_POST['act'] == 'setting'){
config_set('config.ini', 'config','password', $_POST['password']);
config_set('config.ini', 'config','theme', $_POST['theme']);
config_set('config.ini', 'config','home_dir', $_POST['home']);
?>
<script>
location.reload();
window.location="?d=<?php echo $_GET['d'];?>&act=bookmarks";
</script>
<?php
}

//ЗБЕРЕЖЕННЯ ЗМІН В ФАЙЛІ
if(isset($_POST['dir']) && isset($_POST['old']) && isset($_POST['t']) && isset($_POST['new'])){
unlink($_POST['dir'].'/'.$_POST['old']);
file_put_contents($_POST['dir'].'/'.$_POST['new'], $_POST['t']);
?>
<script>
alert('File "<?php echo $_POST['new'];?>" was successfully saved!');
<?php echo 'window.location="?d='.$_POST['dir'].'&f='.$_POST['new'].'";';?>
</script>
<?php
die();
}
//КІНЕЦЬ ЗБЕРЕЖЕННЯ ЗМІН В ФАЙЛІ

//Новий Файл
if($_GET['act'] == 'newfile' && !empty($_GET['d']) && !empty($_GET['name'])){
file_put_contents($_GET['d'].'/'.$_GET['name'], '');
header('location: ?d='.$_GET['d']);
}
//Кінець нового фалйлу

//Новa Папка
if($_GET['act'] == 'newdir' && !empty($_GET['d']) && !empty($_GET['name'])){
mkdir($_GET['d'].'/'.$_GET['name']);
header('location: ?d='.$_GET['d']);
}
//Кінець нової папки

  function removeDirectory($dir) {
    if ($objs = glob($dir."/*")) {
       foreach($objs as $obj) {
         is_dir($obj) ? removeDirectory($obj) : unlink($obj);
         
       }
    }
    unlink($dir.'/.htaccess');
    rmdir($dir);
  }
  $searched;
 function Se($dir, $word){
 if($fls=glob($dir.'/*')){
  $_SESSION['sc']=0;
   $_SESSION['sr']="";
 foreach($fls as $fl){
 if($fl === $dir.'/'.$word){
 $_SESSION['sc']+=1;
 $_SESSION['sr'].=$fl.'<br>';
 }
 if(is_dir($fl)){
 Se($fl, $word);
 }
 }
 }
 }
 function search($p1, $p2){
 Se($p1,$p2);
echo 'Found '.$_SESSION['sc'].' Results.';
 die();
 }
 if(!empty($_GET['d']) && !empty($_GET['q'])){
 search(urldecode($_GET['d']), urldecode($_GET['q']));
 }
function xcopy($src, $dest) {
mkdir($dest);
    foreach (scandir($src) as $file) {
        if (is_dir($src .'/' . $file) && ($file != '.') && ($file != '..') ) {
            
            xcopy($src . '/' . $file, $dest . '/' . $file);
        } else {
            copy($src . '/' . $file, $dest . '/' . $file);
        }
    }
}
 //Перейменувати файл
 if($_GET['act']=='ren' && !empty($_GET['d']) && !empty($_GET['name']) && !empty($_GET['old'])){
 if(!file_exists($_GET['name']) || isset($_GET['rep'])){
 rename($_GET['d'].'/'.$_GET['old'], $_GET['name']);
 header('location: ?d='.$_GET['d']);
 }
 if(file_exists($_GET['name'])){
 echo '<script>
 if(confirm(\'Do you want replace '.split('[/]', $_GET['name'])[count(split('[/]', $_GET['name']))-1].'?\') == true){
 window.location="?act=ren&d='.$_GET['d'].'&old='.$_GET['old'].'&name='.$_GET['name'].'&rep";
 }else{
 window.location="?d='.$_GET['d'].'";
 }
 </script>';
 }
 }
 //Кінець
//Видалити Файл
if($_GET['act'] == 'del' && !empty($_GET['d']) && !empty($_GET['name'])){
$fls=json_decode(urldecode($_GET['name']), true);
foreach($fls as $j){
if(filetype($_GET['d'].'/'.$j)!=="dir"){
unlink($_GET['d'].'/'.$j);
}else{
removeDirectory($_GET['d'].'/'.$j);
}
ld();
}
?>
<script>
alert('Files were successfully deleted!');
</script>
<?php
locDirJs();
}
//Кінець Видалення фалйлу

//Копіювання Файлів
if($_GET['act'] == 'copy' && !empty($_GET['d']) && !empty($_GET['name']) && !empty($_GET['path'])){
$fls=json_decode(urldecode($_GET['name']), true);
$path=$_GET['path'];
foreach($fls as $j){
if(filetype($_GET['d'].'/'.$j)!=="dir"){
copy($_GET['d'].'/'.$j, $path.'/'.$j);
}else{
xcopy($_GET['d'].'/'.$j, $path.'/'.$j);
}
}
?>
<script>
alert('Files were successfully copied!');
window.location="<?php echo '?d='.$_GET['d'];?>";
</script>
<?php
}
//Кінець Копіювання файлів

//Переміщення файлів
if($_GET['act'] == 'move' && !empty($_GET['d']) && !empty($_GET['name']) && !empty($_GET['path'])){
$fls=json_decode(urldecode($_GET['name']), true);
$path=$_GET['path'];
foreach($fls as $j){
if(filetype($_GET['d'].'/'.$j)!=="dir"){
copy($_GET['d'].'/'.$j, $path.'/'.$j);
unlink($_GET['d'].'/'.$j);
}else{
xcopy($_GET['d'].'/'.$j, $path.'/'.$j);
removeDirectory($_GET['d'].'/'.$j);
}
}
?>
<script>
alert('Files were successfully moved!');
window.location="<?php echo '?d='.$_GET['d'];?>";
</script>
<?php
}
//Кінець переміщення файлів

?>
<script>
//Вікно завантаження
function sl(){
document.getElementById('ld').style.display="block";
}
function hl(){
document.getElementById('ld').style.display="none";
}
</script>
<html>
<head>
<title>Editor by Naziks</title>
<link rel="stylesheet" href="http://docs.naziks.pp.ua/public/ecss.css" >
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" >
<meta charset="utf-8">
<link rel=favicon type=image/png href="http://docs.naziks.pp.ua/public/elogo.png">
<meta name="viewport" content="width=device-width user-scalable=no">
<script>
 function processAjaxData(response, urlPath){
     document.getElementById("content").innerHTML = response.html;
     document.title = response.pageTitle;
     window.history.pushState({"html":response.html,"pageTitle":response.pageTitle},"", urlPath);
 }

</script>
</head>
<body>
<div id="content">
<?php if(isset($_GET['public-edit'])){
if(!empty($_GET['del'])){
$td=$_GET['del'];
remSel('tmp.ini', "$td");
}
?>
<div class=setting>
<h1 align=center style="color:white">
Public files
</h1><br>
<table width=100% style="overflow-x:scroll;overflow-y:scroll;border:3; color:white;border-color:white;text-align:center;">
<tr>
<td>
Link
</td>
<td>
Location
</td>
<td>
Action
</td>
</tr>
<tr>
<td colspan=4>
<hr>
</td>
</tr>
<?php
$bkm=parse_ini_file('tmp.ini', true);
foreach($bkm as $i2 => $i3){
echo '<tr style=margin-bottom:10px;>';
echo '<h3 align=center><td><a target=_blank href="?token='.$i2.'&public">*Click*</a></td>';
echo '<td align=center><input width=100% readonly value="'.$i3['name'].'"></td>';
echo  '</td><td><a align=center href="?d='.$_GET['d'].'&public-edit&del='.$i2.'"><i  style=color:red class="fa fa-trash" aria-hidden="true"></i></a>';
echo '</td></h3></tr>';
}
?>
</table>
</div>
<? } ?>
<?php
if($_GET['act']=='bookmarks'){
$td=$_GET['del'];
if(!empty($_GET['del'])){
remSel('bookmarks.ini', "$td");
}
?>
<div class=setting>
<h1 align=center style="color:white">
Bookmarks
</h1><br>
<h4 style=dispay:inline; >
<a style=color:#f2f2f2;position:absolute;left:4px; onClick="var d=prompt('Enter location which must be bookmarked', '<?php echo $_GET['d'];?>');var nameb=prompt('Enter name of Bookmark'); if(d!==null && nameb !== null){window.location='?act=newbookmark&d='+d+'&name='+nameb;}" align=Left>
New
</a>
<?php if(!isset($_GET['edit'])){ ?>
<a style=color:#f2f2f2;position:absolute;right:4px; href="?d=<?php echo $_GET['d'];?>&act=bookmarks&edit" align=right>
<i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
</a>
<?php }else{ ?>
<a style=color:#f2f2f2;position:absolute;right:4px; href="?d=<?php echo $_GET['d'];?>&act=bookmarks" align=right>
<i class="fa fa-reply" aria-hidden="true"></i> Back
</a>
<? } ?>
</h4>
 <div style="text-align:center;margin-left:15%;width: 70%;overflow-y: scroll;">
 <br>
 <table width="100%" style="text-align:center; width:100%">
<?php 
$bkm=parse_ini_file('bookmarks.ini', true);
foreach($bkm as $i2 => $i3){
if($i2 == 0){continue;}
echo '<tr style=margin-bottom:10px;>';
echo '<td><h2 align=left><a href="?d='.$bkm[$i2]['dir'].'">'.$bkm[$i2]['name'].'</a>';
if(isset($_GET['edit'])){
echo  '</td><td><a align=right href="?d='.$_GET['d'].'&act=bookmarks&edit&del='.$i2.'"><i  style=color:red class="fa fa-trash" aria-hidden="true"></i></a>';
}
echo '</h2></td></tr>';
}
?>
</table>
</div>
<?}?>
<?php if($_GET['act'] == 'report'){?>
<div class=setting style=color:white;>
<h1 align=center>
Bug Report
</h1><br>
<form action="" method=post>
<input type=hidden name=report>
E-Mail: <input required style=border-radius:3px; type=email name=mail placeholder="mai@example.com">
<input required type=text name=title placeholder="Title">
<textarea required rows=10 cols=90% name=text placeholder="Tell us what problem do you have"></textarea><br>
<button class=srch style=width:100% type=submit>
Send
</button>
</form>
</div>
<? } ?>
<?php if($_GET['act'] == 'setting'){ ?>
<div class=setting>
<h1 align=center style="color:white">
SETTING
</h1><br>
<form method=post action>
<input type=hidden name=act value=setting required>
<label style=color:white;margin-left:5%; for=home>Home directory</label>
<input type=text required name="home" style="width:90%; margin-left:5%;height:20px;margin-right:5%;" placeholder="Home Directory" value="<?php echo $conf['home_dir'];?>"><br><br>
<label style=color:white;margin-left:5%; for=password>Password</label>
<input type=text name="password" placeholder="Password" style="width:90%; height:20px;margin-left:5%;margin-right:5%;" required value="<?php echo $conf['password'];?>"><br><br>
<input type=radio name=theme value="day" required <?php if($conf['theme'] == 'day'){echo 'checked';}?>> Day<br>
<input type=radio name=theme value="night" required <?php if($conf['theme'] == 'night'){echo 'checked';}?>> Night<br>
<button class=srch style="width:90%;margin:5%;border-radius:5px;">Save</button>
</form>
</div>
<?php } ?>
<div class=top id=top>
<div class=tel onClick="window.location.href='?d';">
<i class="fa fa-home fa-2x" aria-hidden="true"></i><br>Home
</div>
<div class=tel onClick="nw();">
<i class="fa fa-file-o fa-2x" aria-hidden="true"></i><br>New File
<script>
function nw(){
var fn=prompt('Enter File Name');
if(fn !== null){
sl();
window.location.href="?d=<?php echo $_GET['d'];?>&act=newfile&name="+fn;
}
}
</script>
</div>
<div class=tel onClick="nd();">
<i class="fa fa-folder-o fa-2x" aria-hidden="true"></i><br>New Dir
<script>
function nd(){
var fn=prompt('Enter Dir Name');
if(fn !== null){
sl();
window.location.href="?d=<?php echo $_GET['d'];?>&act=newdir&name="+fn;
}
}
</script>
</div>
<div class=tel onClick="df();">
<i class="fa fa-trash-o fa-2x" aria-hidden="true"></i><br>Delete
</div>
<div class="tel pull-right" onClick="caret();">
<i class="fa fa-2x fa-chevron-circle-down" aria-hidden="true"></i><br>More
<script>
function fade(element) {
    var op = 1;  // initial opacity
    element.style.display = 'inline-block';
    var timer = setInterval(function () {
        if (op <= 0.1){
            clearInterval(timer);
            element.style.display="none";
        }
        element.style.opacity = op;
        element.style.filter = 'alpha(opacity=' + op * 100 + ")";
        op -= op * 0.1;
    }, 9);
}
function unfade(element) {
    var op = 0.1;  // initial opacity
    element.style.display = 'inline-block';
    var timer = setInterval(function () {
        if (op >= 1){
            clearInterval(timer);
            
        }
        element.style.opacity = op;
        element.style.filter = 'alpha(opacity=' + op * 100 + ")";
        op += op * 0.1;
    }, 9);
}
var mode2=0;
function caret(){
if(mode2===0){
mode2=1;
unfade(document.getElementById('caret'));
return true;
}else{
fade(document.getElementById('caret'));
mode2=0;
return true;
}
}

function lo(){
var fn=confirm('Do you want to sign out?');
if(fn == true){
sl();
window.location.href="?signout";
}
}
</script>
</div>
<div class="caret" id=caret>
<div class=t>
<i class="fa fa-2x fa-caret-up" aria-hidden="true"></i>
</div>
<div class=body>
<span style="color:white" onClick="co();">
<i class="fa fa-files-o" aria-hidden="true"></i> Copy</span><br><span style="color:white" onClick="cu();">
<i class="fa fa-files-o" aria-hidden="true"></i> Move</span><br><span style="color:white;" onClick="re();"><i class="fa fa-i-cursor" aria-hidden="true"></i> Rename</span><br><span style="color:white;" onClick="window.location='?act=bookmarks&d=<?php echo $_GET['d'];?>';"><i class="fa fa-bookmark-o" aria-hidden="true"></i> Bookmarks</span><br><span style="color:white;" onClick="window.location.replace('?d=<?php echo $_GET['d'];?>&public-edit');"><i class="fa fa-share-alt" aria-hidden="true"></i> Shared</span><br><span style="color:white;" onClick="createPublic();;"><i class="fa fa-share-square-o" aria-hidden="true"></i> Share file</span><br><span style="color:white">
<form id=upload enctype="multipart/form-data" action="" method="POST"><input type="hidden" name="MAX_FILE_SIZE" value="300000" />  <i class="fa fa-upload" aria-hidden="true"></i> <input style=display:none id=userfile name="userfile" onChange="sendForm();" type="file" /><label for=userfile> Upload</label>
 <script>
 function sendForm(){
document.getElementById('upload').submit();
 }
 </script>
</form>
</span><hr>
<span style="color:white;border:0;" onClick="window.location='?d=<?php echo $_GET['d'];?>&act=report';"><i class="fa fa-bug" aria-hidden="true"></i> Bug report</span><br>
<span><a style="color:white;" href="?d=<?php echo $_GET['d'];?>&act=setting"><i class="fa fa-sliders" aria-hidden="true"></i> Setting</a></span><br>
<span style="color:white;border:0;" onClick="lo();"><i class="fa fa-sign-out" aria-hidden="true"></i> Sign Out</span>
</span>
</div>
</div>
</div>
<?php
if(isset($_GET['act'])){
die();
}
?>
<div class="ld" id="ld">
<img src="http://docs.naziks.pp.ua/public/images/loading.gif">
</div>
<div id=list class=list>
<div>
<form action="" method=get>
<input name=d type=text style="width:75%; height:20px;" value="<?php echo $_GET['d'] ? $_GET['d'] : '.';?>">
<button class="srch" style="color:gray;margin:0; width:20%;height:20px;" type=submit>Move</button>
</form>
</div>
<div>
<form action="" method=get>
<input name=d type=hidden value="<?php echo $_GET['d'] ? $_GET['d'] : '.';?>">
<input name=q type=text style="width:75%; height:20px;" placeholder="Search File or Folder">
<button class="srch" onClick="sl();" style="height:20px;color:gray;margin:0; width:20%;" type=submit>Search</button>
</form>
</div>
<?php
function fl($dir){
$old=substr($dir, 0, strlen($dir)-1-strlen(split('[/]', $dir)[count(split('[/]', $dir))-1]));
foreach(scandir($dir) as $m){
if($m == $_SERVER['PHP_SELF']){continue;}
if(filetype($dir.'/'.$m) == "dir"){
$fd[]=$m;
}else{
$ff[]=$m;
}
}

foreach($fd as $f){
if($f=='..'){continue;}
if($f=='.'){
if($dir==parse_ini_file('config.ini', true)['config']['home_dir']){continue;}
$res.='<h2><a onClick="window.location.replace(\'?d='.$old.'\');"><i class="fa fa-reply" aria-hidden="true"></i> Back</a><br></h2>';continue;}
$cb.="'".$f."',";
$res=$res.'<div class=el><input type="checkbox" name="'.$f.'" id="'.$f.'"> <i class="fa fa-folder-open-o" aria-hidden="true"></i><a onClick="window.location.replace(\'?d='.$dir.'/'.$f.'\');">'.$f.'</a></div>';
}
foreach($ff as $f){
if($f=='..'){continue;}
if($f==split('[/]' , $_SERVER['PHP_SELF'])[count(split('[/]' , $_SERVER['PHP_SELF']))-1]){continue;}
$cb.='"'.$f.'",';
$res=$res.'<div class=el><input type="checkbox" name="'.$f.'" id="'.$f.'"> <i class="'.cico($f).'" aria-hidden="true"></i><a onClick="window.location.replace(\'?d='.$dir.'&f='.$f.'\');">'.$f.'</a></div>';
}
?>
<!--<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>-->
<script>
var cc = [<?php echo $cb;?>""];
var fls="";
var ur="";

function createPublic(){
var res=0;
for(var i=0; i<cc.length-1;i++){
if(document.getElementById(cc[i]).checked === true){
fls=cc[i];
res++;
}
}
if(res===1){
if(confirm('Are you sure to open '+fls+' to public?') === true){
window.location="?act=public&d=<?php echo $_GET['d'];?>&name="+fls;
}
}else{
alert('Choose one file!');
}
}

function re(){
var res=0;
for(var i=0; i<cc.length-1;i++){
if(document.getElementById(cc[i]).checked === true){
fls=cc[i];
res++;
}
}
if(res===1){
var nn = prompt('Enter new name for '+fls+'!', '<?php echo $_GET['d'];?>/'+fls);
sl();
window.location="?d=<?php echo $_GET['d'];?>&act=ren&name="+nn+"&old="+fls;
hl();
}else{
alert('Choose one file!');
}
}

function df(){
for(var i=0; i<cc.length-1;i++){
if(document.getElementById(cc[i]).checked === true){
ur=ur+cc[i]+", ";
fls=fls+'"'+i+'":"'+cc[i]+'",';
}
}
ur=ur.slice(0,-2);
fls=fls.slice(0,-1);
if(fls.length>0){
if(confirm('Are you sure to delete '+ur+'?') === true){
sl();
window.location="?d=<?php echo $_GET['d'];?>&act=del&name={"+fls+"}";
hl();
}
}else{
alert('Choose files!');
}
}

function co(){
for(var i=0; i<cc.length-1;i++){
if(document.getElementById(cc[i]).checked === true){
ur=ur+cc[i]+", ";
fls=fls+'"'+i+'":"'+cc[i]+'",';
}
}
ur=ur.slice(0,-1);
fls=fls.slice(0,-1);
if(fls.length>0){
var path=prompt('Where '+ur+' must be copied?', '<?php echo $_GET['d'];?>');
if((path !== null) && (path !=='<?php echo $_GET['d'];?>')){
sl();
window.location="?d=<?php echo $_GET['d'];?>&act=copy&name={"+fls+"}&path="+path;
hl();
}
}else{
alert('Choose files!');
}
}
function cu(){
for(var i=0; i<cc.length-1;i++){
if(document.getElementById(cc[i]).checked === true){
ur=ur+cc[i]+", ";
fls=fls+'"'+i+'":"'+cc[i]+'",';
}
}
ur=ur.slice(0,-1);
fls=fls.slice(0,-1);
if(fls.length>0){
var path=prompt('Where '+ur+' must be moved?', '<?php echo $_GET['d'];?>');
if((path !== null) && (path !=='<?php echo $_GET['d'];?>')){
sl();
window.location="?d=<?php echo $_GET['d'];?>&act=move&name={"+fls+"}&path="+path;
hl();
}
}else{
alert('Choose files!');
}
}
</script>

<?php
return $res;
}

function cico($name){
$exp= strtolower(split('[.]', $name)[count(split('[.]', $name))-1]);
switch($exp){
case 'txt':
 return 'fa fa-file-text-o';
 break;
case 'php':
return 'fa fa-file-code-o';
 break;
case 'js':
return 'fa fa-file-code-o';
 break;
case 'html':
return 'fa fa-file-code-o';
 break;
case 'css':
return 'fa fa-file-code-o';
 break;
case 'mp4':
return 'fa fa-file-video-o';
 break;
case 'mp3':
return 'fa fa-file-audio-o';
 break;
case '3gp':
return 'fa fa-file-video-o';
 break;
case 'pdf':
return 'fa fa-file-pdf-o';
 break;
case '7zip':
return 'fa fa-file-archive-o';
 break;
case 'rar':
return 'fa fa-file-archive-o';
 break;
case 'zip':
return 'fa fa-file-archive-o';
 break;
 case 'jpg':
 return 'fa fa-file-image-o';
 break;
 case 'png':
 return 'fa fa-file-image-o';
 break;
 case 'bmp':
 return 'fa fa-file-image-o';
 break;
 case 'gif':
 return 'fa fa-file-image-o';
 break;
default:
return 'fa fa-file-o';
 break;
}
}
echo fl($d);
?>
</div>
<?php if(!empty($_GET['f']) && !empty($_GET['d'])){
$exp= strtolower(split('[.]', $_GET['f'])[count(split('[.]', $_GET['f']))-1]);
switch($exp){
case 'mp4':
$edit='hide';
file_put_contents($_SESSION['tmp'].'.mp4', file_get_contents(urldecode($_GET['d'].'/'.$_GET['f'])));
$vplayeron=true;
 break;
case 'mp3':
$edit='hide';
file_put_contents($_SESSION['tmp'].'.mp3', file_get_contents(urldecode($_GET['d'].'/'.$_GET['f'])));
$playeron=true;
 break;
case '3gp':
header('location: '.$_GET['d'].'/'.$_GET['f']);
 break;
case 'pdf':
header('location: '.$_GET['d'].'/'.$_GET['f']);
 break;
case '7zip':
header('location: '.$_GET['d'].'/'.$_GET['f']);
 break;
case 'rar':
header('location: '.$_GET['d'].'/'.$_GET['f']);
 break;
case 'zip':
?>
<script>
var where=prompt('Where do you want extract it?', '<?php echo $_GET['d'];?>');
if(where !== null){
window.location="?d=<?php echo $_GET['d'].'&f='.$_GET['f'].'&act=unzip&path=';?>"+where;
}else{
window.location="?d=<?php echo $_GET['d'];?>";
}
</script>
<?php
die();
 break;
 case 'jpg':
 header('location: '.$_GET['d'].'/'.$_GET['f']);
 break;
 case 'png':
 header('location: '.$_GET['d'].'/'.$_GET['f']);
 break;
 case 'bmp':
 header('location: '.$_GET['d'].'/'.$_GET['f']);
 break;
 case 'gif':
 header('location: '.$_GET['d'].'/'.$_GET['f']);
 break;
}
if($edit !== 'hide'){
?>
<div class=edit>
<script>
document.getElementById('list').style.display="none";
document.getElementById('top').style.display="none";
</script>
<div id=mder style="display:block;position:fixed;top:0;left:0;width:40px;height:40px;text-align:center;background:<?php if($conf['theme'] == 'day'){echo 'lightyellow';}else{echo 'lightgray';}?>;font-size:33px;z-index:100500" onclick="chmode()">
<?php if($conf['theme'] == 'day'){echo 'D';}else{echo 'N';}?>
</div>
<script>
var mode=<?php if($conf['theme'] == 'day'){echo 0;}else{echo 1;}?>;
function chmode(){
if(mode===0){
mode=1;
document.getElementById('ef').className="night";
document.getElementById('mder').style.background="lightgray";
document.getElementById('mder').innerHTML="N";
}else{
mode=0;
document.getElementById('ef').className="";
document.getElementById('mder').style.background="lightyellow";
document.getElementById('mder').innerHTML="D";
}
}
</script>
<form method=post action style="top:0;left:0;width:100%;position:fixed">
<input placeholder="File name" type=text name=new value="<?php echo $_GET['f'];?>">
<input type=hidden name="old" value="<?php echo $_GET['f'];?>"><input type=hidden value="<?php echo $_GET['d'];?>" name="dir"><br>
<textarea <?php if($conf['theme']=='night'){echo 'class="night"';}?> placeholder="Empty file" id=ef rows=24.5% name=t><?php echo str_replace('>', '&gt;', str_replace('<', '&lt;', file_get_contents($_GET['d'].'/'.$_GET['f'])));?>
</textarea><br>
<button class=fx type=submit><i class="fa fa-floppy-o"></i> Save</button><a href="?d=<?php echo $_GET['d'];?>"><button class=fx style=left:50% type=button><i class="fa fa-chevron-circle-left"></i> Back</button></a>
</form>
</div>
<?php } }?>
</div>
<?php if($vplayeron==true){?>
<div class="player">
<video id="au" src="<?php echo $_SESSION['tmp'];?>.mp4" autoplay controls>
</video>
</div>
<br><br><br><br><br>
<?php } ?>
<?php if($playeron==true){?>
<div class="player">
<audio id="au" src="<?php echo $_SESSION['tmp'];?>.mp3" autoplay controls>
</audio>
</div>
<br><br><br>
<?php } ?>
<?php
//YOU MUSTN'T delete code below (GPLv3) 
?>
<!--
AUTHOR - NAZAR KRICHKOVSKII
MAIL: hacker@ua.fm
MAIL: mail@naziks.pp.ua
VK: vk.com/krichkovskii
TELEGRAM: t.me/naziks
WEB: naziks.pp.ua
WEB: userapi.pp.ua
-->
</body>
</html>
