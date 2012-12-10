<?php
  require_once '../util/sessions.php';
  SessionUtil::checkUserIsLoggedInAdmin();
  SessionUtil::logoutUserIfNotLoggedIn("admin/manageTeams.php");
?>

<!DOCTYPE html>
<html>
<head>
<title>St Pete's Rotiss - Manage Teams</title>
<link href='../css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='../css/stpetes.css' rel='stylesheet' type='text/css'>
</head>

<body>
<?php
  require_once '../dao/playerDao.php';
  require_once '../dao/teamDao.php';
  require_once '../util/layout.php';

  // Display nav bar.
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_ROSTERS_BUTTON);
  
  /**
   * Creates a select tag for the assignment of the specified player to any of the fantasy teams.
   */
  function displayTeamSelectForPlayer(Player $player) {
    $playerTeam = $player->getFantasyTeam();
    echo "<select name='tp" . $player->getId() . "' class='input-xsmall' size=1>";

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
    echo "<table class='table vertmiddle table-striped table-condensed table-bordered
                        center smallfonttable'>";
    echo "<thead><tr><th>Player</th><th>Rotiss Team</th></tr></thead>";
    foreach ($players as $player) {
      echo "<tr><td>" . $player->getNameLink(false) . "</td>
                <td>";
      displayTeamSelectForPlayer($player);
      echo "</td></tr>";
    }
    echo "</table>";
  }

  /**
   * Displays all players assigned to the specified team in a table.
   */
  function displayPlayersByTeam(Team $team) {
    echo "<h5>" . $team->getIdLink(false, $team->getAbbreviation()) . "</h5>";
    displayArrayOfPlayers(PlayerDao::getPlayersByTeam($team));
  }

  echo "<div class='row-fluid'>
          <div class='span6 offset3 center'>
            <h3>Roster Grid</h3>
          </div>
        </div>";
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

  // display unassigned players
  echo "<div class='row-fluid'>
          <div class='span3 center'>
            <h4>Unassigned players</h4>";
  displayArrayOfPlayers(PlayerDao::getUnassignedPlayers());
  echo "  </div>";
  
  echo "  <div class='span9 center'>
            <h4>Assigned players</h4>";
  echo "<p><button class=\"btn btn-primary\" name='save' type=\"submit\">Save my changes</button>";
  echo "&nbsp&nbsp<button class=\"btn\" name='cancel' type=\"submit\">Reset</button></p>";
  
  $teams = TeamDao::getAllTeams();
  $teamCount = count($teams);
  for ($i=0; $i<6; $i++) {
  	echo "<div class='row-fluid'>";
  	for ($j=0; $j<3; $j++) {
  	  if ($teamCount > 0) {
        echo "<div class='span4'>";
        displayPlayersByTeam($teams[count($teams) - $teamCount]);
        echo "</div>";
        $teamCount--;
  	  }
  	}
  	echo "</div>";
  }

  echo "<p><button class=\"btn btn-primary\" name='save' type=\"submit\">Save my changes</button>";
  echo "&nbsp&nbsp<button class=\"btn\" name='cancel' type=\"submit\">Reset</button></p>";
  echo "</form>";
  echo "</div>"; // span9
  echo "</div>"; // row-fluid

  // Footer
  LayoutUtil::displayAdminFooter();
?>

</body>
</html>