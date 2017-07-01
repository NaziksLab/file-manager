<?php
class FTP_MOD{
  protected $session;
  public $host;
  private $login;
  private $password;
  
  //CONNECT & LOGIN 
  function connect($host, $login, $pass, $port=21){
    
    $session=ftp_connect($host, $port);
    if(!$session){
      return '{"error":"Cannot connect to '.$host.':'.$post.'l"}';
    }
    $login=ftp_login($session, $login, $password);
    if(!$login){
      return '{"error":"Incorrect login or/and password"}';
    }
      return '{"error":0, "session":"'.$session.'"}';
  }
  
  //LIST OF FILES
  function list($dir, $session){
    if($session){
      return ftp_nlist($session, $dir);
    }
  }
  
  //CHANGE MOD OF FILE
  function cm($session, $file, $prem){
    ftp_chmod($sesssion, 	
  }
  //GET FILE
  function get($session, $file, $binary=true){
    if($binary==true){
    return ftp_nb_get($session, $file, FTP_BINARY);
    }else{
      return ftp_nb_get($session, $file, FTP_ASCII);
    }
  }
  
  //CHANGE DIRECTORY
  function cd($session, $dir){
    return ftp_chdir($session, $dir);
  }
  
  //PUT FILE
  function put($session, $file, $local_file, $binary=true){
    if($binary==true){
      return ftp_nb_put($session, $file, $local_file, FTP_BINARY);
    }else{
      return ftp_nb_put($session, $file, $local_file, FTP_ASCII);
    }
  }
  
  //RAW LIST
  function rawlist($session, $dir){
    return ftp_rawlist($session, $dir);
  }
  
  //CLOSE CONNECT
  function close($session){
    return ftp_close($session);
  }
}
?>