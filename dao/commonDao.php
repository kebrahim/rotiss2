<?php
class CommonDao {
  // TODO make this class abstract

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

  /**
   * Returns the integer value [count, min, max] retrieved from the specified query.
   */
  static function getIntegerValueFromQuery($query) {
    CommonDao::connectToDb();
    $res = mysql_query($query);
    if ($res == false) {
      return 0;
    }
    $row = mysql_fetch_row($res);
    return $row[0];
  }

  /**
   * Returns true if the specified query returns at least one row.
   */
  static function hasAnyRowsMatchingQuery($query) {
    CommonDao::connectToDb();
    $res = mysql_query($query);
    return (mysql_num_rows($res) > 0);
  }
}
?>
