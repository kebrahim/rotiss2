<html>
<head>
<title>Auction</title>
</head>

<script>
function showPlayer(playerId) {

}

function showTeam(teamId) {

}
</script>

<body>

<?php
require_once '../dao/teamDao.php';
require_once '../util/time.php';
require_once '../entity/trade.php';

echo "<center><h1>Do I hear 100?</h1>";
echo "<FORM ACTION='manageAuction.php' METHOD=POST>";

// If auction button was pressed...
if(isset($_POST['auction'])) {
  // TODO display what will be auctioned & by whom
} elseif (isset($_POST['confirmAuction'])) {
  // TODO Re-create auction scenario from POST
  // TODO pull data from session

  // TODO execute auction
} else {
  // TODO allow user to select one player from list of players eligible to be auctioned.
  $players = PlayerDao::getPlayersForAuction();
  echo "<center>Select Player:<br><select name='player' onchange='showPlayer(this.value)'>
                         <option value='0'></option>";
  foreach ($players as $player) {
    echo "<option value='" . $player->getId() . "'" . ">" . $player->getFullName() . "</option>";
  }
  echo "</select><br>";
  echo "<div id='playerDisplay'></div><br/>";

  // allow user to select which team bid on player & how much they bid
  $teams = TeamDao::getAllTeams();
  echo "Select Team:<br><select name='team1' onchange='selectTeam(this.value)'>
                           <option value='0'></option>";
  foreach ($teams as $team) {
    echo "<option value='" . $team->getId() . "'" . ">" . $team->getName() . "</option>";
  }
  echo "</select><br>";
  echo "<div id='teamDisplay'></div><br/>";

  // TODO input box for bid amount

  echo "<div id='auctionButton' style='display:none'>
          <input class='button' type=submit name='auction' value='Auction player'>
          <input class='button' type=submit name='cancel' value='Cancel'>
        </div>";
}
echo "</form>";
?>

</body>
</html>
