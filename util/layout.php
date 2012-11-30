<?php
require_once "sessions.php";

class LayoutUtil {
  const TEAM_SUMMARY_BUTTON = 1;
  const BUDGET_BUTTON = 2;
  const PLAYERS_BUTTON = 3;
  const RANKING_BUTTON = 4;
  const DRAFT_BUTTON = 5;
  const AUCTION_BUTTON = 6;
  const ADMIN_BUTTON = 7;
  const MANAGE_ROSTERS_BUTTON = 8;
  const MANAGE_TRADE_BUTTON = 9;
  const MANAGE_AUCTION_BUTTON = 10;
  const MANAGE_KEEPERS_BUTTON = 11;
  const MANAGE_BROGNAS_BUTTON = 12;
  const MANAGE_TEAM_BUTTON = 13;
  const MANAGE_DRAFT_BUTTON = 14;
  const MANAGE_PLACEHOLDERS_BUTTON = 15;
  const MANAGE_RANKS_BUTTON = 16;

  /**
   * Displays the header banner with navigation links.
   */
  public static function displayNavBar($isTopLevel, $selectedButton) {
    echo "<div id='wrap'><div class='container'>
          <div class='navbar'><div class='navbar-inner'>
          <img src='";
    if (!$isTopLevel) {
    	echo "../";
    }
    echo "img/rotiss2.jpg' width='80' />
          <ul class='nav'>";
    
    // Summary page
    LayoutUtil::displayListItem("summaryPage.php", "Team Summary", $isTopLevel, $selectedButton,
        self::TEAM_SUMMARY_BUTTON);

    // Budget page
    LayoutUtil::displayListItem("budgetPage.php", "Budget", $isTopLevel, $selectedButton,
        self::BUDGET_BUTTON);
    
    // Players page
    LayoutUtil::displayListItem("playersPage.php", "Players", $isTopLevel, $selectedButton,
        self::PLAYERS_BUTTON);
    
    // Ranking page
    // TODO only show ranking page after placeholders have been set
    LayoutUtil::displayListItem("rankPage.php", "Ranking", $isTopLevel, $selectedButton,
        self::RANKING_BUTTON);

    // Draft page
    LayoutUtil::displayListItem("draftPage.php", "Draft", $isTopLevel, $selectedButton,
        self::DRAFT_BUTTON);

    // Auction page
    LayoutUtil::displayListItem("auctionPage.php", "Auction", $isTopLevel, $selectedButton,
        self::AUCTION_BUTTON);

    // if admin user, show admin button/menu
    if (SessionUtil::isLoggedInAdmin()) {
      LayoutUtil::displayListItem("admin/manageTeams.php", "Admin", $isTopLevel, $selectedButton,
          self::ADMIN_BUTTON);
    }
    
    echo "</ul>";
    
    // show logged-in user name with links for editing profile & signing out
    LayoutUtil::displayProfileInfo($isTopLevel);

    echo "</div>"; // navbar-inner
    echo "</div>"; // navbar
  }

  private static function displayProfileInfo($isTopLevel) {
  	$user = SessionUtil::getLoggedInUser();
  	echo "<ul class='nav pull-right'>
  	        <li class='dropdown'>
  	          <a href='#' class='dropdown-toggle' data-toggle='dropdown'>
  	      Hi " . $user->getFirstName() . "!&nbsp<b class='caret'></b></a>";
  	echo "<ul class='dropdown-menu'>
  	        <li><a href='" . ($isTopLevel ? "" : "../") . 
  	            "editProfilePage.php'>Edit profile</a></li>
  	        <li><a href='" . ($isTopLevel ? "" : "../") . "logoutPage.php'>Sign out</a></li>
  	      </ul>
  	      </li></ul>";	
  }

  private static function displayListItem($url, $caption, $isTopLevel, $selectedButton, $listButton) {
    echo "<li";
    if ($selectedButton == $listButton) {
      echo " class='active'";
    }
    echo ">";
    LayoutUtil::displayLink($url, $caption, $isTopLevel);
    echo "</li>";
  }

  private static function displayLink($url, $caption, $isTopLevel) {
  	echo "<a href='" . ($isTopLevel ? "" : "../") . $url . "'>" . $caption . "</a>";
  }

  // TODO show admin menu on sidebar, on every admin page
  private static function displayAdminMenu($isTopLevel, $selectedButton) {
  	// top-level button directs to manage teams page
  	echo "<li class='dropdown'>";
  	$adminSelected = ($selectedButton >= self::ADMIN_BUTTON);
  	LayoutUtil::displayLink(
  			"admin/manageTeams.php", "Admin", $isTopLevel, $adminSelected);

  	// sub-menu includes all admin options
  	echo "<ul class='dropdown'>";

    // Manage teams
  	LayoutUtil::displayListItem("admin/manageTeams.php", "Manage Rosters", $isTopLevel,
  	    $selectedButton, self::MANAGE_ROSTERS_BUTTON);

  	// Trade
  	LayoutUtil::displayListItem("admin/manageTrade.php", "Trades", $isTopLevel, $selectedButton,
  		self::MANAGE_TRADE_BUTTON);

  	// Auction
  	LayoutUtil::displayListItem("admin/manageAuction.php", "Auction", $isTopLevel,
  		$selectedButton, self::MANAGE_AUCTION_BUTTON);

  	// Keepers
  	LayoutUtil::displayListItem("admin/manageKeepers.php", "Keepers", $isTopLevel,
  		$selectedButton, self::MANAGE_KEEPERS_BUTTON);

  	// Brognas
  	LayoutUtil::displayListItem("admin/manageBrognas.php", "Brognas", $isTopLevel,
  	    $selectedButton, self::MANAGE_BROGNAS_BUTTON);

  	// Manage individual team
  	LayoutUtil::displayListItem("admin/manageTeam.php", "Manage Team", $isTopLevel,
  	    $selectedButton, self::MANAGE_TEAM_BUTTON);
  	
  	// Manage draft
  	LayoutUtil::displayListItem("admin/manageDraft.php", "Manage Draft", $isTopLevel,
  			$selectedButton, self::MANAGE_DRAFT_BUTTON);
  	 
  	// If super-admin, show ranks & placeholders pages
  	if (SessionUtil::isLoggedInSuperAdmin()) {
  	  // Placeholders
  	  LayoutUtil::displayListItem("admin/managePlaceholders.php", "Placeholders", $isTopLevel,
          $selectedButton, self::MANAGE_PLACEHOLDERS_BUTTON);

  	  // Ranks
  	  LayoutUtil::displayListItem("admin/manageRanks.php", "Manage Ranks", $isTopLevel,
  	      $selectedButton, self::MANAGE_RANKS_BUTTON);
  	}
  	echo "</ul></li>";
  }

  /**
   * Displays the footer, attached to the bottom of the page.
   */
  public static function displayFooter() {
    echo "</div>";  // container
    echo "<div id='push'></div>";
    echo "</div>"; // wrap
    echo "<div id=\"footer\">
            <div class=\"container\">
              <div class=\"row\">
                <div class=\"span6 center muted credit\">
                  St. Pete's Rotiss 2.0
                </div>
                <div class=\"span6 center\">
                  <img src='img/rotiss2.jpg' width='125' />
                </div>
              </div>
            </div>
          </div>
          <script src=\"http://code.jquery.com/jquery-latest.js\"></script>
          <script src=\"js/bootstrap.js\"></script>";
  }
}