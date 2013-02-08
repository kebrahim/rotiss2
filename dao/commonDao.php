<?php
class CommonDao {

  // TODO remove passwords from file
  static function connectToDb() {
    CommonDao::requireFileIn("/../util/", "config.php");
    if (ConfigUtil::isProduction()) {
      $dbUser = "rotiss_kebrahim";
      $dbPass = "timebomb";
    } else {
      $dbUser = "root";
      $dbPass = "karma";
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

  static function getCountValue($query) {
    $res = mysql_query($query);
    $row = mysql_fetch_row($res);
    return $row[0];
  }
}
?>
