<?php
  require_once 'util/sessions.php';
  SessionUtil::checkUserIsLoggedIn();
?>

<!DOCTYPE html>
<html>
<head>
<title>St Pete's Rotiss - Player Summary</title>
<link href='css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='css/stpetes.css' rel='stylesheet' type='text/css'>
</head>

<body>

<?php
  require_once 'dao/playerDao.php';
  require_once 'util/layout.php';

  // Display header.
  LayoutUtil::displayNavBar(TRUE, LayoutUtil::PLAYERS_BUTTON);

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

  echo "<div class='row-fluid'>";

  // Headshot
  echo "<div class='span2 center headshotimg nexttoh1'>";
  if ($player->hasSportslineId()) {
    echo "<a href='" . $player->getStPetesUrl() . "'>" .
          $player->getHeadshotImg(42, 56) . "</a><br/><br/>";
  } else {
    // TODO show blank face
  }
  echo "</div>"; // span2

  // player name heading
  echo "<div class='span10 center'>
            <h1>" . $player->getFullName() . "</h1>
          </div>";
  echo "</div>"; // row-fluid

  // Summary
  echo "<div class='row-fluid'>
          <div class='span12'>";
  echo "<h4>Player Summary</h4>
        <table class='table vertmiddle table-striped table-condensed table-bordered'>";

  // ID
  echo "<tr>
          <td><strong>Rotiss Id:</strong></td>
          <td>" . $player->getId() . "</td>
        </tr>";

  // MLB Team
  $mlbTeam = $player->getMlbTeam();
  echo "<tr><td><strong>Team:</strong></td>
            <td>" . $mlbTeam->getCity() . " " . $mlbTeam->getName() . "&nbsp&nbsp" .
                    $mlbTeam->getImageTag(30, 30) . "</td>
        </tr>";

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
  	echo $fantasyTeam->getNameLink(true) . "&nbsp&nbsp" . $fantasyTeam->getSportslineImg(30, 30);
  }
  echo "</td></tr>";

  echo "</table>";

  // if admin user, show edit link
  if (SessionUtil::isLoggedInAdmin()) {
    echo "<div class='managelink'>
            <a href='admin/managePlayer.php?player_id=" . $player->getId() . "'>Manage player</a>
          </div>";
  }

  // TODO displayPlayer: show contract history
  // TODO displayPlayer: show draft/pingpong history
  // TODO displayPlayer: show auction history

  echo "</div></div>"; // span12, row-fluid

  // Display footer
  LayoutUtil::displayFooter();
?>

</body>
</html>
