<?php
  require_once 'util/sessions.php';
  SessionUtil::checkUserIsLoggedIn();
?>
<html>
<head>
<title>Rotiss.com - Display Player</title>
<link href='css/style.css' rel='stylesheet' type='text/css'>
</head>

<body>

<?php
  require_once 'dao/playerDao.php';
  require_once 'util/navigation.php';

  // Display header.
  NavigationUtil::printHeader(true, true, NavigationUtil::MY_TEAM_BUTTON);

  if (isset($_REQUEST["player_id"])) {
    $playerId = $_REQUEST["player_id"];
  } else {
    die("<h1>Missing playerId for player page</h1>");
  }

  // Get player from db.
  $player = PlayerDao::getPlayerById($playerId);
  if ($player == null) {
    die("<h1>player id " . $playerId . " does not exist!</h1>");
  }

  // Display player attributes.
  echo "<div class='bodyleft'>";
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
            <td>" . $mlbTeam->getCity() . " " . $mlbTeam->getName() . " 
            " . $mlbTeam->getImageTag(30, 30) . "</td></tr>";

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
  	echo $fantasyTeam->getNameLink(true);
  }
  echo "</td></tr>";

  echo "</table><br/>";

  // if admin user, show edit link
  if (SessionUtil::isLoggedInAdmin()) {
    echo "<a href='admin/managePlayer.php?player_id=" . $player->getId() .
        "'>Manage player</a><br>";
  }

  // TODO displayPlayer: show contract history
  // TODO displayPlayer: show draft/pingpong history
  // TODO displayPlayer: show auction history
  echo "</div>";

  // Display footer
  NavigationUtil::printFooter();
?>

</body>
</html>
