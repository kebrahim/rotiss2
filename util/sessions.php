<?php

require_once 'commonUtil.php';
CommonUtil::requireFileIn('/../dao/', 'teamDao.php');
CommonUtil::requireFileIn('/../entity/', 'user.php');

class SessionUtil {
  // TODO add prefix to session vars so they do not conflict w/ topchef rotiss

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

  /**
   * Clears out all session variables that start with the specified prefix.
   */
  public static function clearSessionVarsWithPrefix($prefix) {
  	if (!isset($_SESSION)) {
  	  session_start();
  	}
  	foreach ($_SESSION as $key=>$val) {
  	  $pos = strpos($key, $prefix);
      if (($pos !== false) && ($pos == 0)) {
  	  	unset($_SESSION[$key]);
  	  }
  	}
  }

  /**
   * Login with the specified user, after clearing out the session, and redirect to the specified
   * URL.
   */
  public static function loginAndRedirect(User $user, $redirectUrl) {
    if (!isset($_SESSION)) {
      session_start();
    }
    session_unset();
    $_SESSION["loggedinuserid"] = $user->getId();
    $_SESSION["loggedinteamid"] = $user->getTeam()->getId();
    $_SESSION["loggedinadmin"] = $user->isAdmin();
    $_SESSION["loggedinsuperadmin"] = $user->isSuperAdmin();


    // redirect to URL if specified; otherwise teamPage
    SessionUtil::redirectToUrl(($redirectUrl != null) ? $redirectUrl : "teamPage.php");
  }

  /**
   * Determine if a user is logged in & if not, redirect the user back to the login page.
   */
  public static function checkUserIsLoggedIn() {
    if (SessionUtil::hasUserTimedOut() || !SessionUtil::isLoggedIn()) {
      SessionUtil::logOut();
    }
  }

  /**
   * Determine if a user is logged in & if not, redirect the user back to the login page with the
   * specified continue URL.
   */
  public static function logoutUserIfNotLoggedIn($continueUrl) {
  	if (SessionUtil::hasUserTimedOut() || !SessionUtil::isLoggedIn()) {
  	  SessionUtil::logOutAndRedirect($continueUrl);
  	}
  }

  /**
   * Returns true if a user is currently logged in.
   */
  public static function isLoggedIn() {
    if (!isset($_SESSION)) {
      session_start();
    }

    if (empty($_SESSION["loggedinuserid"])) {
      return false;
    }
    return true;
  }

  /**
   * Determine if a user is an admin, & if not, redirect the user back to the login page.
   */
  // TODO combine w/ continueURL method?
  public static function checkUserIsLoggedInAdmin() {
    if (!SessionUtil::isLoggedInAdmin()) {
      SessionUtil::logOut();
    }
  }

  /**
   * Determine if a user is a super-admin, & if not, redirect the user back to the login page.
   */
  public static function checkUserIsLoggedInSuperAdmin() {
    if (!SessionUtil::isLoggedInSuperAdmin()) {
      SessionUtil::logOut();
    }
  }

  /**
   * Returns the logged-in user.
   */
  public static function getLoggedInUser() {
    return SessionUtil::isLoggedIn() ? UserDao::getUserById($_SESSION["loggedinuserid"]) : null;
  }

  /**
   * Returns the fantasy team of the currently logged-in user.
   */
  public static function getLoggedInTeam() {
    return SessionUtil::isLoggedIn() ? TeamDao::getTeamById($_SESSION["loggedinteamid"]) : null;
  }

  /**
   * Returns true if the logged-in user is an admin.
   */
  public static function isLoggedInAdmin() {
    return SessionUtil::isLoggedIn() ? $_SESSION["loggedinadmin"] : false;
  }

  /**
   * Returns true if the logged-in user is a super-admin.
   */
  public static function isLoggedInSuperAdmin() {
    return SessionUtil::isLoggedIn() ? $_SESSION["loggedinsuperadmin"] : false;
  }

  /**
   * Logs out the currently logged-in user.
   */
  public static function logOut() {
  	SessionUtil::logOutAndRedirect(null);
  }

  public static function logOutAndRedirect($continueUrl) {
    if (!isset($_SESSION)) {
      session_start();
    }
    SessionUtil::unsetSessionVariable("loggedinuserid");
    SessionUtil::unsetSessionVariable("loggedinteamid");
    SessionUtil::unsetSessionVariable("loggedinadmin");
    SessionUtil::unsetSessionVariable("loggedinsuperadmin");

    // clear out the rest of the session.
    session_unset();

    // redirect to home page.
	SessionUtil::redirectHome($continueUrl);
  }

  /**
   * Redirects the user to the specified URL
   */
  public static function redirectToUrl($url) {
    header("Location: $url");
    exit;
  }

  /**
   * Redirects to home page and adds redirect URL to query string if specified.
   */
  public static function redirectHome($continueUrl) {
    // TODO Change to http://stpetes.rotiss.com
  	$homePage = "http://localhost/rotiss2/";
  	if ($continueUrl != null) {
  	  $homePage .= "?continue=$continueUrl";
  	}
    SessionUtil::redirectToUrl($homePage);
  }

  /**
   * Returns true if the logged-in user has not generated any activity in the past 20 minutes.
   */
  private static function hasUserTimedOut() {
    session_cache_expire(20);
    if (!isset($_SESSION)) {
      session_start();
    }
    $inactive = 1200;
    if (isset($_SESSION['start']) ) {
      $session_life = time() - $_SESSION['start'];
      if ($session_life > $inactive) {
        return true;
      }
    }
    $_SESSION['start'] = time();
    return false;
  }

  private static function unsetSessionVariable($sessionVar) {
    $_SESSION[$sessionVar] = null;
    unset($_SESSION[$sessionVar]);
  }
}
?>