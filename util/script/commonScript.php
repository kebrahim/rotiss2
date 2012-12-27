<?php
class CommonScript {
  static function requireFileIn($path, $file) {
    $now_at_dir = getcwd();
    chdir(realpath(dirname(__FILE__).$path));
    require_once $file;
    chdir($now_at_dir);
  }
}
?>