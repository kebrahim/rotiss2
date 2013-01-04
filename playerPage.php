<?php
  require_once 'util/sessions.php';

  $redirectUrl = "playerPage.php";
  if (isset($_REQUEST['player_id'])) {
  	$playerId = $_REQUEST['player_id'];
  	$redirectUrl .= "?player_id=$playerId";
  } else {
  	die("<h1>Missing playerId for player page</h1>");
  }
  SessionUtil::logoutUserIfNotLoggedIn($redirectUrl);
?>

<!DOCTYPE html>
<html>
<head>
<title>St Pete's Rotiss - Player Summary</title>
<link href='css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='css/stpetes.css' rel='stylesheet' type='text/css'>
<link rel="shortcut icon" href="img/background-tiles-01.png" />
</head>

<body>

<?php
  require_once 'dao/playerDao.php';
  require_once 'util/layout.php';

  // Display header.
  LayoutUtil::displayNavBar(TRUE, LayoutUtil::PLAYERS_BUTTON);

  echo "<div class='row-fluid'>";
  // Get player from db.
  $player = PlayerDao::getPlayerById($playerId);
  if ($player == null) {
  	die("<div class='span12 center'>
  			<h1>player id " . $playerId . " does not exist!</h1>
  	     </div></div>");
  }

  echo "<div class='span2 center headshotimg nexttoh1'>";

  // Headshot
  if ($player->hasSportslineId()) {
    echo "<a href='" . $player->getStPetesUrl() . "' target='_blank'>" .
          $player->getHeadshotImg(42, 56) . "</a><br/><br/>";
  } else {
    // TODO show blank face
  }
  echo "</div>"; // span2

  // player name heading
  echo "<div class='span10 center'>
            <h3>" . $player->getFullName() . "</h3>
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
                    $mlbTeam->getImageTag(32) . "</td>
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
  	echo $fantasyTeam->getNameLink(true) . "&nbsp&nbsp" . $fantasyTeam->getSportslineImg(32);
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
