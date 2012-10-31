<?php session_start(); ?>
<html>
<head>
<title>Manage Teams</title>
</head>

<style type="text/css">
html {height:100%;}
body {text-align:center;}
table {text-align:center;}
table.center {margin-left:auto; margin-right:auto;}
#column_container {padding:0; margin:0 0 0 50%; width:50%; float:right;}
#left_col {float:left; width:100%; margin-left:-100%; text-align:center;}
#left_col_inner {padding:10px;}
#right_col {float:right; width:100%; text-align:center;}
#right_col_inner {padding:10px;}
#placeholder_row {background-color:#E3F2F9;}
#vert_td {vertical-align:top;}
</style>

<script>
</script>

<body>
<?php
  require_once '../dao/playerDao.php';
  require_once '../dao/teamDao.php';

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
    echo "> -- None -- </option>";

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
    echo "<table border class='center'>";
    echo "<tr><th>Player</th><th>Pos</th><th>Team</th><th>Fantasy team</th></tr>";
    foreach ($players as $player) {
      echo "<tr><td><a href='../displayPlayer.php?player_id=" . $player->getId() . "'>" .
      $player->getFullName() . "</a></td>
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
    echo "<h4>" . $team->getAbbreviation() . "</h4>";
    echo "<img src='" . $team->getSportslineImageUrl() . "' height=36 width=36><br/><br/>";
    displayArrayOfPlayers(PlayerDao::getPlayersByTeam($team));
  }

  echo "<h1>Roster Grid</h1>";
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
  echo "<table border id='teams' class='center'>";
  for ($i=0; $i<4; $i++) {
  	echo "<tr>";
  	for ($j=0; $j<4; $j++) {
      echo "<td id='vert_td'>";
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
  echo "</FORM>";
?>
</body>
</html>