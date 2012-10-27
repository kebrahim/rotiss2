<html>
<head>
<title>Display Player</title>
</head>

<body>

<?php
  require_once 'dao/playerDao.php';

  if (isset($_GET["player_id"])) {
    $playerId = $_GET["player_id"];
  } else if (isset($_POST["player_id"])) {
    $playerId = $_POST["player_id"];
  } else {
    die("<h1>Missing playerId for player page</h1>");
  }

  // Get player from db.
  $player = PlayerDao::getPlayerById($playerId);
  if ($player == null) {
    die("<h1>player id " . $playerId . " does not exist!</h1>");
  }

  // Display player attributes.
  echo "<h1>" . $player->getFullName() . "</h1>";

  // Headshot
  if ($player->hasSportslineId()) {
    echo "<a href='" . $player->getStPetesUrl() . "'>
          <img src='" . $player->getHeadshotUrl() . "'></a><br/><br/>";
  }

  // ID
  echo "<table>";
  echo "<tr><td><strong>Rotiss Id:</strong></td><td>" . $player->getId() . "</td></tr>";

  // MLB Team
  $mlbTeam = $player->getMlbTeam();
  echo "<tr><td><strong>Team:</strong></td>
            <td>" . $mlbTeam->getCity() . " " . $mlbTeam->getName() . "</td></tr>";

  // Birth date & age
  echo "<tr><td><strong>Birth Date:</strong></td>
        <td>" . $player->getBirthDate() . "</td></tr>";
  echo "<tr><td><strong>Age:</strong></td>
        <td>" . $player->getAge() . "</td></tr>";

  // Positions
  echo "<tr><td><strong>Position(s):</strong></td>
        <td>" . $player->getPositionString() . "</td></tr>";

  // Fantasy team
  echo "<tr><td><strong>Fantasy Team:</strong></td><td>";
  $fantasyTeam = $player->getFantasyTeam();
  if ($fantasyTeam == null) {
  	echo "--";
  } else {
  	echo $fantasyTeam->getName() . " (" . $fantasyTeam->getAbbreviation() . ")";
  }
  echo "</td></tr>";
  
  echo "</table><br/>";
  
  // TODO displayPlayer: if admin user, show edit link
  echo "<a href='admin/managePlayer.php?player_id=" . $player->getId() . "'>Edit player</a><br>";

  // TODO displayPlayer: show contract history
  // TODO displayPlayer: show draft/pingpong history
  // TODO displayPlayer: show auction history
?>

</body>
</html>
