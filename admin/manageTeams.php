<?php
  require_once '../util/sessions.php';
  SessionUtil::checkUserIsLoggedInAdmin();
?>

<html>
<head>
<title>Rotiss.com - Manage Teams</title>
<link href='../css/style.css' rel='stylesheet' type='text/css'>
</head>

<body>
<?php
  require_once '../dao/playerDao.php';
  require_once '../dao/teamDao.php';
  require_once '../util/navigation.php';

  // Display header.
  NavigationUtil::printHeader(true, false, NavigationUtil::MANAGE_ROSTERS_BUTTON);
  echo "<div class='bodycenter'>";

  /**
   * Creates a select tag for the assignment of the specified player to any of the fantasy teams.
   */
  function displayTeamSelectForPlayer(Player $player) {
    $playerTeam = $player->getFantasyTeam();
    echo "<select name='tp" . $player->getId() . "' size=1>";

    // option for unassigning from all teams
    echo "<option value='0'";
    if ($playerTeam == null) {
      echo " selected";
    }
    echo "></option>";

    // option to assign to each team
    $teams = TeamDao::getAllTeams();
    foreach ($teams as $team) {
      echo "<option value='" . $team->getId() . "'";
      if (($playerTeam != null) && ($playerTeam->getId() == $team->getId())) {
      	echo " selected";
      }
      echo ">" . $team->getAbbreviation() . "</option>";
    }
    echo "</select>";
  }

  /**
   * Displays the array of players in a table.
   */
  function displayArrayOfPlayers($players) {
    echo "<table border class='center smallfonttable'>";
    echo "<tr><th>Player</th><th>Pos</th><th>Team</th><th>Rotiss Team</th></tr>";
    foreach ($players as $player) {
      echo "<tr><td>" . $player->getNameLink(false) . "</td>
      	        <td>" . $player->getPositionString() . "</td>
      	        <td>" . $player->getMlbTeam()->getAbbreviation() . "</td><td>";
      displayTeamSelectForPlayer($player);
      echo "</td></tr>";
    }
    echo "</table>";
  }

  /**
   * Displays all players assigned to the specified team in a table.
   */
  function displayPlayersByTeam(Team $team) {
    echo "<h4><a href='../summaryPage.php?team_id=" . $team->getId() . "'>" .
          $team->getAbbreviation() . "</a></h4>";
    echo "<img src='" . $team->getSportslineImageUrl() . "' height=36 width=36><br/><br/>";
    displayArrayOfPlayers(PlayerDao::getPlayersByTeam($team));
  }

  echo "<h1>Manage Rosters</h1><hr/>";
  echo "<FORM ACTION='manageTeams.php' METHOD=POST>";

  if (isset($_POST['save'])) {
    // for every player, check if a player was assigned to a new team.
    $players = PlayerDao::getAllPlayers();
    foreach ($players as $player) {
      $teamSelection = 'tp' . $player->getId();
      $currentTeamId = ($player->getFantasyTeam() == null) ? 0 : $player->getFantasyTeam()->getId();
      // if team id changed, assign player to team
      if (isset($_POST[$teamSelection]) && (intval($_POST[$teamSelection]) != $currentTeamId)) {
        TeamDao::assignPlayerToTeam($player, intval($_POST[$teamSelection]));
      }
    }
  }

  $teams = TeamDao::getAllTeams();
  echo "<table id='teams' class='center'>";
  for ($i=0; $i<4; $i++) {
  	echo "<tr>";
  	for ($j=0; $j<4; $j++) {
      echo "<td class='vert_td_top'>";
      displayPlayersByTeam($teams[($i * 4) + $j]);
      echo "</td>";
  	}
  	echo "</tr>";
  }
  echo "</table><br/><br/>";

  echo "<input type='submit' name='save' value='Save changes'>";
  echo "<input type='submit' name='cancel' value='Cancel'>";

  // display unassigned players
  echo "<h2>Unassigned players</h2>";
  displayArrayOfPlayers(PlayerDao::getUnassignedPlayers());
  echo "</form></div>";

  // Footer
  NavigationUtil::printFooter();
?>
</body>
</html>