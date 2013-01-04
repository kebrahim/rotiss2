<?php
  require_once 'util/sessions.php';

  $redirectUrl = "playersPage.php";
  if (isset($_REQUEST['name'])) {
  	$nameString = $_REQUEST['name'];
  	$redirectUrl .= "?name=$nameString";
  } else {
  	$nameString = null;
  }
  SessionUtil::logoutUserIfNotLoggedIn($redirectUrl);
?>

<!DOCTYPE html>
<html>

<?php
  require_once 'util/layout.php';
  LayoutUtil::displayHeadTag("Players", true);
?>

<body>

<?php
  require_once 'dao/playerDao.php';
  require_once 'util/teamManager.php';
  
  // Nav bar
  LayoutUtil::displayNavBar(true, LayoutUtil::PLAYERS_BUTTON);

  // header section
  echo "<div class=\"row-fluid\">
          <div class=\"span8 center\">
            <h3>MLB Players</h3>
          </div>";

  // search section
  echo "  <div class=\"span4 center nexttoh1\">
            <form class=\"form-search\" action='playersPage.php' method=\"post\">";
  echo "<input type='text' class='input-medium search-query' placeholder='Enter player name'
         name='name'";
  if ($nameString != null) {
  	echo " value='" . $nameString . "'";
  }
  echo ">&nbsp&nbsp";
  echo "<button type=\"submit\" class=\"btn\">Search</button>
            </form>
          </div>
        </div>";

  // players section - display search results, or all players if no string has been entered
  echo "<div class=\"row-fluid\">
          <div class=\"span12 center\">";

  if ($nameString != null) {
    echo "<h4>Search results</h4>";
  	$players = PlayerDao::getPlayersByName($nameString);
  } else {
  	echo "<h4>All Players</h4>";
  	$players = PlayerDao::getAllPlayers();
  }
  echo "<table class='table table-bordered table-striped table-condensed center'>
         <thead><tr><th colspan=2>Player</th><th>MLB Team</th><th>Position</th>
                    <th colspan=2>Fantasy Team</th>
         </tr></thead>";
  foreach ($players as $player) {
  	echo "<tr><td>" . $player->getMiniHeadshotImg() . "</td>
  	          <td>" . $player->getNameLink(true) . "</td>
  	          <td>" . $player->getMlbTeam()->getImageTag(32) . "</td>
  	          <td>" . $player->getPositionString() . "</td>" .
  	          TeamManager::getNameAndLogoRow($player->getFantasyTeam()) . "</tr>";
  }
  echo "</table>
        </div></div>"; // span12, row-fluid

  // footer
  LayoutUtil::displayFooter();
?>

</body>
</html>