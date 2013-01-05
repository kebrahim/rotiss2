<?php
class CommonDao {
  static function connectToDb() {
    CommonDao::requireFileIn("/../util/", "config.php");
    if (!ConfigUtil::isProduction(false)) {
      $dbUser = "root";
      $dbPass = "karma";
    } else {
      $dbUser = "rotiss_kebrahim";
      $dbPass = "timebomb";
    }

    $dbh=mysql_connect ("localhost", $dbUser, $dbPass) or
        die ('I cannot connect to the database because: ' . mysql_error());
    mysql_select_db ("rotiss_baseball");
  }

  static function requireFileIn($path, $file) {
    $now_at_dir = getcwd();
    chdir(realpath(dirname(__FILE__).$path));
    require_once $file;
    chdir($now_at_dir);
  }
}
?>
