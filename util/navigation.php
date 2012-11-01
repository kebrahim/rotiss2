<?php
require_once "sessions.php";

class NavigationUtil {

  /**
   * Displays the header banner with navigation links.
   */
  public static function printHeader() {
    echo "<header>
            <div id='banner'>
              <div id='logo'>
                <img src='images/rotiss.jpg' width='240'>
              </div>
              <nav id='menu'>
                <ul>";

    // if admin user, show admin button
    if (SessionUtil::isLoggedInAdmin()) {
      // TODO add sub-menu for admin options
      echo "<li><a href='summaryPage.php'>Admin</a></li>";
    }

    // Summary page
    echo "<li><a href='summaryPage.php'>My Team</a></li>";

    // Ranking page
    echo "<li><a href='allRanksPage.php'>Ranking</a></li>";

    // Budget page
    echo "<li><a href='budgetPage.php'>Budget</a></li>";

    // Draft page
    echo "<li><a href='draftPage.php'>Draft</a></li>";

    // Auction page
    echo "<li><a href='auctionPage.php'>Auction</a></li>";

    // Sign out link
    echo "<li><a href='logoutPage.php'>Sign out</a></li>

                </ul>
              </nav>";

    // TODO show logged-in user information w/ links for editing profile & signing out

    echo "  </div>
          </header>";
  }

  /**
   * Displays the footer.
   */
  public static function printFooter() {
    echo "<footer>
          </footer>";
  }
}