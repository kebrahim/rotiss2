<?php
require_once "sessions.php";

class NavigationUtil {
  const MY_TEAM_BUTTON = 1;
  const RANKING_BUTTON = 2;
  const BUDGET_BUTTON = 3;
  const DRAFT_BUTTON = 4;
  const AUCTION_BUTTON = 5;
  const ADMIN_BUTTON = 6;
  const MANAGE_ROSTERS_BUTTON = 7;
  const MANAGE_TRADE_BUTTON = 8;
  const MANAGE_AUCTION_BUTTON = 9;
  const MANAGE_KEEPERS_BUTTON = 10;
  const MANAGE_TEAM_BUTTON = 11;
  const MANAGE_PLACEHOLDERS_BUTTON = 12;
  const MANAGE_RANKS_BUTTON = 13;

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

      // if admin user, show admin button/menu
      if (SessionUtil::isLoggedInAdmin()) {
      	NavigationUtil::printAdminMenu($isTopLevel, $selectedButton);
      }

      // Summary page
      NavigationUtil::printListItem("summaryPage.php", "Team Summary", $isTopLevel, $selectedButton,
          self::MY_TEAM_BUTTON);

      // Budget page
      NavigationUtil::printListItem("budgetPage.php", "Budget", $isTopLevel, $selectedButton,
          self::BUDGET_BUTTON);

      // Ranking page
      // TODO only show ranking page after placeholders have been set
      NavigationUtil::printListItem("rankPage.php", "Ranking", $isTopLevel, $selectedButton,
          self::RANKING_BUTTON);

      // Draft page
      NavigationUtil::printListItem("draftPage.php", "Draft", $isTopLevel, $selectedButton,
          self::DRAFT_BUTTON);

      // Auction page
      NavigationUtil::printListItem("auctionPage.php", "Auction", $isTopLevel, $selectedButton,
          self::AUCTION_BUTTON);

      echo "</ul></nav>";

      // show logged-in user name with links for editing profile & signing out
      NavigationUtil::printProfileInfo($isTopLevel);

      echo "</div>"; // banner
    }
    echo "</header>";
    echo "<div id='$wrapperId'>";
  }

  private static function printProfileInfo($isTopLevel) {
  	$user = SessionUtil::getLoggedInUser();
  	echo "<div id='profileinfo'>";
  	echo "Hi " . $user->getFirstName() . "!
  	      <a href='" . ($isTopLevel ? "" : "../") . "editProfilePage.php'>Edit profile</a>
  	      <a href='" . ($isTopLevel ? "" : "../") . "logoutPage.php'>Sign out</a>";
  	echo "</div>";
  }

  private static function printListItem($url, $caption, $isTopLevel, $selectedButton, $listButton) {
    echo "<li>";
    NavigationUtil::printLink($url, $caption, $isTopLevel, ($selectedButton == $listButton));
    echo "</li>";
  }

  private static function printLink($url, $caption, $isTopLevel, $isSelected) {
  	echo "<a";
  	if ($isSelected) {
  	  echo " id='navselected'";
  	}
  	echo " href='" . ($isTopLevel ? "" : "../") . $url . "'>" . $caption . "</a>";
  }

  private static function printAdminMenu($isTopLevel, $selectedButton) {
  	// top-level button directs to manage teams page
  	echo "<li class='dropdown'>";
  	$adminSelected = ($selectedButton >= self::ADMIN_BUTTON);
  	NavigationUtil::printLink(
  			"admin/manageTeams.php", "Admin", $isTopLevel, $adminSelected);

  	// sub-menu includes all admin options
  	echo "<ul class='dropdown'>";

    // Manage teams
  	NavigationUtil::printListItem("admin/manageTeams.php", "Manage Rosters", $isTopLevel,
  	    $selectedButton, self::MANAGE_ROSTERS_BUTTON);

  	// Trade
  	NavigationUtil::printListItem("admin/manageTrade.php", "Trades", $isTopLevel, $selectedButton,
  		self::MANAGE_TRADE_BUTTON);

  	// Auction
  	NavigationUtil::printListItem("admin/manageAuction.php", "Auction", $isTopLevel,
  		$selectedButton, self::MANAGE_AUCTION_BUTTON);

  	// Keepers
  	NavigationUtil::printListItem("admin/manageKeepers.php", "Keepers", $isTopLevel,
  		$selectedButton, self::MANAGE_KEEPERS_BUTTON);

  	// TODO Brognas?

  	// Manage individual team
  	NavigationUtil::printListItem("admin/manageTeam.php", "Manage Team", $isTopLevel,
  	    $selectedButton, self::MANAGE_TEAM_BUTTON);

  	// If super-admin, show ranks & placeholders pages
  	if (SessionUtil::isLoggedInSuperAdmin()) {
  	  // Placeholders
  	  NavigationUtil::printListItem("admin/managePlaceholders.php", "Placeholders", $isTopLevel,
          $selectedButton, self::MANAGE_PLACEHOLDERS_BUTTON);

  	  // Ranks
  	  NavigationUtil::printListItem("admin/manageRanks.php", "Manage Ranks", $isTopLevel,
  	      $selectedButton, self::MANAGE_RANKS_BUTTON);
  	}
  	echo "</ul></li>";
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