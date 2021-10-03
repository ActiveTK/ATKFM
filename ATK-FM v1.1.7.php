<?php // been updated automatically on 2021/10/03 - Oct (Sun) 12:03:30(UNIXTIME:1633230210) from https://www.activetk.cf/ATK-FM/update/go/1.1.7.php
  /////////////////////////////////////////////////
  define('fmversion', 'v1.1.7');
  define('lastupdateinfo', '2021/10/03 - Oct (Sun) 12:03:30');
  define('publishupdateinfo', '2021/10/03 - Oct (Sun) 12:01:13');
  define('copyright',
    "Copyright &copy; 2021 ActiveTK. All rights reserved.\n".
    "Released under the <a href='https://license.activetk.cf/?project=atk-fm'>ActiveTK Projects license</a>"
  );
  /////////////////////////////////////////////////
  /*!
   * ATK-FM v1.1.7 | PHP/HTML/JS/CSS
   * Copyright 2021 ActiveTK. All rights reserved.
   * Released under the ActiveTK Projects license
   * https://license.activetk.cf/?project=atk-fm
  */
  /////////////////////////////////////////////////
  $phpstarttime = microtime(true);
  ini_set("display_startup_errors", 0);
  error_reporting(E_ALL);
  ini_set('session.gc_divisor', 1);
  ini_set('session.gc_maxlifetime', 7776000);
  session_start();
  $s = mb_strstr($_SERVER["REQUEST_URI"], '?', true);
  if ($s == "") $s = $_SERVER["REQUEST_URI"];
  $meurl = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ) ? "https://" : "http://").$_SERVER['HTTP_HOST'].$s;
  @MakeLog();
  function remove_directory($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
      if (is_dir("$dir/$file"))
        remove_directory("$dir/$file");
      else
        unlink("$dir/$file");
    }
    return rmdir($dir);
  }
  function zipDirectory($dir, $file, $root="") {
    $zip = new ZipArchive();
    $res = $zip->open($file, ZipArchive::CREATE);
    if($res) {
      if($root != "") {
        $zip->addEmptyDir($root);
        $root .= DIRECTORY_SEPARATOR;
      }
      $baseLen = mb_strlen($dir);
      $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
          $dir,
          FilesystemIterator::SKIP_DOTS|
          FilesystemIterator::KEY_AS_PATHNAME|
          FilesystemIterator::CURRENT_AS_FILEINFO
        ), RecursiveIteratorIterator::SELF_FIRST
      );
      $list = array();
      foreach($iterator as $pathname => $info) {
        $localpath = $root . mb_substr($pathname, $baseLen);
        if($info->isFile())
          $zip->addFile($pathname, $localpath);
        else
          $res = $zip->addEmptyDir($localpath);
      }
      $zip->close();
    }
    else
      return false;
  }
  function is_utf8($str)
  {
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
      $c = ord($str[$i]);
      if ($c > 128) {
        if (($c > 247))
          return false;
        else if ($c > 239)
          $bytes = 4;
        else if ($c > 223)
          $bytes = 3;
        else if ($c > 191)
          $bytes = 2;
        else
          return false;
        if (($i + $bytes) > $len)
          return false;
        while ($bytes > 1) {
          $i++;
          $b = ord($str[$i]);
          if ($b < 128 || $b > 191)
            return false;
          $bytes--;
        }
      }
    }
    return true;
  }
  function dir_size($dir) {
    $handle = opendir($dir);
    $mas = 0;
    while ($file = readdir($handle)) {
      if ($file != '..' && $file != '.' && !is_dir($dir.'/'.$file))
        $mas += filesize($dir.'/'.$file);
      else if (is_dir($dir.'/'.$file) && $file != '..' && $file != '.')
        $mas += dir_size($dir.'/'.$file);
    }
    return $mas;
  }
  function used_bytes($dir){
    $si_prefix = array('B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB');
    $base = 1024;
    $dir_size = dir_size($dir);
    $class = min((int)log($dir_size, $base), count($si_prefix) - 1);
    return sprintf('%1.2f', $dir_size / pow($base, $class)) . $si_prefix[$class];
  }
  function calcFileSize($size)
  {
    $b = 1024;
    $mb = pow($b, 2);
    $gb = pow($b, 3);
    switch(true){
      case $size >= $gb:
        $target = $gb;
        $unit = 'GB';
        break;
      case $size >= $mb:
        $target = $mb;
        $unit = 'MB';
        break;
      default:
        $target = $b;
        $unit = 'KB';
        break;
    }
    $new_size = round($size / $target, 2);
    $file_size = number_format($new_size, 2, '.', ',') . $unit;
    return $file_size;
  }
  function convert($size)
  {
    $unit=array('Byte','KB','MB','GB','TB','PD');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
  }
  function getlistbyzip($zipPath){
    if(!is_file($zipPath)) return;
    unset($res);
    @exec("unzip -l {$zipPath} | awk '{print $4}'" , $res);
    if(count($res) > 6){
      $res = array_splice($res , 4, -2);
      sort($res);
      return json_encode($res , JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    else return null;
  }
  function getdatabyzip($zipPath , $filePath){
    if(!is_file($zipPath)) return;
    unset($res);
    @exec("unzip -p {$zipPath} {$filePath}" , $res);
    return join("\n", $res);
  }
  function dir_copy($dir_name, $new_dir)
  {
    if (!is_dir($new_dir))
      mkdir($new_dir);
    if (is_dir($dir_name)) {
      if ($dh = opendir($dir_name)) {
        while (($file = readdir($dh)) !== false) {
          if ($file == "." || $file == "..")
            continue;
          if (is_dir($dir_name . "/" . $file))
            dir_copy($dir_name . "/" . $file, $new_dir . "/" . $file);
          else
            copy($dir_name . "/" . $file, $new_dir . "/" . $file);
        }
        closedir($dh);
      }
    }
    return true;
  }
  function download($weburl, $filepath){
    $fp = fopen ($filepath, 'w+');
    $ch = curl_init($weburl);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; AccessBot 1.1; PHP; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121');
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
  }
  function downloadtext($url){
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; AccessBot 1.1; PHP; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121');
    curl_setopt($curl, CURLOPT_REFERER, 'https://www.activetk.cf/ATK-FM/');   
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
    curl_setopt($curl, CURLOPT_AUTOREFERER, true);
    $html = curl_exec($curl);
    $head = curl_getinfo($curl, CURLINFO_HEADER_OUT);
    $info = curl_getinfo($curl);
    if($errno = curl_errno($curl)) {
      $error_message = curl_strerror($errno);
      curl_close($curl);
      return false;
    }
    $header = substr($html, 0, $info["header_size"]);
    $html = substr($html, $info["header_size"]);
    curl_close($curl);
    return $html;
  }
  function CheckUpdate($real, $isr) {
    if (lastupdateinfo == 'BETA!') return false;
    if ($real || $isr) { }
    else if (file_get_contents(dirname(__FILE__) . "/ATK-FM/lastupdate.txt") == date("Ymd")) return false;
    $hasupdate = downloadtext("https://www.activetk.cf/ATK-FM/update/lastbuild");
    file_put_contents(dirname(__FILE__) . "/ATK-FM/lastupdate.txt", date("Ymd"));
    if ("v{$hasupdate}" == fmversion && $real != true) return false;
    download("https://www.activetk.cf/ATK-FM/update/go/" . $hasupdate . ".php", __FILE__);
    if ($isr) return $hasupdate;
    return true;
  }
  function MakeLog() {
    if (!file_exists(dirname(__FILE__) . "/ATK-FM/config.php"))
      return false;
    $logfile = dirname(__FILE__) . "/ATK-FM/.access_log.php";
    if (!file_exists($logfile))
      file_put_contents($logfile,
      '<?php die(); ?>'."\r\n".
      "*****************************************************************************\r\n** ATK-FM ACCESS LOG FILE\r\n** ".
      copyright."\r\n*****************************************************************************\r\n\r\n");
    $fpadd = fopen($logfile, "a");
    $timeinfo = date("Y/m/d H:i:s");
    $timest = time();
    $ipaddr = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_USER_AGENT']))
      $useragent = $_SERVER['HTTP_USER_AGENT'];
    else
      $useragent = "";
    $command = "";
    $uri = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ) ? "https://" : "http://").$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
    if ($_SERVER["REQUEST_METHOD"] != "POST")
      $postdata = json_encode($_GET);
    else
      $postdata = json_encode($_POST);
    $method = $_SERVER["REQUEST_METHOD"];
    @fwrite($fpadd, json_encode(array($timeinfo, $ipaddr, $uri, $timest, $method, $postdata, $useragent)) . PHP_EOL);
    fclose($fpadd);
  }
  if (isset($_POST["setup"]) && isset($_POST["password"]))
  {
    if (File_Exists(dirname(__FILE__) . "/ATK-FM/config.php"))
      exit("<p>SetupError: 既にセットアップされています</p>");
    else
    {
      if (!file_exists(__DIR__ . "/ATK-FM"))
        mkdir(dirname(__FILE__) . "/ATK-FM");
      if (file_exists(__DIR__ . "/ATK-FM/password.txt"))
        unlink(__DIR__ . "/ATK-FM/password.txt");
      $pw = md5($_POST['password']);
      file_put_contents(dirname(__FILE__) . "/ATK-FM/config.php", "<?php \n\n  define('config_load', true);\n  define('fm_password_md5', '".$pw."');\n\n");
      file_put_contents(dirname(__FILE__) . "/ATK-FM/lastupdate.txt", "NONE");
      $_SESSION["login"] = $pw;
      $_SESSION["cd"] = realpath(null) . "/";
      header("Location: {$meurl}");
      exit("<p>セットアップが完了しました。ホームへリダイレクトしています。。</p>");
    }
  }
  if (!File_Exists(dirname(__FILE__) . "/ATK-FM/config.php")) ViewStartForm();
  require_once(dirname(__FILE__) . "/ATK-FM/config.php");
  if (isset($_SESSION["login"]) && $_SESSION['login'] == fm_password_md5) {}
  else if (isset($_POST["login"]) && isset($_POST["password"]))
  {
    if ($_POST["password"] == "" && isset($_POST["_trykey"]))
    {
      if (!isset($_SESSION["logincode"]) || $_SESSION["logincode"] == "")
      {
        exit("<!-- ".time()." -->\n<p>LoginError: セッション切れが発生しました。</p>\n");
      }
      if (md5(fm_password_md5 . $_SESSION["logincode"]) == $_POST["_trykey"])
      {
        $_SESSION["login"] = fm_password_md5;
        $_SESSION["cd"] = realpath(null) . "/";
      }
      else
        exit("<!-- ".time()." -->\n<p>LoginError: パスワードが違います</p>\n");
    }
    else
      exit("<!-- ".time()." -->\n<p>LoginError: ログインするにはお使いのブラウザのJavaScriptを有効にしてください。</p>\n");
  }
  else ViewLoginForm();
  if (isset($_POST["save-password"]))
  {
    $pw = md5($_POST['passwordnew']);
    if ($pw == fm_password_md5)
    {
      file_put_contents(dirname(__FILE__) . "/ATK-FM/config.php", "<?php \n\n  define('config_load', true);\n  define('fm_password_md5', '{$pw}');\n\n");
      $_SESSION["login"] = $pw;
      exit("パスワードを保存しました。");
    }
    else
      exit("元のパスワードが違う為、パスワードを変更出来ませんでした。<br>\nパスワードを忘れた場合には、「ATK-FM/config.php」を削除してください。");
  }
  if (isset($_POST["password-reset"])) ViewPasswordResetForm();
  if (isset($_GET["checkupdate"]))
  {
    if ($_GET["checkupdate"] == "false") {
      if (!CheckUpdate(false, true))
        exit("アップデートはありません。");
      else
        exit("最新バージョンへのアップデートが完了しました。");
    }
    else
    {
      if (lastupdateinfo != 'BETA!')
        CheckUpdate(true, true);
      exit("強制的に最新のバージョンへアップデートしました。<br>注意1:何回強制アップデートをしても更新内容は変わりません。<br>注意2:アップデートが無い場合も最新版に置き換えます。");
    }
  }
  if (isset($_GET["viewaccesslog"]))
  {
    session_write_close();
    ?><pre><?=htmlspecialchars(file_get_contents(dirname(__FILE__) . "/ATK-FM/.access_log.php"))?></pre><?php
    exit();
  }
  if (isset($_GET["ajax-typeof"]) || isset($_GET["ajaxtypeof"]))
  {
    if (isset($_GET["ajax-typeof"])) $command = htmlspecialchars($_GET["ajax-typeof"]);
    else $command = htmlspecialchars($_GET["ajaxtypeof"]);
    $hiki = "";
    $hiki2 = "";
    if (isset($_GET["ajax-option"])) $hiki = $_GET["ajax-option"];
    if (isset($_GET["ajaxoption"])) $hiki = $_GET["ajaxoption"];
    if (isset($_GET["ajax-option2"])) $hiki2 = $_GET["ajax-option2"];
    if (isset($_GET["ajaxoption2"])) $hiki2 = $_GET["ajax-option2"];
    if ($command == "get-directory" || $command == "getdirectory")
    {
      session_write_close();
      if (!is_file(substr($hiki, 0, -1))) {
        $listofs = glob("{$hiki}{*,.*}", GLOB_BRACE);
        $listof = array();
        $access_ok = false;
        if (file_exists("{$hiki}.denyfm"))
        {
          $denyinfo = @json_decode(@file_get_contents("{$hiki}.denyfm"), true);
          if ($denyinfo == false)
            exit(json_encode(array('atk-fm-error'=>'アクセスが拒否されました。')));
          else
          {
            if ($hiki2 == "")
              exit(@json_encode(array('access'=>'denied', 'admin'=>$denyinfo["admin"])));
            else if (!@password_verify($hiki2, $denyinfo["password"]))
              exit(@json_encode(array('access'=>'denied::PW_NOT_MATCH', 'admin'=>$denyinfo["admin"])));
            else
              $access_ok = true;
          }
        }
        foreach ($listofs as $file)
        {
          if (is_file($file))
          {
            $sds = pathinfo($file);
            if (isset($sds["extension"])) $scc = $sds["extension"];
            else $scc = "";
            if ($scc == "zip" || $scc == "7z" || $scc == "rar" || $scc == "gz" || $scc == "bz2" || $scc == "lzh") $listof += array($file=>'c');
            else $listof += array($file=>'a');
          }
          else if (is_dir($file) && basename($file) != "." && basename($file) != "..")
            $listof += array($file=>'b');
        }
        session_start();
        $_SESSION["cd"] = realpath($hiki);
        session_write_close();
        if ($listof == array()) $listof = array('atk-fm-error'=>'(ファイル/ディレクトリは存在しません)');
        if ($listof == false) exit(json_encode(array('atk-fm-error'=>'指定されたディレクトリが存在しない、又はアクセスが拒否されました。')));
        if ($access_ok)
          exit(json_encode($listof += array('access'=>'ok')));
        else
          exit(json_encode($listof));
      }
      else {
        $jsd = getlistbyzip(substr($hiki, 0, -1));
        if ($jsd == null) exit(json_encode(array('atk-fm-error'=>'(ファイル/ディレクトリは存在しません:'.substr($hiki, 0, -1).')')));
        $listof = array();
        foreach(json_decode($jsd) as $file)
          $listof += array($file=>'d');
        session_start();
        $_SESSION["cd"] = realpath(substr($hiki, 0, -1));
        session_write_close();
        if ($listof == array()) $listof = array('atk-fm-error'=>'(ファイル/ディレクトリは存在しません)');
        exit(json_encode($listof));
      }
    }
    else if ($command == "get-item" || $command == "getitem")
    {
      session_write_close();
      if (File_Exists($hiki)) ViewNotePad($hiki);
      else exit("エラー: ファイルが見つかりません。");
    }
    else if ($command == "view-html" || $command == "viewhtml")
    {
      session_write_close();
      if (File_Exists($hiki))
      {
        if ($hiki2 == "")
          exit(file_get_contents($hiki));
        else
          exit("<base href='{$hiki2}'>\n".file_get_contents($hiki));
      }
      else exit("エラー: ファイルが見つかりません。");
    }
    else if ($command == "remove-directory" || $command == "removedirectory")
    {
      session_write_close();
      if (File_Exists($hiki))
      {
        remove_directory($hiki);
        exit("ディレクトリを削除しました。");
      }
      else exit("Error: 指定されたディレクトリは存在しません。");
    }
    else if ($command == "remove-item" || $command == "removeitem")
    {
      session_write_close();
      if (File_Exists($hiki))
      {
        if (!unlink($hiki)) exit("Error: 指定されたファイルを削除出来ません。");
        else exit("");
      }
      else exit("Error: 指定されたファイルは存在しません。");
    }
    else if ($command == "download-item" || $command == "downloaditem")
    {
      session_write_close();
      if (!is_readable($hiki)) { exit("ファイルを読み込めませんでした。"); }
      if (preg_match('/MSIE (\d{1,2})\.\d;/', getenv('HTTP_USER_AGENT'), $matchAry))
      {
        header('X-Download-Options: noopen');
        if (getenv('HTTPS') && (int) $matchAry[1] <= 8) {
          header('Cache-Control: public');
          header('Pragma: public');
        }
        header('Content-Type: application/force-download');
      }
      else header('Content-Type: application/zip');
      header('X-Content-Type-Options: nosniff');
      header('Content-Length: ' . filesize($hiki));
      header('Content-Disposition: attachment; filename="' . basename($hiki) . '"');
      header('Connection: close');
      while (ob_get_level()) { ob_end_clean(); }
      readfile($hiki);
    }
    else if ($command == "upload-item" || $command == "uploaditem")
    {
      session_write_close();
      $erro = false;
      for($i = 0; $i < count($_FILES["file"]["name"]); $i++ ){
        if(is_uploaded_file($_FILES["file"]["tmp_name"][$i])){
          $filename = $_SESSION["cd"] . "/" . $_FILES['file']['name'][$i];
          if (move_uploaded_file($_FILES["file"]["tmp_name"][$i], $filename))
            echo "";
          else {
            echo $_FILES["file"]["name"][$i] . "をアップロード出来ませんでした。";
            $erro = true;
          }
        }
      }
      if (!$erro)
        echo "ファイルをアップロードしました。";
    }
    else if ($command == "min-upload-item" || $command == "minuploaditem")
    {
      session_write_close();
      if(isset($_POST["filename"]) && is_uploaded_file($_FILES["data"]["tmp_name"])){
        $filename = $_SESSION["cd"] . "/" . basename($_POST["filename"]);
        $a = fopen($filename, "a");
        @fwrite($a, file_get_contents($_FILES["data"]["tmp_name"]));
        fclose($a);
        exit(json_encode(['size' => filesize($filename)]));
      }
    }
    else if ($command == "uploadfromurl" || $command == "uploadfromurl")
    {
      session_write_close();
      $fn = pathinfo($hiki)["filename"] . "." . pathinfo($hiki)["extension"];
      if ($fn == "") $fn = "NoName.txt";
      $filename = $_SESSION["cd"] . "/" . substr($fn, 0, 120);
      @download($hiki, $filename);
      exit("URLから{$filename}をアップロードしました。");
    }
    else if ($command == "add-item" || $command == "additem")
    {
      exit(file_put_contents($_SESSION["cd"] . "/" . $hiki, ""));
    }
    else if ($command == "add-directory" || $command == "adddirectory")
    {
      mkdir($_SESSION["cd"] . "/" . $hiki, 0705);
    }
    else if ($command == "open-zip" || $command == "openzip")
    {
      session_write_close();
      $zip = new ZipArchive();
      if ($zip->open($hiki) !== true) exit("ファイルの展開に失敗しました。");
      $unzip_dir = $_SESSION["cd"] . "/" . pathinfo($hiki)['filename'];
      $unzip_dir = (substr($unzip_dir, -1) == '/') ? $unzip_dir : $unzip_dir.'/';
      if ($zip->extractTo($unzip_dir) !== true) {
        $zip->close();
        exit("ファイルの展開に失敗しました。unzipdir={$unzip_dir}");
      }
      $zip->close();
      exit("展開に成功しました!({$unzip_dir})");
    }
    else if ($command == "make-zip" || $command == "makezip")
    {
      session_write_close();
      zipDirectory($hiki, $hiki . ".zip");
      exit("フォルダーを圧縮しました。");
    }
    else if ($command == "copy-item" || $command == "copyitem")
    {
      session_write_close();
      if (is_dir($hiki))
      {
        $new_dir = $hiki . " (コピー)/";
        dir_copy($hiki, $new_dir);
      }
      else exit(copy($hiki, $hiki . " (コピー)"));
    }
    else if ($command == "rename-item" || $command == "renameitem")
    {
      exit(rename($hiki, $hiki2));
    }
    else if ($command == "count-directory-files" || $command == "countdirectoryfiles")
    {
      session_write_close();
      $starttime = microtime(true);
      $fairusuu = 0;
      $dsuu = 0;
      $iterator = new RecursiveDirectoryIterator($hiki);
      $iterator = new RecursiveIteratorIterator($iterator);
      foreach ($iterator as $fileinfo) {
        if ($fileinfo->isFile())
          $fairusuu++;
        else if ($fileinfo->isDir())
          $dsuu++;
      }
      exit(json_encode(array('File'=>$fairusuu, 'Directory'=>$dsuu, 'Time'=>(microtime(true)-$starttime), 'SIZE'=>used_bytes($hiki."/"))));
    }
    else if ($command == "get-filesize" || $command == "getfilesize")
    {
      session_write_close();
      exit(calcFileSize(filesize($hiki)));
    }
    else if ($command == "get-server" || $command == "getserver") ViewServerInfo();
    else if ($command == "save-item" || $command == "saveitem")
    {
      session_write_close();
      exit(file_put_contents($hiki, $_POST["naka"]));
    }
    else if ($command == "get-item-zip" || $command == "getitemzip")
    {
      session_write_close();
      $alltext = getdatabyzip(substr($hiki, 0, -1), $hiki2);
      if ($alltext == "") echo '//! テキストが空です';
      if (@is_utf8($alltext)) echo $alltext;
      else
        echo @mb_convert_encoding($alltext, 'UTF-8', 'SJIS');
    }
    else if ($command == "remove-item-zip" || $command == "removeitemzip")
    {
      session_write_close();
      $zip = new ZipArchive;
      if ($zip->open(substr($hiki, 0, -1)) === TRUE) { 
        $zip->deleteName($hiki2);
        $zip->close();
        echo '削除に成功しました。';
      }
      else
        echo 'エラー: ファイルが見つかりませんでした。';
    }
    else
    {
      header("HTTP/1.1 404 Not Found");
      exit("CommandNotFoundException: 存在しないコマンドです。");
    }
    exit();
  }
  else if (isset($_POST["logout"]))
  {
    $_SESSION["login"] = "false";
    header("Location: {$meurl}");
    exit();
  }
  function ViewLoginForm() { 
    $meurl = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ) ? "https://" : "http://").$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
    $title = "Login | ATK-FM " . fmversion;
    $_SESSION["logincode"] = "_" . @bin2hex(@openssl_random_pseudo_bytes(64));
  ?>
<!DOCTYPE html>
<html lang="ja" dir="ltr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <title><?php echo $title; ?></title>
    <base href="<?=$meurl?>">
    <meta name="robots" content="noindex">
    <meta name="copyright" content="Copyright &copy; 2021 ActiveTK. All rights reserved.">
    <link rel="canonical" href="<?=$meurl?>">
    <style>a{color: #00ff00;position: relative;display: inline-block;transition: .3s;}a::after {position: absolute;bottom: 0;left: 50%;content: '';width: 0;height: 2px;background-color: #31aae2;transition: .3s;transform: translateX(-50%);}a:hover::after{width: 100%;}</style>
    <?php ViewActiveTKJS(); ?>
    <script type="text/javascript">
    function CSubmit(){return""==_("password").value?(alert("パスワードを入力してください"),!1):(_("login").disabled="true",_("login").value="ログインしています。。",_("_trykey").value=CybozuLabs.MD5.calc(CybozuLabs.MD5.calc(_("password").value)+"<?=$_SESSION['logincode']?>"),_("password").value="",!0)}
    </script>
  </head>
  <body style="background-color:#e6e6fa;text:#363636;">
    <div align="center">
      <br>
      <h1><?=$title?></h1>
      <br><br>
      <form action='' method='POST' onsubmit='return CSubmit();'>
        <p>パスワード: <input type="password" id="password" name="password" value="" placeholder="パスワードを入力してください" required></p>
        <input type="submit" style="height:60px;width:140px;" id="login" value="ログイン" title="ログイン">
        <input type="hidden" name="login" value="">
        <input type="hidden" name="_trykey" id="_trykey" value="" style="display:none;">
      </form>
      <br>
      <p title="チャレンジレスポンス認証を採用しています">
        <img style="height:14px;width:14px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAAXNSR0IArs4c6QAABe1JREFUeF7tm2uoFGUYx//P7Kk8euzDqSiiD4WftDCELmi39bozOzMrFVZkiaYFBUolFKSQggYFaigUlKZYhiUV7byzM3u8tN2ULiBJ6SepDxFF5Yc8eszOvk/MdpTDcWZ2dmb37HTc99vuPpf/85t35r3sO4RRarlcrjeDjK6Q8hgR9TLzFQB6h9KfIKI/mfmEZPlWFVW7XC6fGA1p1Ook+Xl5Q1GUhwA8AOCSiPn+AfC+lHJ3qa8kIvrEMmsZAK9wKHhCgWLGUjbkJCEtSLzRKhAtAWCq5mYGL09S+EhfAm2xXGtFM2N6sZoOwNTMEjNrzRZaE0vkWI6Vb2bspgIwVMMFkGumQJ9YZeEKtVk5mgbAUI01AF5slrA6cdYKV3j5EremAIhdPONYrQLC5BiVNAVCYgDaPG1WRsnsj1jAISjYyYN85NTZU0cqlUq/55fNZnsmXDphKnXRVEgsAjA9SryqrM52+pwDUWyDbBIDMFTDBlD3wcTgbbZrL4siVlf1rQRaGsG2JFyhR7ALNEkEwFTNpQzeWk8AM99tl+3P69kN/13P6XcR0Wf1fAi0zHKtbfXsWtID8mq+WG+iwxmeYtv2f/d6g03X9clUpaNhbt5EqeSWCg2GPm8euwdks9lxPeN6vPl6dyBdotWWY62PK87zMzVzFTOvC4kx0H+mv7dSqZyJkyc2gKE5vhWYlHG4/+/+GXGFnYtbA31Zz0EQpgXlklKacafKsQHoOX0dEa0Kvrdoq+Vaj8e5KiN9TNV8k8GBD1BmXm+X7dVxcsUGYOSM10B4MiTpSuGKjXFEjfQxVONZABtCetvroiyeipMrNgBTM3cz84MhSWcKV1TiiPIBkAXwSciz5j3Lsbwld8MtNgBDNfYCmBOUUblUubxYLJ5sWJGPQ6FQmCjPyr9CYu0TrpgbJ1cSAN4V8a6MbxOuiB3bL6ChGhxSYEW4YmYHQAwCsa+SoRqdHtC5BTrPgM5DsDMKXFTDoKZpk0jSDIWUSQDuCXsIAljbyKgkpayN84qinB+ZvO+GfQ7bb/RmnJ9KlsdZ4YOO4xyPmjvSMFjb8yPcD8ZNUQO31Y7wPRgfRNk4DQWQn5u/RckoHwO4tq0FxU/+i6zK+aW9pW8D1xFBPyxYsKB74OTA6fi50+PZPbF7/J49ewb8FAX2gHpr8PSUV18JIXhvwheAruoFAnldf8w0Bs+3Xbs4siBfAAWt8Jxk+fKYqd4bXUh5vugUX4kEYCx1/3MFB90Gvj2g3krvf9ozfPcMOgACdl9C1/qdHpCQAIHe9v7RkSQP1aa/rEz3/mFi8KMJQ4e5p+MWYOIVtmNv8VOqa/pyYtrcIgjtB8DgTbZre3v8gU1X9Y0EeqYFENoLgIj6BzE42XGcn8OK0zTtui50HWPmniZDaC8AAAeFK+6IUpShGl8CmBHFtgGb9gIgpletshWpa5s5cxMTP91AcVFM2wuAwTts114SRamu6tsJtDiKbQM27QVAREcsx7o5imBTM79j5qlRbBuwaS8ATyiBbrNc65sw0aZq3srgrxsoLKpp+wEA+IPBS/2WpV4VQ8tw77zPlVGrasAuFQBqev2WpqOwBE8PACJ613KshcOvnqmZu5j54QauaKOmqQJwweGpCIehGi14pH16AAC4T7jio+EKDdW4F8CHSasM8U8PgEEM3uC67k/Dxaqqen0Xun68KAAEnR6pcwokKZuU9ADCb8IR1/hVY2jGr2BcnbTSAP90ACCiA5ZjzfYTaWrmfmaeNaYBgLABDP83wQgGGCvTAMATmOgYeouKSBLWFq4wRgbw3RU2NXM9M7+QJFvafInoJcuxLjja6wsgr+YXK1C2p62IJHok5JKSW9oRqQdoc7Rpma7MFwDGJ0maIt/T1cHqnc4+53AkAJ5R7BehUlT1OSlSyjWlvpLviZXQAxKj9B5gq5GFvmdY94iMoRkLwXin1SpbEp/wiHDErrDYdQF4zvl5+dtJoUUEmgLgRgBXtURw8qC/A/iBwUdZ8s5SX+mreiH/BfASqF/wcyGvAAAAAElFTkSuQmCC">
        チャレンジレスポンス認証を採用しています
      </p>
    </div>
  </body>
</html>
<?php
    exit();
  }
  function ViewActiveTKJS(){
?>
<script type="text/javascript">
    /*! ATK-FM / (c) 2021 ActiveTK. */
    typeof console.log==='function'&&console.log("\n\n%cATK-FM / 無料で使えるファイルマネージャー\n%cCopyright (c) 2021 ActiveTK. All rights reserved.\nhttps://www.activetk.cf/ATK-FM/\n\n","color:green;font-size:large;font-weight:bold;","");
    /*!
     * ActiveTK.js
     * Copyright 2020 ActiveTK. All rights reserved.
     * Released under the MIT license
     * http://ActiveTK.CF/ActiveTK.JS/readme.txt
     */
     ;var startx=!0;try{ActiveTK.onload(),startx=!1}catch(t){}if(startx){'use strict';onload=function(){try{_("body").style="a{color: #00ff00;position: relative;display: inline-block;transition: .3s;}a::after {position: absolute;bottom: 0;left: 50%;content: '';width: 0;height: 2px;background-color: #31aae2;transition: .3s;transform: translateX(-50%);}a:hover::after{width: 100%;}"}catch(e){}};let now=new Date,mon="",regex="",sleep_starttime=new Date,sleep_newtime=new Date,c_data,n,m,data,c_date,c_limit,ns;var ActiveTK={onload:function(){return!0},version:function(){return"2020.11.29"},copyright:function(){return"ActiveTK."},license:function(){return"http://ACTIVETK.CF/ActiveTK.JS/readme.txt"}},atk={version:function(){return ActiveTK.version()},copyright:function(){return ActiveTK.copyright()},printf:function(e){return console.log(e),!0},time:function(){return now=new Date,mon=now.getMonth()+1,now.getFullYear()+" "+mon+"/"+now.getDate()+" "+now.getHours()+":"+now.getMinutes()},dev:function(){debugger},param:function(e,t){if(t||(t=window.location.href),e=e.replace(/[\[\]]/g,"\\$&"),regex=new RegExp("[?&]"+e+"(=([^&#]*)|&|#|$)"),results=regex.exec(t),results)return decodeURIComponent(results[2].replace(/\+/g," "))},sleep:function(e){for(sleep_newtime=new Date,sleep_starttime=new Date;!((sleep_newtime=new Date)-sleep_starttime>e););return!0},Cookie:{Use:function(){return navigator.cookieEnabled},Get:function(e){return c_data=document.cookie,(n=c_data.indexOf(e,0))>-1?(-1==(m=c_data.indexOf(";",n+e.length))&&(m=c_data.length),data=unescape(c_data.substring(n+e.length,m))):data=void 0,data},Write:function(e,t,n){return c_data=escape(t),e+="=",c_date=new Date,ns=c_date.getTime()+864e5*n,c_date.setTime(ns),c_limit=c_date.toGMTString(),document.cookie=e+c_data+"; expires="+c_limit,!0},Delete:function(e){return this.Write(e,"undefined",0),!0}},Math:{enzn:function(q){if(Number.isFinite(eval(q)))return eval(q);atk.Error("This object is not calculation formula.")},jyou:function(e,t){return Math.pow(e,t)},zet:function(e){return Math.abs(e)},hant:function(kazu){if(Number.isFinite(eval(q))){if(kazu%2!=0)return!1;if(kazu%2==0)return!0}else atk.Error("This object is not calculation formula.")}},t:function(e){return 1!=e&&(0==e||void 0)},print:function(){return window.print(),!0},msgbox:function(e){return alert(e),!0},confirm:function(e){return!!confirm(e)},undefined:function(){return void(0)},infinity:function(){return this.Math.jyou(1e3,1e3)},encode:function(e){return encodeURIComponent(e)},decode:function(e){return decodeURIComponent(e)},search:function(e){location.href="https://www.google.com/search?q="+this.encode(e)},js:function(e,t,n){var o=document.createElement("script");return t||(t="text/javascript"),n||(n="head"),o.type=t,o.src=e,document.getElementsByTagName(n)[0].appendChild(o),!0},new:function(e,t,n,o){let r=document.createElement(t);return e||(e="body"),n||(n="None"),o||(e="None"),!!t&&(r.id=n,r.name=o,document.getElementsByTagName(e)[0].appendChild(r),!0)},ua:function(){return window.navigator.userAgent.toLowerCase()},null:function(){return null},href:function(e){if(!e)return location.href;location.href=e},Error:function(e){throw new Error(e)},Java:function(){return navigator.javaEnabled()},copy:function(e){let t=document.createElement("div");t.appendChild(document.createElement("pre")).textContent=e;let n=t.style;n.position="fixed",n.left="-100%",document.body.appendChild(t),document.getSelection().selectAllChildren(t);let o=document.execCommand("copy");return document.body.removeChild(t),o},download:function(e,o){if(!e)return!1;if(o||(o="download.txt"),"IE"==platform.name){let n=new XMLHttpRequest;n.open("GET",e),n.responseType="blob",n.onloadend=function(){200===n.status&&window.navigator.msSaveBlob(n.response,o)},n.send()}else{let n=document.createElement("a");n.href=e,n.download=o,document.body.appendChild(n),n.click(),document.body.removeChild(n)}}};var Ease={easeInOut:e=>e<.5?4*e*e*e:(e-1)*(2*e-2)*(2*e-2)+1},duration=500;addEventListener("DOMContentLoaded",()=>{document.querySelectorAll('a[href^="#"]').forEach(function(e){e.addEventListener("click",function(t){var n=e.getAttribute("href"),o=document.documentElement.scrollTop||document.body.scrollTop,r=document.getElementById(n.replace("#",""));if(r){t.preventDefault(),t.stopPropagation();var a=pageYOffset+r.getBoundingClientRect().top-115,c=performance.now(),i=function(e){var t=(e-c)/duration;t<1?(scrollTo(0,o+(a-o)*Ease.easeInOut(t)),requestAnimationFrame(i)):window.scrollTo(0,a)};requestAnimationFrame(i)}})})});function GetRequestType(){try{return new XMLHttpRequest}catch(e){}try{return new ActiveXObject("MSXML2.XMLHTTP.6.0")}catch(e){}try{return new ActiveXObject("MSXML2.XMLHTTP.3.0")}catch(e){}try{return new ActiveXObject("MSXML2.XMLHTTP")}catch(e){}return!1}var tose_xhr=GetRequestType(),toses=new Object;function tose(e,t,n,o,r,i){if(!e)return!1;for(t||(t=location.href),r||(r="everyone"),i||(i="password"),o||(o=50),(tose_xhr=GetRequestType()).open(e,t,!0,r,i),tose_xhr.send(n);;){if(4==tose_xhr.readyState){if(0==tose_xhr.status)break;toses.status=tose_xhr.status,toses.responseText=tose_xhr.responseText;break}atk.sleep(o)}return toses}function _(e){return"null"==typeof document.getElementById(e)?document.getElementsByName(e):document.getElementById(e)}}
    /*! jQuery v2.0.3 | (c) 2005, 2013 jQuery Foundation, Inc. | jquery.org/license */
    (function(e,undefined){var t,n,r=typeof undefined,i=e.location,o=e.document,s=o.documentElement,a=e.jQuery,u=e.$,l={},c=[],p="2.0.3",f=c.concat,h=c.push,d=c.slice,g=c.indexOf,m=l.toString,y=l.hasOwnProperty,v=p.trim,x=function(e,n){return new x.fn.init(e,n,t)},b=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,w=/\S+/g,T=/^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]*))$/,C=/^<(\w+)\s*\/?>(?:<\/\1>|)$/,k=/^-ms-/,N=/-([\da-z])/gi,E=function(e,t){return t.toUpperCase()},S=function(){o.removeEventListener("DOMContentLoaded",S,!1),e.removeEventListener("load",S,!1),x.ready()};x.fn=x.prototype={jquery:p,constructor:x,init:function(e,t,n){var r,i;if(!e)return this;if("string"==typeof e){if(r="<"===e.charAt(0)&&">"===e.charAt(e.length-1)&&e.length>=3?[null,e,null]:T.exec(e),!r||!r[1]&&t)return!t||t.jquery?(t||n).find(e):this.constructor(t).find(e);if(r[1]){if(t=t instanceof x?t[0]:t,x.merge(this,x.parseHTML(r[1],t&&t.nodeType?t.ownerDocument||t:o,!0)),C.test(r[1])&&x.isPlainObject(t))for(r in t)x.isFunction(this[r])?this[r](t[r]):this.attr(r,t[r]);return this}return i=o.getElementById(r[2]),i&&i.parentNode&&(this.length=1,this[0]=i),this.context=o,this.selector=e,this}return e.nodeType?(this.context=this[0]=e,this.length=1,this):x.isFunction(e)?n.ready(e):(e.selector!==undefined&&(this.selector=e.selector,this.context=e.context),x.makeArray(e,this))},selector:"",length:0,toArray:function(){return d.call(this)},get:function(e){return null==e?this.toArray():0>e?this[this.length+e]:this[e]},pushStack:function(e){var t=x.merge(this.constructor(),e);return t.prevObject=this,t.context=this.context,t},each:function(e,t){return x.each(this,e,t)},ready:function(e){return x.ready.promise().done(e),this},slice:function(){return this.pushStack(d.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},eq:function(e){var t=this.length,n=+e+(0>e?t:0);return this.pushStack(n>=0&&t>n?[this[n]]:[])},map:function(e){return this.pushStack(x.map(this,function(t,n){return e.call(t,n,t)}))},end:function(){return this.prevObject||this.constructor(null)},push:h,sort:[].sort,splice:[].splice},x.fn.init.prototype=x.fn,x.extend=x.fn.extend=function(){var e,t,n,r,i,o,s=arguments[0]||{},a=1,u=arguments.length,l=!1;for("boolean"==typeof s&&(l=s,s=arguments[1]||{},a=2),"object"==typeof s||x.isFunction(s)||(s={}),u===a&&(s=this,--a);u>a;a++)if(null!=(e=arguments[a]))for(t in e)n=s[t],r=e[t],s!==r&&(l&&r&&(x.isPlainObject(r)||(i=x.isArray(r)))?(i?(i=!1,o=n&&x.isArray(n)?n:[]):o=n&&x.isPlainObject(n)?n:{},s[t]=x.extend(l,o,r)):r!==undefined&&(s[t]=r));return s},x.extend({expando:"jQuery"+(p+Math.random()).replace(/\D/g,""),noConflict:function(t){return e.$===x&&(e.$=u),t&&e.jQuery===x&&(e.jQuery=a),x},isReady:!1,readyWait:1,holdReady:function(e){e?x.readyWait++:x.ready(!0)},ready:function(e){(e===!0?--x.readyWait:x.isReady)||(x.isReady=!0,e!==!0&&--x.readyWait>0||(n.resolveWith(o,[x]),x.fn.trigger&&x(o).trigger("ready").off("ready")))},isFunction:function(e){return"function"===x.type(e)},isArray:Array.isArray,isWindow:function(e){return null!=e&&e===e.window},isNumeric:function(e){return!isNaN(parseFloat(e))&&isFinite(e)},type:function(e){return null==e?e+"":"object"==typeof e||"function"==typeof e?l[m.call(e)]||"object":typeof e},isPlainObject:function(e){if("object"!==x.type(e)||e.nodeType||x.isWindow(e))return!1;try{if(e.constructor&&!y.call(e.constructor.prototype,"isPrototypeOf"))return!1}catch(t){return!1}return!0},isEmptyObject:function(e){var t;for(t in e)return!1;return!0},error:function(e){throw Error(e)},parseHTML:function(e,t,n){if(!e||"string"!=typeof e)return null;"boolean"==typeof t&&(n=t,t=!1),t=t||o;var r=C.exec(e),i=!n&&[];return r?[t.createElement(r[1])]:(r=x.buildFragment([e],t,i),i&&x(i).remove(),x.merge([],r.childNodes))},parseJSON:JSON.parse,parseXML:function(e){var t,n;if(!e||"string"!=typeof e)return null;try{n=new DOMParser,t=n.parseFromString(e,"text/xml")}catch(r){t=undefined}return(!t||t.getElementsByTagName("parsererror").length)&&x.error("Invalid XML: "+e),t},noop:function(){},globalEval:function(e){var t,n=eval;e=x.trim(e),e&&(1===e.indexOf("use strict")?(t=o.createElement("script"),t.text=e,o.head.appendChild(t).parentNode.removeChild(t)):n(e))},camelCase:function(e){return e.replace(k,"ms-").replace(N,E)},nodeName:function(e,t){return e.nodeName&&e.nodeName.toLowerCase()===t.toLowerCase()},each:function(e,t,n){var r,i=0,o=e.length,s=j(e);if(n){if(s){for(;o>i;i++)if(r=t.apply(e[i],n),r===!1)break}else for(i in e)if(r=t.apply(e[i],n),r===!1)break}else if(s){for(;o>i;i++)if(r=t.call(e[i],i,e[i]),r===!1)break}else for(i in e)if(r=t.call(e[i],i,e[i]),r===!1)break;return e},trim:function(e){return null==e?"":v.call(e)},makeArray:function(e,t){var n=t||[];return null!=e&&(j(Object(e))?x.merge(n,"string"==typeof e?[e]:e):h.call(n,e)),n},inArray:function(e,t,n){return null==t?-1:g.call(t,e,n)},merge:function(e,t){var n=t.length,r=e.length,i=0;if("number"==typeof n)for(;n>i;i++)e[r++]=t[i];else while(t[i]!==undefined)e[r++]=t[i++];return e.length=r,e},grep:function(e,t,n){var r,i=[],o=0,s=e.length;for(n=!!n;s>o;o++)r=!!t(e[o],o),n!==r&&i.push(e[o]);return i},map:function(e,t,n){var r,i=0,o=e.length,s=j(e),a=[];if(s)for(;o>i;i++)r=t(e[i],i,n),null!=r&&(a[a.length]=r);else for(i in e)r=t(e[i],i,n),null!=r&&(a[a.length]=r);return f.apply([],a)},guid:1,proxy:function(e,t){var n,r,i;return"string"==typeof t&&(n=e[t],t=e,e=n),x.isFunction(e)?(r=d.call(arguments,2),i=function(){return e.apply(t||this,r.concat(d.call(arguments)))},i.guid=e.guid=e.guid||x.guid++,i):undefined},access:function(e,t,n,r,i,o,s){var a=0,u=e.length,l=null==n;if("object"===x.type(n)){i=!0;for(a in n)x.access(e,t,a,n[a],!0,o,s)}else if(r!==undefined&&(i=!0,x.isFunction(r)||(s=!0),l&&(s?(t.call(e,r),t=null):(l=t,t=function(e,t,n){return l.call(x(e),n)})),t))for(;u>a;a++)t(e[a],n,s?r:r.call(e[a],a,t(e[a],n)));return i?e:l?t.call(e):u?t(e[0],n):o},now:Date.now,swap:function(e,t,n,r){var i,o,s={};for(o in t)s[o]=e.style[o],e.style[o]=t[o];i=n.apply(e,r||[]);for(o in t)e.style[o]=s[o];return i}}),x.ready.promise=function(t){return n||(n=x.Deferred(),"complete"===o.readyState?setTimeout(x.ready):(o.addEventListener("DOMContentLoaded",S,!1),e.addEventListener("load",S,!1))),n.promise(t)},x.each("Boolean Number String Function Array Date RegExp Object Error".split(" "),function(e,t){l["[object "+t+"]"]=t.toLowerCase()});function j(e){var t=e.length,n=x.type(e);return x.isWindow(e)?!1:1===e.nodeType&&t?!0:"array"===n||"function"!==n&&(0===t||"number"==typeof t&&t>0&&t-1 in e)}t=x(o),function(e,undefined){var t,n,r,i,o,s,a,u,l,c,p,f,h,d,g,m,y,v="sizzle"+-new Date,b=e.document,w=0,T=0,C=st(),k=st(),N=st(),E=!1,S=function(e,t){return e===t?(E=!0,0):0},j=typeof undefined,D=1<<31,A={}.hasOwnProperty,L=[],q=L.pop,H=L.push,O=L.push,F=L.slice,P=L.indexOf||function(e){var t=0,n=this.length;for(;n>t;t++)if(this[t]===e)return t;return-1},R="checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",M="[\\x20\\t\\r\\n\\f]",W="(?:\\\\.|[\\w-]|[^\\x00-\\xa0])+",$=W.replace("w","w#"),B="\\["+M+"*("+W+")"+M+"*(?:([*^$|!~]?=)"+M+"*(?:(['\"])((?:\\\\.|[^\\\\])*?)\\3|("+$+")|)|)"+M+"*\\]",I=":("+W+")(?:\\(((['\"])((?:\\\\.|[^\\\\])*?)\\3|((?:\\\\.|[^\\\\()[\\]]|"+B.replace(3,8)+")*)|.*)\\)|)",z=RegExp("^"+M+"+|((?:^|[^\\\\])(?:\\\\.)*)"+M+"+$","g"),_=RegExp("^"+M+"*,"+M+"*"),X=RegExp("^"+M+"*([>+~]|"+M+")"+M+"*"),U=RegExp(M+"*[+~]"),Y=RegExp("="+M+"*([^\\]'\"]*)"+M+"*\\]","g"),V=RegExp(I),G=RegExp("^"+$+"$"),J={ID:RegExp("^#("+W+")"),CLASS:RegExp("^\\.("+W+")"),TAG:RegExp("^("+W.replace("w","w*")+")"),ATTR:RegExp("^"+B),PSEUDO:RegExp("^"+I),CHILD:RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+M+"*(even|odd|(([+-]|)(\\d*)n|)"+M+"*(?:([+-]|)"+M+"*(\\d+)|))"+M+"*\\)|)","i"),bool:RegExp("^(?:"+R+")$","i"),needsContext:RegExp("^"+M+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+M+"*((?:-\\d)?\\d*)"+M+"*\\)|)(?=[^-]|$)","i")},Q=/^[^{]+\{\s*\[native \w/,K=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,Z=/^(?:input|select|textarea|button)$/i,et=/^h\d$/i,tt=/'|\\/g,nt=RegExp("\\\\([\\da-f]{1,6}"+M+"?|("+M+")|.)","ig"),rt=function(e,t,n){var r="0x"+t-65536;return r!==r||n?t:0>r?String.fromCharCode(r+65536):String.fromCharCode(55296|r>>10,56320|1023&r)};try{O.apply(L=F.call(b.childNodes),b.childNodes),L[b.childNodes.length].nodeType}catch(it){O={apply:L.length?function(e,t){H.apply(e,F.call(t))}:function(e,t){var n=e.length,r=0;while(e[n++]=t[r++]);e.length=n-1}}}function ot(e,t,r,i){var o,s,a,u,l,f,g,m,x,w;if((t?t.ownerDocument||t:b)!==p&&c(t),t=t||p,r=r||[],!e||"string"!=typeof e)return r;if(1!==(u=t.nodeType)&&9!==u)return[];if(h&&!i){if(o=K.exec(e))if(a=o[1]){if(9===u){if(s=t.getElementById(a),!s||!s.parentNode)return r;if(s.id===a)return r.push(s),r}else if(t.ownerDocument&&(s=t.ownerDocument.getElementById(a))&&y(t,s)&&s.id===a)return r.push(s),r}else{if(o[2])return O.apply(r,t.getElementsByTagName(e)),r;if((a=o[3])&&n.getElementsByClassName&&t.getElementsByClassName)return O.apply(r,t.getElementsByClassName(a)),r}if(n.qsa&&(!d||!d.test(e))){if(m=g=v,x=t,w=9===u&&e,1===u&&"object"!==t.nodeName.toLowerCase()){f=gt(e),(g=t.getAttribute("id"))?m=g.replace(tt,"\\$&"):t.setAttribute("id",m),m="[id='"+m+"'] ",l=f.length;while(l--)f[l]=m+mt(f[l]);x=U.test(e)&&t.parentNode||t,w=f.join(",")}if(w)try{return O.apply(r,x.querySelectorAll(w)),r}catch(T){}finally{g||t.removeAttribute("id")}}}return kt(e.replace(z,"$1"),t,r,i)}function st(){var e=[];function t(n,r){return e.push(n+=" ")>i.cacheLength&&delete t[e.shift()],t[n]=r}return t}function at(e){return e[v]=!0,e}function ut(e){var t=p.createElement("div");try{return!!e(t)}catch(n){return!1}finally{t.parentNode&&t.parentNode.removeChild(t),t=null}}function lt(e,t){var n=e.split("|"),r=e.length;while(r--)i.attrHandle[n[r]]=t}function ct(e,t){var n=t&&e,r=n&&1===e.nodeType&&1===t.nodeType&&(~t.sourceIndex||D)-(~e.sourceIndex||D);if(r)return r;if(n)while(n=n.nextSibling)if(n===t)return-1;return e?1:-1}function pt(e){return function(t){var n=t.nodeName.toLowerCase();return"input"===n&&t.type===e}}function ft(e){return function(t){var n=t.nodeName.toLowerCase();return("input"===n||"button"===n)&&t.type===e}}function ht(e){return at(function(t){return t=+t,at(function(n,r){var i,o=e([],n.length,t),s=o.length;while(s--)n[i=o[s]]&&(n[i]=!(r[i]=n[i]))})})}s=ot.isXML=function(e){var t=e&&(e.ownerDocument||e).documentElement;return t?"HTML"!==t.nodeName:!1},n=ot.support={},c=ot.setDocument=function(e){var t=e?e.ownerDocument||e:b,r=t.defaultView;return t!==p&&9===t.nodeType&&t.documentElement?(p=t,f=t.documentElement,h=!s(t),r&&r.attachEvent&&r!==r.top&&r.attachEvent("onbeforeunload",function(){c()}),n.attributes=ut(function(e){return e.className="i",!e.getAttribute("className")}),n.getElementsByTagName=ut(function(e){return e.appendChild(t.createComment("")),!e.getElementsByTagName("*").length}),n.getElementsByClassName=ut(function(e){return e.innerHTML="<div class='a'></div><div class='a i'></div>",e.firstChild.className="i",2===e.getElementsByClassName("i").length}),n.getById=ut(function(e){return f.appendChild(e).id=v,!t.getElementsByName||!t.getElementsByName(v).length}),n.getById?(i.find.ID=function(e,t){if(typeof t.getElementById!==j&&h){var n=t.getElementById(e);return n&&n.parentNode?[n]:[]}},i.filter.ID=function(e){var t=e.replace(nt,rt);return function(e){return e.getAttribute("id")===t}}):(delete i.find.ID,i.filter.ID=function(e){var t=e.replace(nt,rt);return function(e){var n=typeof e.getAttributeNode!==j&&e.getAttributeNode("id");return n&&n.value===t}}),i.find.TAG=n.getElementsByTagName?function(e,t){return typeof t.getElementsByTagName!==j?t.getElementsByTagName(e):undefined}:function(e,t){var n,r=[],i=0,o=t.getElementsByTagName(e);if("*"===e){while(n=o[i++])1===n.nodeType&&r.push(n);return r}return o},i.find.CLASS=n.getElementsByClassName&&function(e,t){return typeof t.getElementsByClassName!==j&&h?t.getElementsByClassName(e):undefined},g=[],d=[],(n.qsa=Q.test(t.querySelectorAll))&&(ut(function(e){e.innerHTML="<select><option selected=''></option></select>",e.querySelectorAll("[selected]").length||d.push("\\["+M+"*(?:value|"+R+")"),e.querySelectorAll(":checked").length||d.push(":checked")}),ut(function(e){var n=t.createElement("input");n.setAttribute("type","hidden"),e.appendChild(n).setAttribute("t",""),e.querySelectorAll("[t^='']").length&&d.push("[*^$]="+M+"*(?:''|\"\")"),e.querySelectorAll(":enabled").length||d.push(":enabled",":disabled"),e.querySelectorAll("*,:x"),d.push(",.*:")})),(n.matchesSelector=Q.test(m=f.webkitMatchesSelector||f.mozMatchesSelector||f.oMatchesSelector||f.msMatchesSelector))&&ut(function(e){n.disconnectedMatch=m.call(e,"div"),m.call(e,"[s!='']:x"),g.push("!=",I)}),d=d.length&&RegExp(d.join("|")),g=g.length&&RegExp(g.join("|")),y=Q.test(f.contains)||f.compareDocumentPosition?function(e,t){var n=9===e.nodeType?e.documentElement:e,r=t&&t.parentNode;return e===r||!(!r||1!==r.nodeType||!(n.contains?n.contains(r):e.compareDocumentPosition&&16&e.compareDocumentPosition(r)))}:function(e,t){if(t)while(t=t.parentNode)if(t===e)return!0;return!1},S=f.compareDocumentPosition?function(e,r){if(e===r)return E=!0,0;var i=r.compareDocumentPosition&&e.compareDocumentPosition&&e.compareDocumentPosition(r);return i?1&i||!n.sortDetached&&r.compareDocumentPosition(e)===i?e===t||y(b,e)?-1:r===t||y(b,r)?1:l?P.call(l,e)-P.call(l,r):0:4&i?-1:1:e.compareDocumentPosition?-1:1}:function(e,n){var r,i=0,o=e.parentNode,s=n.parentNode,a=[e],u=[n];if(e===n)return E=!0,0;if(!o||!s)return e===t?-1:n===t?1:o?-1:s?1:l?P.call(l,e)-P.call(l,n):0;if(o===s)return ct(e,n);r=e;while(r=r.parentNode)a.unshift(r);r=n;while(r=r.parentNode)u.unshift(r);while(a[i]===u[i])i++;return i?ct(a[i],u[i]):a[i]===b?-1:u[i]===b?1:0},t):p},ot.matches=function(e,t){return ot(e,null,null,t)},ot.matchesSelector=function(e,t){if((e.ownerDocument||e)!==p&&c(e),t=t.replace(Y,"='$1']"),!(!n.matchesSelector||!h||g&&g.test(t)||d&&d.test(t)))try{var r=m.call(e,t);if(r||n.disconnectedMatch||e.document&&11!==e.document.nodeType)return r}catch(i){}return ot(t,p,null,[e]).length>0},ot.contains=function(e,t){return(e.ownerDocument||e)!==p&&c(e),y(e,t)},ot.attr=function(e,t){(e.ownerDocument||e)!==p&&c(e);var r=i.attrHandle[t.toLowerCase()],o=r&&A.call(i.attrHandle,t.toLowerCase())?r(e,t,!h):undefined;return o===undefined?n.attributes||!h?e.getAttribute(t):(o=e.getAttributeNode(t))&&o.specified?o.value:null:o},ot.error=function(e){throw Error("Syntax error, unrecognized expression: "+e)},ot.uniqueSort=function(e){var t,r=[],i=0,o=0;if(E=!n.detectDuplicates,l=!n.sortStable&&e.slice(0),e.sort(S),E){while(t=e[o++])t===e[o]&&(i=r.push(o));while(i--)e.splice(r[i],1)}return e},o=ot.getText=function(e){var t,n="",r=0,i=e.nodeType;if(i){if(1===i||9===i||11===i){if("string"==typeof e.textContent)return e.textContent;for(e=e.firstChild;e;e=e.nextSibling)n+=o(e)}else if(3===i||4===i)return e.nodeValue}else for(;t=e[r];r++)n+=o(t);return n},i=ot.selectors={cacheLength:50,createPseudo:at,match:J,attrHandle:{},find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(e){return e[1]=e[1].replace(nt,rt),e[3]=(e[4]||e[5]||"").replace(nt,rt),"~="===e[2]&&(e[3]=" "+e[3]+" "),e.slice(0,4)},CHILD:function(e){return e[1]=e[1].toLowerCase(),"nth"===e[1].slice(0,3)?(e[3]||ot.error(e[0]),e[4]=+(e[4]?e[5]+(e[6]||1):2*("even"===e[3]||"odd"===e[3])),e[5]=+(e[7]+e[8]||"odd"===e[3])):e[3]&&ot.error(e[0]),e},PSEUDO:function(e){var t,n=!e[5]&&e[2];return J.CHILD.test(e[0])?null:(e[3]&&e[4]!==undefined?e[2]=e[4]:n&&V.test(n)&&(t=gt(n,!0))&&(t=n.indexOf(")",n.length-t)-n.length)&&(e[0]=e[0].slice(0,t),e[2]=n.slice(0,t)),e.slice(0,3))}},filter:{TAG:function(e){var t=e.replace(nt,rt).toLowerCase();return"*"===e?function(){return!0}:function(e){return e.nodeName&&e.nodeName.toLowerCase()===t}},CLASS:function(e){var t=C[e+" "];return t||(t=RegExp("(^|"+M+")"+e+"("+M+"|$)"))&&C(e,function(e){return t.test("string"==typeof e.className&&e.className||typeof e.getAttribute!==j&&e.getAttribute("class")||"")})},ATTR:function(e,t,n){return function(r){var i=ot.attr(r,e);return null==i?"!="===t:t?(i+="","="===t?i===n:"!="===t?i!==n:"^="===t?n&&0===i.indexOf(n):"*="===t?n&&i.indexOf(n)>-1:"$="===t?n&&i.slice(-n.length)===n:"~="===t?(" "+i+" ").indexOf(n)>-1:"|="===t?i===n||i.slice(0,n.length+1)===n+"-":!1):!0}},CHILD:function(e,t,n,r,i){var o="nth"!==e.slice(0,3),s="last"!==e.slice(-4),a="of-type"===t;return 1===r&&0===i?function(e){return!!e.parentNode}:function(t,n,u){var l,c,p,f,h,d,g=o!==s?"nextSibling":"previousSibling",m=t.parentNode,y=a&&t.nodeName.toLowerCase(),x=!u&&!a;if(m){if(o){while(g){p=t;while(p=p[g])if(a?p.nodeName.toLowerCase()===y:1===p.nodeType)return!1;d=g="only"===e&&!d&&"nextSibling"}return!0}if(d=[s?m.firstChild:m.lastChild],s&&x){c=m[v]||(m[v]={}),l=c[e]||[],h=l[0]===w&&l[1],f=l[0]===w&&l[2],p=h&&m.childNodes[h];while(p=++h&&p&&p[g]||(f=h=0)||d.pop())if(1===p.nodeType&&++f&&p===t){c[e]=[w,h,f];break}}else if(x&&(l=(t[v]||(t[v]={}))[e])&&l[0]===w)f=l[1];else while(p=++h&&p&&p[g]||(f=h=0)||d.pop())if((a?p.nodeName.toLowerCase()===y:1===p.nodeType)&&++f&&(x&&((p[v]||(p[v]={}))[e]=[w,f]),p===t))break;return f-=i,f===r||0===f%r&&f/r>=0}}},PSEUDO:function(e,t){var n,r=i.pseudos[e]||i.setFilters[e.toLowerCase()]||ot.error("unsupported pseudo: "+e);return r[v]?r(t):r.length>1?(n=[e,e,"",t],i.setFilters.hasOwnProperty(e.toLowerCase())?at(function(e,n){var i,o=r(e,t),s=o.length;while(s--)i=P.call(e,o[s]),e[i]=!(n[i]=o[s])}):function(e){return r(e,0,n)}):r}},pseudos:{not:at(function(e){var t=[],n=[],r=a(e.replace(z,"$1"));return r[v]?at(function(e,t,n,i){var o,s=r(e,null,i,[]),a=e.length;while(a--)(o=s[a])&&(e[a]=!(t[a]=o))}):function(e,i,o){return t[0]=e,r(t,null,o,n),!n.pop()}}),has:at(function(e){return function(t){return ot(e,t).length>0}}),contains:at(function(e){return function(t){return(t.textContent||t.innerText||o(t)).indexOf(e)>-1}}),lang:at(function(e){return G.test(e||"")||ot.error("unsupported lang: "+e),e=e.replace(nt,rt).toLowerCase(),function(t){var n;do if(n=h?t.lang:t.getAttribute("xml:lang")||t.getAttribute("lang"))return n=n.toLowerCase(),n===e||0===n.indexOf(e+"-");while((t=t.parentNode)&&1===t.nodeType);return!1}}),target:function(t){var n=e.location&&e.location.hash;return n&&n.slice(1)===t.id},root:function(e){return e===f},focus:function(e){return e===p.activeElement&&(!p.hasFocus||p.hasFocus())&&!!(e.type||e.href||~e.tabIndex)},enabled:function(e){return e.disabled===!1},disabled:function(e){return e.disabled===!0},checked:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&!!e.checked||"option"===t&&!!e.selected},selected:function(e){return e.parentNode&&e.parentNode.selectedIndex,e.selected===!0},empty:function(e){for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeName>"@"||3===e.nodeType||4===e.nodeType)return!1;return!0},parent:function(e){return!i.pseudos.empty(e)},header:function(e){return et.test(e.nodeName)},input:function(e){return Z.test(e.nodeName)},button:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&"button"===e.type||"button"===t},text:function(e){var t;return"input"===e.nodeName.toLowerCase()&&"text"===e.type&&(null==(t=e.getAttribute("type"))||t.toLowerCase()===e.type)},first:ht(function(){return[0]}),last:ht(function(e,t){return[t-1]}),eq:ht(function(e,t,n){return[0>n?n+t:n]}),even:ht(function(e,t){var n=0;for(;t>n;n+=2)e.push(n);return e}),odd:ht(function(e,t){var n=1;for(;t>n;n+=2)e.push(n);return e}),lt:ht(function(e,t,n){var r=0>n?n+t:n;for(;--r>=0;)e.push(r);return e}),gt:ht(function(e,t,n){var r=0>n?n+t:n;for(;t>++r;)e.push(r);return e})}},i.pseudos.nth=i.pseudos.eq;for(t in{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})i.pseudos[t]=pt(t);for(t in{submit:!0,reset:!0})i.pseudos[t]=ft(t);function dt(){}dt.prototype=i.filters=i.pseudos,i.setFilters=new dt;function gt(e,t){var n,r,o,s,a,u,l,c=k[e+" "];if(c)return t?0:c.slice(0);a=e,u=[],l=i.preFilter;while(a){(!n||(r=_.exec(a)))&&(r&&(a=a.slice(r[0].length)||a),u.push(o=[])),n=!1,(r=X.exec(a))&&(n=r.shift(),o.push({value:n,type:r[0].replace(z," ")}),a=a.slice(n.length));for(s in i.filter)!(r=J[s].exec(a))||l[s]&&!(r=l[s](r))||(n=r.shift(),o.push({value:n,type:s,matches:r}),a=a.slice(n.length));if(!n)break}return t?a.length:a?ot.error(e):k(e,u).slice(0)}function mt(e){var t=0,n=e.length,r="";for(;n>t;t++)r+=e[t].value;return r}function yt(e,t,n){var i=t.dir,o=n&&"parentNode"===i,s=T++;return t.first?function(t,n,r){while(t=t[i])if(1===t.nodeType||o)return e(t,n,r)}:function(t,n,a){var u,l,c,p=w+" "+s;if(a){while(t=t[i])if((1===t.nodeType||o)&&e(t,n,a))return!0}else while(t=t[i])if(1===t.nodeType||o)if(c=t[v]||(t[v]={}),(l=c[i])&&l[0]===p){if((u=l[1])===!0||u===r)return u===!0}else if(l=c[i]=[p],l[1]=e(t,n,a)||r,l[1]===!0)return!0}}function vt(e){return e.length>1?function(t,n,r){var i=e.length;while(i--)if(!e[i](t,n,r))return!1;return!0}:e[0]}function xt(e,t,n,r,i){var o,s=[],a=0,u=e.length,l=null!=t;for(;u>a;a++)(o=e[a])&&(!n||n(o,r,i))&&(s.push(o),l&&t.push(a));return s}function bt(e,t,n,r,i,o){return r&&!r[v]&&(r=bt(r)),i&&!i[v]&&(i=bt(i,o)),at(function(o,s,a,u){var l,c,p,f=[],h=[],d=s.length,g=o||Ct(t||"*",a.nodeType?[a]:a,[]),m=!e||!o&&t?g:xt(g,f,e,a,u),y=n?i||(o?e:d||r)?[]:s:m;if(n&&n(m,y,a,u),r){l=xt(y,h),r(l,[],a,u),c=l.length;while(c--)(p=l[c])&&(y[h[c]]=!(m[h[c]]=p))}if(o){if(i||e){if(i){l=[],c=y.length;while(c--)(p=y[c])&&l.push(m[c]=p);i(null,y=[],l,u)}c=y.length;while(c--)(p=y[c])&&(l=i?P.call(o,p):f[c])>-1&&(o[l]=!(s[l]=p))}}else y=xt(y===s?y.splice(d,y.length):y),i?i(null,s,y,u):O.apply(s,y)})}function wt(e){var t,n,r,o=e.length,s=i.relative[e[0].type],a=s||i.relative[" "],l=s?1:0,c=yt(function(e){return e===t},a,!0),p=yt(function(e){return P.call(t,e)>-1},a,!0),f=[function(e,n,r){return!s&&(r||n!==u)||((t=n).nodeType?c(e,n,r):p(e,n,r))}];for(;o>l;l++)if(n=i.relative[e[l].type])f=[yt(vt(f),n)];else{if(n=i.filter[e[l].type].apply(null,e[l].matches),n[v]){for(r=++l;o>r;r++)if(i.relative[e[r].type])break;return bt(l>1&&vt(f),l>1&&mt(e.slice(0,l-1).concat({value:" "===e[l-2].type?"*":""})).replace(z,"$1"),n,r>l&&wt(e.slice(l,r)),o>r&&wt(e=e.slice(r)),o>r&&mt(e))}f.push(n)}return vt(f)}function Tt(e,t){var n=0,o=t.length>0,s=e.length>0,a=function(a,l,c,f,h){var d,g,m,y=[],v=0,x="0",b=a&&[],T=null!=h,C=u,k=a||s&&i.find.TAG("*",h&&l.parentNode||l),N=w+=null==C?1:Math.random()||.1;for(T&&(u=l!==p&&l,r=n);null!=(d=k[x]);x++){if(s&&d){g=0;while(m=e[g++])if(m(d,l,c)){f.push(d);break}T&&(w=N,r=++n)}o&&((d=!m&&d)&&v--,a&&b.push(d))}if(v+=x,o&&x!==v){g=0;while(m=t[g++])m(b,y,l,c);if(a){if(v>0)while(x--)b[x]||y[x]||(y[x]=q.call(f));y=xt(y)}O.apply(f,y),T&&!a&&y.length>0&&v+t.length>1&&ot.uniqueSort(f)}return T&&(w=N,u=C),b};return o?at(a):a}a=ot.compile=function(e,t){var n,r=[],i=[],o=N[e+" "];if(!o){t||(t=gt(e)),n=t.length;while(n--)o=wt(t[n]),o[v]?r.push(o):i.push(o);o=N(e,Tt(i,r))}return o};function Ct(e,t,n){var r=0,i=t.length;for(;i>r;r++)ot(e,t[r],n);return n}function kt(e,t,r,o){var s,u,l,c,p,f=gt(e);if(!o&&1===f.length){if(u=f[0]=f[0].slice(0),u.length>2&&"ID"===(l=u[0]).type&&n.getById&&9===t.nodeType&&h&&i.relative[u[1].type]){if(t=(i.find.ID(l.matches[0].replace(nt,rt),t)||[])[0],!t)return r;e=e.slice(u.shift().value.length)}s=J.needsContext.test(e)?0:u.length;while(s--){if(l=u[s],i.relative[c=l.type])break;if((p=i.find[c])&&(o=p(l.matches[0].replace(nt,rt),U.test(u[0].type)&&t.parentNode||t))){if(u.splice(s,1),e=o.length&&mt(u),!e)return O.apply(r,o),r;break}}}return a(e,f)(o,t,!h,r,U.test(e)),r}n.sortStable=v.split("").sort(S).join("")===v,n.detectDuplicates=E,c(),n.sortDetached=ut(function(e){return 1&e.compareDocumentPosition(p.createElement("div"))}),ut(function(e){return e.innerHTML="<a href='#'></a>","#"===e.firstChild.getAttribute("href")})||lt("type|href|height|width",function(e,t,n){return n?undefined:e.getAttribute(t,"type"===t.toLowerCase()?1:2)}),n.attributes&&ut(function(e){return e.innerHTML="<input/>",e.firstChild.setAttribute("value",""),""===e.firstChild.getAttribute("value")})||lt("value",function(e,t,n){return n||"input"!==e.nodeName.toLowerCase()?undefined:e.defaultValue}),ut(function(e){return null==e.getAttribute("disabled")})||lt(R,function(e,t,n){var r;return n?undefined:(r=e.getAttributeNode(t))&&r.specified?r.value:e[t]===!0?t.toLowerCase():null}),x.find=ot,x.expr=ot.selectors,x.expr[":"]=x.expr.pseudos,x.unique=ot.uniqueSort,x.text=ot.getText,x.isXMLDoc=ot.isXML,x.contains=ot.contains}(e);var D={};function A(e){var t=D[e]={};return x.each(e.match(w)||[],function(e,n){t[n]=!0}),t}x.Callbacks=function(e){e="string"==typeof e?D[e]||A(e):x.extend({},e);var t,n,r,i,o,s,a=[],u=!e.once&&[],l=function(p){for(t=e.memory&&p,n=!0,s=i||0,i=0,o=a.length,r=!0;a&&o>s;s++)if(a[s].apply(p[0],p[1])===!1&&e.stopOnFalse){t=!1;break}r=!1,a&&(u?u.length&&l(u.shift()):t?a=[]:c.disable())},c={add:function(){if(a){var n=a.length;(function s(t){x.each(t,function(t,n){var r=x.type(n);"function"===r?e.unique&&c.has(n)||a.push(n):n&&n.length&&"string"!==r&&s(n)})})(arguments),r?o=a.length:t&&(i=n,l(t))}return this},remove:function(){return a&&x.each(arguments,function(e,t){var n;while((n=x.inArray(t,a,n))>-1)a.splice(n,1),r&&(o>=n&&o--,s>=n&&s--)}),this},has:function(e){return e?x.inArray(e,a)>-1:!(!a||!a.length)},empty:function(){return a=[],o=0,this},disable:function(){return a=u=t=undefined,this},disabled:function(){return!a},lock:function(){return u=undefined,t||c.disable(),this},locked:function(){return!u},fireWith:function(e,t){return!a||n&&!u||(t=t||[],t=[e,t.slice?t.slice():t],r?u.push(t):l(t)),this},fire:function(){return c.fireWith(this,arguments),this},fired:function(){return!!n}};return c},x.extend({Deferred:function(e){var t=[["resolve","done",x.Callbacks("once memory"),"resolved"],["reject","fail",x.Callbacks("once memory"),"rejected"],["notify","progress",x.Callbacks("memory")]],n="pending",r={state:function(){return n},always:function(){return i.done(arguments).fail(arguments),this},then:function(){var e=arguments;return x.Deferred(function(n){x.each(t,function(t,o){var s=o[0],a=x.isFunction(e[t])&&e[t];i[o[1]](function(){var e=a&&a.apply(this,arguments);e&&x.isFunction(e.promise)?e.promise().done(n.resolve).fail(n.reject).progress(n.notify):n[s+"With"](this===r?n.promise():this,a?[e]:arguments)})}),e=null}).promise()},promise:function(e){return null!=e?x.extend(e,r):r}},i={};return r.pipe=r.then,x.each(t,function(e,o){var s=o[2],a=o[3];r[o[1]]=s.add,a&&s.add(function(){n=a},t[1^e][2].disable,t[2][2].lock),i[o[0]]=function(){return i[o[0]+"With"](this===i?r:this,arguments),this},i[o[0]+"With"]=s.fireWith}),r.promise(i),e&&e.call(i,i),i},when:function(e){var t=0,n=d.call(arguments),r=n.length,i=1!==r||e&&x.isFunction(e.promise)?r:0,o=1===i?e:x.Deferred(),s=function(e,t,n){return function(r){t[e]=this,n[e]=arguments.length>1?d.call(arguments):r,n===a?o.notifyWith(t,n):--i||o.resolveWith(t,n)}},a,u,l;if(r>1)for(a=Array(r),u=Array(r),l=Array(r);r>t;t++)n[t]&&x.isFunction(n[t].promise)?n[t].promise().done(s(t,l,n)).fail(o.reject).progress(s(t,u,a)):--i;return i||o.resolveWith(l,n),o.promise()}}),x.support=function(t){var n=o.createElement("input"),r=o.createDocumentFragment(),i=o.createElement("div"),s=o.createElement("select"),a=s.appendChild(o.createElement("option"));return n.type?(n.type="checkbox",t.checkOn=""!==n.value,t.optSelected=a.selected,t.reliableMarginRight=!0,t.boxSizingReliable=!0,t.pixelPosition=!1,n.checked=!0,t.noCloneChecked=n.cloneNode(!0).checked,s.disabled=!0,t.optDisabled=!a.disabled,n=o.createElement("input"),n.value="t",n.type="radio",t.radioValue="t"===n.value,n.setAttribute("checked","t"),n.setAttribute("name","t"),r.appendChild(n),t.checkClone=r.cloneNode(!0).cloneNode(!0).lastChild.checked,t.focusinBubbles="onfocusin"in e,i.style.backgroundClip="content-box",i.cloneNode(!0).style.backgroundClip="",t.clearCloneStyle="content-box"===i.style.backgroundClip,x(function(){var n,r,s="padding:0;margin:0;border:0;display:block;-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box",a=o.getElementsByTagName("body")[0];a&&(n=o.createElement("div"),n.style.cssText="border:0;width:0;height:0;position:absolute;top:0;left:-9999px;margin-top:1px",a.appendChild(n).appendChild(i),i.innerHTML="",i.style.cssText="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;padding:1px;border:1px;display:block;width:4px;margin-top:1%;position:absolute;top:1%",x.swap(a,null!=a.style.zoom?{zoom:1}:{},function(){t.boxSizing=4===i.offsetWidth}),e.getComputedStyle&&(t.pixelPosition="1%"!==(e.getComputedStyle(i,null)||{}).top,t.boxSizingReliable="4px"===(e.getComputedStyle(i,null)||{width:"4px"}).width,r=i.appendChild(o.createElement("div")),r.style.cssText=i.style.cssText=s,r.style.marginRight=r.style.width="0",i.style.width="1px",t.reliableMarginRight=!parseFloat((e.getComputedStyle(r,null)||{}).marginRight)),a.removeChild(n))}),t):t}({});var L,q,H=/(?:\{[\s\S]*\}|\[[\s\S]*\])$/,O=/([A-Z])/g;function F(){Object.defineProperty(this.cache={},0,{get:function(){return{}}}),this.expando=x.expando+Math.random()}F.uid=1,F.accepts=function(e){return e.nodeType?1===e.nodeType||9===e.nodeType:!0},F.prototype={key:function(e){if(!F.accepts(e))return 0;var t={},n=e[this.expando];if(!n){n=F.uid++;try{t[this.expando]={value:n},Object.defineProperties(e,t)}catch(r){t[this.expando]=n,x.extend(e,t)}}return this.cache[n]||(this.cache[n]={}),n},set:function(e,t,n){var r,i=this.key(e),o=this.cache[i];if("string"==typeof t)o[t]=n;else if(x.isEmptyObject(o))x.extend(this.cache[i],t);else for(r in t)o[r]=t[r];return o},get:function(e,t){var n=this.cache[this.key(e)];return t===undefined?n:n[t]},access:function(e,t,n){var r;return t===undefined||t&&"string"==typeof t&&n===undefined?(r=this.get(e,t),r!==undefined?r:this.get(e,x.camelCase(t))):(this.set(e,t,n),n!==undefined?n:t)},remove:function(e,t){var n,r,i,o=this.key(e),s=this.cache[o];if(t===undefined)this.cache[o]={};else{x.isArray(t)?r=t.concat(t.map(x.camelCase)):(i=x.camelCase(t),t in s?r=[t,i]:(r=i,r=r in s?[r]:r.match(w)||[])),n=r.length;while(n--)delete s[r[n]]}},hasData:function(e){return!x.isEmptyObject(this.cache[e[this.expando]]||{})},discard:function(e){e[this.expando]&&delete this.cache[e[this.expando]]}},L=new F,q=new F,x.extend({acceptData:F.accepts,hasData:function(e){return L.hasData(e)||q.hasData(e)},data:function(e,t,n){return L.access(e,t,n)},removeData:function(e,t){L.remove(e,t)},_data:function(e,t,n){return q.access(e,t,n)},_removeData:function(e,t){q.remove(e,t)}}),x.fn.extend({data:function(e,t){var n,r,i=this[0],o=0,s=null;if(e===undefined){if(this.length&&(s=L.get(i),1===i.nodeType&&!q.get(i,"hasDataAttrs"))){for(n=i.attributes;n.length>o;o++)r=n[o].name,0===r.indexOf("data-")&&(r=x.camelCase(r.slice(5)),P(i,r,s[r]));q.set(i,"hasDataAttrs",!0)}return s}return"object"==typeof e?this.each(function(){L.set(this,e)}):x.access(this,function(t){var n,r=x.camelCase(e);if(i&&t===undefined){if(n=L.get(i,e),n!==undefined)return n;if(n=L.get(i,r),n!==undefined)return n;if(n=P(i,r,undefined),n!==undefined)return n}else this.each(function(){var n=L.get(this,r);L.set(this,r,t),-1!==e.indexOf("-")&&n!==undefined&&L.set(this,e,t)})},null,t,arguments.length>1,null,!0)},removeData:function(e){return this.each(function(){L.remove(this,e)})}});function P(e,t,n){var r;if(n===undefined&&1===e.nodeType)if(r="data-"+t.replace(O,"-$1").toLowerCase(),n=e.getAttribute(r),"string"==typeof n){try{n="true"===n?!0:"false"===n?!1:"null"===n?null:+n+""===n?+n:H.test(n)?JSON.parse(n):n}catch(i){}L.set(e,t,n)}else n=undefined;return n}x.extend({queue:function(e,t,n){var r;return e?(t=(t||"fx")+"queue",r=q.get(e,t),n&&(!r||x.isArray(n)?r=q.access(e,t,x.makeArray(n)):r.push(n)),r||[]):undefined},dequeue:function(e,t){t=t||"fx";var n=x.queue(e,t),r=n.length,i=n.shift(),o=x._queueHooks(e,t),s=function(){x.dequeue(e,t)
    };"inprogress"===i&&(i=n.shift(),r--),i&&("fx"===t&&n.unshift("inprogress"),delete o.stop,i.call(e,s,o)),!r&&o&&o.empty.fire()},_queueHooks:function(e,t){var n=t+"queueHooks";return q.get(e,n)||q.access(e,n,{empty:x.Callbacks("once memory").add(function(){q.remove(e,[t+"queue",n])})})}}),x.fn.extend({queue:function(e,t){var n=2;return"string"!=typeof e&&(t=e,e="fx",n--),n>arguments.length?x.queue(this[0],e):t===undefined?this:this.each(function(){var n=x.queue(this,e,t);x._queueHooks(this,e),"fx"===e&&"inprogress"!==n[0]&&x.dequeue(this,e)})},dequeue:function(e){return this.each(function(){x.dequeue(this,e)})},delay:function(e,t){return e=x.fx?x.fx.speeds[e]||e:e,t=t||"fx",this.queue(t,function(t,n){var r=setTimeout(t,e);n.stop=function(){clearTimeout(r)}})},clearQueue:function(e){return this.queue(e||"fx",[])},promise:function(e,t){var n,r=1,i=x.Deferred(),o=this,s=this.length,a=function(){--r||i.resolveWith(o,[o])};"string"!=typeof e&&(t=e,e=undefined),e=e||"fx";while(s--)n=q.get(o[s],e+"queueHooks"),n&&n.empty&&(r++,n.empty.add(a));return a(),i.promise(t)}});var R,M,W=/[\t\r\n\f]/g,$=/\r/g,B=/^(?:input|select|textarea|button)$/i;x.fn.extend({attr:function(e,t){return x.access(this,x.attr,e,t,arguments.length>1)},removeAttr:function(e){return this.each(function(){x.removeAttr(this,e)})},prop:function(e,t){return x.access(this,x.prop,e,t,arguments.length>1)},removeProp:function(e){return this.each(function(){delete this[x.propFix[e]||e]})},addClass:function(e){var t,n,r,i,o,s=0,a=this.length,u="string"==typeof e&&e;if(x.isFunction(e))return this.each(function(t){x(this).addClass(e.call(this,t,this.className))});if(u)for(t=(e||"").match(w)||[];a>s;s++)if(n=this[s],r=1===n.nodeType&&(n.className?(" "+n.className+" ").replace(W," "):" ")){o=0;while(i=t[o++])0>r.indexOf(" "+i+" ")&&(r+=i+" ");n.className=x.trim(r)}return this},removeClass:function(e){var t,n,r,i,o,s=0,a=this.length,u=0===arguments.length||"string"==typeof e&&e;if(x.isFunction(e))return this.each(function(t){x(this).removeClass(e.call(this,t,this.className))});if(u)for(t=(e||"").match(w)||[];a>s;s++)if(n=this[s],r=1===n.nodeType&&(n.className?(" "+n.className+" ").replace(W," "):"")){o=0;while(i=t[o++])while(r.indexOf(" "+i+" ")>=0)r=r.replace(" "+i+" "," ");n.className=e?x.trim(r):""}return this},toggleClass:function(e,t){var n=typeof e;return"boolean"==typeof t&&"string"===n?t?this.addClass(e):this.removeClass(e):x.isFunction(e)?this.each(function(n){x(this).toggleClass(e.call(this,n,this.className,t),t)}):this.each(function(){if("string"===n){var t,i=0,o=x(this),s=e.match(w)||[];while(t=s[i++])o.hasClass(t)?o.removeClass(t):o.addClass(t)}else(n===r||"boolean"===n)&&(this.className&&q.set(this,"__className__",this.className),this.className=this.className||e===!1?"":q.get(this,"__className__")||"")})},hasClass:function(e){var t=" "+e+" ",n=0,r=this.length;for(;r>n;n++)if(1===this[n].nodeType&&(" "+this[n].className+" ").replace(W," ").indexOf(t)>=0)return!0;return!1},val:function(e){var t,n,r,i=this[0];{if(arguments.length)return r=x.isFunction(e),this.each(function(n){var i;1===this.nodeType&&(i=r?e.call(this,n,x(this).val()):e,null==i?i="":"number"==typeof i?i+="":x.isArray(i)&&(i=x.map(i,function(e){return null==e?"":e+""})),t=x.valHooks[this.type]||x.valHooks[this.nodeName.toLowerCase()],t&&"set"in t&&t.set(this,i,"value")!==undefined||(this.value=i))});if(i)return t=x.valHooks[i.type]||x.valHooks[i.nodeName.toLowerCase()],t&&"get"in t&&(n=t.get(i,"value"))!==undefined?n:(n=i.value,"string"==typeof n?n.replace($,""):null==n?"":n)}}}),x.extend({valHooks:{option:{get:function(e){var t=e.attributes.value;return!t||t.specified?e.value:e.text}},select:{get:function(e){var t,n,r=e.options,i=e.selectedIndex,o="select-one"===e.type||0>i,s=o?null:[],a=o?i+1:r.length,u=0>i?a:o?i:0;for(;a>u;u++)if(n=r[u],!(!n.selected&&u!==i||(x.support.optDisabled?n.disabled:null!==n.getAttribute("disabled"))||n.parentNode.disabled&&x.nodeName(n.parentNode,"optgroup"))){if(t=x(n).val(),o)return t;s.push(t)}return s},set:function(e,t){var n,r,i=e.options,o=x.makeArray(t),s=i.length;while(s--)r=i[s],(r.selected=x.inArray(x(r).val(),o)>=0)&&(n=!0);return n||(e.selectedIndex=-1),o}}},attr:function(e,t,n){var i,o,s=e.nodeType;if(e&&3!==s&&8!==s&&2!==s)return typeof e.getAttribute===r?x.prop(e,t,n):(1===s&&x.isXMLDoc(e)||(t=t.toLowerCase(),i=x.attrHooks[t]||(x.expr.match.bool.test(t)?M:R)),n===undefined?i&&"get"in i&&null!==(o=i.get(e,t))?o:(o=x.find.attr(e,t),null==o?undefined:o):null!==n?i&&"set"in i&&(o=i.set(e,n,t))!==undefined?o:(e.setAttribute(t,n+""),n):(x.removeAttr(e,t),undefined))},removeAttr:function(e,t){var n,r,i=0,o=t&&t.match(w);if(o&&1===e.nodeType)while(n=o[i++])r=x.propFix[n]||n,x.expr.match.bool.test(n)&&(e[r]=!1),e.removeAttribute(n)},attrHooks:{type:{set:function(e,t){if(!x.support.radioValue&&"radio"===t&&x.nodeName(e,"input")){var n=e.value;return e.setAttribute("type",t),n&&(e.value=n),t}}}},propFix:{"for":"htmlFor","class":"className"},prop:function(e,t,n){var r,i,o,s=e.nodeType;if(e&&3!==s&&8!==s&&2!==s)return o=1!==s||!x.isXMLDoc(e),o&&(t=x.propFix[t]||t,i=x.propHooks[t]),n!==undefined?i&&"set"in i&&(r=i.set(e,n,t))!==undefined?r:e[t]=n:i&&"get"in i&&null!==(r=i.get(e,t))?r:e[t]},propHooks:{tabIndex:{get:function(e){return e.hasAttribute("tabindex")||B.test(e.nodeName)||e.href?e.tabIndex:-1}}}}),M={set:function(e,t,n){return t===!1?x.removeAttr(e,n):e.setAttribute(n,n),n}},x.each(x.expr.match.bool.source.match(/\w+/g),function(e,t){var n=x.expr.attrHandle[t]||x.find.attr;x.expr.attrHandle[t]=function(e,t,r){var i=x.expr.attrHandle[t],o=r?undefined:(x.expr.attrHandle[t]=undefined)!=n(e,t,r)?t.toLowerCase():null;return x.expr.attrHandle[t]=i,o}}),x.support.optSelected||(x.propHooks.selected={get:function(e){var t=e.parentNode;return t&&t.parentNode&&t.parentNode.selectedIndex,null}}),x.each(["tabIndex","readOnly","maxLength","cellSpacing","cellPadding","rowSpan","colSpan","useMap","frameBorder","contentEditable"],function(){x.propFix[this.toLowerCase()]=this}),x.each(["radio","checkbox"],function(){x.valHooks[this]={set:function(e,t){return x.isArray(t)?e.checked=x.inArray(x(e).val(),t)>=0:undefined}},x.support.checkOn||(x.valHooks[this].get=function(e){return null===e.getAttribute("value")?"on":e.value})});var I=/^key/,z=/^(?:mouse|contextmenu)|click/,_=/^(?:focusinfocus|focusoutblur)$/,X=/^([^.]*)(?:\.(.+)|)$/;function U(){return!0}function Y(){return!1}function V(){try{return o.activeElement}catch(e){}}x.event={global:{},add:function(e,t,n,i,o){var s,a,u,l,c,p,f,h,d,g,m,y=q.get(e);if(y){n.handler&&(s=n,n=s.handler,o=s.selector),n.guid||(n.guid=x.guid++),(l=y.events)||(l=y.events={}),(a=y.handle)||(a=y.handle=function(e){return typeof x===r||e&&x.event.triggered===e.type?undefined:x.event.dispatch.apply(a.elem,arguments)},a.elem=e),t=(t||"").match(w)||[""],c=t.length;while(c--)u=X.exec(t[c])||[],d=m=u[1],g=(u[2]||"").split(".").sort(),d&&(f=x.event.special[d]||{},d=(o?f.delegateType:f.bindType)||d,f=x.event.special[d]||{},p=x.extend({type:d,origType:m,data:i,handler:n,guid:n.guid,selector:o,needsContext:o&&x.expr.match.needsContext.test(o),namespace:g.join(".")},s),(h=l[d])||(h=l[d]=[],h.delegateCount=0,f.setup&&f.setup.call(e,i,g,a)!==!1||e.addEventListener&&e.addEventListener(d,a,!1)),f.add&&(f.add.call(e,p),p.handler.guid||(p.handler.guid=n.guid)),o?h.splice(h.delegateCount++,0,p):h.push(p),x.event.global[d]=!0);e=null}},remove:function(e,t,n,r,i){var o,s,a,u,l,c,p,f,h,d,g,m=q.hasData(e)&&q.get(e);if(m&&(u=m.events)){t=(t||"").match(w)||[""],l=t.length;while(l--)if(a=X.exec(t[l])||[],h=g=a[1],d=(a[2]||"").split(".").sort(),h){p=x.event.special[h]||{},h=(r?p.delegateType:p.bindType)||h,f=u[h]||[],a=a[2]&&RegExp("(^|\\.)"+d.join("\\.(?:.*\\.|)")+"(\\.|$)"),s=o=f.length;while(o--)c=f[o],!i&&g!==c.origType||n&&n.guid!==c.guid||a&&!a.test(c.namespace)||r&&r!==c.selector&&("**"!==r||!c.selector)||(f.splice(o,1),c.selector&&f.delegateCount--,p.remove&&p.remove.call(e,c));s&&!f.length&&(p.teardown&&p.teardown.call(e,d,m.handle)!==!1||x.removeEvent(e,h,m.handle),delete u[h])}else for(h in u)x.event.remove(e,h+t[l],n,r,!0);x.isEmptyObject(u)&&(delete m.handle,q.remove(e,"events"))}},trigger:function(t,n,r,i){var s,a,u,l,c,p,f,h=[r||o],d=y.call(t,"type")?t.type:t,g=y.call(t,"namespace")?t.namespace.split("."):[];if(a=u=r=r||o,3!==r.nodeType&&8!==r.nodeType&&!_.test(d+x.event.triggered)&&(d.indexOf(".")>=0&&(g=d.split("."),d=g.shift(),g.sort()),c=0>d.indexOf(":")&&"on"+d,t=t[x.expando]?t:new x.Event(d,"object"==typeof t&&t),t.isTrigger=i?2:3,t.namespace=g.join("."),t.namespace_re=t.namespace?RegExp("(^|\\.)"+g.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,t.result=undefined,t.target||(t.target=r),n=null==n?[t]:x.makeArray(n,[t]),f=x.event.special[d]||{},i||!f.trigger||f.trigger.apply(r,n)!==!1)){if(!i&&!f.noBubble&&!x.isWindow(r)){for(l=f.delegateType||d,_.test(l+d)||(a=a.parentNode);a;a=a.parentNode)h.push(a),u=a;u===(r.ownerDocument||o)&&h.push(u.defaultView||u.parentWindow||e)}s=0;while((a=h[s++])&&!t.isPropagationStopped())t.type=s>1?l:f.bindType||d,p=(q.get(a,"events")||{})[t.type]&&q.get(a,"handle"),p&&p.apply(a,n),p=c&&a[c],p&&x.acceptData(a)&&p.apply&&p.apply(a,n)===!1&&t.preventDefault();return t.type=d,i||t.isDefaultPrevented()||f._default&&f._default.apply(h.pop(),n)!==!1||!x.acceptData(r)||c&&x.isFunction(r[d])&&!x.isWindow(r)&&(u=r[c],u&&(r[c]=null),x.event.triggered=d,r[d](),x.event.triggered=undefined,u&&(r[c]=u)),t.result}},dispatch:function(e){e=x.event.fix(e);var t,n,r,i,o,s=[],a=d.call(arguments),u=(q.get(this,"events")||{})[e.type]||[],l=x.event.special[e.type]||{};if(a[0]=e,e.delegateTarget=this,!l.preDispatch||l.preDispatch.call(this,e)!==!1){s=x.event.handlers.call(this,e,u),t=0;while((i=s[t++])&&!e.isPropagationStopped()){e.currentTarget=i.elem,n=0;while((o=i.handlers[n++])&&!e.isImmediatePropagationStopped())(!e.namespace_re||e.namespace_re.test(o.namespace))&&(e.handleObj=o,e.data=o.data,r=((x.event.special[o.origType]||{}).handle||o.handler).apply(i.elem,a),r!==undefined&&(e.result=r)===!1&&(e.preventDefault(),e.stopPropagation()))}return l.postDispatch&&l.postDispatch.call(this,e),e.result}},handlers:function(e,t){var n,r,i,o,s=[],a=t.delegateCount,u=e.target;if(a&&u.nodeType&&(!e.button||"click"!==e.type))for(;u!==this;u=u.parentNode||this)if(u.disabled!==!0||"click"!==e.type){for(r=[],n=0;a>n;n++)o=t[n],i=o.selector+" ",r[i]===undefined&&(r[i]=o.needsContext?x(i,this).index(u)>=0:x.find(i,this,null,[u]).length),r[i]&&r.push(o);r.length&&s.push({elem:u,handlers:r})}return t.length>a&&s.push({elem:this,handlers:t.slice(a)}),s},props:"altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),fixHooks:{},keyHooks:{props:"char charCode key keyCode".split(" "),filter:function(e,t){return null==e.which&&(e.which=null!=t.charCode?t.charCode:t.keyCode),e}},mouseHooks:{props:"button buttons clientX clientY offsetX offsetY pageX pageY screenX screenY toElement".split(" "),filter:function(e,t){var n,r,i,s=t.button;return null==e.pageX&&null!=t.clientX&&(n=e.target.ownerDocument||o,r=n.documentElement,i=n.body,e.pageX=t.clientX+(r&&r.scrollLeft||i&&i.scrollLeft||0)-(r&&r.clientLeft||i&&i.clientLeft||0),e.pageY=t.clientY+(r&&r.scrollTop||i&&i.scrollTop||0)-(r&&r.clientTop||i&&i.clientTop||0)),e.which||s===undefined||(e.which=1&s?1:2&s?3:4&s?2:0),e}},fix:function(e){if(e[x.expando])return e;var t,n,r,i=e.type,s=e,a=this.fixHooks[i];a||(this.fixHooks[i]=a=z.test(i)?this.mouseHooks:I.test(i)?this.keyHooks:{}),r=a.props?this.props.concat(a.props):this.props,e=new x.Event(s),t=r.length;while(t--)n=r[t],e[n]=s[n];return e.target||(e.target=o),3===e.target.nodeType&&(e.target=e.target.parentNode),a.filter?a.filter(e,s):e},special:{load:{noBubble:!0},focus:{trigger:function(){return this!==V()&&this.focus?(this.focus(),!1):undefined},delegateType:"focusin"},blur:{trigger:function(){return this===V()&&this.blur?(this.blur(),!1):undefined},delegateType:"focusout"},click:{trigger:function(){return"checkbox"===this.type&&this.click&&x.nodeName(this,"input")?(this.click(),!1):undefined},_default:function(e){return x.nodeName(e.target,"a")}},beforeunload:{postDispatch:function(e){e.result!==undefined&&(e.originalEvent.returnValue=e.result)}}},simulate:function(e,t,n,r){var i=x.extend(new x.Event,n,{type:e,isSimulated:!0,originalEvent:{}});r?x.event.trigger(i,null,t):x.event.dispatch.call(t,i),i.isDefaultPrevented()&&n.preventDefault()}},x.removeEvent=function(e,t,n){e.removeEventListener&&e.removeEventListener(t,n,!1)},x.Event=function(e,t){return this instanceof x.Event?(e&&e.type?(this.originalEvent=e,this.type=e.type,this.isDefaultPrevented=e.defaultPrevented||e.getPreventDefault&&e.getPreventDefault()?U:Y):this.type=e,t&&x.extend(this,t),this.timeStamp=e&&e.timeStamp||x.now(),this[x.expando]=!0,undefined):new x.Event(e,t)},x.Event.prototype={isDefaultPrevented:Y,isPropagationStopped:Y,isImmediatePropagationStopped:Y,preventDefault:function(){var e=this.originalEvent;this.isDefaultPrevented=U,e&&e.preventDefault&&e.preventDefault()},stopPropagation:function(){var e=this.originalEvent;this.isPropagationStopped=U,e&&e.stopPropagation&&e.stopPropagation()},stopImmediatePropagation:function(){this.isImmediatePropagationStopped=U,this.stopPropagation()}},x.each({mouseenter:"mouseover",mouseleave:"mouseout"},function(e,t){x.event.special[e]={delegateType:t,bindType:t,handle:function(e){var n,r=this,i=e.relatedTarget,o=e.handleObj;return(!i||i!==r&&!x.contains(r,i))&&(e.type=o.origType,n=o.handler.apply(this,arguments),e.type=t),n}}}),x.support.focusinBubbles||x.each({focus:"focusin",blur:"focusout"},function(e,t){var n=0,r=function(e){x.event.simulate(t,e.target,x.event.fix(e),!0)};x.event.special[t]={setup:function(){0===n++&&o.addEventListener(e,r,!0)},teardown:function(){0===--n&&o.removeEventListener(e,r,!0)}}}),x.fn.extend({on:function(e,t,n,r,i){var o,s;if("object"==typeof e){"string"!=typeof t&&(n=n||t,t=undefined);for(s in e)this.on(s,t,n,e[s],i);return this}if(null==n&&null==r?(r=t,n=t=undefined):null==r&&("string"==typeof t?(r=n,n=undefined):(r=n,n=t,t=undefined)),r===!1)r=Y;else if(!r)return this;return 1===i&&(o=r,r=function(e){return x().off(e),o.apply(this,arguments)},r.guid=o.guid||(o.guid=x.guid++)),this.each(function(){x.event.add(this,e,r,n,t)})},one:function(e,t,n,r){return this.on(e,t,n,r,1)},off:function(e,t,n){var r,i;if(e&&e.preventDefault&&e.handleObj)return r=e.handleObj,x(e.delegateTarget).off(r.namespace?r.origType+"."+r.namespace:r.origType,r.selector,r.handler),this;if("object"==typeof e){for(i in e)this.off(i,t,e[i]);return this}return(t===!1||"function"==typeof t)&&(n=t,t=undefined),n===!1&&(n=Y),this.each(function(){x.event.remove(this,e,n,t)})},trigger:function(e,t){return this.each(function(){x.event.trigger(e,t,this)})},triggerHandler:function(e,t){var n=this[0];return n?x.event.trigger(e,t,n,!0):undefined}});var G=/^.[^:#\[\.,]*$/,J=/^(?:parents|prev(?:Until|All))/,Q=x.expr.match.needsContext,K={children:!0,contents:!0,next:!0,prev:!0};x.fn.extend({find:function(e){var t,n=[],r=this,i=r.length;if("string"!=typeof e)return this.pushStack(x(e).filter(function(){for(t=0;i>t;t++)if(x.contains(r[t],this))return!0}));for(t=0;i>t;t++)x.find(e,r[t],n);return n=this.pushStack(i>1?x.unique(n):n),n.selector=this.selector?this.selector+" "+e:e,n},has:function(e){var t=x(e,this),n=t.length;return this.filter(function(){var e=0;for(;n>e;e++)if(x.contains(this,t[e]))return!0})},not:function(e){return this.pushStack(et(this,e||[],!0))},filter:function(e){return this.pushStack(et(this,e||[],!1))},is:function(e){return!!et(this,"string"==typeof e&&Q.test(e)?x(e):e||[],!1).length},closest:function(e,t){var n,r=0,i=this.length,o=[],s=Q.test(e)||"string"!=typeof e?x(e,t||this.context):0;for(;i>r;r++)for(n=this[r];n&&n!==t;n=n.parentNode)if(11>n.nodeType&&(s?s.index(n)>-1:1===n.nodeType&&x.find.matchesSelector(n,e))){n=o.push(n);break}return this.pushStack(o.length>1?x.unique(o):o)},index:function(e){return e?"string"==typeof e?g.call(x(e),this[0]):g.call(this,e.jquery?e[0]:e):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(e,t){var n="string"==typeof e?x(e,t):x.makeArray(e&&e.nodeType?[e]:e),r=x.merge(this.get(),n);return this.pushStack(x.unique(r))},addBack:function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}});function Z(e,t){while((e=e[t])&&1!==e.nodeType);return e}x.each({parent:function(e){var t=e.parentNode;return t&&11!==t.nodeType?t:null},parents:function(e){return x.dir(e,"parentNode")},parentsUntil:function(e,t,n){return x.dir(e,"parentNode",n)},next:function(e){return Z(e,"nextSibling")},prev:function(e){return Z(e,"previousSibling")},nextAll:function(e){return x.dir(e,"nextSibling")},prevAll:function(e){return x.dir(e,"previousSibling")},nextUntil:function(e,t,n){return x.dir(e,"nextSibling",n)},prevUntil:function(e,t,n){return x.dir(e,"previousSibling",n)},siblings:function(e){return x.sibling((e.parentNode||{}).firstChild,e)},children:function(e){return x.sibling(e.firstChild)},contents:function(e){return e.contentDocument||x.merge([],e.childNodes)}},function(e,t){x.fn[e]=function(n,r){var i=x.map(this,t,n);return"Until"!==e.slice(-5)&&(r=n),r&&"string"==typeof r&&(i=x.filter(r,i)),this.length>1&&(K[e]||x.unique(i),J.test(e)&&i.reverse()),this.pushStack(i)}}),x.extend({filter:function(e,t,n){var r=t[0];return n&&(e=":not("+e+")"),1===t.length&&1===r.nodeType?x.find.matchesSelector(r,e)?[r]:[]:x.find.matches(e,x.grep(t,function(e){return 1===e.nodeType}))},dir:function(e,t,n){var r=[],i=n!==undefined;while((e=e[t])&&9!==e.nodeType)if(1===e.nodeType){if(i&&x(e).is(n))break;r.push(e)}return r},sibling:function(e,t){var n=[];for(;e;e=e.nextSibling)1===e.nodeType&&e!==t&&n.push(e);return n}});function et(e,t,n){if(x.isFunction(t))return x.grep(e,function(e,r){return!!t.call(e,r,e)!==n});if(t.nodeType)return x.grep(e,function(e){return e===t!==n});if("string"==typeof t){if(G.test(t))return x.filter(t,e,n);t=x.filter(t,e)}return x.grep(e,function(e){return g.call(t,e)>=0!==n})}var tt=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,nt=/<([\w:]+)/,rt=/<|&#?\w+;/,it=/<(?:script|style|link)/i,ot=/^(?:checkbox|radio)$/i,st=/checked\s*(?:[^=]|=\s*.checked.)/i,at=/^$|\/(?:java|ecma)script/i,ut=/^true\/(.*)/,lt=/^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g,ct={option:[1,"<select multiple='multiple'>","</select>"],thead:[1,"<table>","</table>"],col:[2,"<table><colgroup>","</colgroup></table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:[0,"",""]};ct.optgroup=ct.option,ct.tbody=ct.tfoot=ct.colgroup=ct.caption=ct.thead,ct.th=ct.td,x.fn.extend({text:function(e){return x.access(this,function(e){return e===undefined?x.text(this):this.empty().append((this[0]&&this[0].ownerDocument||o).createTextNode(e))},null,e,arguments.length)},append:function(){return this.domManip(arguments,function(e){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var t=pt(this,e);t.appendChild(e)}})},prepend:function(){return this.domManip(arguments,function(e){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var t=pt(this,e);t.insertBefore(e,t.firstChild)}})},before:function(){return this.domManip(arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this)})},after:function(){return this.domManip(arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this.nextSibling)})},remove:function(e,t){var n,r=e?x.filter(e,this):this,i=0;for(;null!=(n=r[i]);i++)t||1!==n.nodeType||x.cleanData(mt(n)),n.parentNode&&(t&&x.contains(n.ownerDocument,n)&&dt(mt(n,"script")),n.parentNode.removeChild(n));return this},empty:function(){var e,t=0;for(;null!=(e=this[t]);t++)1===e.nodeType&&(x.cleanData(mt(e,!1)),e.textContent="");return this},clone:function(e,t){return e=null==e?!1:e,t=null==t?e:t,this.map(function(){return x.clone(this,e,t)})},html:function(e){return x.access(this,function(e){var t=this[0]||{},n=0,r=this.length;if(e===undefined&&1===t.nodeType)return t.innerHTML;if("string"==typeof e&&!it.test(e)&&!ct[(nt.exec(e)||["",""])[1].toLowerCase()]){e=e.replace(tt,"<$1></$2>");try{for(;r>n;n++)t=this[n]||{},1===t.nodeType&&(x.cleanData(mt(t,!1)),t.innerHTML=e);t=0}catch(i){}}t&&this.empty().append(e)},null,e,arguments.length)},replaceWith:function(){var e=x.map(this,function(e){return[e.nextSibling,e.parentNode]}),t=0;return this.domManip(arguments,function(n){var r=e[t++],i=e[t++];i&&(r&&r.parentNode!==i&&(r=this.nextSibling),x(this).remove(),i.insertBefore(n,r))},!0),t?this:this.remove()},detach:function(e){return this.remove(e,!0)},domManip:function(e,t,n){e=f.apply([],e);var r,i,o,s,a,u,l=0,c=this.length,p=this,h=c-1,d=e[0],g=x.isFunction(d);if(g||!(1>=c||"string"!=typeof d||x.support.checkClone)&&st.test(d))return this.each(function(r){var i=p.eq(r);g&&(e[0]=d.call(this,r,i.html())),i.domManip(e,t,n)});if(c&&(r=x.buildFragment(e,this[0].ownerDocument,!1,!n&&this),i=r.firstChild,1===r.childNodes.length&&(r=i),i)){for(o=x.map(mt(r,"script"),ft),s=o.length;c>l;l++)a=r,l!==h&&(a=x.clone(a,!0,!0),s&&x.merge(o,mt(a,"script"))),t.call(this[l],a,l);if(s)for(u=o[o.length-1].ownerDocument,x.map(o,ht),l=0;s>l;l++)a=o[l],at.test(a.type||"")&&!q.access(a,"globalEval")&&x.contains(u,a)&&(a.src?x._evalUrl(a.src):x.globalEval(a.textContent.replace(lt,"")))}return this}}),x.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(e,t){x.fn[e]=function(e){var n,r=[],i=x(e),o=i.length-1,s=0;for(;o>=s;s++)n=s===o?this:this.clone(!0),x(i[s])[t](n),h.apply(r,n.get());return this.pushStack(r)}}),x.extend({clone:function(e,t,n){var r,i,o,s,a=e.cloneNode(!0),u=x.contains(e.ownerDocument,e);if(!(x.support.noCloneChecked||1!==e.nodeType&&11!==e.nodeType||x.isXMLDoc(e)))for(s=mt(a),o=mt(e),r=0,i=o.length;i>r;r++)yt(o[r],s[r]);if(t)if(n)for(o=o||mt(e),s=s||mt(a),r=0,i=o.length;i>r;r++)gt(o[r],s[r]);else gt(e,a);return s=mt(a,"script"),s.length>0&&dt(s,!u&&mt(e,"script")),a},buildFragment:function(e,t,n,r){var i,o,s,a,u,l,c=0,p=e.length,f=t.createDocumentFragment(),h=[];for(;p>c;c++)if(i=e[c],i||0===i)if("object"===x.type(i))x.merge(h,i.nodeType?[i]:i);else if(rt.test(i)){o=o||f.appendChild(t.createElement("div")),s=(nt.exec(i)||["",""])[1].toLowerCase(),a=ct[s]||ct._default,o.innerHTML=a[1]+i.replace(tt,"<$1></$2>")+a[2],l=a[0];while(l--)o=o.lastChild;x.merge(h,o.childNodes),o=f.firstChild,o.textContent=""}else h.push(t.createTextNode(i));f.textContent="",c=0;while(i=h[c++])if((!r||-1===x.inArray(i,r))&&(u=x.contains(i.ownerDocument,i),o=mt(f.appendChild(i),"script"),u&&dt(o),n)){l=0;while(i=o[l++])at.test(i.type||"")&&n.push(i)}return f},cleanData:function(e){var t,n,r,i,o,s,a=x.event.special,u=0;for(;(n=e[u])!==undefined;u++){if(F.accepts(n)&&(o=n[q.expando],o&&(t=q.cache[o]))){if(r=Object.keys(t.events||{}),r.length)for(s=0;(i=r[s])!==undefined;s++)a[i]?x.event.remove(n,i):x.removeEvent(n,i,t.handle);q.cache[o]&&delete q.cache[o]}delete L.cache[n[L.expando]]}},_evalUrl:function(e){return x.ajax({url:e,type:"GET",dataType:"script",async:!1,global:!1,"throws":!0})}});function pt(e,t){return x.nodeName(e,"table")&&x.nodeName(1===t.nodeType?t:t.firstChild,"tr")?e.getElementsByTagName("tbody")[0]||e.appendChild(e.ownerDocument.createElement("tbody")):e}function ft(e){return e.type=(null!==e.getAttribute("type"))+"/"+e.type,e}function ht(e){var t=ut.exec(e.type);return t?e.type=t[1]:e.removeAttribute("type"),e}function dt(e,t){var n=e.length,r=0;for(;n>r;r++)q.set(e[r],"globalEval",!t||q.get(t[r],"globalEval"))}function gt(e,t){var n,r,i,o,s,a,u,l;if(1===t.nodeType){if(q.hasData(e)&&(o=q.access(e),s=q.set(t,o),l=o.events)){delete s.handle,s.events={};for(i in l)for(n=0,r=l[i].length;r>n;n++)x.event.add(t,i,l[i][n])}L.hasData(e)&&(a=L.access(e),u=x.extend({},a),L.set(t,u))}}function mt(e,t){var n=e.getElementsByTagName?e.getElementsByTagName(t||"*"):e.querySelectorAll?e.querySelectorAll(t||"*"):[];return t===undefined||t&&x.nodeName(e,t)?x.merge([e],n):n}function yt(e,t){var n=t.nodeName.toLowerCase();"input"===n&&ot.test(e.type)?t.checked=e.checked:("input"===n||"textarea"===n)&&(t.defaultValue=e.defaultValue)}x.fn.extend({wrapAll:function(e){var t;return x.isFunction(e)?this.each(function(t){x(this).wrapAll(e.call(this,t))}):(this[0]&&(t=x(e,this[0].ownerDocument).eq(0).clone(!0),this[0].parentNode&&t.insertBefore(this[0]),t.map(function(){var e=this;while(e.firstElementChild)e=e.firstElementChild;return e}).append(this)),this)},wrapInner:function(e){return x.isFunction(e)?this.each(function(t){x(this).wrapInner(e.call(this,t))}):this.each(function(){var t=x(this),n=t.contents();n.length?n.wrapAll(e):t.append(e)})},wrap:function(e){var t=x.isFunction(e);return this.each(function(n){x(this).wrapAll(t?e.call(this,n):e)})},unwrap:function(){return this.parent().each(function(){x.nodeName(this,"body")||x(this).replaceWith(this.childNodes)}).end()}});var vt,xt,bt=/^(none|table(?!-c[ea]).+)/,wt=/^margin/,Tt=RegExp("^("+b+")(.*)$","i"),Ct=RegExp("^("+b+")(?!px)[a-z%]+$","i"),kt=RegExp("^([+-])=("+b+")","i"),Nt={BODY:"block"},Et={position:"absolute",visibility:"hidden",display:"block"},St={letterSpacing:0,fontWeight:400},jt=["Top","Right","Bottom","Left"],Dt=["Webkit","O","Moz","ms"];function At(e,t){if(t in e)return t;var n=t.charAt(0).toUpperCase()+t.slice(1),r=t,i=Dt.length;while(i--)if(t=Dt[i]+n,t in e)return t;return r}function Lt(e,t){return e=t||e,"none"===x.css(e,"display")||!x.contains(e.ownerDocument,e)}function qt(t){return e.getComputedStyle(t,null)}function Ht(e,t){var n,r,i,o=[],s=0,a=e.length;for(;a>s;s++)r=e[s],r.style&&(o[s]=q.get(r,"olddisplay"),n=r.style.display,t?(o[s]||"none"!==n||(r.style.display=""),""===r.style.display&&Lt(r)&&(o[s]=q.access(r,"olddisplay",Rt(r.nodeName)))):o[s]||(i=Lt(r),(n&&"none"!==n||!i)&&q.set(r,"olddisplay",i?n:x.css(r,"display"))));for(s=0;a>s;s++)r=e[s],r.style&&(t&&"none"!==r.style.display&&""!==r.style.display||(r.style.display=t?o[s]||"":"none"));return e}x.fn.extend({css:function(e,t){return x.access(this,function(e,t,n){var r,i,o={},s=0;if(x.isArray(t)){for(r=qt(e),i=t.length;i>s;s++)o[t[s]]=x.css(e,t[s],!1,r);return o}return n!==undefined?x.style(e,t,n):x.css(e,t)},e,t,arguments.length>1)},show:function(){return Ht(this,!0)},hide:function(){return Ht(this)},toggle:function(e){return"boolean"==typeof e?e?this.show():this.hide():this.each(function(){Lt(this)?x(this).show():x(this).hide()})}}),x.extend({cssHooks:{opacity:{get:function(e,t){if(t){var n=vt(e,"opacity");return""===n?"1":n}}}},cssNumber:{columnCount:!0,fillOpacity:!0,fontWeight:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{"float":"cssFloat"},style:function(e,t,n,r){if(e&&3!==e.nodeType&&8!==e.nodeType&&e.style){var i,o,s,a=x.camelCase(t),u=e.style;return t=x.cssProps[a]||(x.cssProps[a]=At(u,a)),s=x.cssHooks[t]||x.cssHooks[a],n===undefined?s&&"get"in s&&(i=s.get(e,!1,r))!==undefined?i:u[t]:(o=typeof n,"string"===o&&(i=kt.exec(n))&&(n=(i[1]+1)*i[2]+parseFloat(x.css(e,t)),o="number"),null==n||"number"===o&&isNaN(n)||("number"!==o||x.cssNumber[a]||(n+="px"),x.support.clearCloneStyle||""!==n||0!==t.indexOf("background")||(u[t]="inherit"),s&&"set"in s&&(n=s.set(e,n,r))===undefined||(u[t]=n)),undefined)}},css:function(e,t,n,r){var i,o,s,a=x.camelCase(t);return t=x.cssProps[a]||(x.cssProps[a]=At(e.style,a)),s=x.cssHooks[t]||x.cssHooks[a],s&&"get"in s&&(i=s.get(e,!0,n)),i===undefined&&(i=vt(e,t,r)),"normal"===i&&t in St&&(i=St[t]),""===n||n?(o=parseFloat(i),n===!0||x.isNumeric(o)?o||0:i):i}}),vt=function(e,t,n){var r,i,o,s=n||qt(e),a=s?s.getPropertyValue(t)||s[t]:undefined,u=e.style;return s&&(""!==a||x.contains(e.ownerDocument,e)||(a=x.style(e,t)),Ct.test(a)&&wt.test(t)&&(r=u.width,i=u.minWidth,o=u.maxWidth,u.minWidth=u.maxWidth=u.width=a,a=s.width,u.width=r,u.minWidth=i,u.maxWidth=o)),a};function Ot(e,t,n){var r=Tt.exec(t);return r?Math.max(0,r[1]-(n||0))+(r[2]||"px"):t}function Ft(e,t,n,r,i){var o=n===(r?"border":"content")?4:"width"===t?1:0,s=0;for(;4>o;o+=2)"margin"===n&&(s+=x.css(e,n+jt[o],!0,i)),r?("content"===n&&(s-=x.css(e,"padding"+jt[o],!0,i)),"margin"!==n&&(s-=x.css(e,"border"+jt[o]+"Width",!0,i))):(s+=x.css(e,"padding"+jt[o],!0,i),"padding"!==n&&(s+=x.css(e,"border"+jt[o]+"Width",!0,i)));return s}function Pt(e,t,n){var r=!0,i="width"===t?e.offsetWidth:e.offsetHeight,o=qt(e),s=x.support.boxSizing&&"border-box"===x.css(e,"boxSizing",!1,o);if(0>=i||null==i){if(i=vt(e,t,o),(0>i||null==i)&&(i=e.style[t]),Ct.test(i))return i;r=s&&(x.support.boxSizingReliable||i===e.style[t]),i=parseFloat(i)||0}return i+Ft(e,t,n||(s?"border":"content"),r,o)+"px"}function Rt(e){var t=o,n=Nt[e];return n||(n=Mt(e,t),"none"!==n&&n||(xt=(xt||x("<iframe frameborder='0' width='0' height='0'/>").css("cssText","display:block !important")).appendTo(t.documentElement),t=(xt[0].contentWindow||xt[0].contentDocument).document,t.write("<!doctype html><html>"),t.close(),n=Mt(e,t),xt.detach()),Nt[e]=n),n}function Mt(e,t){var n=x(t.createElement(e)).appendTo(t.body),r=x.css(n[0],"display");return n.remove(),r}x.each(["height","width"],function(e,t){x.cssHooks[t]={get:function(e,n,r){return n?0===e.offsetWidth&&bt.test(x.css(e,"display"))?x.swap(e,Et,function(){return Pt(e,t,r)}):Pt(e,t,r):undefined},set:function(e,n,r){var i=r&&qt(e);return Ot(e,n,r?Ft(e,t,r,x.support.boxSizing&&"border-box"===x.css(e,"boxSizing",!1,i),i):0)}}}),x(function(){x.support.reliableMarginRight||(x.cssHooks.marginRight={get:function(e,t){return t?x.swap(e,{display:"inline-block"},vt,[e,"marginRight"]):undefined}}),!x.support.pixelPosition&&x.fn.position&&x.each(["top","left"],function(e,t){x.cssHooks[t]={get:function(e,n){return n?(n=vt(e,t),Ct.test(n)?x(e).position()[t]+"px":n):undefined}}})}),x.expr&&x.expr.filters&&(x.expr.filters.hidden=function(e){return 0>=e.offsetWidth&&0>=e.offsetHeight},x.expr.filters.visible=function(e){return!x.expr.filters.hidden(e)}),x.each({margin:"",padding:"",border:"Width"},function(e,t){x.cssHooks[e+t]={expand:function(n){var r=0,i={},o="string"==typeof n?n.split(" "):[n];for(;4>r;r++)i[e+jt[r]+t]=o[r]||o[r-2]||o[0];return i}},wt.test(e)||(x.cssHooks[e+t].set=Ot)});var Wt=/%20/g,$t=/\[\]$/,Bt=/\r?\n/g,It=/^(?:submit|button|image|reset|file)$/i,zt=/^(?:input|select|textarea|keygen)/i;x.fn.extend({serialize:function(){return x.param(this.serializeArray())},serializeArray:function(){return this.map(function(){var e=x.prop(this,"elements");return e?x.makeArray(e):this}).filter(function(){var e=this.type;return this.name&&!x(this).is(":disabled")&&zt.test(this.nodeName)&&!It.test(e)&&(this.checked||!ot.test(e))}).map(function(e,t){var n=x(this).val();return null==n?null:x.isArray(n)?x.map(n,function(e){return{name:t.name,value:e.replace(Bt,"\r\n")}}):{name:t.name,value:n.replace(Bt,"\r\n")}}).get()}}),x.param=function(e,t){var n,r=[],i=function(e,t){t=x.isFunction(t)?t():null==t?"":t,r[r.length]=encodeURIComponent(e)+"="+encodeURIComponent(t)};if(t===undefined&&(t=x.ajaxSettings&&x.ajaxSettings.traditional),x.isArray(e)||e.jquery&&!x.isPlainObject(e))x.each(e,function(){i(this.name,this.value)});else for(n in e)_t(n,e[n],t,i);return r.join("&").replace(Wt,"+")};function _t(e,t,n,r){var i;if(x.isArray(t))x.each(t,function(t,i){n||$t.test(e)?r(e,i):_t(e+"["+("object"==typeof i?t:"")+"]",i,n,r)});else if(n||"object"!==x.type(t))r(e,t);else for(i in t)_t(e+"["+i+"]",t[i],n,r)}x.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error contextmenu".split(" "),function(e,t){x.fn[t]=function(e,n){return arguments.length>0?this.on(t,null,e,n):this.trigger(t)}}),x.fn.extend({hover:function(e,t){return this.mouseenter(e).mouseleave(t||e)},bind:function(e,t,n){return this.on(e,null,t,n)},unbind:function(e,t){return this.off(e,null,t)
    },delegate:function(e,t,n,r){return this.on(t,e,n,r)},undelegate:function(e,t,n){return 1===arguments.length?this.off(e,"**"):this.off(t,e||"**",n)}});var Xt,Ut,Yt=x.now(),Vt=/\?/,Gt=/#.*$/,Jt=/([?&])_=[^&]*/,Qt=/^(.*?):[ \t]*([^\r\n]*)$/gm,Kt=/^(?:about|app|app-storage|.+-extension|file|res|widget):$/,Zt=/^(?:GET|HEAD)$/,en=/^\/\//,tn=/^([\w.+-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/,nn=x.fn.load,rn={},on={},sn="*/".concat("*");try{Ut=i.href}catch(an){Ut=o.createElement("a"),Ut.href="",Ut=Ut.href}Xt=tn.exec(Ut.toLowerCase())||[];function un(e){return function(t,n){"string"!=typeof t&&(n=t,t="*");var r,i=0,o=t.toLowerCase().match(w)||[];if(x.isFunction(n))while(r=o[i++])"+"===r[0]?(r=r.slice(1)||"*",(e[r]=e[r]||[]).unshift(n)):(e[r]=e[r]||[]).push(n)}}function ln(e,t,n,r){var i={},o=e===on;function s(a){var u;return i[a]=!0,x.each(e[a]||[],function(e,a){var l=a(t,n,r);return"string"!=typeof l||o||i[l]?o?!(u=l):undefined:(t.dataTypes.unshift(l),s(l),!1)}),u}return s(t.dataTypes[0])||!i["*"]&&s("*")}function cn(e,t){var n,r,i=x.ajaxSettings.flatOptions||{};for(n in t)t[n]!==undefined&&((i[n]?e:r||(r={}))[n]=t[n]);return r&&x.extend(!0,e,r),e}x.fn.load=function(e,t,n){if("string"!=typeof e&&nn)return nn.apply(this,arguments);var r,i,o,s=this,a=e.indexOf(" ");return a>=0&&(r=e.slice(a),e=e.slice(0,a)),x.isFunction(t)?(n=t,t=undefined):t&&"object"==typeof t&&(i="POST"),s.length>0&&x.ajax({url:e,type:i,dataType:"html",data:t}).done(function(e){o=arguments,s.html(r?x("<div>").append(x.parseHTML(e)).find(r):e)}).complete(n&&function(e,t){s.each(n,o||[e.responseText,t,e])}),this},x.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],function(e,t){x.fn[t]=function(e){return this.on(t,e)}}),x.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:Ut,type:"GET",isLocal:Kt.test(Xt[1]),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",accepts:{"*":sn,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/xml/,html:/html/,json:/json/},responseFields:{xml:"responseXML",text:"responseText",json:"responseJSON"},converters:{"* text":String,"text html":!0,"text json":x.parseJSON,"text xml":x.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(e,t){return t?cn(cn(e,x.ajaxSettings),t):cn(x.ajaxSettings,e)},ajaxPrefilter:un(rn),ajaxTransport:un(on),ajax:function(e,t){"object"==typeof e&&(t=e,e=undefined),t=t||{};var n,r,i,o,s,a,u,l,c=x.ajaxSetup({},t),p=c.context||c,f=c.context&&(p.nodeType||p.jquery)?x(p):x.event,h=x.Deferred(),d=x.Callbacks("once memory"),g=c.statusCode||{},m={},y={},v=0,b="canceled",T={readyState:0,getResponseHeader:function(e){var t;if(2===v){if(!o){o={};while(t=Qt.exec(i))o[t[1].toLowerCase()]=t[2]}t=o[e.toLowerCase()]}return null==t?null:t},getAllResponseHeaders:function(){return 2===v?i:null},setRequestHeader:function(e,t){var n=e.toLowerCase();return v||(e=y[n]=y[n]||e,m[e]=t),this},overrideMimeType:function(e){return v||(c.mimeType=e),this},statusCode:function(e){var t;if(e)if(2>v)for(t in e)g[t]=[g[t],e[t]];else T.always(e[T.status]);return this},abort:function(e){var t=e||b;return n&&n.abort(t),k(0,t),this}};if(h.promise(T).complete=d.add,T.success=T.done,T.error=T.fail,c.url=((e||c.url||Ut)+"").replace(Gt,"").replace(en,Xt[1]+"//"),c.type=t.method||t.type||c.method||c.type,c.dataTypes=x.trim(c.dataType||"*").toLowerCase().match(w)||[""],null==c.crossDomain&&(a=tn.exec(c.url.toLowerCase()),c.crossDomain=!(!a||a[1]===Xt[1]&&a[2]===Xt[2]&&(a[3]||("http:"===a[1]?"80":"443"))===(Xt[3]||("http:"===Xt[1]?"80":"443")))),c.data&&c.processData&&"string"!=typeof c.data&&(c.data=x.param(c.data,c.traditional)),ln(rn,c,t,T),2===v)return T;u=c.global,u&&0===x.active++&&x.event.trigger("ajaxStart"),c.type=c.type.toUpperCase(),c.hasContent=!Zt.test(c.type),r=c.url,c.hasContent||(c.data&&(r=c.url+=(Vt.test(r)?"&":"?")+c.data,delete c.data),c.cache===!1&&(c.url=Jt.test(r)?r.replace(Jt,"$1_="+Yt++):r+(Vt.test(r)?"&":"?")+"_="+Yt++)),c.ifModified&&(x.lastModified[r]&&T.setRequestHeader("If-Modified-Since",x.lastModified[r]),x.etag[r]&&T.setRequestHeader("If-None-Match",x.etag[r])),(c.data&&c.hasContent&&c.contentType!==!1||t.contentType)&&T.setRequestHeader("Content-Type",c.contentType),T.setRequestHeader("Accept",c.dataTypes[0]&&c.accepts[c.dataTypes[0]]?c.accepts[c.dataTypes[0]]+("*"!==c.dataTypes[0]?", "+sn+"; q=0.01":""):c.accepts["*"]);for(l in c.headers)T.setRequestHeader(l,c.headers[l]);if(c.beforeSend&&(c.beforeSend.call(p,T,c)===!1||2===v))return T.abort();b="abort";for(l in{success:1,error:1,complete:1})T[l](c[l]);if(n=ln(on,c,t,T)){T.readyState=1,u&&f.trigger("ajaxSend",[T,c]),c.async&&c.timeout>0&&(s=setTimeout(function(){T.abort("timeout")},c.timeout));try{v=1,n.send(m,k)}catch(C){if(!(2>v))throw C;k(-1,C)}}else k(-1,"No Transport");function k(e,t,o,a){var l,m,y,b,w,C=t;2!==v&&(v=2,s&&clearTimeout(s),n=undefined,i=a||"",T.readyState=e>0?4:0,l=e>=200&&300>e||304===e,o&&(b=pn(c,T,o)),b=fn(c,b,T,l),l?(c.ifModified&&(w=T.getResponseHeader("Last-Modified"),w&&(x.lastModified[r]=w),w=T.getResponseHeader("etag"),w&&(x.etag[r]=w)),204===e||"HEAD"===c.type?C="nocontent":304===e?C="notmodified":(C=b.state,m=b.data,y=b.error,l=!y)):(y=C,(e||!C)&&(C="error",0>e&&(e=0))),T.status=e,T.statusText=(t||C)+"",l?h.resolveWith(p,[m,C,T]):h.rejectWith(p,[T,C,y]),T.statusCode(g),g=undefined,u&&f.trigger(l?"ajaxSuccess":"ajaxError",[T,c,l?m:y]),d.fireWith(p,[T,C]),u&&(f.trigger("ajaxComplete",[T,c]),--x.active||x.event.trigger("ajaxStop")))}return T},getJSON:function(e,t,n){return x.get(e,t,n,"json")},getScript:function(e,t){return x.get(e,undefined,t,"script")}}),x.each(["get","post"],function(e,t){x[t]=function(e,n,r,i){return x.isFunction(n)&&(i=i||r,r=n,n=undefined),x.ajax({url:e,type:t,dataType:i,data:n,success:r})}});function pn(e,t,n){var r,i,o,s,a=e.contents,u=e.dataTypes;while("*"===u[0])u.shift(),r===undefined&&(r=e.mimeType||t.getResponseHeader("Content-Type"));if(r)for(i in a)if(a[i]&&a[i].test(r)){u.unshift(i);break}if(u[0]in n)o=u[0];else{for(i in n){if(!u[0]||e.converters[i+" "+u[0]]){o=i;break}s||(s=i)}o=o||s}return o?(o!==u[0]&&u.unshift(o),n[o]):undefined}function fn(e,t,n,r){var i,o,s,a,u,l={},c=e.dataTypes.slice();if(c[1])for(s in e.converters)l[s.toLowerCase()]=e.converters[s];o=c.shift();while(o)if(e.responseFields[o]&&(n[e.responseFields[o]]=t),!u&&r&&e.dataFilter&&(t=e.dataFilter(t,e.dataType)),u=o,o=c.shift())if("*"===o)o=u;else if("*"!==u&&u!==o){if(s=l[u+" "+o]||l["* "+o],!s)for(i in l)if(a=i.split(" "),a[1]===o&&(s=l[u+" "+a[0]]||l["* "+a[0]])){s===!0?s=l[i]:l[i]!==!0&&(o=a[0],c.unshift(a[1]));break}if(s!==!0)if(s&&e["throws"])t=s(t);else try{t=s(t)}catch(p){return{state:"parsererror",error:s?p:"No conversion from "+u+" to "+o}}}return{state:"success",data:t}}x.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/(?:java|ecma)script/},converters:{"text script":function(e){return x.globalEval(e),e}}}),x.ajaxPrefilter("script",function(e){e.cache===undefined&&(e.cache=!1),e.crossDomain&&(e.type="GET")}),x.ajaxTransport("script",function(e){if(e.crossDomain){var t,n;return{send:function(r,i){t=x("<script>").prop({async:!0,charset:e.scriptCharset,src:e.url}).on("load error",n=function(e){t.remove(),n=null,e&&i("error"===e.type?404:200,e.type)}),o.head.appendChild(t[0])},abort:function(){n&&n()}}}});var hn=[],dn=/(=)\?(?=&|$)|\?\?/;x.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var e=hn.pop()||x.expando+"_"+Yt++;return this[e]=!0,e}}),x.ajaxPrefilter("json jsonp",function(t,n,r){var i,o,s,a=t.jsonp!==!1&&(dn.test(t.url)?"url":"string"==typeof t.data&&!(t.contentType||"").indexOf("application/x-www-form-urlencoded")&&dn.test(t.data)&&"data");return a||"jsonp"===t.dataTypes[0]?(i=t.jsonpCallback=x.isFunction(t.jsonpCallback)?t.jsonpCallback():t.jsonpCallback,a?t[a]=t[a].replace(dn,"$1"+i):t.jsonp!==!1&&(t.url+=(Vt.test(t.url)?"&":"?")+t.jsonp+"="+i),t.converters["script json"]=function(){return s||x.error(i+" was not called"),s[0]},t.dataTypes[0]="json",o=e[i],e[i]=function(){s=arguments},r.always(function(){e[i]=o,t[i]&&(t.jsonpCallback=n.jsonpCallback,hn.push(i)),s&&x.isFunction(o)&&o(s[0]),s=o=undefined}),"script"):undefined}),x.ajaxSettings.xhr=function(){try{return new XMLHttpRequest}catch(e){}};var gn=x.ajaxSettings.xhr(),mn={0:200,1223:204},yn=0,vn={};e.ActiveXObject&&x(e).on("unload",function(){for(var e in vn)vn[e]();vn=undefined}),x.support.cors=!!gn&&"withCredentials"in gn,x.support.ajax=gn=!!gn,x.ajaxTransport(function(e){var t;return x.support.cors||gn&&!e.crossDomain?{send:function(n,r){var i,o,s=e.xhr();if(s.open(e.type,e.url,e.async,e.username,e.password),e.xhrFields)for(i in e.xhrFields)s[i]=e.xhrFields[i];e.mimeType&&s.overrideMimeType&&s.overrideMimeType(e.mimeType),e.crossDomain||n["X-Requested-With"]||(n["X-Requested-With"]="XMLHttpRequest");for(i in n)s.setRequestHeader(i,n[i]);t=function(e){return function(){t&&(delete vn[o],t=s.onload=s.onerror=null,"abort"===e?s.abort():"error"===e?r(s.status||404,s.statusText):r(mn[s.status]||s.status,s.statusText,"string"==typeof s.responseText?{text:s.responseText}:undefined,s.getAllResponseHeaders()))}},s.onload=t(),s.onerror=t("error"),t=vn[o=yn++]=t("abort"),s.send(e.hasContent&&e.data||null)},abort:function(){t&&t()}}:undefined});var xn,bn,wn=/^(?:toggle|show|hide)$/,Tn=RegExp("^(?:([+-])=|)("+b+")([a-z%]*)$","i"),Cn=/queueHooks$/,kn=[An],Nn={"*":[function(e,t){var n=this.createTween(e,t),r=n.cur(),i=Tn.exec(t),o=i&&i[3]||(x.cssNumber[e]?"":"px"),s=(x.cssNumber[e]||"px"!==o&&+r)&&Tn.exec(x.css(n.elem,e)),a=1,u=20;if(s&&s[3]!==o){o=o||s[3],i=i||[],s=+r||1;do a=a||".5",s/=a,x.style(n.elem,e,s+o);while(a!==(a=n.cur()/r)&&1!==a&&--u)}return i&&(s=n.start=+s||+r||0,n.unit=o,n.end=i[1]?s+(i[1]+1)*i[2]:+i[2]),n}]};function En(){return setTimeout(function(){xn=undefined}),xn=x.now()}function Sn(e,t,n){var r,i=(Nn[t]||[]).concat(Nn["*"]),o=0,s=i.length;for(;s>o;o++)if(r=i[o].call(n,t,e))return r}function jn(e,t,n){var r,i,o=0,s=kn.length,a=x.Deferred().always(function(){delete u.elem}),u=function(){if(i)return!1;var t=xn||En(),n=Math.max(0,l.startTime+l.duration-t),r=n/l.duration||0,o=1-r,s=0,u=l.tweens.length;for(;u>s;s++)l.tweens[s].run(o);return a.notifyWith(e,[l,o,n]),1>o&&u?n:(a.resolveWith(e,[l]),!1)},l=a.promise({elem:e,props:x.extend({},t),opts:x.extend(!0,{specialEasing:{}},n),originalProperties:t,originalOptions:n,startTime:xn||En(),duration:n.duration,tweens:[],createTween:function(t,n){var r=x.Tween(e,l.opts,t,n,l.opts.specialEasing[t]||l.opts.easing);return l.tweens.push(r),r},stop:function(t){var n=0,r=t?l.tweens.length:0;if(i)return this;for(i=!0;r>n;n++)l.tweens[n].run(1);return t?a.resolveWith(e,[l,t]):a.rejectWith(e,[l,t]),this}}),c=l.props;for(Dn(c,l.opts.specialEasing);s>o;o++)if(r=kn[o].call(l,e,c,l.opts))return r;return x.map(c,Sn,l),x.isFunction(l.opts.start)&&l.opts.start.call(e,l),x.fx.timer(x.extend(u,{elem:e,anim:l,queue:l.opts.queue})),l.progress(l.opts.progress).done(l.opts.done,l.opts.complete).fail(l.opts.fail).always(l.opts.always)}function Dn(e,t){var n,r,i,o,s;for(n in e)if(r=x.camelCase(n),i=t[r],o=e[n],x.isArray(o)&&(i=o[1],o=e[n]=o[0]),n!==r&&(e[r]=o,delete e[n]),s=x.cssHooks[r],s&&"expand"in s){o=s.expand(o),delete e[r];for(n in o)n in e||(e[n]=o[n],t[n]=i)}else t[r]=i}x.Animation=x.extend(jn,{tweener:function(e,t){x.isFunction(e)?(t=e,e=["*"]):e=e.split(" ");var n,r=0,i=e.length;for(;i>r;r++)n=e[r],Nn[n]=Nn[n]||[],Nn[n].unshift(t)},prefilter:function(e,t){t?kn.unshift(e):kn.push(e)}});function An(e,t,n){var r,i,o,s,a,u,l=this,c={},p=e.style,f=e.nodeType&&Lt(e),h=q.get(e,"fxshow");n.queue||(a=x._queueHooks(e,"fx"),null==a.unqueued&&(a.unqueued=0,u=a.empty.fire,a.empty.fire=function(){a.unqueued||u()}),a.unqueued++,l.always(function(){l.always(function(){a.unqueued--,x.queue(e,"fx").length||a.empty.fire()})})),1===e.nodeType&&("height"in t||"width"in t)&&(n.overflow=[p.overflow,p.overflowX,p.overflowY],"inline"===x.css(e,"display")&&"none"===x.css(e,"float")&&(p.display="inline-block")),n.overflow&&(p.overflow="hidden",l.always(function(){p.overflow=n.overflow[0],p.overflowX=n.overflow[1],p.overflowY=n.overflow[2]}));for(r in t)if(i=t[r],wn.exec(i)){if(delete t[r],o=o||"toggle"===i,i===(f?"hide":"show")){if("show"!==i||!h||h[r]===undefined)continue;f=!0}c[r]=h&&h[r]||x.style(e,r)}if(!x.isEmptyObject(c)){h?"hidden"in h&&(f=h.hidden):h=q.access(e,"fxshow",{}),o&&(h.hidden=!f),f?x(e).show():l.done(function(){x(e).hide()}),l.done(function(){var t;q.remove(e,"fxshow");for(t in c)x.style(e,t,c[t])});for(r in c)s=Sn(f?h[r]:0,r,l),r in h||(h[r]=s.start,f&&(s.end=s.start,s.start="width"===r||"height"===r?1:0))}}function Ln(e,t,n,r,i){return new Ln.prototype.init(e,t,n,r,i)}x.Tween=Ln,Ln.prototype={constructor:Ln,init:function(e,t,n,r,i,o){this.elem=e,this.prop=n,this.easing=i||"swing",this.options=t,this.start=this.now=this.cur(),this.end=r,this.unit=o||(x.cssNumber[n]?"":"px")},cur:function(){var e=Ln.propHooks[this.prop];return e&&e.get?e.get(this):Ln.propHooks._default.get(this)},run:function(e){var t,n=Ln.propHooks[this.prop];return this.pos=t=this.options.duration?x.easing[this.easing](e,this.options.duration*e,0,1,this.options.duration):e,this.now=(this.end-this.start)*t+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),n&&n.set?n.set(this):Ln.propHooks._default.set(this),this}},Ln.prototype.init.prototype=Ln.prototype,Ln.propHooks={_default:{get:function(e){var t;return null==e.elem[e.prop]||e.elem.style&&null!=e.elem.style[e.prop]?(t=x.css(e.elem,e.prop,""),t&&"auto"!==t?t:0):e.elem[e.prop]},set:function(e){x.fx.step[e.prop]?x.fx.step[e.prop](e):e.elem.style&&(null!=e.elem.style[x.cssProps[e.prop]]||x.cssHooks[e.prop])?x.style(e.elem,e.prop,e.now+e.unit):e.elem[e.prop]=e.now}}},Ln.propHooks.scrollTop=Ln.propHooks.scrollLeft={set:function(e){e.elem.nodeType&&e.elem.parentNode&&(e.elem[e.prop]=e.now)}},x.each(["toggle","show","hide"],function(e,t){var n=x.fn[t];x.fn[t]=function(e,r,i){return null==e||"boolean"==typeof e?n.apply(this,arguments):this.animate(qn(t,!0),e,r,i)}}),x.fn.extend({fadeTo:function(e,t,n,r){return this.filter(Lt).css("opacity",0).show().end().animate({opacity:t},e,n,r)},animate:function(e,t,n,r){var i=x.isEmptyObject(e),o=x.speed(t,n,r),s=function(){var t=jn(this,x.extend({},e),o);(i||q.get(this,"finish"))&&t.stop(!0)};return s.finish=s,i||o.queue===!1?this.each(s):this.queue(o.queue,s)},stop:function(e,t,n){var r=function(e){var t=e.stop;delete e.stop,t(n)};return"string"!=typeof e&&(n=t,t=e,e=undefined),t&&e!==!1&&this.queue(e||"fx",[]),this.each(function(){var t=!0,i=null!=e&&e+"queueHooks",o=x.timers,s=q.get(this);if(i)s[i]&&s[i].stop&&r(s[i]);else for(i in s)s[i]&&s[i].stop&&Cn.test(i)&&r(s[i]);for(i=o.length;i--;)o[i].elem!==this||null!=e&&o[i].queue!==e||(o[i].anim.stop(n),t=!1,o.splice(i,1));(t||!n)&&x.dequeue(this,e)})},finish:function(e){return e!==!1&&(e=e||"fx"),this.each(function(){var t,n=q.get(this),r=n[e+"queue"],i=n[e+"queueHooks"],o=x.timers,s=r?r.length:0;for(n.finish=!0,x.queue(this,e,[]),i&&i.stop&&i.stop.call(this,!0),t=o.length;t--;)o[t].elem===this&&o[t].queue===e&&(o[t].anim.stop(!0),o.splice(t,1));for(t=0;s>t;t++)r[t]&&r[t].finish&&r[t].finish.call(this);delete n.finish})}});function qn(e,t){var n,r={height:e},i=0;for(t=t?1:0;4>i;i+=2-t)n=jt[i],r["margin"+n]=r["padding"+n]=e;return t&&(r.opacity=r.width=e),r}x.each({slideDown:qn("show"),slideUp:qn("hide"),slideToggle:qn("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(e,t){x.fn[e]=function(e,n,r){return this.animate(t,e,n,r)}}),x.speed=function(e,t,n){var r=e&&"object"==typeof e?x.extend({},e):{complete:n||!n&&t||x.isFunction(e)&&e,duration:e,easing:n&&t||t&&!x.isFunction(t)&&t};return r.duration=x.fx.off?0:"number"==typeof r.duration?r.duration:r.duration in x.fx.speeds?x.fx.speeds[r.duration]:x.fx.speeds._default,(null==r.queue||r.queue===!0)&&(r.queue="fx"),r.old=r.complete,r.complete=function(){x.isFunction(r.old)&&r.old.call(this),r.queue&&x.dequeue(this,r.queue)},r},x.easing={linear:function(e){return e},swing:function(e){return.5-Math.cos(e*Math.PI)/2}},x.timers=[],x.fx=Ln.prototype.init,x.fx.tick=function(){var e,t=x.timers,n=0;for(xn=x.now();t.length>n;n++)e=t[n],e()||t[n]!==e||t.splice(n--,1);t.length||x.fx.stop(),xn=undefined},x.fx.timer=function(e){e()&&x.timers.push(e)&&x.fx.start()},x.fx.interval=13,x.fx.start=function(){bn||(bn=setInterval(x.fx.tick,x.fx.interval))},x.fx.stop=function(){clearInterval(bn),bn=null},x.fx.speeds={slow:600,fast:200,_default:400},x.fx.step={},x.expr&&x.expr.filters&&(x.expr.filters.animated=function(e){return x.grep(x.timers,function(t){return e===t.elem}).length}),x.fn.offset=function(e){if(arguments.length)return e===undefined?this:this.each(function(t){x.offset.setOffset(this,e,t)});var t,n,i=this[0],o={top:0,left:0},s=i&&i.ownerDocument;if(s)return t=s.documentElement,x.contains(t,i)?(typeof i.getBoundingClientRect!==r&&(o=i.getBoundingClientRect()),n=Hn(s),{top:o.top+n.pageYOffset-t.clientTop,left:o.left+n.pageXOffset-t.clientLeft}):o},x.offset={setOffset:function(e,t,n){var r,i,o,s,a,u,l,c=x.css(e,"position"),p=x(e),f={};"static"===c&&(e.style.position="relative"),a=p.offset(),o=x.css(e,"top"),u=x.css(e,"left"),l=("absolute"===c||"fixed"===c)&&(o+u).indexOf("auto")>-1,l?(r=p.position(),s=r.top,i=r.left):(s=parseFloat(o)||0,i=parseFloat(u)||0),x.isFunction(t)&&(t=t.call(e,n,a)),null!=t.top&&(f.top=t.top-a.top+s),null!=t.left&&(f.left=t.left-a.left+i),"using"in t?t.using.call(e,f):p.css(f)}},x.fn.extend({position:function(){if(this[0]){var e,t,n=this[0],r={top:0,left:0};return"fixed"===x.css(n,"position")?t=n.getBoundingClientRect():(e=this.offsetParent(),t=this.offset(),x.nodeName(e[0],"html")||(r=e.offset()),r.top+=x.css(e[0],"borderTopWidth",!0),r.left+=x.css(e[0],"borderLeftWidth",!0)),{top:t.top-r.top-x.css(n,"marginTop",!0),left:t.left-r.left-x.css(n,"marginLeft",!0)}}},offsetParent:function(){return this.map(function(){var e=this.offsetParent||s;while(e&&!x.nodeName(e,"html")&&"static"===x.css(e,"position"))e=e.offsetParent;return e||s})}}),x.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(t,n){var r="pageYOffset"===n;x.fn[t]=function(i){return x.access(this,function(t,i,o){var s=Hn(t);return o===undefined?s?s[n]:t[i]:(s?s.scrollTo(r?e.pageXOffset:o,r?o:e.pageYOffset):t[i]=o,undefined)},t,i,arguments.length,null)}});function Hn(e){return x.isWindow(e)?e:9===e.nodeType&&e.defaultView}x.each({Height:"height",Width:"width"},function(e,t){x.each({padding:"inner"+e,content:t,"":"outer"+e},function(n,r){x.fn[r]=function(r,i){var o=arguments.length&&(n||"boolean"!=typeof r),s=n||(r===!0||i===!0?"margin":"border");return x.access(this,function(t,n,r){var i;return x.isWindow(t)?t.document.documentElement["client"+e]:9===t.nodeType?(i=t.documentElement,Math.max(t.body["scroll"+e],i["scroll"+e],t.body["offset"+e],i["offset"+e],i["client"+e])):r===undefined?x.css(t,n,s):x.style(t,n,r,s)},t,o?r:undefined,o,null)}})}),x.fn.size=function(){return this.length},x.fn.andSelf=x.fn.addBack,"object"==typeof module&&module&&"object"==typeof module.exports?module.exports=x:"function"==typeof define&&define.amd&&define("jquery",[],function(){return x}),"object"==typeof e&&"object"==typeof e.document&&(e.jQuery=e.$=x)})(window);
    /*
	    MD5
	    Copyright (C) 2007 MITSUNARI Shigeo at Cybozu Labs, Inc.
    	license:new BSD license
    	how to use
	    CybozuLabs.MD5.calc(<ascii string>);
	    CybozuLabs.MD5.calc(<unicode(UTF16) string>, CybozuLabs.MD5.BY_UTF16);

	    ex. CybozuLabs.MD5.calc("abc") == "900150983cd24fb0d6963f7d28e17f72";
    */
    var CybozuLabs={MD5:{int2hex8_Fx:function(t){return this.int2hex8(65536*t[1]+t[0])},update_Fx:function(t,h){var a,r,e,c,o,d,C,A,i,s,_,n,b,f,u,l,x,F,z,v,g,m,p,y,B,D,L,M,S,Y,I,T,U,w=this.a_[0],E=this.a_[1],N=this.b_[0],O=this.b_[1],R=this.c_[0],V=this.c_[1],j=this.d_[0],k=this.d_[1];1==h?(a=t.charCodeAt(0)|t.charCodeAt(1)<<8,x=t.charCodeAt(2)|t.charCodeAt(3)<<8,r=t.charCodeAt(4)|t.charCodeAt(5)<<8,F=t.charCodeAt(6)|t.charCodeAt(7)<<8,e=t.charCodeAt(8)|t.charCodeAt(9)<<8,z=t.charCodeAt(10)|t.charCodeAt(11)<<8,c=t.charCodeAt(12)|t.charCodeAt(13)<<8,v=t.charCodeAt(14)|t.charCodeAt(15)<<8,o=t.charCodeAt(16)|t.charCodeAt(17)<<8,g=t.charCodeAt(18)|t.charCodeAt(19)<<8,d=t.charCodeAt(20)|t.charCodeAt(21)<<8,m=t.charCodeAt(22)|t.charCodeAt(23)<<8,C=t.charCodeAt(24)|t.charCodeAt(25)<<8,p=t.charCodeAt(26)|t.charCodeAt(27)<<8,A=t.charCodeAt(28)|t.charCodeAt(29)<<8,y=t.charCodeAt(30)|t.charCodeAt(31)<<8,i=t.charCodeAt(32)|t.charCodeAt(33)<<8,B=t.charCodeAt(34)|t.charCodeAt(35)<<8,s=t.charCodeAt(36)|t.charCodeAt(37)<<8,D=t.charCodeAt(38)|t.charCodeAt(39)<<8,_=t.charCodeAt(40)|t.charCodeAt(41)<<8,L=t.charCodeAt(42)|t.charCodeAt(43)<<8,n=t.charCodeAt(44)|t.charCodeAt(45)<<8,M=t.charCodeAt(46)|t.charCodeAt(47)<<8,b=t.charCodeAt(48)|t.charCodeAt(49)<<8,S=t.charCodeAt(50)|t.charCodeAt(51)<<8,f=t.charCodeAt(52)|t.charCodeAt(53)<<8,Y=t.charCodeAt(54)|t.charCodeAt(55)<<8,u=t.charCodeAt(56)|t.charCodeAt(57)<<8,I=t.charCodeAt(58)|t.charCodeAt(59)<<8,l=t.charCodeAt(60)|t.charCodeAt(61)<<8,T=t.charCodeAt(62)|t.charCodeAt(63)<<8):(a=t.charCodeAt(0),x=t.charCodeAt(1),r=t.charCodeAt(2),F=t.charCodeAt(3),e=t.charCodeAt(4),z=t.charCodeAt(5),c=t.charCodeAt(6),v=t.charCodeAt(7),o=t.charCodeAt(8),g=t.charCodeAt(9),d=t.charCodeAt(10),m=t.charCodeAt(11),C=t.charCodeAt(12),p=t.charCodeAt(13),A=t.charCodeAt(14),y=t.charCodeAt(15),i=t.charCodeAt(16),B=t.charCodeAt(17),s=t.charCodeAt(18),D=t.charCodeAt(19),_=t.charCodeAt(20),L=t.charCodeAt(21),n=t.charCodeAt(22),M=t.charCodeAt(23),b=t.charCodeAt(24),S=t.charCodeAt(25),f=t.charCodeAt(26),Y=t.charCodeAt(27),u=t.charCodeAt(28),I=t.charCodeAt(29),l=t.charCodeAt(30),T=t.charCodeAt(31)),E+=(O&V|~O&k)+x+55146,E+=(w+=(N&R|~N&j)+a+42104)>>16,U=(E&=65535)>>9|(w&=65535)<<7&65535,E=w>>9|E<<7&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=((E&=65535)&O|~E&V)+F+59591,k+=(j+=(w&N|~w&R)+r+46934)>>16,U=(k&=65535)>>4|(j&=65535)<<12&65535,k=j>>4|k<<12&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=((k&=65535)&E|~k&O)+z+9248,V+=(R+=(j&w|~j&N)+e+28891)>>16,U=(R&=65535)>>15|(V&=65535)<<1&65535,V=V>>15|R<<1&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=((V&=65535)&k|~V&E)+v+49597,O+=(N+=(R&j|~R&w)+c+52974)>>16,U=(N&=65535)>>10|(O&=65535)<<6&65535,O=O>>10|N<<6&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=((O&=65535)&V|~O&k)+g+62844,E+=(w+=(N&R|~N&j)+o+4015)>>16,U=(E&=65535)>>9|(w&=65535)<<7&65535,E=w>>9|E<<7&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=((E&=65535)&O|~E&V)+m+18311,k+=(j+=(w&N|~w&R)+d+50730)>>16,U=(k&=65535)>>4|(j&=65535)<<12&65535,k=j>>4|k<<12&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=((k&=65535)&E|~k&O)+p+43056,V+=(R+=(j&w|~j&N)+C+17939)>>16,U=(R&=65535)>>15|(V&=65535)<<1&65535,V=V>>15|R<<1&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=((V&=65535)&k|~V&E)+y+64838,O+=(N+=(R&j|~R&w)+A+38145)>>16,U=(N&=65535)>>10|(O&=65535)<<6&65535,O=O>>10|N<<6&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=((O&=65535)&V|~O&k)+B+27008,E+=(w+=(N&R|~N&j)+i+39128)>>16,U=(E&=65535)>>9|(w&=65535)<<7&65535,E=w>>9|E<<7&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=((E&=65535)&O|~E&V)+D+35652,k+=(j+=(w&N|~w&R)+s+63407)>>16,U=(k&=65535)>>4|(j&=65535)<<12&65535,k=j>>4|k<<12&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=((k&=65535)&E|~k&O)+L+65535,V+=(R+=(j&w|~j&N)+_+23473)>>16,U=(R&=65535)>>15|(V&=65535)<<1&65535,V=V>>15|R<<1&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=((V&=65535)&k|~V&E)+M+35164,O+=(N+=(R&j|~R&w)+n+55230)>>16,U=(N&=65535)>>10|(O&=65535)<<6&65535,O=O>>10|N<<6&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=((O&=65535)&V|~O&k)+S+27536,E+=(w+=(N&R|~N&j)+b+4386)>>16,U=(E&=65535)>>9|(w&=65535)<<7&65535,E=w>>9|E<<7&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=((E&=65535)&O|~E&V)+Y+64920,k+=(j+=(w&N|~w&R)+f+29075)>>16,U=(k&=65535)>>4|(j&=65535)<<12&65535,k=j>>4|k<<12&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=((k&=65535)&E|~k&O)+I+42617,V+=(R+=(j&w|~j&N)+u+17294)>>16,U=(R&=65535)>>15|(V&=65535)<<1&65535,V=V>>15|R<<1&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=((V&=65535)&k|~V&E)+T+18868,O+=(N+=(R&j|~R&w)+l+2081)>>16,U=(N&=65535)>>10|(O&=65535)<<6&65535,O=O>>10|N<<6&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=((O&=65535)&k|V&~k)+F+63006,E+=(w+=(N&j|R&~j)+r+9570)>>16,U=(E&=65535)>>11|(w&=65535)<<5&65535,E=w>>11|E<<5&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=((E&=65535)&V|O&~V)+p+49216,k+=(j+=(w&R|N&~R)+C+45888)>>16,U=(k&=65535)>>7|(j&=65535)<<9&65535,k=j>>7|k<<9&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=((k&=65535)&O|E&~O)+M+9822,V+=(R+=(j&N|w&~N)+n+23121)>>16,U=(V&=65535)>>2|(R&=65535)<<14&65535,V=R>>2|V<<14&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=((V&=65535)&E|k&~E)+x+59830,O+=(N+=(R&w|j&~w)+a+51114)>>16,U=(N&=65535)>>12|(O&=65535)<<4&65535,O=O>>12|N<<4&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=((O&=65535)&k|V&~k)+m+54831,E+=(w+=(N&j|R&~j)+d+4189)>>16,U=(E&=65535)>>11|(w&=65535)<<5&65535,E=w>>11|E<<5&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=((E&=65535)&V|O&~V)+L+580,k+=(j+=(w&R|N&~R)+_+5203)>>16,U=(k&=65535)>>7|(j&=65535)<<9&65535,k=j>>7|k<<9&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=((k&=65535)&O|E&~O)+T+55457,V+=(R+=(j&N|w&~N)+l+59009)>>16,U=(V&=65535)>>2|(R&=65535)<<14&65535,V=R>>2|V<<14&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=((V&=65535)&E|k&~E)+g+59347,O+=(N+=(R&w|j&~w)+o+64456)>>16,U=(N&=65535)>>12|(O&=65535)<<4&65535,O=O>>12|N<<4&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=((O&=65535)&k|V&~k)+D+8673,E+=(w+=(N&j|R&~j)+s+52710)>>16,U=(E&=65535)>>11|(w&=65535)<<5&65535,E=w>>11|E<<5&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=((E&=65535)&V|O&~V)+I+49975,k+=(j+=(w&R|N&~R)+u+2006)>>16,U=(k&=65535)>>7|(j&=65535)<<9&65535,k=j>>7|k<<9&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=((k&=65535)&O|E&~O)+v+62677,V+=(R+=(j&N|w&~N)+c+3463)>>16,U=(V&=65535)>>2|(R&=65535)<<14&65535,V=R>>2|V<<14&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=((V&=65535)&E|k&~E)+B+17754,O+=(N+=(R&w|j&~w)+i+5357)>>16,U=(N&=65535)>>12|(O&=65535)<<4&65535,O=O>>12|N<<4&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=((O&=65535)&k|V&~k)+Y+43491,E+=(w+=(N&j|R&~j)+f+59653)>>16,U=(E&=65535)>>11|(w&=65535)<<5&65535,E=w>>11|E<<5&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=((E&=65535)&V|O&~V)+z+64751,k+=(j+=(w&R|N&~R)+e+41976)>>16,U=(k&=65535)>>7|(j&=65535)<<9&65535,k=j>>7|k<<9&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=((k&=65535)&O|E&~O)+y+26479,V+=(R+=(j&N|w&~N)+A+729)>>16,U=(V&=65535)>>2|(R&=65535)<<14&65535,V=R>>2|V<<14&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=((V&=65535)&E|k&~E)+S+36138,O+=(N+=(R&w|j&~w)+b+19594)>>16,U=(N&=65535)>>12|(O&=65535)<<4&65535,O=O>>12|N<<4&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=((O&=65535)^V^k)+m+65530,E+=(w+=(N^R^j)+d+14658)>>16,U=(E&=65535)>>12|(w&=65535)<<4&65535,E=w>>12|E<<4&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=((E&=65535)^O^V)+B+34673,k+=(j+=(w^N^R)+i+63105)>>16,U=(k&=65535)>>5|(j&=65535)<<11&65535,k=j>>5|k<<11&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=((k&=65535)^E^O)+M+28061,V+=(R+=(j^w^N)+n+24866)>>16,U=(R&=65535)>>16|(V&=65535)<<0&65535,V=V>>16|R<<0&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=((V&=65535)^k^E)+I+64997,O+=(N+=(R^j^w)+u+14348)>>16,U=(N&=65535)>>9|(O&=65535)<<7&65535,O=O>>9|N<<7&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=((O&=65535)^V^k)+F+42174,E+=(w+=(N^R^j)+r+59972)>>16,U=(E&=65535)>>12|(w&=65535)<<4&65535,E=w>>12|E<<4&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=((E&=65535)^O^V)+g+19422,k+=(j+=(w^N^R)+o+53161)>>16,U=(k&=65535)>>5|(j&=65535)<<11&65535,k=j>>5|k<<11&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=((k&=65535)^E^O)+y+63163,V+=(R+=(j^w^N)+A+19296)>>16,U=(R&=65535)>>16|(V&=65535)<<0&65535,V=V>>16|R<<0&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=((V&=65535)^k^E)+L+48831,O+=(N+=(R^j^w)+_+48240)>>16,U=(N&=65535)>>9|(O&=65535)<<7&65535,O=O>>9|N<<7&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=((O&=65535)^V^k)+Y+10395,E+=(w+=(N^R^j)+f+32454)>>16,U=(E&=65535)>>12|(w&=65535)<<4&65535,E=w>>12|E<<4&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=((E&=65535)^O^V)+x+60065,k+=(j+=(w^N^R)+a+10234)>>16,U=(k&=65535)>>5|(j&=65535)<<11&65535,k=j>>5|k<<11&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=((k&=65535)^E^O)+v+54511,V+=(R+=(j^w^N)+c+12421)>>16,U=(R&=65535)>>16|(V&=65535)<<0&65535,V=V>>16|R<<0&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=((V&=65535)^k^E)+p+1160,O+=(N+=(R^j^w)+C+7429)>>16,U=(N&=65535)>>9|(O&=65535)<<7&65535,O=O>>9|N<<7&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=((O&=65535)^V^k)+D+55764,E+=(w+=(N^R^j)+s+53305)>>16,U=(E&=65535)>>12|(w&=65535)<<4&65535,E=w>>12|E<<4&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=((E&=65535)^O^V)+S+59099,k+=(j+=(w^N^R)+b+39397)>>16,U=(k&=65535)>>5|(j&=65535)<<11&65535,k=j>>5|k<<11&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=((k&=65535)^E^O)+T+8098,V+=(R+=(j^w^N)+l+31992)>>16,U=(R&=65535)>>16|(V&=65535)<<0&65535,V=V>>16|R<<0&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=((V&=65535)^k^E)+z+50348,O+=(N+=(R^j^w)+e+22117)>>16,U=(N&=65535)>>9|(O&=65535)<<7&65535,O=O>>9|N<<7&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=(V^(65535-k|(O&=65535)))+x+62505,E+=(w+=(R^(65535-j|N))+a+8772)>>16,U=(E&=65535)>>10|(w&=65535)<<6&65535,E=w>>10|E<<6&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=(O^(65535-V|(E&=65535)))+y+17194,k+=(j+=(N^(65535-R|w))+A+65431)>>16,U=(k&=65535)>>6|(j&=65535)<<10&65535,k=j>>6|k<<10&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=(E^(65535-O|(k&=65535)))+I+43924,V+=(R+=(w^(65535-N|j))+u+9127)>>16,U=(V&=65535)>>1|(R&=65535)<<15&65535,V=R>>1|V<<15&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=(k^(65535-E|(V&=65535)))+m+64659,O+=(N+=(j^(65535-w|R))+d+41017)>>16,U=(N&=65535)>>11|(O&=65535)<<5&65535,O=O>>11|N<<5&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=(V^(65535-k|(O&=65535)))+S+25947,E+=(w+=(R^(65535-j|N))+b+22979)>>16,U=(E&=65535)>>10|(w&=65535)<<6&65535,E=w>>10|E<<6&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=(O^(65535-V|(E&=65535)))+v+36620,k+=(j+=(N^(65535-R|w))+c+52370)>>16,U=(k&=65535)>>6|(j&=65535)<<10&65535,k=j>>6|k<<10&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=(E^(65535-O|(k&=65535)))+L+65519,V+=(R+=(w^(65535-N|j))+_+62589)>>16,U=(V&=65535)>>1|(R&=65535)<<15&65535,V=R>>1|V<<15&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=(k^(65535-E|(V&=65535)))+F+34180,O+=(N+=(j^(65535-w|R))+r+24017)>>16,U=(N&=65535)>>11|(O&=65535)<<5&65535,O=O>>11|N<<5&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=(V^(65535-k|(O&=65535)))+B+28584,E+=(w+=(R^(65535-j|N))+i+32335)>>16,U=(E&=65535)>>10|(w&=65535)<<6&65535,E=w>>10|E<<6&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=(O^(65535-V|(E&=65535)))+T+65068,k+=(j+=(N^(65535-R|w))+l+59104)>>16,U=(k&=65535)>>6|(j&=65535)<<10&65535,k=j>>6|k<<10&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=(E^(65535-O|(k&=65535)))+p+41729,V+=(R+=(w^(65535-N|j))+C+17172)>>16,U=(V&=65535)>>1|(R&=65535)<<15&65535,V=R>>1|V<<15&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=(k^(65535-E|(V&=65535)))+Y+19976,O+=(N+=(j^(65535-w|R))+f+4513)>>16,U=(N&=65535)>>11|(O&=65535)<<5&65535,O=O>>11|N<<5&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),E+=(V^(65535-k|(O&=65535)))+g+63315,E+=(w+=(R^(65535-j|N))+o+32386)>>16,U=(E&=65535)>>10|(w&=65535)<<6&65535,E=w>>10|E<<6&65535,E+=O,(w=U+N)>65535&&(w&=65535,E++),k+=(O^(65535-V|(E&=65535)))+M+48442,k+=(j+=(N^(65535-R|w))+n+62005)>>16,U=(k&=65535)>>6|(j&=65535)<<10&65535,k=j>>6|k<<10&65535,k+=E,(j=U+w)>65535&&(j&=65535,k++),V+=(E^(65535-O|(k&=65535)))+z+10967,V+=(R+=(w^(65535-N|j))+e+53947)>>16,U=(V&=65535)>>1|(R&=65535)<<15&65535,V=R>>1|V<<15&65535,V+=k,(R=U+j)>65535&&(R&=65535,V++),O+=(k^(65535-E|(V&=65535)))+D+60294,O+=(N+=(j^(65535-w|R))+s+54161)>>16,U=(N&=65535)>>11|(O&=65535)<<5&65535,O=O>>11|N<<5&65535,O+=V,(N=U+R)>65535&&(N&=65535,O++),O&=65535,U=this.a_[0]+=w,this.a_[1]+=E,U>65535&&(this.a_[0]-=65536,this.a_[1]++),this.a_[1]&=65535,U=this.b_[0]+=N,this.b_[1]+=O,U>65535&&(this.b_[0]-=65536,this.b_[1]++),this.b_[1]&=65535,U=this.c_[0]+=R,this.c_[1]+=V,U>65535&&(this.c_[0]-=65536,this.c_[1]++),this.c_[1]&=65535,U=this.d_[0]+=j,this.d_[1]+=k,U>65535&&(this.d_[0]-=65536,this.d_[1]++),this.d_[1]&=65535},int2hex8:function(t){var h,a,r="",e="0123456789abcdef";for(h=0;h<4;h++)r+=e.charAt((a=t>>>8*h)>>4&15),r+=e.charAt(15&a);return r},update_std:function(t,h){var a,r,e,c,o,d,C,A,i,s,_,n,b,f,u,l,x=this.a_,F=this.b_,z=this.c_,v=this.d_;1==h?(a=t.charCodeAt(0)|t.charCodeAt(1)<<8|t.charCodeAt(2)<<16|t.charCodeAt(3)<<24,r=t.charCodeAt(4)|t.charCodeAt(5)<<8|t.charCodeAt(6)<<16|t.charCodeAt(7)<<24,e=t.charCodeAt(8)|t.charCodeAt(9)<<8|t.charCodeAt(10)<<16|t.charCodeAt(11)<<24,c=t.charCodeAt(12)|t.charCodeAt(13)<<8|t.charCodeAt(14)<<16|t.charCodeAt(15)<<24,o=t.charCodeAt(16)|t.charCodeAt(17)<<8|t.charCodeAt(18)<<16|t.charCodeAt(19)<<24,d=t.charCodeAt(20)|t.charCodeAt(21)<<8|t.charCodeAt(22)<<16|t.charCodeAt(23)<<24,C=t.charCodeAt(24)|t.charCodeAt(25)<<8|t.charCodeAt(26)<<16|t.charCodeAt(27)<<24,A=t.charCodeAt(28)|t.charCodeAt(29)<<8|t.charCodeAt(30)<<16|t.charCodeAt(31)<<24,i=t.charCodeAt(32)|t.charCodeAt(33)<<8|t.charCodeAt(34)<<16|t.charCodeAt(35)<<24,s=t.charCodeAt(36)|t.charCodeAt(37)<<8|t.charCodeAt(38)<<16|t.charCodeAt(39)<<24,_=t.charCodeAt(40)|t.charCodeAt(41)<<8|t.charCodeAt(42)<<16|t.charCodeAt(43)<<24,n=t.charCodeAt(44)|t.charCodeAt(45)<<8|t.charCodeAt(46)<<16|t.charCodeAt(47)<<24,b=t.charCodeAt(48)|t.charCodeAt(49)<<8|t.charCodeAt(50)<<16|t.charCodeAt(51)<<24,f=t.charCodeAt(52)|t.charCodeAt(53)<<8|t.charCodeAt(54)<<16|t.charCodeAt(55)<<24,u=t.charCodeAt(56)|t.charCodeAt(57)<<8|t.charCodeAt(58)<<16|t.charCodeAt(59)<<24,l=t.charCodeAt(60)|t.charCodeAt(61)<<8|t.charCodeAt(62)<<16|t.charCodeAt(63)<<24):(a=t.charCodeAt(0)|t.charCodeAt(1)<<16,r=t.charCodeAt(2)|t.charCodeAt(3)<<16,e=t.charCodeAt(4)|t.charCodeAt(5)<<16,c=t.charCodeAt(6)|t.charCodeAt(7)<<16,o=t.charCodeAt(8)|t.charCodeAt(9)<<16,d=t.charCodeAt(10)|t.charCodeAt(11)<<16,C=t.charCodeAt(12)|t.charCodeAt(13)<<16,A=t.charCodeAt(14)|t.charCodeAt(15)<<16,i=t.charCodeAt(16)|t.charCodeAt(17)<<16,s=t.charCodeAt(18)|t.charCodeAt(19)<<16,_=t.charCodeAt(20)|t.charCodeAt(21)<<16,n=t.charCodeAt(22)|t.charCodeAt(23)<<16,b=t.charCodeAt(24)|t.charCodeAt(25)<<16,f=t.charCodeAt(26)|t.charCodeAt(27)<<16,u=t.charCodeAt(28)|t.charCodeAt(29)<<16,l=t.charCodeAt(30)|t.charCodeAt(31)<<16),F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=(F=(z=(v=(x=F+((x+=a+3614090360+(F&z|~F&v))<<7|x>>>25))+((v+=r+3905402710+(x&F|~x&z))<<12|v>>>20))+((z+=e+606105819+(v&x|~v&F))<<17|z>>>15))+((F+=c+3250441966+(z&v|~z&x))<<22|F>>>10))+((x+=o+4118548399+(F&z|~F&v))<<7|x>>>25))+((v+=d+1200080426+(x&F|~x&z))<<12|v>>>20))+((z+=C+2821735955+(v&x|~v&F))<<17|z>>>15))+((F+=A+4249261313+(z&v|~z&x))<<22|F>>>10))+((x+=i+1770035416+(F&z|~F&v))<<7|x>>>25))+((v+=s+2336552879+(x&F|~x&z))<<12|v>>>20))+((z+=_+4294925233+(v&x|~v&F))<<17|z>>>15))+((F+=n+2304563134+(z&v|~z&x))<<22|F>>>10))+((x+=b+1804603682+(F&z|~F&v))<<7|x>>>25))+((v+=f+4254626195+(x&F|~x&z))<<12|v>>>20))+((z+=u+2792965006+(v&x|~v&F))<<17|z>>>15))+((F+=l+1236535329+(z&v|~z&x))<<22|F>>>10))+((x+=r+4129170786+(F&v|z&~v))<<5|x>>>27))+((v+=C+3225465664+(x&z|F&~z))<<9|v>>>23))+((z+=n+643717713+(v&F|x&~F))<<14|z>>>18))+((F+=a+3921069994+(z&x|v&~x))<<20|F>>>12))+((x+=d+3593408605+(F&v|z&~v))<<5|x>>>27))+((v+=_+38016083+(x&z|F&~z))<<9|v>>>23))+((z+=l+3634488961+(v&F|x&~F))<<14|z>>>18))+((F+=o+3889429448+(z&x|v&~x))<<20|F>>>12))+((x+=s+568446438+(F&v|z&~v))<<5|x>>>27))+((v+=u+3275163606+(x&z|F&~z))<<9|v>>>23))+((z+=c+4107603335+(v&F|x&~F))<<14|z>>>18))+((F+=i+1163531501+(z&x|v&~x))<<20|F>>>12))+((x+=f+2850285829+(F&v|z&~v))<<5|x>>>27))+((v+=e+4243563512+(x&z|F&~z))<<9|v>>>23))+((z+=A+1735328473+(v&F|x&~F))<<14|z>>>18))+((F+=b+2368359562+(z&x|v&~x))<<20|F>>>12))+((x+=d+4294588738+(F^z^v))<<4|x>>>28))+((v+=i+2272392833+(x^F^z))<<11|v>>>21))+((z+=n+1839030562+(v^x^F))<<16|z>>>16))+((F+=u+4259657740+(z^v^x))<<23|F>>>9))+((x+=r+2763975236+(F^z^v))<<4|x>>>28))+((v+=o+1272893353+(x^F^z))<<11|v>>>21))+((z+=A+4139469664+(v^x^F))<<16|z>>>16))+((F+=_+3200236656+(z^v^x))<<23|F>>>9))+((x+=f+681279174+(F^z^v))<<4|x>>>28))+((v+=a+3936430074+(x^F^z))<<11|v>>>21))+((z+=c+3572445317+(v^x^F))<<16|z>>>16))+((F+=C+76029189+(z^v^x))<<23|F>>>9))+((x+=s+3654602809+(F^z^v))<<4|x>>>28))+((v+=b+3873151461+(x^F^z))<<11|v>>>21))+((z+=l+530742520+(v^x^F))<<16|z>>>16))+((F+=e+3299628645+(z^v^x))<<23|F>>>9))+((x+=a+4096336452+(z^(~v|F)))<<6|x>>>26))+((v+=A+1126891415+(F^(~z|x)))<<10|v>>>22))+((z+=u+2878612391+(x^(~F|v)))<<15|z>>>17))+((F+=d+4237533241+(v^(~x|z)))<<21|F>>>11))+((x+=b+1700485571+(z^(~v|F)))<<6|x>>>26))+((v+=c+2399980690+(F^(~z|x)))<<10|v>>>22))+((z+=_+4293915773+(x^(~F|v)))<<15|z>>>17))+((F+=r+2240044497+(v^(~x|z)))<<21|F>>>11))+((x+=i+1873313359+(z^(~v|F)))<<6|x>>>26))+((v+=l+4264355552+(F^(~z|x)))<<10|v>>>22))+((z+=C+2734768916+(x^(~F|v)))<<15|z>>>17))+((F+=f+1309151649+(v^(~x|z)))<<21|F>>>11))+((x+=o+4149444226+(z^(~v|F)))<<6|x>>>26))+((v+=n+3174756917+(F^(~z|x)))<<10|v>>>22))+((z+=e+718787259+(x^(~F|v)))<<15|z>>>17))+((F+=s+3951481745+(v^(~x|z)))<<21|F>>>11),this.a_=this.a_+x&4294967295,this.b_=this.b_+F&4294967295,this.c_=this.c_+z&4294967295,this.d_=this.d_+v&4294967295},fillzero:function(t){for(var h="",a=0;a<t;a++)h+="\0";return h},main:function(t,h,a,r,e){if(1==e){for(var c=8*h;h>=64;)r[a](t,e),t=t.substr(64),h-=64;t+="",h>=56?(t+=this.fillzero(63-h),r[a](t,e),t=this.fillzero(56)):t+=this.fillzero(55-h),t+=String.fromCharCode(255&c,c>>>8&255,c>>>16&255,c>>>24),t+="\0\0\0\0",r[a](t,e)}else{for(c=16*h;h>=32;)r[a](t,e),t=t.substr(32),h-=32;t+="",h>=28?(t+=this.fillzero(31-h),r[a](t,e),t=this.fillzero(28)):t+=this.fillzero(27-h),t+=String.fromCharCode(65535&c,c>>>16),t+="\0\0",r[a](t,e)}},VERSION:"1.0",BY_ASCII:0,BY_UTF16:1,calc_Fx:function(t,h){var a=2==arguments.length&&h==this.BY_UTF16?2:1;return this.a_=[8961,26437],this.b_=[43913,61389],this.c_=[56574,39098],this.d_=[21622,4146],this.main(t,t.length,"update_Fx",this,a),this.int2hex8_Fx(this.a_)+this.int2hex8_Fx(this.b_)+this.int2hex8_Fx(this.c_)+this.int2hex8_Fx(this.d_)},calc_std:function(t,h){var a=2==arguments.length&&h==this.BY_UTF16?2:1;return this.a_=1732584193,this.b_=4023233417,this.c_=2562383102,this.d_=271733878,this.main(t,t.length,"update_std",this,a),this.int2hex8(this.a_)+this.int2hex8(this.b_)+this.int2hex8(this.c_)+this.int2hex8(this.d_)}}};new function(){CybozuLabs.MD5.calc=navigator.userAgent.match(/Firefox/)?CybozuLabs.MD5.calc_Fx:CybozuLabs.MD5.calc_std};
    </script>
<? }
  function ViewStartForm() {
    $meurl = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ) ? "https://" : "http://").$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
    $title = "Setup | ATK-FM " . fmversion;
?>
<!DOCTYPE html>
<html lang="ja" dir="ltr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <title><?php echo $title; ?></title>
    <base href="<?=$meurl?>">
    <meta name="robots" content="noindex">
    <link rel="canonical" href="<?=$meurl?>">
    <style>a{color: #00ff00;position: relative;display: inline-block;transition: .3s;}a::after {position: absolute;bottom: 0;left: 50%;content: '';width: 0;height: 2px;background-color: #31aae2;transition: .3s;transform: translateX(-50%);}a:hover::after{width: 100%;}</style>
  </head>
  <body style="background-color:#e6e6fa;text:#363636;">
    <div align="center">
      <h1><?=$title?></h1>
      <form action='<?=$meurl?>' method='POST'>
        <p>パスワード: <input type="password" name="password" title="パスワード" placeholder="パスワードを入力してください" required></p>
        <input type="submit" name="setup" value="Setup" title="Setup">
      </form>
      <?php
        if (file_exists(__DIR__ . "/ATK-FM/password.txt"))
          echo "<p>注意: バージョン1.1.5以前のバージョンからバージョン1.1.5以降のバージョンにアップデートした場合、根本的なパスワードの管理方法が変わる為、パスワードは初期化されます。</p>";
      ?>
    </div>
  </body>
</html>
<?php
    exit();
  }
  function ViewPasswordResetForm() {
    $meurl = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ) ? "https://" : "http://").$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
    $title = "パスワード変更 | ATK-FM " . fmversion;
?>
<!DOCTYPE html>
<html lang="ja" dir="ltr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <title><?php echo $title; ?></title>
    <base href="<?=$meurl?>">
    <meta name="robots" content="noindex">
    <link rel="canonical" href="<?=$meurl?>">
    <style>a{color: #00ff00;position: relative;display: inline-block;transition: .3s;}a::after {position: absolute;bottom: 0;left: 50%;content: '';width: 0;height: 2px;background-color: #31aae2;transition: .3s;transform: translateX(-50%);}a:hover::after{width: 100%;}</style>
  </head>
  <body style="background-color:#e6e6fa;text:#363636;">
    <div align="center">
      <h1><?=$title?></h1>
      <form action='<?=$meurl?>' method='POST'>
        <p>現在のパスワード: <input type="password" name="passwordold" title="パスワード" placeholder="パスワードを入力してください" required></p>
        <p>変更後のパスワード: <input type="password" name="passwordnew" title="パスワード" placeholder="パスワードを入力してください" required></p>
        <input type="submit" name="save-password" value="保存" title="保存">
      </form>
    </div>
  </body>
</html>
<?php
    exit();
  }
  $title = "ATK-FM " . fmversion;
  CheckUpdate(false, false);
?>
<!DOCTYPE html>
<html lang="ja" dir="ltr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <title><?php echo $title; ?></title>
    <base href="<?=$meurl?>">
    <meta name="robots" content="noindex">
    <link rel="canonical" href="<?=$meurl?>">
    <?php ViewActiveTKJS(); ?>
    <script type="text/javascript">
    var fzimage = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAGMElEQVR4Xu1bbYgUdRh/nv/slZDezs7OHuUbBAUiJgdKVtCLiJbZh76cN3N6UB8KwyyDUi4UjCJTLCwTIgqC053V+1IfyrTCXqA3PDASEQoK35Lb2ZnZsw/m7fz/MXs3srvOy3929vaWZffTcvO8/ub5P/Pc7PNDACDQoI8p9W9FQt5ukLlQMwzYBABcRMALgHACbRhNGtqJUMUKAWwUAOPyxico2J9FcT49sizbVYJ9t1m533jsNwQAS1KeBoIf8ThsigwDAwD6xIL2bZi/2ABYGeUVYPhWmKOZuE7txD2SOXw2yHcsAExZ3YEAr93kgMEqHvTjglKQNsxGdn2p0JVYChSeYQC9NTavUDuxOgiEugEw0uo2grDbM4kmAVDr28oMHAPG1lT/nWVFPTfoB3ZdAJiS8iISfMf3Ds4QAE48ZlrdjwhbKmNDxHXJfPZLr3gjA2CklecI4vuB5RsAgJEanB+kK5nDF+MeDUtWvwaAla4dCnSvpB8Zig1AsUfdxCgcDA0wAIBiZuA9xthmz2AQDybz2RdC7YcImBnleWT4boXY96Ku3QCkqjp45wAz078FGdnPFdwMA2Cl1UcA4Rs3Vgbsr5Seu6vuCjBl9SUE2MeVvCM0wwAYqb45REhYFQBMpPTcrLoAqOc5z0pCb8o69LuXQyOtbg/sAQVtDzfQAYKWrNqVl0VdEyIDYGXUIWDwRuSAJlASi9liZL0GKsQG4Kqk7LQJ7ooakw3s9bSei6wX1U+YfGwAag2EOSxfp0wRjdwIl+w0CzUeAAar/GKeQGrdcn3238nxj280nmnOL9R8wwHwayKhkdQIOHMAAFO89TDXiDnAsd3SAEz3INQBoNUrABjLeB4BxHzbH4GoPaNe+ZbtAfUmFFWvA0DcUZgXwah3plnyvPH7vhDhNRA1ocn3AbDI8x8ThHNt3wSb8UKkMwe0+hzAGFvtfQTwq7Y/AlF7Rr3yvD2s6U2w3oSi6nUA6MwBMd8J8pZQ1NIsPwZtds2zCQo4q+2bYGcOaMIvQ51BqJUHoctzn/X8hcbtCXMvf+jZH6L2Gt4e1pkD/H4c5UUw6p1pljxv/J0KaHYFmD3KhqAqSI3lDjeiSlq2AjpzQGcOmP4VmZYehMZT6+8POuPd5tGf2roHNCI5Hhst2wR5gm+ETAeAVn0hYsmqc8bv9bnLv4q6FtgjeKujJSvgDCxOzJd7LwCwHu9EcOyifnrBEjhb4k3UT64lARiXN95NwT4XlBwBYVG3fuiPtgSgmOp/lAnki6Dk0KaPJ80jx9sSAKNH3URCdo0pgc3SmPbBjANgyso1BOxyA7EpSaaNw//GCcySVYdfsK1sg8B2cUwrr99aPerLQMHdEN0r6prnZjevb4dIIRB6Y1HTIVdFXpU1ZeVPBLzTdUrt0oOSOfIjbxBeckVZ1RjAeucaY/BUqqANO9/NtDqICJ843xHgaFLX1Dh+jFTfA0RI/ODaqGtZ2pLVkwDwkGsECWxOxizNqkcghTWioZU3ui1JXQUEXLpb7Eehx1p/9HX52j1hBDid1LVlce6MJQ/84z4CbcoWpo3cJcdeQVLmCQTPT9rGMVHP3hHHT1FWR6v4Qwg7xLzmSe/xfSNUlNQ1jMCxqkAQT4j57Np6ghufo0r0Vsi7urWLl5XPbfIfZLqvag71LfLHizeEFNb6ESoDKTOWrAwD4EBlFIzBgVRB2xo1Mj3dtzyBiV/K5x8gn9K12yttmLJ6BQHK63MlVlohF0ZORfXhxRcCiEGaysv9vV1AnF7QXRPMSYbsU6R4htLSqGSOXA0L1pKUPiCYm5I7ZQOrqi4B0Kms5eXrnEvXZWIESSxjhC1Bhk9W8oSm/IxPAF2Z0Y+c9osvlDRVSz8JS/Sm61PsEcshSiC8yaXP4FWxoO1plO8gn6EAOMpGanAxEezPAdhCrgSqzswkibIgK7sEwJ08+i7noH4A8Dy1hXVhrNFyy+UlTU2CMDFU2xNCE5oaeCb1S540mlobLuW1ZkAKdTUpwLLU7trNk3wkAFzvxczAYzazHyZA7mPAFgDA/MqJ0StKt+MXpL4VAkk4pMZ5PtlcsmnpQNoYKTfLMNJGJX2eAv1ZQOE7P4KkH3r/AzoXzu6+5mX7AAAAAElFTkSuQmCC';
    var fileimage = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAIAAAC0Ujn1AAAACXBIWXMAAA7EAAAOxAGVKw4bAAADIUlEQVRIia2Wv0vrUBTHz7kJDZGKUCmEKoooWpQiKP4aVHAQKuKPriI4OLmoi6Cgg+CgSB0c/B86dFIqDi0ILukmoqgguvg7FmpIqWnufUMe9b3YpM3jfack53s+OZyce2+QMQalpOv65ubmyckJIaSkoaQopcFgcHd31+/3ox06k8mMjIxcXV1JklQJ3TAMSmkmkxEEIZlMdnZ28haHoiiHh4eqqmqa9vr6WltbOzk56fF47CowRQgZGhoKhUKrq6uJRIJSCgBWdDKZnJ+fZ4wVKz04OHAuFgAopdlsdnx8vLq6uhj6jX57e3t8fOR5/ubmplAohMPhrq4u8+UO8nq9TU1NT09P6+vrqqpaojwA6Lq+srISj8d5nv/6+gKA09NTWZadm4CIkiTFYrHu7u6tra2fZh4ACoXC3d0dISQcDouiCADOUJObTqevr68VRZEkqaTnu9eBQGBvb8/v9ztDi1pbW7u8vHQwuJhZt/prQhDx/f09Fot9fn4i4k83YwwRBwcHBwYG3KEJIRcXFxsbG3ZoADAMY2FhwTXaMIy+vr5EIqFpmh0aEZubm8tyrWgAEAShp6enkkzXaADQNK1QKNglIKIoijxfItEJzXHc+fn54uJiNpu1+4yMsdnZ2eXlZXdoxhjHcVVVVbqu2/WaMSYIQlmuFU0p7ejoiMfjzrvHv6BdZbpDI2Iul0un0w7DBwDBYLCxsdEdmhAiy/L09HTZJbO/v+8OTSkNhULRaPTn5lsUIvb395flWtGMMZ/PNzc3V0lmWf3Pnc+c+uKttdf39/fRaNRuyZj5ExMTkUjkz4ccx52dnc3MzMiyXBqNiM/Pz8fHxw5oSml9fb0FjYgvLy+pVIpS2tra6vP5rGjDMHp7e1OpVD6fdxg+8yRCRMMwjo6Oampqcrnc6Ojozs4OAHi93rq6Oiva7IkZqESU0u3tbfM6EAi0tbX9Gf1G5/P529vbj4+PsmcuAHAcpyiKx+NZWlpqb28HgJ8TyReLfXh4mJqaIoRUgkZEVVV5nh8bGxseHi7p4QFAFMVIJEIIcfvn2NDQ0NLSYmf4BTPBaDOjZahRAAAAAElFTkSuQmCC';
    var ffimage = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAMAAAAM7l6QAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAACplBMVEUAAADFljWxgyjmtVTJmjnuvVvislHmuFvot1XsxHXYqkz4yWzsu1f4xmLuulP4w1ruuE34wVPutkf4vkvutEH4vETusjv4uTzusDX4tjXuri/4tC3urCn4siburCj4sSTtqyj3sSTipizpqSnFljXElTTBkjKvgSjgsE/xwF70w2Hzw2HywWDmtVSugCHnt1X8y2n5yGbquljmtlTnt1Xnt1TltVPaq0n8y2jxwmPsvmDtvmDtvmHrvF/crlDislD1xGDht2LUpkjis1TpulrmtVL5x2PpuFTntE78x17pt1Dns0n8xFXptUrnsUT8wU3ps0Xnrz/8v0XpsUDnrjr8vD3przrnrDb8uTXprjXnqjD8tizprDDnqSz8tCTpqivnqCr8syLpqin7syPpqSneoy7sqyjvrCfurCftqyjfpC3/zmz/zmv+zWv5x2P4x2P4xmPsvV7twGjyx3HyyHPyyXbyynjyynryy3zyzH7yzIHyzYPyzobwzILktVbktVfktVjvwmj+033/1oP/14b/2Ir/2o3/25H/3JX/3Zj/3pz/4KD/4aP91on9y2b9y2j9zGv9zW//0XX/0nj/03z/1ID/14f/2Y7/25L/3Jb/3Zr/2Iv9zGr/ymD/y2L/zGb/zWr/zm7/0HL/0nn/1H3/1YH/1oX/2In/2Y3/1YP9yF7/x1f/x1j/yFv/yl//y2P/zGf/z2//0HP/0nf/03v/1H//yV39xVb/xE//xE7/xlP/yFz/y2T/zWj/zm3/z3D/xVL9wk3/wUb/wkf/w0z/xVD/xlX/yFn/yV7/xVH/wUX9v0X/vj3/v0D/wUT/wkn/w0v9vT3/uzT/uzX/vDf/vTj/vDb9ujT/uSv/uCv9tyz/tiP9tCT/tSH9tCL+tSH8syL///8SRQNdAAAAa3RSTlMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADCgkCQcHCwr9fBFb974BPUVFRIf3++/v7/GZW/WdW/WdW/WdW/WdW/WdW/WdW/WdW/WdW/WdW/WdW/WdW/Wf9ZzKUlZWVPFGEemsAAAABYktHROFfCM+mAAAAB3RJTUUH5QQQCRsObpsM3AAAAV9JREFUKM9jYBjKgFFFFQzU1JmwSTNraGppA4GOrh4LFmlWfYNsMDA0MjYBAVNTUxMzcza4tEV2DhBk51paWYOBjY2NrR07VJrD3iEvPz8/r6CwqLiktKy8orKquqbWkRMqzeXkXFdXV9/Q0NjU3NLa1t7R2dXd0+vCDZXmcXXr6+vrnzBx0uQpU5untU6fMXPW7DnuvFBpPg/PuUAwb/6ChYsmLV6ydNnyFSvnr/Lih0oLePusBoI1a9et35CzcdPmLVsnbVu93VcQKi3k579jJxDs2LV699w9e/ft33Ng546DAcJQaZHAoENgcPjI0WPHT2w7eejUoUOng0Wh0mIhoWcg4Oy58xcOnwUzL4aJQ6UlwiMuQcDlK1evQZnXIyWh0lJR0TduooNbMdJQaZnYuNsY4E68LFRaLiHxLga4lyQPlVZISL6PAR6kKEKllVLT0jPQQHpmlvJAJ1OKAAAOqNlNFy4CpgAAACV0RVh0ZGF0ZTpjcmVhdGUAMjAyMS0wNC0xNlQwOToyNzoxNCswMDowMCMrIaoAAAAldEVYdGRhdGU6bW9kaWZ5ADIwMjEtMDQtMTZUMDk6Mjc6MTQrMDA6MDBSdpkWAAAAAElFTkSuQmCC';
    function MakeZip(ed){_("info").innerHTML="ZIP圧縮しています。。(この処理には数分間かかる場合があります)",$.ajax({url:"<?=$meurl?>?ajax-typeof=make-zip&ajax-option="+encodeURIComponent(ed),type:"get",success:function(e){_("info").innerHTML=e,GetFileList()}}).fail(function(e,n,t){_("info").innerHTML="エラー:zip圧縮出来ませんでした。詳細 : "+t})}
    function CloneItem(){_("info").innerHTML="URLからファイルをアップロードしています。。(この処理には数分間かかる場合があります)",$.ajax({url:"<?=$meurl?>?ajax-typeof=uploadfromurl&ajax-option="+encodeURIComponent(_("clone-url").value),type:"get",success:function(e){_("info").innerHTML=e,GetFileList()}}).fail(function(e,n,t){_("info").innerHTML="エラー:URLからファイルをアップロード出来ませんでした。詳細 : "+t})}
    function edit(e){_("selecteditem").value=e,_("pp").style="display:block;text-align:center;position:fixed;bottom:10%;left:10%;height:80%;width:80%;background-color:#00ff7f;color:#080808;overflow-x:hidden;overflow-y:visible;",_("run-file").style="display:block"}
    function closep(){_("selecteditem").value="",_("pp").style="display:none;text-align:center;position:fixed;bottom:10%;left:10%;height:80%;width:80%;background-color:#00ff7f;color:#080808;overflow-x:hidden;overflow-y:visible;",_("run-file").style="display:none;"}
    function GetFileList(pw){let dds="";if(pw)dds="&ajax-option2="+atk.encode(pw);"/"!=_("directory-root").value.substr(-1,1)&&(_("directory-root").value=_("directory-root").value+"/"),$.ajax({url:"<?=$meurl?>?ajax-typeof=get-directory&ajax-option="+encodeURIComponent(_("directory-root").value)+dds,type:"get",dataType:"json",success:function(e){if(e["access"]=="ok"){_("info").innerHTML="アクセスに成功しました！";e["access"]="s";}if(_("server-data").innerHTML="",e["atk-fm-error"])_("info").innerHTML=e["atk-fm-error"];else if(e["access"]=="denied"){_("server-data").innerHTML="";_("info").innerHTML="フォルダーにアクセスするには"+e["admin"]+"さんの許可が必要です。[<a href='javascript:void(NotDenied(\"" + _("directory-root").value + "\"))'>続行</a>]";}else if(e["access"]=="denied::PW_NOT_MATCH"){_("server-data").innerHTML="";_("info").innerHTML="パスワードが違います。フォルダーにアクセスするには"+e["admin"]+"さんの許可が必要です。[<a href='javascript:void(NotDenied(\""+_("directory-root").value+"\"))'>続行</a>]";}else for(let n in e){let t=e[n],o=n.slice(n.lastIndexOf("/")+1);_("server-data").innerHTML="a"==t?_("server-data").innerHTML+"<img src='"+fileimage+"' style='width:22px;height:22px;'> "+o+"<a href='<?=$meurl?>?ajax-typeof=get-item&ajax-option="+encodeURIComponent(n)+"' target='_blank' rel='noopener noreferrer'><input type='button' value='開く'></a><a href='javascript:edit(\""+n+"\");'><input type='button' value='操作'></a><br>":"b"==t?_("server-data").innerHTML+"<img src='"+ffimage+"' style='width:22px;height:22px;'>"+o+'(フォルダー)<a href=\'javascript:_("directory-root").value="'+n+"\";GetFileList();'><input type='button' value='開く'></a><a href='javascript:DirectoryRemove(\""+n+"\");'><input type='button' value='削除'></a><a href='javascript:MakeZip(\""+n+"\");'><input type='button' value='圧縮'></a><a href='javascript:RenameItem(\""+n+"\");'><input type='button' value='リネーム'></a><a href='javascript:CopyD(\""+n+"\");'><input type='button' value='コピー'></a><a href='javascript:CountDirectoryFiles(\""+n+"\");'><input type='button' value='詳細'></a><br>":"c"==t?_("server-data").innerHTML+"<img src='"+fzimage+"' style='width:22px;height:22px;'>"+o+'(zip)<a href=\'javascript:_("directory-root").value="'+n+"\";GetFileList();'><input type='button' value='プレビュー'></a><a href='javascript:OpenZip(\""+n+"\");'><input type='button' value='展開'></a><a href='javascript:FileRemove(\""+n+"\");'><input type='button' value='削除'></a><a href='<?=$meurl?>?ajax-typeof=download-item&ajax-option="+encodeURIComponent(n)+"'><input type='button' value='ダウンロード'></a><a href='javascript:RenameItem(\""+n+"\");'><input type='button' value='リネーム'></a><a href='javascript:CopyItem(\""+n+"\");'><input type='button' value='コピー'></a><a href='javascript:GetSize(\""+n+"\");'><input type='button' value='容量取得'></a><br>":"s"==t?_("server-data").innerHTML:"d"==t?_("server-data").innerHTML+"<img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAIAAAC0Ujn1AAAACXBIWXMAAA7EAAAOxAGVKw4bAAADIUlEQVRIia2Wv0vrUBTHz7kJDZGKUCmEKoooWpQiKP4aVHAQKuKPriI4OLmoi6Cgg+CgSB0c/B86dFIqDi0ILukmoqgguvg7FmpIqWnufUMe9b3YpM3jfack53s+OZyce2+QMQalpOv65ubmyckJIaSkoaQopcFgcHd31+/3ox06k8mMjIxcXV1JklQJ3TAMSmkmkxEEIZlMdnZ28haHoiiHh4eqqmqa9vr6WltbOzk56fF47CowRQgZGhoKhUKrq6uJRIJSCgBWdDKZnJ+fZ4wVKz04OHAuFgAopdlsdnx8vLq6uhj6jX57e3t8fOR5/ubmplAohMPhrq4u8+UO8nq9TU1NT09P6+vrqqpaojwA6Lq+srISj8d5nv/6+gKA09NTWZadm4CIkiTFYrHu7u6tra2fZh4ACoXC3d0dISQcDouiCADOUJObTqevr68VRZEkqaTnu9eBQGBvb8/v9ztDi1pbW7u8vHQwuJhZt/prQhDx/f09Fot9fn4i4k83YwwRBwcHBwYG3KEJIRcXFxsbG3ZoADAMY2FhwTXaMIy+vr5EIqFpmh0aEZubm8tyrWgAEAShp6enkkzXaADQNK1QKNglIKIoijxfItEJzXHc+fn54uJiNpu1+4yMsdnZ2eXlZXdoxhjHcVVVVbqu2/WaMSYIQlmuFU0p7ejoiMfjzrvHv6BdZbpDI2Iul0un0w7DBwDBYLCxsdEdmhAiy/L09HTZJbO/v+8OTSkNhULRaPTn5lsUIvb395flWtGMMZ/PNzc3V0lmWf3Pnc+c+uKttdf39/fRaNRuyZj5ExMTkUjkz4ccx52dnc3MzMiyXBqNiM/Pz8fHxw5oSml9fb0FjYgvLy+pVIpS2tra6vP5rGjDMHp7e1OpVD6fdxg+8yRCRMMwjo6Oampqcrnc6Ojozs4OAHi93rq6Oiva7IkZqESU0u3tbfM6EAi0tbX9Gf1G5/P529vbj4+PsmcuAHAcpyiKx+NZWlpqb28HgJ8TyReLfXh4mJqaIoRUgkZEVVV5nh8bGxseHi7p4QFAFMVIJEIIcfvn2NDQ0NLSYmf4BTPBaDOjZahRAAAAAElFTkSuQmCC' style='width:22px;height:22px;'> "+o+"(zip内ファイル)<a href='<?=$meurl?>?ajax-typeof=get-item-zip&ajax-option="+encodeURIComponent(_("directory-root").value)+"&ajax-option2="+encodeURIComponent(n)+"' target='_blank' rel='noopener noreferrer'><input type='button' value='表示'></a><a href='javascript:RemoveZip(\""+n+"\");'><input type='button' value='削除'></a><br>":_("server-data").innerHTML+"(未対応のファイル)<br>"}}}).fail(function(e,n,t){_("info").innerHTML="エラー:データを更新出来ませんでした。詳細 : "+t})}
    function DirectoryRemove(e){confirm("本当にディレクトリを削除しますか？")&&$.ajax({url:"<?=$meurl?>?ajax-typeof=remove-directory&ajax-option="+encodeURIComponent(e),type:"get",success:function(e){_("info").innerHTML="ディレクトリを削除しました。",GetFileList()}}).fail(function(e,n,t){_("info").innerHTML="エラー:ディレクトリを削除出来ませんでした。詳細 : "+t})}
    function FileRemove(e){confirm("本当にファイルを削除しますか？")&&$.ajax({url:"<?=$meurl?>?ajax-typeof=remove-item&ajax-option="+encodeURIComponent(e),type:"get",success:function(e){_("info").innerHTML=e,GetFileList()}}).fail(function(e,n,t){_("info").innerHTML="エラー:ファイルを削除出来ませんでした。詳細 : "+t})}
    function AddItem(){$.ajax({url:"<?=$meurl?>?ajax-typeof=add-item&ajax-option="+encodeURIComponent(_("add-item-name").value),type:"get",success:function(e){_("info").innerHTML="ファイルを新規作成しました。",GetFileList()}}).fail(function(e,n,t){_("info").innerHTML="エラー:ファイルを作成出来ませんでした。詳細 : "+t})}
    function UploadFile(){window.startuploaddata = new Date();_("info").innerHTML="ファイルをアップロードしています。。(この処理には数分間かかる場合があります)",$.ajax({xhr:function(){var xhr=new window.XMLHttpRequest();xhr.upload.addEventListener("progress",function(evt){if(evt.lengthComputable){let byou=(new Date().getTime()-window.startuploaddata.getTime())*1000,speed=(evt.loaded / byou),parsent=(evt.loaded/evt.total*100);_("config").innerText="";try{_("config").innerText="ConnectionType: "+navigator.connection.type+"\nConnectionEffectType: "+navigator.connection.effectiveType+"\n";}catch(e){}_("config").innerText+="Upload Time: "+byou+"\nUploaded/Total: "+evt.loaded+"/"+evt.total+"\nUpload %: "+parsent+"\nUpload Speed: "+speed;_("info").innerHTML="ファイルをアップロードしています。。("+parsent.toFixed(2)+"%完了、"+speed.toFixed(2)+"MB/s)";}},false);return xhr;},url:"<?=$meurl?>?ajax-typeof=upload-item",type:"POST",data:new FormData($("#upload-file").get(0)),cache:!1,contentType:!1,processData:!1,dataType:"html"}).done(function(e){_("info").innerHTML=e;_("file").value="",GetFileList()}).fail(function(e,n,t){_("info").innerHTML="エラー:ファイルをアップロード出来ませんでした。詳細 : "+t})}
    function RenameItem(e,n){if(!(n=prompt("パスを入力してください",e)))return!1;$.ajax({url:"<?=$meurl?>?ajax-typeof=rename-item&ajax-option="+encodeURIComponent(e)+"&ajax-option2="+encodeURIComponent(n),type:"get",success:function(e){_("info").innerHTML="ファイル名を変更しました。",GetFileList()}}).fail(function(e,n,t){_("info").innerHTML="エラー:ファイル名を変更出来ませんでした。詳細 : "+t})}
    function CopyItem(e){_("info").innerHTML="ファイルをコピーしています。。",$.ajax({url:"<?=$meurl?>?ajax-typeof=copy-item&ajax-option="+encodeURIComponent(e),type:"get",success:function(e){_("info").innerHTML="ファイルをコピーしました。",GetFileList()}}).fail(function(e,n,t){_("info").innerHTML="エラー:ファイルをコピー出来ませんでした。詳細 : "+t})}
    function AddDirectory(){"/"!=_("directory-root").value.substr(-1,1)&&(_("add-directory-name").value=_("add-directory-name").value+"/"),$.ajax({url:"<?=$meurl?>?ajax-typeof=add-directory&ajax-option="+encodeURIComponent(_("add-directory-name").value),type:"get",success:function(e){_("info").innerHTML="ディレクトリを新規作成しました。",GetFileList()}}).fail(function(e,n,t){_("info").innerHTML="エラー:ディレクトリを作成出来ませんでした。詳細 : "+t})}
    function CountDirectoryFiles(e){_("info").innerHTML="詳細情報を取得しています。。",$.ajax({url:"<?=$meurl?>?ajax-typeof=count-directory-files&ajax-option="+encodeURIComponent(e),type:"get",dataType:"json",success:function(e){_("info").innerHTML=e.File+"個のファイル、"+e.Directory+"個のディレクトリがあります。計測時間:"+e.Time+"s、サイズ:"+e.SIZE,GetFileList()}}).fail(function(e,n,t){_("info").innerHTML="エラー:詳細情報を取得出来ませんでした。詳細 : "+t})}
    function GetSize(e){_("info").innerHTML="ファイルのサイズを取得しています。。",$.ajax({url:"<?=$meurl?>?ajax-typeof=get-filesize&ajax-option="+encodeURIComponent(e),type:"get",success:function(e){_("info").innerHTML="ファイルのサイズ: "+e}}).fail(function(e,n,t){_("info").innerHTML="エラー:ファイルのサイズを取得出来ませんでした。詳細 : "+t})}
    function OpenZip(e){_("info").innerHTML="展開しています。。",$.ajax({url:"<?=$meurl?>?ajax-typeof=open-zip&ajax-option="+encodeURIComponent(e),type:"get",success:function(e){_("info").innerHTML=e,GetFileList()}}).fail(function(e,n,t){_("info").innerHTML="エラー:ファイルを展開出来ませんでした。詳細 : "+t})}
    function RemoveZip(e){confirm("本当にファイルを削除しますか？")&&(_("info").innerHTML="削除しています。。",$.ajax({url:"<?=$meurl?>?ajax-typeof=remove-item-zip&ajax-option="+encodeURIComponent(_("directory-root").value)+"&ajax-option2="+encodeURIComponent(e),type:"get",success:function(e){_("info").innerHTML=e,GetFileList()}}).fail(function(e,n,t){_("info").innerHTML="エラー:ファイルを削除出来ませんでした。詳細 : "+t}))}
    function CopyD(e){_("info").innerHTML="ディレクトリをコピーしています。。",$.ajax({url:"<?=$meurl?>?ajax-typeof=copy-item&ajax-option="+encodeURIComponent(e),type:"get",success:function(e){_("info").innerHTML="ディレクトリをコピーしました。",GetFileList()}}).fail(function(e,n,t){_("info").innerHTML="エラー:ディレクトリをコピー出来ませんでした。詳細 : "+t})}
    function NotDenied(e){let s=prompt("続行するにはパスワードを入力してください");if(s==null||s==false)return false;GetFileList(s);}
    function AccessCt(){_("accessdenysetting").style="display:block;text-align:center;position:fixed;bottom:10%;left:10%;height:80%;width:80%;background-color:#00ff7f;color:#080808;overflow-x:hidden;overflow-y:visible;";}
    function closep2(){_("accessdenysetting").style="display:none;text-align:center;position:fixed;bottom:10%;left:10%;height:80%;width:80%;background-color:#00ff7f;color:#080808;overflow-x:hidden;overflow-y:visible;"}
    window.onload=function(){GetFileList(),(()=>{let e=0,t=0;_("min-upload-file").onsubmit=(async i=>{i.preventDefault();let a=_("file2").files[0];e=a.size;let n=1*_("upload-mb").value*1024*1024;t=Math.ceil(e/n);for(let e=0;e<t;e++){let i=new FormData;i.append("data",a.slice(e*n,(e+1)*n)),i.append("filename",_("file2").files[0].name),_("info").innerText="ファイルを分割アップロードしています。。("+Math.ceil(100*(e+1)/t).toString()+"%完了)",_("config").innerText="Upload %: "+Math.ceil(100*(e+1)/t).toString()+"\nUploaded/Total: "+e+"/"+t;await fetch("https://root.nextchat2.cf/beta/?ajax-typeof=min-upload-item",{body:i,method:"POST",headers:{Accept:"application/json"}}).then(e=>e.json());await new Promise(e=>setTimeout(()=>{e()},10))}return _("info").innerText="ファイルを分割アップロードしました！",_("file2").value="",GetFileList(),!1})})()};
    </script>
    <style>a{color:#0f0;position:relative;display:inline-block}a,a:after{transition:.3s}a:after{position:absolute;bottom:0;left:50%;content:'';width:0;height:2px;background-color:#31aae2;transform:translateX(-50%)}a:hover:after{width:100%}</style>
  </head>
  <body style="background-color:#e6e6fa;text:#363636;">
    <form action='<?=$meurl?>' method='POST'>
      <span><?=$title?></span>
      <input type="button" value="更新" onclick="GetFileList();">
      <input type="submit" name="logout" value="ログアウト">
      <input type="submit" name="password-reset" value="PW変更">
      <a href="<?$meurl?>?ajax-typeof=get-server" target="_blank" rel="noopener noreferrer"><input type="button" value="サーバー情報"></a>
      <span id="info"></span>
    </form>
    <div id="client" style="text-align:left;position:fixed;bottom:2%;left:02%;height:92%;width:46%;background-color:#6495ed;color:#080808;overflow-x:hidden;overflow-y:visible;">
      <form action='<?=$meurl?>' method='POST' onsubmit="AddItem();return false;">
        ファイル名: <input type="text" name="add-item-name" id="add-item-name" value="example.txt" size="40">
        <input type='button' onclick="AddItem();" value="新規作成">
      </form>
      <form action='<?=$meurl?>' method='POST' onsubmit="AddDirectory();return false;">
        ディレクトリ名: <input type="text" name="add-directory-name" id="add-directory-name" value="example/" size="40">
        <input type='button' onclick="AddDirectory();" value="新規作成">
      </form>
      <form action="<?=$meurl?>" id="upload-file" name="upload-file" enctype="multipart/form-data" method="POST" onsubmit="UploadFile();return false;">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php
          $maxfileu = ini_get("upload_max_filesize");
          $tani = substr($maxfileu, -1);
          $tanim = substr($maxfileu, 0, -1);
          if ($tani == "K")
            $maxfileu = $tanim * 1024;
          else if ($tani == "M")
            $maxfileu = $tanim * 1024 * 1024;
          else if ($tani == "G")
            $maxfileu = $tanim * 1024 * 1024 * 1024;
          else $maxfileu = $tanim * 1;
          echo $maxfileu;
        ?>">
        アップロード: <input multiple name="file[]" type="file" id="file" required>
        <input type="button" onclick="UploadFile();" value="保存">
      </form>
      <form action="<?=$meurl?>" id="min-upload-file" name="min-upload-file" enctype="multipart/form-data" method="POST">
        分割アップロード(<input type="number" value="3" id="upload-mb" style="width:28px;">MB): <input name="file" type="file" id="file2" required>
        <input type="submit" value="保存">
      </form>
      <form action='<?=$meurl?>' method='POST' onsubmit="CloneItem();return false;">
        URLからクローン: <input type="text" name="clone-url" id="clone-url" placeholder="URLを入力してください" size="40">
        <input type='button' onclick="CloneItem();" value="保存">
      </form>
      アクセス制限: <a href='javascript:return false;void(AccessCt())' title="現在作成中、v1.2.nあたりで実装予定です">
        <input type='button' value='アクセス制限を追加' disabled>
      </a>
      <hr size=40 color="#e6e6fa">
      デバッグ情報<br>
      <pre id="config"></pre>
    </div>
    <div id="server" style="text-align:left;position:fixed;bottom:2%;left:50%;height:92%;width:46%;background-color:#6495ed;color:#080808;overflow-x:hidden;overflow-y:visible;">
      <form action='<?=$meurl?>' method='POST' onsubmit='GetFileList();return false;'>
        <input type="text" name="ajax-option" id="directory-root" value="<?=$_SESSION["cd"]?>" size="50">
        <input type='button' onclick="GetFileList();" value="送信">
        <input type='button' onclick='"/"==_("directory-root").value.substr(-1,1)&&(_("directory-root").value=_("directory-root").value.slice(0,-1)),_("directory-root").value=_("directory-root").value.substring(0,_("directory-root").value.lastIndexOf("/")),GetFileList();' value="1つ上へ">
      </form>
     <img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAMAAAAM7l6QAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAACplBMVEUAAADFljWxgyjmtVTJmjnuvVvislHmuFvot1XsxHXYqkz4yWzsu1f4xmLuulP4w1ruuE34wVPutkf4vkvutEH4vETusjv4uTzusDX4tjXuri/4tC3urCn4siburCj4sSTtqyj3sSTipizpqSnFljXElTTBkjKvgSjgsE/xwF70w2Hzw2HywWDmtVSugCHnt1X8y2n5yGbquljmtlTnt1Xnt1TltVPaq0n8y2jxwmPsvmDtvmDtvmHrvF/crlDislD1xGDht2LUpkjis1TpulrmtVL5x2PpuFTntE78x17pt1Dns0n8xFXptUrnsUT8wU3ps0Xnrz/8v0XpsUDnrjr8vD3przrnrDb8uTXprjXnqjD8tizprDDnqSz8tCTpqivnqCr8syLpqin7syPpqSneoy7sqyjvrCfurCftqyjfpC3/zmz/zmv+zWv5x2P4x2P4xmPsvV7twGjyx3HyyHPyyXbyynjyynryy3zyzH7yzIHyzYPyzobwzILktVbktVfktVjvwmj+033/1oP/14b/2Ir/2o3/25H/3JX/3Zj/3pz/4KD/4aP91on9y2b9y2j9zGv9zW//0XX/0nj/03z/1ID/14f/2Y7/25L/3Jb/3Zr/2Iv9zGr/ymD/y2L/zGb/zWr/zm7/0HL/0nn/1H3/1YH/1oX/2In/2Y3/1YP9yF7/x1f/x1j/yFv/yl//y2P/zGf/z2//0HP/0nf/03v/1H//yV39xVb/xE//xE7/xlP/yFz/y2T/zWj/zm3/z3D/xVL9wk3/wUb/wkf/w0z/xVD/xlX/yFn/yV7/xVH/wUX9v0X/vj3/v0D/wUT/wkn/w0v9vT3/uzT/uzX/vDf/vTj/vDb9ujT/uSv/uCv9tyz/tiP9tCT/tSH9tCL+tSH8syL///8SRQNdAAAAa3RSTlMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADCgkCQcHCwr9fBFb974BPUVFRIf3++/v7/GZW/WdW/WdW/WdW/WdW/WdW/WdW/WdW/WdW/WdW/WdW/WdW/Wf9ZzKUlZWVPFGEemsAAAABYktHROFfCM+mAAAAB3RJTUUH5QQQCRsObpsM3AAAAV9JREFUKM9jYBjKgFFFFQzU1JmwSTNraGppA4GOrh4LFmlWfYNsMDA0MjYBAVNTUxMzcza4tEV2DhBk51paWYOBjY2NrR07VJrD3iEvPz8/r6CwqLiktKy8orKquqbWkRMqzeXkXFdXV9/Q0NjU3NLa1t7R2dXd0+vCDZXmcXXr6+vrnzBx0uQpU5untU6fMXPW7DnuvFBpPg/PuUAwb/6ChYsmLV6ydNnyFSvnr/Lih0oLePusBoI1a9et35CzcdPmLVsnbVu93VcQKi3k579jJxDs2LV699w9e/ft33Ng546DAcJQaZHAoENgcPjI0WPHT2w7eejUoUOng0Wh0mIhoWcg4Oy58xcOnwUzL4aJQ6UlwiMuQcDlK1evQZnXIyWh0lJR0TduooNbMdJQaZnYuNsY4E68LFRaLiHxLga4lyQPlVZISL6PAR6kKEKllVLT0jPQQHpmlvJAJ1OKAAAOqNlNFy4CpgAAACV0RVh0ZGF0ZTpjcmVhdGUAMjAyMS0wNC0xNlQwOToyNzoxNCswMDowMCMrIaoAAAAldEVYdGRhdGU6bW9kaWZ5ADIwMjEtMDQtMTZUMDk6Mjc6MTQrMDA6MDBSdpkWAAAAAElFTkSuQmCC' style='width:22px;height:22px;'><a href="#" style="color:#00f;" onclick='"/"==_("directory-root").value.substr(-1,1)&&(_("directory-root").value=_("directory-root").value.slice(0,-1)),_("directory-root").value=_("directory-root").value.substring(0,_("directory-root").value.lastIndexOf("/")),GetFileList();'>..</a>
      <div id="server-data"></div>
    </div>
    <div id="pp" style="display:none;text-align:center;position:fixed;bottom:10%;left:10%;height:80%;width:80%;background-color:#00ff7f;color:#080808;overflow-x:hidden;overflow-y:visible;">
      <div align="right" style="text-align:right;">
        <input type='button' onclick="closep();" value="閉じる" style="height:50px;width:80px;">
      </div>
      <h1>操作</h1>
      <div id="options">
        <input type="text" size="80" value="" id="selecteditem" align="center" readonly><a href='javascript:atk.copy(_("selecteditem").value);alert("コピーしました！");'><input type='button' value='コピー'></a>
        <div id="run-file" style="display:none;">
          <a href='javascript:window.open("<?=$meurl?>?ajax-typeof=get-item&ajax-option="+encodeURIComponent(_("selecteditem").value), "_blank");closep();'>
            <input type='button' value='開く(編集モード)'>
          </a>
          <a href='javascript:window.open("<?=$meurl?>?ajax-typeof=get-item&readonly=true&ajax-option="+encodeURIComponent(_("selecteditem").value), "_blank");closep();'>
            <input type='button' value='読み取り専用で開く'>
          </a>
          <br>
          ファイルをエディタで開きます。読み取り専用で開くと編集出来なくなります。
          <br>
          <a href='javascript:window.open("<?=$meurl?>?ajax-typeof=view-html&ajax-option="+encodeURIComponent(_("selecteditem").value), "_blank");closep();'>
            <input type='button' value='HTML表示'>
          </a>
          <br>
          HTML形式で直接レスポンスします。PHP等は動作しません。
          <br>
          <a href='javascript:FileRemove(_("selecteditem").value);closep();'>
            <input type='button' value='削除'>
          </a>
          <br>
          ファイルを削除します。
          <br>
          <a href='javascript:window.open("<?=$meurl?>?ajax-typeof=download-item&ajax-option="+encodeURIComponent(_("selecteditem").value));closep();'>
            <input type='button' value='ダウンロード'>
          </a>
          <br>
          ファイルをダウンロードします。ブラウザによっては正常にダウンロードされず、プレビューされてしまう場合があります。
          <br>
          <a href='javascript:RenameItem(_("selecteditem").value);closep();'>
            <input type='button' value='リネーム'>
          </a>
          <br>
          ファイル名を変更出来ます。ディレクトリから指定するので、移動させる事も可能です。
          <br>
          <a href='javascript:CopyItem(_("selecteditem").value);closep();'>
            <input type='button' value='コピー'>
          </a>
          <br>
          ファイルをコピー出来ます。
          <br>
          <a href='javascript:GetSize(_("selecteditem").value);closep();'>
            <input type='button' value='ファイルサイズ取得'>
          </a>
          <br>
          ファイルサイズを取得/表示します。
        </div>
      </div>
    </div>
    <div id="accessdenysetting" style="display:none;text-align:center;position:fixed;bottom:10%;left:10%;height:80%;width:80%;background-color:#00ff7f;color:#080808;overflow-x:hidden;overflow-y:visible;">
      <div align="right" style="text-align:right;">
        <input type='button' onclick="closep2();" value="閉じる" style="height:50px;width:80px;">
      </div>
      <h1>ディレクトリへのアクセスコントロール</h1>
      <p>パスワードで認証</p>
      <input type="text" id="accessdenypw" placeholder="パスワードを入力してください.." value="" size="30">
      <a href='javascript:window.open("<?=$meurl?>?ajax-typeof=get-item&ajax-option="+encodeURIComponent(_("selecteditem").value), "_blank");closep2();'>
        <input type='button' value='設定'>
      </a>
    </div>
  </body>
</html>
<?php
  function ViewNotePad($filepath) {
    $s = mb_strstr($_SERVER["REQUEST_URI"], '?', true);
    if ($s == "") $s = $_SERVER["REQUEST_URI"];
    $meurl = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ) ? "https://" : "http://").$_SERVER['HTTP_HOST'].$s;
    $title = basename($filepath);
    if (isset(pathinfo($filepath)['extension'])) $ka = strtolower(pathinfo($filepath)['extension']);
    else $ka = "";
    if ($ka == "bmp" || $ka == "png" || $ka == "jpeg" ||$ka == "ico" || $ka == "gif" || $ka == "jpg")
    {
      $data = base64_encode(file_get_contents($filepath));
      $path_parts = pathinfo($filepath);
      if (isset($path_parts['extension'])) $file_ext = strtolower($path_parts['extension']);
      else $file_ext = "";
      if ($file_ext == 'jpeg')
        $file_ext = 'jpg';
      echo '<div align="center"><img src="data: ' . $file_ext . ';base64,' . $data . '"></div>';
      exit();
    }
    else if ($ka == "mp3" || $ka == "m4a" || $ka == "ogg")
    {
      header("Content-Type: audio/mpeg");
      header("Content-Length: ".filesize($filepath));
      exit(file_get_contents($filepath));
    }
    else if ($ka == "pdf")
    {
      $data = base64_encode(file_get_contents($filepath));
      exit('<body style="height: 100%; width: 100%; overflow: hidden; margin:0px; background-color: rgb(82, 86, 89);"><embed style="position:absolute; left: 0; top: 0;" width="100%" height="100%" type="application/pdf" src="data:application/pdf;base64,' . $data . '"></body>');
    }
    else if ($ka == "mp4")
    {
      header("Content-Type: video/mp4");
      header("Content-Length: ".filesize($filepath));
      exit(file_get_contents($filepath));
    }
    else if ($ka == "zip" || $ka == "7z")
    {
      header("Content-Type: application/zip");
      header("Content-Length: ".filesize($filepath));
      exit(file_get_contents($filepath));
    }
?>
<html>
  <head>
    <title><?=$title?></title>
    <?php ViewActiveTKJS(); ?>
    <?php if (!isset($_GET["readonly"])) { ?>
    <script type="text/javascript">var starttitle="<?=$title?>";function save(){_("info").innerHTML="保存しています。。",$.ajax({url:"<?=$meurl?>?ajax-typeof=save-item&ajax-option="+encodeURIComponent(_("pt").value),type:"post",data:{naka:_("naka").value},success:function(t){""==t?(_("info").innerHTML="変更を保存しました。",$("title").html(starttitle)):_("info").innerHTML="変更を保存出来ませんでした。"}}).fail(function(t,n,e){_("info").innerHTML="エラー:変更を保存出来ませんでした。詳細: "+e,alert("変更を保存出来ませんでした。")})}$(document).ready(function(){$("#naka").on("change",function(){$("title").html("*"+starttitle)})});document.onkeydown=function(e){if(event.ctrlKey&&83==event.keyCode)return save(),event.keyCode=0,!1};function newfm(){window.open("<?=$meurl?>?ajax-typeof=get-item&ajax-option="+atk.encode(_("pt").value),"_blank")}</script>
    <?php } ?>
	<script>!function(e){e.fn.linedtextarea=function(t){var i=e.extend({},e.fn.linedtextarea.defaults,t),n=function(e,t,n){for(;e.height()-t<=0;)n==i.selectedLine?e.append("<div class='lineno lineselect'>"+n+"</div>"):e.append("<div class='lineno'>"+n+"</div>"),n++;return n};return this.each(function(){var t,s=e(this);s.attr("wrap","off"),s.css({resize:"none"});var a=s.outerWidth();s.wrap("<div class='linedtextarea'></div>");var r=s.parent().wrap("<div class='linedwrap' style='width:"+a+"px'></div>").parent();r.prepend("<div class='lines' style='width:50px'></div>");var d=r.find(".lines");d.height(s.height()+6),d.append("<div class='codelines'></div>");var l=d.find(".codelines");if(t=n(l,d.height(),1),-1!=i.selectedLine&&!isNaN(i.selectedLine)){var c=parseInt(s.height()/(t-2)),h=parseInt(c*i.selectedLine)-s.height()/2;s[0].scrollTop=h}var p=d.outerWidth(),o=parseInt(r.css("border-left-width"))+parseInt(r.css("border-right-width"))+parseInt(r.css("padding-left"))+parseInt(r.css("padding-right")),v=a-o,f=a-p-o-20;s.width(f),r.width(v);var u=null;s.scroll(function(t){if(null===u){var i=this;u=setTimeout(function(){l.empty();var t=e(i)[0].scrollTop,s=Math.floor(t/15+1),a=t/15%1;n(l,d.height(),s),l.css({"margin-top":15*a*-1+"px"}),u=null},150)}}),s.resize(function(t){var i=e(this)[0];d.height(i.clientHeight+6)})})},e.fn.linedtextarea.defaults={selectedLine:-1,selectedClass:"lineselect"}}(jQuery);</script>
	<style>.linedwrap{border:1px solid silver;padding:3px}.linedtextarea{padding:0;margin:0}.linedtextarea textarea,.linedwrap .codelines .lineno{font-size:10pt;font-family:monospace;line-height:15px!important}.linedtextarea textarea{padding-right:.3em;padding-top:.3em;border:0}.linedwrap .lines{margin-top:0;width:50px;float:left;overflow:hidden;border-right:1px solid silver;margin-right:10px}.linedwrap .codelines{padding-top:5px}.linedwrap .codelines .lineno{color:#aaa;padding-right:.5em;padding-top:0;text-align:right;white-space:nowrap}.linedwrap .codelines .lineselect{color:red}</style>
  </head>
  <body>
    <form action="<?=$meurl?>?ajax-type=get-item&ajax-option=<?php echo $filepath; ?>" method="POST" onsubmit="save();return false;">
      ATK-FM <?=fmversion?>
      <input type="text" id="pt" name="write" size="80" placeholder="FilePath" value="<?php echo $filepath; ?>" required>
      <input type="button" value="開く" onclick="newfm();">
      <span id="info"></span>
      <br>
      <textarea class="lined" <?php if (isset($_GET["readonly"])) echo "readonly "; ?> id="naka" style="text-align:left;position:fixed;overflow-wrap:break-word;overflow-x:scroll;overflow-y:visible;width:100%;height:80%;background-color:#000000;color:#ffffff;margin:5px 5px;"><?
      $file = fopen($filepath, "r");
      $alltext = "";
      if($file) {
        while ($line = @fgets($file))
          $alltext .= $line;
        if ($alltext == "") echo "// Your code goes here!";
        if (@is_utf8($alltext)) echo @htmlspecialchars($alltext);
        else
          echo @htmlspecialchars(@mb_convert_encoding($alltext, 'UTF-8', 'SJIS'));
      }
      else
        echo "// ファイルが見つかりませんでした。";
      fclose($file);
      ?></textarea>
      <br>
      <div style="position:fixed;bottom:3%;left:40px;">
        <input type="submit" value="保存" style="width:120px;height:40px;" <?php if (isset($_GET["readonly"])) echo "disabled"; ?>>
        <input type="button" value="クリア" onclick="_('naka').value='';" <?php if (isset($_GET["readonly"])) echo "disabled"; ?>>
        <input type="button" value="置き換え" <?php if (isset($_GET["readonly"])) echo "disabled"; ?> onclick='let e=_("naka").value,n=window.prompt("置き換えるテキストを入力してください"),o=window.prompt("置き換え後のテキストを入力してください");if(n){for(p=e.replace(n,o);p!==e;)e=e.replace(n,o),p=p.replace(n,o);_("naka").value=p}else;'>
        <input type="button" value="文字数取得" onclick="_('info').innerHTML='現在、'+_('naka').value.length+'文字です。';">
        <?php if (isset($_GET["readonly"])) echo "注意：読み取り専用モードです。"; ?>
      </div>
    </form>
    <script>$(function(){$(".lined").linedtextarea();});</script>
  </body>
</html>
<?php
    exit();
  }
  function ViewServerInfo() {
    $loads = sys_getloadavg();
    try {
      $mem = @array_merge(@array_filter(@explode(" ", @explode("\n", (string)@trim(@shell_exec('free')))[1])));
    } catch(¥Throwable $e) {
      $mem = false;
    }
    if ($mem == false)
      $mem = 0;
    $gb = 1024 * 1024 * 1024;
    $free_space = @disk_free_space("/");
    $total_space = @disk_total_space("/");
    $directives = array(
  	"mbstring" => array(
        "mbstring.detect_order",
        "mbstring.encoding_translation",
        "mbstring.func_overload",
        "mbstring.http_input    pass",
        "mbstring.http_output",
        "mbstring.internal_encoding",
        "mbstring.language",
        "mbstring.substitute_character",
        )
    );
    foreach ($directives["mbstring"] as $v)
	  $info["mbstring"][$v] = ini_get($v);
?>
<!DOCTYPE html>
<html lang="ja" dir="ltr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <title>サーバー情報 - ATK-FM <?=fmversion?></title>
    <meta name="robots" content="noindex">
    <style>a{color: #00ff00;position: relative;display: inline-block;transition: .3s;}a::after {position: absolute;bottom: 0;left: 50%;content: '';width: 0;height: 2px;background-color: #31aae2;transition: .3s;transform: translateX(-50%);}a:hover::after{width: 100%;}</style>
  </head>
  <body style="background-color:#e6e6fa;text:#363636;">
    <div align="center">
      <h1>ATK-FM <?=fmversion?> (公開時刻:<?=publishupdateinfo?>)</h1>
      <pre><?=copyright?></pre>
      <p>サーバー情報</p>
      <table border="1" style="position:relative;">
        <tr>
          <th style="position:relative;background-color:#0000ff;color:#ffffff;">項目</th>
          <th style="position:relative;background-color:#0000ff;color:#ffffff;">情報</th>
        </tr>
        <tr>
          <th style="position:relative;">最終アップデート実行時刻</th>
          <th style="position:relative;"><?=lastupdateinfo?></th>
        </tr>
        <tr>
          <th style="position:relative;">最終アップデート確認日</th>
          <?php
            $lastupdateday = file_get_contents(dirname(__FILE__) . "/ATK-FM/lastupdate.txt");
            if ($lastupdateday == date("Ymd"))
            {
              echo "<th style=\"position:relative;background-color:#00ff00;\">";
              echo $lastupdateday;
              echo " (本日)";
            }
            else
            {
              echo "<th style=\"position:relative;\">";
              echo $lastupdateday;
            }
          ?> [<a href="<?=$_SERVER["REQUEST_URI"]?>&checkupdate=false"><span style="background-color:#ffffff;">アップデートを確認</span></a>] [<a href="<?=$_SERVER["REQUEST_URI"]?>&checkupdate=true"><span style="background-color:#ffffff;">強制アップデートを実行</span></a>] (※1)</th>
        </tr>
        <tr>
          <th style="position:relative;">CPU(loadavg[0])</th>
          <th style="position:relative;"><?php echo sys_getloadavg()[0]; ?></th>
        </tr>
        <tr>
          <th style="position:relative;">Memory</th>
          <th style="position:relative;"><?php
            try {
              $memd = @($mem[2]/$mem[1]*100);
              if ($memd > 0)
                echo $memd . "%使用中";
              else
                throw new Exception('Can not run memory check!!');
              } catch (¥Throwable $e) {
                echo "取得に失敗しました。";
              }
          ?></th>
        </tr>
        <tr>
          <th style="position:relative;">PHPのバージョン</th>
          <?php
            if (version_compare(PHP_VERSION, '5.6', '<')) {
              echo '<th style="position:relative;background-color:#ff0000;">' . PHP_VERSION . " (未対応のバージョンです、今すぐにアップデートしてください)";
            }
            else if (version_compare(PHP_VERSION, '7.2', '<')) {
              echo '<th style="position:relative;">' . PHP_VERSION . " (アップデートする事を推奨します)";
            }
            else {
              echo '<th style="position:relative;background-color:#00ff00;">' . PHP_VERSION . " (安定しているバージョンです)";
            }
            ?></th>
        </tr>
        <tr>
          <th style="position:relative;">サーバーのIPアドレス</th>
          <th style="position:relative;"><?php
            if (isset($_SERVER) && isset($_SERVER['SERVER_ADDR']))
            {
              $serverip = $_SERVER['SERVER_ADDR'];
              echo htmlspecialchars($serverip);
              $serverhost = @gethostbyaddr($serverip);
              if ($serverhost && $serverhost != "" && $serverhost != NULL && $serverhost != $serverip)
              {
                echo " (逆引きホスト名:{$serverhost}";
                $serverip2 = @gethostbyname($serverhost);
                if ($serverip2 != $serverip)
                  echo "、再逆引きIP({$serverip2})が不一致です!";
                else
                  echo "、再逆引きIP({$serverip2})と一致しました";
                echo ")";
              }
              else
                echo " (逆引き不可)";
            }
            else
              echo "不明";
             ?></th>
        </tr>
        <tr>
          <th style="position:relative;">サーバー</th>
          <th style="position:relative;"><?=$_SERVER["SERVER_SOFTWARE"]?></th>
        </tr>
        <tr>
          <th style="position:relative;">タイムゾーン</th>
          <th style="position:relative;"><?=date_default_timezone_get()?></th>
        </tr>
        <tr>
          <th style="position:relative;">現在時刻</th>
          <th style="position:relative;"><?=date("Y/m/d - M (D) H:i:s")?> (UNIXTIME:<?=time()?>)</th>
        </tr>
        <tr>
          <th style="position:relative;">ATK-FMのファイルパス</th>
          <th style="position:relative;"><?=__FILE__?></th>
        </tr>
        <tr>
          <th style="position:relative;">ATK-FMのURL</th>
          <th style="position:relative;"><?php
            $s = mb_strstr($_SERVER["REQUEST_URI"], '?', true);
            if ($s == "") $s = $_SERVER["REQUEST_URI"];
            echo htmlspecialchars((((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ) ? "https://" : "http://").$_SERVER['HTTP_HOST'].$s);
          ?></th>
        </tr>
        <tr>
          <th style="position:relative;">ATK-FMのアクセスログ</th>
          <th style="position:relative;"><a href="?viewaccesslog=true">アクセスログを閲覧</a></th>
        </tr>
        <tr>
          <th style="position:relative;">SSL/TLSによるセキュリティ保護</th>
          <?php
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') echo "<th style=\"position:relative;background-color:#00ff00;\">有効";
            else echo "<th style=\"position:relative;background-color:#ff0000;\">無効 (危険！第三者に盗聴/改竄されるリスクがあります！)";
          ?></th>
        </tr>
        <tr>
          <th style="position:relative;">このPHPのメモリー負荷</th>
          <th style="position:relative;"><?php
            $f = memory_get_usage(true);
            echo convert($f) . " ({$f}byte)";
          ?></th>
        </tr>
        <tr>
          <th style="position:relative;">Loadavg[0]:1分の平均負荷</th>
          <th style="position:relative;"><?=$loads[0]?></th>
        </tr>
        <tr>
          <th style="position:relative;">Loadavg[1]:5分の平均負荷</th>
          <th style="position:relative;"><?=$loads[1]?></th>
        </tr>
        <tr>
          <th style="position:relative;">Loadavg[2]:15分の平均負荷</th>
          <th style="position:relative;"><?=$loads[2]?></th>
        </tr>
        <tr>
          <th style="position:relative;">ディスクの空き容量</th>
          <th style="position:relative;"><?php
            echo $free_space ."Byte(".@round(($free_space/1024*1024),2)."MB、".@round(($free_space/$gb), 2) . "GB)";
          ?></th>
        </tr>
        <tr>
          <th style="position:relative;">ディスクの合計容量</th>
          <th style="position:relative;"><?php
            echo "約" . @round(($total_space/$gb),2) . "GB";
          ?></th>
        </tr>
      </table>
      <br>
      <pre>※1 アップデートの確認は毎日最初のアクセス時に行われます。</pre>
      <p>$_SERVER</p>
      <table border="1" style="position:relative;">
        <tr>
          <th style="position:relative;background-color:#0000ff;color:#ffffff;">項目</th>
          <th style="position:relative;background-color:#0000ff;color:#ffffff;">情報</th>
        </tr>
        <?php
          $sf = 0;
          foreach($_SERVER as $name => $value)
          {
            if ($sf == 0)
            {
              $sf = 1;
              echo "<tr>\n";
            }
            else echo "        <tr>\n";
            echo "          <th style=\"position:relative;\">".htmlspecialchars($name)."</th>\n";
            if ($value != "")
              echo "          <th style=\"position:relative;\">".htmlspecialchars($value)."</th>\n";
            else
              echo "          <th style=\"position:relative;\">(値無し)</th>\n";
            echo "        </tr>\n";
          }
        ?>
      </table>
      <p>ini_get</p>
      <pre>参考: https://bashalog.c-brains.jp/08/11/21-145720.php</pre>
      <br>
      <table border="0" cellpadding="3" width="600">
        <tr class="h"><th>Directive</th><th>Local Value</th></tr>
        <?php 
          foreach ($info["mbstring"] as $k => $v) {
	        if ($v == "")
              $v = "<i>no value</i>";
	        echo "<tr><td class=\"e\">".htmlspecialchars($k)."</td><td class=\"v\">".htmlspecialchars($v)."</td></tr>\n";
          }
        ?>
      </table>
    </div>
    <div align="center"><p>PHPinfo</p></div>
    <?php
      try
      {
        if (@phpinfo() == false)
          throw new Exception('Can not run phpinfo!!');
      } catch (\Throwable $e) {
      ?>
      <div align="center">
        PHPinfo()の実行に失敗しました。<br>
        無料のレンタルサーバーなどではPHP.iniによって実行出来なくされている可能性があります。
      </div>
      <?php
      }
    ?>
  </body>
</html>
<?php
    exit();
  }
