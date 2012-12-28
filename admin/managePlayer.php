<?php
  require_once '../util/sessions.php';

  SessionUtil::checkUserIsLoggedInAdmin();

  // Get player from REQUEST.
  $redirectUrl = "admin/managePlayer.php";
  if (isset($_REQUEST["player_id"])) {
  	$playerId = $_REQUEST["player_id"];
  	$redirectUrl .= "?player_id=$playerId";
  }

  SessionUtil::logoutUserIfNotLoggedIn($redirectUrl);
?>

<!DOCTYPE html>
<html>
<head>
<title>St Pete's Rotiss - Manage Player</title>
<link href='../css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='../css/stpetes.css' rel='stylesheet' type='text/css'>
<link rel="shortcut icon" href="../img/background-tiles-01.png" />
</head>

<body>

<?php
  require_once '../dao/mlbTeamDao.php';
  require_once '../dao/playerDao.php';
  require_once '../dao/positionDao.php';
  require_once '../entity/player.php';
  require_once '../util/layout.php';

  // Nav bar
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_PLAYER_BUTTON);

  $isNew = false;
  if(isset($_POST['create'])) {
    // Create player.
    $unsavedPlayer = new Player(-1, $_POST['firstName'], $_POST['lastName'], $_POST['birthDate'],
        $_POST['mlbTeamId'], $_POST['sportslineId']);
    $createdPlayer = PlayerDao::createPlayer($unsavedPlayer);

    // Save positions.
    $positions = PositionDao::getPositionsByPositionIds($_POST['positions']);
    PositionDao::assignPositionsToPlayer($positions, $createdPlayer);

    // Save fantasy team.
    TeamDao::assignPlayerToTeam($createdPlayer, $_POST['teamId']);

    $playerId = $createdPlayer->getId();

    echo "<div class='alert alert-success center'>
            <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
            <strong>Player successfully created!</strong>
          </div>";
  } else if (isset($_POST['update'])) {
    // Update player.
    $playerToUpdate = new Player($_POST['player_id'], $_POST['firstName'], $_POST['lastName'],
        $_POST['birthDate'], $_POST['mlbTeamId'], $_POST['sportslineId']);
    PlayerDao::updatePlayer($playerToUpdate);

    // Update positions.
    $positions = PositionDao::getPositionsByPositionIds($_POST['positions']);
    PositionDao::assignPositionsToPlayer($positions, $playerToUpdate);

    // Update fantasy team.
    TeamDao::assignPlayerToTeam($playerToUpdate, $_POST['teamId']);
    $playerId = $playerToUpdate->getId();

    echo "<div class='alert alert-success center'>
                <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
                <strong>Player successfully updated!</strong>
              </div>";
  } else if (isset($_REQUEST["player_id"])) {
    $playerId = $_REQUEST["player_id"];
  } else {
    $isNew = true;
  }

  echo "<div class='row-fluid'>";

  // Create/edit depends on whether player id was passed to page.
  if ($isNew) {
    echo "<div class='span12 center'><h3>Create MLB player</h3></div>";
  } else {
    $player = PlayerDao::getPlayerById($playerId);
    if ($player == null) {
      die("<h3>player id " . $playerId . " does not exist!</h3>");
    }

    // Headshot
    echo "<div class='span2 center headshotimg nexttoh1'>";
    if ($player->hasSportslineId()) {
      echo "<a href='" . $player->getStPetesUrl() . "' target='_blank'>" .
          $player->getHeadshotImg(42, 56) . "</a><br/><br/>";
    }
    echo "</div>"; //span2

    // player name heading
    echo "<div class='span10 center'>
                <h3>Manage: " . $player->getFullName() . "</h3>
              </div>";
  }
  echo "</div>"; // row-fluid

  echo "<div class='row-fluid'>
          <div class='span12 center'>";
  echo "<FORM ACTION='managePlayer.php' METHOD=POST>";

  echo "<br/>
        <table class='table vertmiddle table-striped table-condensed table-bordered'>";

  // ID
  if (!$isNew) {
    echo "<tr><td><label>Rotiss Id:</label></td><td>" . $player->getId() . "</td></tr>";
    echo "<input type=hidden name='player_id' value='" . $player->getId() . "'>";
  }

  // Name
  echo "<tr><td><label for='firstName'>Name:</label></td>
            <td><input type=text name='firstName' id='firstName' required " .
       "placeholder='First Name' value='" . ($isNew ? "" : $player->getFirstName()) . "'> ";
  echo "<input type=text name='lastName' required placeholder='Last Name' value='" .
       ($isNew ? "" : $player->getLastName()) . "'></td></tr>";

  // MLB Team
  echo "<tr><td><label for='mlbTeamId'>Team:</label></td>
            <td><select name='mlbTeamId' id='mlbTeamId' required>
        <option value=''>Select MLB Team</option>";
  $mlbTeams = MlbTeamDao::getMlbTeams();
  foreach ($mlbTeams as $mlbTeam) {
    $isSelected = (!$isNew && ($mlbTeam->getId() == $player->getMlbTeam()->getId()));
    echo "<option value='" . $mlbTeam->getId() . "'" . ($isSelected ? " selected" : "") .
         ">" . $mlbTeam->getCity() . " " . $mlbTeam->getName() . "</option>";
  }
  echo "</select></td></tr>";

  // Birth date & age
  echo "<tr><td><label for='birthDate'>Birth Date:</label></td>
            <td><input type=date name='birthDate' id='birthDate' required value='" .
                ($isNew ? "" : $player->getBirthDate()) . "'></td></tr>";
  if (!$isNew) {
    echo "<tr><td><label>Age:</label></td><td>" . $player->getAge() . "</td></tr>";
  }

  // Sportsline ID
  echo "<tr><td><label for='sportslineId'>Sportsline ID:</label></td>
            <td><input type=text name='sportslineId' id='sportslineId' required " .
                "value='" . ($isNew ? "" : $player->getSportslineId()) . "'></td></tr>";

  // Positions
  echo "<tr><td><label for='positions'>Position(s):</label></td>
            <td>" . ($isNew ? "" : $player->getPositionString()) .
                " <select name='positions[]' id='positions' required multiple>";
  $positions = PositionDao::getPositions();
  foreach ($positions as $position) {
    $isSelected = (!$isNew && $player->playsPosition($position));
    echo "<option value='" . $position->getId() . "'" . ($isSelected ? "selected" : "") . ">" .
         $position->getAbbreviation() . "</option>";
  }
  echo "</select></td></tr>";

  // Fantasy team
  echo "<tr><td><label for='teamId'>Fantasy Team:</label></td>
            <td><select name='teamId' id='teamId' class='input-xxlarge'>
                <option value='0'>None</option>";
  $teams = TeamDao::getAllTeams();
  foreach ($teams as $team) {
  	$isSelected = (!$isNew && ($player->getFantasyTeam() != null) &&
  	    ($team->getId() == $player->getFantasyTeam()->getId()));
  	echo "<option value='" . $team->getId() . "'" . ($isSelected ? " selected" : "") .
  	">" . $team->getName() . " (" . $team->getAbbreviation() . ")</option>";
  }
  echo "</select></td></tr>";

  echo "</table>";

  // Buttons
  if ($isNew) {
    echo "<p><button class='btn btn-primary' type=submit name='create'>Create Player</button></p>";
  } else {
    echo "<p><button class='btn btn-primary' type=submit name='update'>Update Player</button>";
    echo "&nbsp&nbsp" . $player->getIdLink(false, "Return to Player") . "</p>";
  }
  echo "</form>";
  echo "</div>"; //span12
  echo "</div>"; //row-fluid

  // footer
  LayoutUtil::displayAdminFooter();
?>

</body>
</html>
