<?php
require_once "sessions.php";

class NavigationUtil {

  /**
   * Displays the header banner with navigation links.
   */
  public static function printHeader($showNavigationLinks, $isTopLevel) {
    echo "<header>";
    if ($showNavigationLinks) {
      echo "<div id='banner'>";
    }
    echo "    <div id='logo'>
                <img src='";
    if (!$isTopLevel) {
      echo "../";
    }
    echo "images/rotiss.jpg' width='240'>
              </div>";
    if ($showNavigationLinks) {
      echo "  <nav id='menu'>
                <ul>";

      // if admin user, show admin button
      if (SessionUtil::isLoggedInAdmin()) {
        // TODO add sub-menu for admin options
        echo "<li><a href='" . ($isTopLevel ? "" : "../") . "summaryPage.php'>Admin</a></li>";
      }

      // Summary page
      echo "<li><a href='" . ($isTopLevel ? "" : "../") . "summaryPage.php'>My Team</a></li>";

      // Ranking page
      echo "<li><a href='". ($isTopLevel ? "" : "../") . "allRanksPage.php'>Ranking</a></li>";

      // Budget page
      echo "<li><a href='". ($isTopLevel ? "" : "../") . "budgetPage.php'>Budget</a></li>";

      // Draft page
      echo "<li><a href='". ($isTopLevel ? "" : "../") . "draftPage.php'>Draft</a></li>";

      // Auction page
      echo "<li><a href='". ($isTopLevel ? "" : "../") . "auctionPage.php'>Auction</a></li>";

      // Sign out link
      echo "<li><a href='". ($isTopLevel ? "" : "../") . "logoutPage.php'>Sign out</a></li>

          </ul>
        </nav>";
      // TODO show logged-in user information w/ links for editing profile & signing out
      echo "</div>";
    }
    echo "</header>";
  }

  /**
   * Displays the footer.
   */
  // TODO attach the footer to the bottom of the page.
  public static function printFooter() {
    echo "<footer>
          </footer>";
  }
}