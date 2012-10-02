<?php

class SessionUtil {
  
  /**
   * Updates the _SESSION array with the value of the specified key within the
   * specified array; if isPost = false, then remove the value from the SESSION.
   */
  // TODO determine if assocArray isPost w/out needing extra param
  public static function updateSession($key, $assocArray, $isPost) {
  	if ($isPost) {
  	  $_SESSION[$key] = $assocArray[$key];
  	} else {
  	  unset($_SESSION[$key]);
  	}
  }
}
?>