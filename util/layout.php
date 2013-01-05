<?php
require_once "config.php";
require_once "sessions.php";

class LayoutUtil {
  const TEAM_SUMMARY_BUTTON = 1;
  const BUDGET_BUTTON = 2;
  const PLAYERS_BUTTON = 3;
  const MY_RANKS_BUTTON = 4;
  const ALL_RANKS_BUTTON = 5;
  const DRAFT_BUTTON = 6;
  const AUCTION_BUTTON = 7;
  const EDIT_PROFILE_BUTTON = 8;
  const ADMIN_BUTTON = 9;
  const MANAGE_ROSTERS_BUTTON = 10;
  const MANAGE_TRADE_BUTTON = 11;
  const MANAGE_AUCTION_BUTTON = 12;
  const MANAGE_KEEPERS_BUTTON = 13;
  const MANAGE_BROGNAS_BUTTON = 14;
  const MANAGE_TEAM_BUTTON = 15;
  const MANAGE_DRAFT_BUTTON = 16;
  const MANAGE_PLACEHOLDERS_BUTTON = 17;
  const MANAGE_RANKS_BUTTON = 18;
  const MANAGE_PLAYER_BUTTON = 19;
  const MANAGE_EVENTS_BUTTON = 20;
  const MANAGE_CHANGES_BUTTON = 21;

  /**
   * Displays the <head> tag for a page, including the specified title.
   */
  public static function displayHeadTag($title, $isTopLevel) {
  	echo "<head>
            <title>St Pete's Rotiss";
  	if ($title) {
      echo " - " . $title;
  	}
    echo   "</title>
            <link href='" . ($isTopLevel ? "" : "../") . "css/bootstrap.css' rel='stylesheet'
                  type='text/css'>
            <link href='" . ($isTopLevel ? "" : "../") . "css/stpetes.css' rel='stylesheet'
                  type='text/css'>
            <link href='" . ($isTopLevel ? "" : "../") . "css/browsers.css' rel='stylesheet'
                  type='text/css'>
            <link href='" . ($isTopLevel ? "" : "../") . "img/background-tiles-01.png'
                  rel='shortcut icon' />
            <script src='" . ($isTopLevel ? "" : "../") . "js/css_browser_selector.js'
                  type='text/javascript'></script>
         </head>";
  }

  public static function displayHeader() {
    echo "<div id='wrap'><div class='container'>";
  }

  /**
   * Displays the header banner with navigation links.
   */
  public static function displayNavBar($isTopLevel, $selectedButton) {
    echo "<div id='wrap'><div class='container'>
          <div class='navbar'><div class='navbar-inner'>";

    echo "<div class=\"brand\">St Pete's Rotiss</div>
          <ul class='nav'>";

    // Summary page
    LayoutUtil::displayListItem("teamPage.php", "Teams", $isTopLevel, $selectedButton,
        self::TEAM_SUMMARY_BUTTON);

    // Budget page
    LayoutUtil::displayListItem("budgetPage.php", "Budget", $isTopLevel, $selectedButton,
        self::BUDGET_BUTTON);

    // Players page
    LayoutUtil::displayListItem("playersPage.php", "Players", $isTopLevel, $selectedButton,
        self::PLAYERS_BUTTON);

    // Ranking page
    // TODO only show ranking page after placeholders have been set - after ranking period begins?
    LayoutUtil::displayRankingDropdown($isTopLevel, $selectedButton);

    // Draft page
    LayoutUtil::displayListItem("draftPage.php", "Draft", $isTopLevel, $selectedButton,
        self::DRAFT_BUTTON);

    // Auction page
    LayoutUtil::displayListItem("auctionPage.php", "Auction", $isTopLevel, $selectedButton,
        self::AUCTION_BUTTON);

    // if admin user, show admin button/menu
    if (SessionUtil::isLoggedInAdmin()) {
      LayoutUtil::displayAdminDropdown($isTopLevel, $selectedButton);
    }

    echo "</ul>";

    // show logged-in user name with links for editing profile & signing out
    LayoutUtil::displayProfileDropdown($isTopLevel, $selectedButton);

    echo "</div>"; // navbar-inner
    echo "</div>"; // navbar
  }

  private static function displayRankingDropdown($isTopLevel, $selectedButton) {
  	$dropdownSelected = ($selectedButton == LayoutUtil::MY_RANKS_BUTTON) ||
  	    ($selectedButton == LayoutUtil::ALL_RANKS_BUTTON);
  	echo "<li class='dropdown";
  	if ($dropdownSelected) {
  	  echo " active";
  	}
  	echo "'><a href='#' class='dropdown-toggle' data-toggle='dropdown'>
  	            Ranking&nbsp<b class='caret'></b></a>";
  	echo "<ul class='dropdown-menu'>";
  	LayoutUtil::displayListItem("rankPage.php", "My Ranks", $isTopLevel, $selectedButton,
  	    LayoutUtil::MY_RANKS_BUTTON);
  	LayoutUtil::displayListItem("allRanksPage.php", "All Ranks", $isTopLevel, $selectedButton,
  	    LayoutUtil::ALL_RANKS_BUTTON);
  	echo "</ul></li>";
  }

  private static function displayProfileDropdown($isTopLevel, $selectedButton) {
    $dropdownSelected = ($selectedButton == self::EDIT_PROFILE_BUTTON);
  	$user = SessionUtil::getLoggedInUser();
  	echo "<ul class='nav pull-right'>
  	        <li class=\"divider-vertical\"></li>
  	        <li class='dropdown";
  	if ($dropdownSelected) {
  	  echo " active";
  	}
    echo "'><a href='#' class='dropdown-toggle  profiledropdown' data-toggle='dropdown'>
  	            Hi " . $user->getFirstName() . "!&nbsp<b class='caret'></b></a>";
  	echo "<ul class='dropdown-menu'>
  	        <li";
  	if ($dropdownSelected) {
  	  echo " class='active'";
  	}
  	echo "><a href='" . ($isTopLevel ? "" : "../") .
  	            "editProfilePage.php'><i class=\"icon-edit\"></i>&nbsp&nbspEdit profile</a></li>
  	        <li class=\"divider\"></li>
  	        <li><a href='" . ($isTopLevel ? "" : "../") . "logoutPage.php'>
  	            <i class=\"icon-eject\"></i>&nbsp&nbspSign out</a></li>
  	      </ul>
  	      </li></ul>";
  }

  private static function displayAdminDropdown($isTopLevel, $selectedButton) {
  	$dropdownSelected = ($selectedButton >= self::ADMIN_BUTTON);
  	echo "<li class='dropdown";
  	if ($dropdownSelected) {
  	  echo " active";
  	}
  	echo "'><a href='#' class='dropdown-toggle' data-toggle='dropdown'>
  	        Admin&nbsp<b class='caret'></b></a>";
  	echo "<ul class='dropdown-menu'>";

  	// Brognas
  	LayoutUtil::displayListItem("admin/manageBrognas.php", "Team Budgets", $isTopLevel,
  	    $selectedButton, self::MANAGE_BROGNAS_BUTTON);

  	// Manage individual team
  	LayoutUtil::displayListItem("admin/manageTeam.php", "Team Mgmt", $isTopLevel,
  	    $selectedButton, self::MANAGE_TEAM_BUTTON);

  	// Manage individual player
  	LayoutUtil::displayListItem("admin/managePlayer.php", "Player Mgmt", $isTopLevel,
  	    $selectedButton, self::MANAGE_PLAYER_BUTTON);

  	// Roster Grid
  	LayoutUtil::displayListItem("admin/manageTeams.php", "Roster Grid", $isTopLevel,
  	    $selectedButton, self::MANAGE_ROSTERS_BUTTON);

  	// Roster Grid
  	LayoutUtil::displayListItem("admin/manageChanges.php", "Change Log", $isTopLevel,
  	    $selectedButton, self::MANAGE_CHANGES_BUTTON);

  	echo "<li class=\"divider\"></li>";

  	// Trade
  	LayoutUtil::displayListItem("admin/manageTrade.php", "Trades", $isTopLevel, $selectedButton,
  		self::MANAGE_TRADE_BUTTON);

  	// Auction
  	LayoutUtil::displayListItem("admin/manageAuction.php", "Auction", $isTopLevel,
  		$selectedButton, self::MANAGE_AUCTION_BUTTON);

  	// Keepers - only if feature is enabled
  	if (ConfigUtil::isFeatureEnabled(ConfigUtil::KEEPER_FEATURE, $isTopLevel)) {
  	  LayoutUtil::displayListItem("admin/manageKeepers.php", "Keepers", $isTopLevel,
  	      $selectedButton, self::MANAGE_KEEPERS_BUTTON);
  	}

  	// Manage draft
  	LayoutUtil::displayListItem("admin/manageDraft.php", "Draft", $isTopLevel,
  	$selectedButton, self::MANAGE_DRAFT_BUTTON);

  	// If super-admin, show super-admin pages
  	if (SessionUtil::isLoggedInSuperAdmin()) {
  	  echo "<li class=\"divider\"></li>";

  	  // Placeholders
  	  LayoutUtil::displayListItem("admin/managePlaceholders.php", "Placeholders", $isTopLevel,
          $selectedButton, self::MANAGE_PLACEHOLDERS_BUTTON);

  	  // Ranks
  	  LayoutUtil::displayListItem("admin/manageRanks.php", "Ranks", $isTopLevel,
  	      $selectedButton, self::MANAGE_RANKS_BUTTON);

  	  // Events
  	  LayoutUtil::displayListItem("admin/manageEvents.php", "Events", $isTopLevel,
  	  		$selectedButton, self::MANAGE_EVENTS_BUTTON);
  	}
  	echo "</ul></li>";
  }

  private static function displayListItem($url, $caption, $isTopLevel, $selectedButton,
  		$listButton) {
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

  /**
   * Displays the footer, attached to the bottom of the page.
   */
  public static function displayFooter() {
    LayoutUtil::closeDivsAndDisplayFooter(true);
  }

  public static function displayAdminFooter() {
  	LayoutUtil::closeDivsAndDisplayFooter(false);
  }

  private static function closeDivsAndDisplayFooter($isTopLevel) {
    echo "</div>";  // container
    echo "<div id='push'></div>";
    echo "</div>"; // wrap
    echo "<div id=\"footer\">
            <div class=\"container\">
              <div class=\"row\">
                <div class=\"span4 center muted credit\">
                  St. Pete's Rotiss " . ConfigUtil::getValue(ConfigUtil::VERSION, $isTopLevel) . "
                </div>
                <div class=\"span4 center\">
                  <img src='" . ($isTopLevel ? "" : "../") . "img/rotiss2.jpg' width='250' />
                </div>
                <div class=\"span4 center muted credit\">
                  a <a href='http://www.zebrahim.com' target='_blank'>zebrahim</a> original
                </div>
              </div>
            </div>
          </div>
          <script src=\"http://code.jquery.com/jquery-latest.js\"></script>
          <script src=\"" . ($isTopLevel ? "" : "../") . "js/bootstrap.js\"></script>";
  }
}