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
  <head>
    <title>St Pete's Rotiss - Players</title>
    <link href="css/bootstrap.css" rel="stylesheet" media="screen">
    <link href="css/stpetes.css" rel="stylesheet" media="screen">
  </head>
  <body>

<?php
  require_once 'dao/playerDao.php';
  require_once 'util/layout.php';

  /**
   * Returns the fantasy team cell in the table of players for the specified player
   */
  function getFantasyTeamRow($player) {
  	if ($player->getFantasyTeam() == null) {
  		return "<td colspan=2>--</td>";
  	}
  	return "<td>" . $player->getFantasyTeam()->getNameLink(true) . "</td>
  	<td>" . $player->getFantasyTeam()->getSportslineImg(32,32) . "</td>";
  }

  // Nav bar
  LayoutUtil::displayNavBar(true, LayoutUtil::PLAYERS_BUTTON);

  // header section
  echo "<div class=\"row-fluid\">
          <div class=\"span8 center\">
            <h1>MLB Players</h1>
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
    echo "<h3>Search results</h3>";
  	$players = PlayerDao::getPlayersByName($nameString);
  } else {
  	echo "<h3>All Players</h3>";
  	$players = PlayerDao::getAllPlayers();
  }
  echo "<table class='table table-bordered table-striped table-condensed center'>
         <thead><tr><th colspan=2>Player</th><th>MLB Team</th><th>Position</th>
                    <th colspan=2>Fantasy Team</th>
         </tr></thead>";
  foreach ($players as $player) {
  	echo "<tr><td>" . $player->getMiniHeadshotImg() . "</td>
  	          <td>" . $player->getNameLink(true) . "</td>
  	          <td>" . $player->getMlbTeam()->getImageTag(32, 32) . "</td>
  	          <td>" . $player->getPositionString() . "</td>" .
  	          getFantasyTeamRow($player) . "</tr>";
  }
  echo "</table>
        </div></div>"; // span12, row-fluid

  // footer
  LayoutUtil::displayFooter();
?>

</body>
</html>