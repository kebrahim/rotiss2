<?php
require_once "sessions.php";

class NavigationUtil {
  const MY_TEAM_BUTTON = 1;
  const RANKING_BUTTON = 2;
  const BUDGET_BUTTON = 3;
  const DRAFT_BUTTON = 4;
  const AUCTION_BUTTON = 5;

  public static function printHeader($showNavigationLinks, $isTopLevel, $selectedButton) {
    NavigationUtil::displayHeader($showNavigationLinks, $isTopLevel, $selectedButton, 'wrapper');
  }

  public static function printNoWidthHeader($showNavigationLinks, $isTopLevel, $selectedButton) {
    NavigationUtil::displayHeader(
        $showNavigationLinks, $isTopLevel, $selectedButton, 'nowidthwrapper');
  }

  /**
   * Displays the header banner with navigation links.
   */
  private static function displayHeader($showNavigationLinks, $isTopLevel, $selectedButton,
      $wrapperId) {
    echo "<div id='container'>";
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
      NavigationUtil::printListItem("summaryPage.php", "Team Summary", $isTopLevel, $selectedButton,
          self::MY_TEAM_BUTTON);

      // Ranking page
      // TODO only show ranking page after placeholders have been set
      NavigationUtil::printListItem("rankPage.php", "Ranking", $isTopLevel, $selectedButton,
          self::RANKING_BUTTON);

      // Budget page
      NavigationUtil::printListItem("budgetPage.php", "Budget", $isTopLevel, $selectedButton,
          self::BUDGET_BUTTON);

      // Draft page
      NavigationUtil::printListItem("draftPage.php", "Draft", $isTopLevel, $selectedButton,
          self::DRAFT_BUTTON);

      // Auction page
      NavigationUtil::printListItem("auctionPage.php", "Auction", $isTopLevel, $selectedButton,
          self::AUCTION_BUTTON);

      // Sign out link
      echo "<li><a href='". ($isTopLevel ? "" : "../") . "logoutPage.php'>Sign out</a></li>

          </ul>
        </nav>";
      // TODO show logged-in user information w/ links for editing profile & signing out
      echo "</div>";
    }
    echo "</header>";
    echo "<div id='$wrapperId'>";
  }

  private static function printListItem($url, $caption, $isTopLevel, $selectedButton, $listButton) {
    echo "<li><a";
    if ($selectedButton == $listButton) {
      echo " id='navselected'";
    }
    echo " href='" . ($isTopLevel ? "" : "../") . $url . "'>" . $caption . "</a></li>";
  }

  /**
   * Displays the footer, attached to the bottom of the page.
   */
  public static function printFooter() {
    echo "</div>";  // wrapper
    echo "<div class='push'></div>";
    echo "</div>"; // container
    echo "<footer>
            <p>Rotiss.com 2.0</p>
          </footer>";
  }
}