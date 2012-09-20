<html>
<head>
<title>Rotiss 2012</title>
</head>

<body>

<?php
  require_once '../dao/mlbTeamDao.php';
  require_once '../dao/playerDao.php';
  require_once '../dao/positionDao.php';
  require_once '../entity/player.php';

  $isNew = false;
  if(isset($_POST['create'])) {
    // Create player.
    $unsavedPlayer = new Player(-1, $_POST['firstName'], $_POST['lastName'], $_POST['birthDate'],
        $_POST['mlbTeamId'], $_POST['sportslineId']);

    $createdPlayer = PlayerDao::createPlayer($unsavedPlayer);
    $positions = PositionDao::getPositionsByPositionIds($_POST['positions']);
    PositionDao::assignPositionsToPlayer($positions, $createdPlayer);
    $playerId = $createdPlayer->getId();
  } else if (isset($_POST['update'])) {
    // Update player.
    $playerToUpdate = new Player($_POST['player_id'], $_POST['firstName'], $_POST['lastName'],
        $_POST['birthDate'], $_POST['mlbTeamId'], $_POST['sportslineId']);
    PlayerDao::updatePlayer($playerToUpdate);
    $positions = PositionDao::getPositionsByPositionIds($_POST['positions']);
    PositionDao::assignPositionsToPlayer($positions, $playerToUpdate);
    $playerId = $playerToUpdate->getId();
  } else if (isset($_GET["player_id"])) {
    $playerId = $_GET["player_id"];
  } else if (isset($_POST["player_id"])) {
    $playerId = $_POST["player_id"];
  } else {
    $isNew = true;
  }

  echo "<FORM ACTION='managePlayer.php' METHOD=POST>";

  // Create/edit depends on whether player id was passed to page.
  if ($isNew) {
    echo "<h1>Create MLB player</h1>";
  } else {
    $player = PlayerDao::getPlayerById($playerId);
    if ($player == null) {
      die("<h1>player id " . $playerId . " does not exist!</h1>");
    }
    echo "<h1>Edit " . $player->getFullName() . "</h1>";

    // Headshot
    if ($player->hasSportslineId()) {
      echo "<a href='" . $player->getStPetesUrl() . "'>
            <img src='" . $player->getHeadshotUrl() . "'></a><br/><br/>";
    }
  }
  echo "<table>";

  // ID
  if (!$isNew) {
    echo "<tr><td><strong>Rotiss Id:</strong></td><td>" . $player->getId() . "</td></tr>";
    echo "<input type=hidden name='player_id' value='" . $player->getId() . "'>";
  }

  // Name
  echo "<tr><td><strong>Name:</strong></td><td><input type=text name='firstName' required " .
       "placeholder='First Name' value='" . ($isNew ? "" : $player->getFirstName()) . "'> ";
  echo "<input type=text name='lastName' required placeholder='Last Name' value='" .
       ($isNew ? "" : $player->getLastName()) . "'></td></tr>";

  // MLB Team
  echo "<tr><td><strong>Team:</strong></td><td><select name='mlbTeamId' required>
        <option value=''>Select MLB Team</option>";
  $mlbTeams = MlbTeamDao::getMlbTeams();
  foreach ($mlbTeams as $mlbTeam) {
    $isSelected = (!$isNew && ($mlbTeam->getId() == $player->getMlbTeam()->getId()));
    echo "<option value='" . $mlbTeam->getId() . "'" . ($isSelected ? " selected" : "") .
         ">" . $mlbTeam->getCity() . " " . $mlbTeam->getName() . "</option>";
  }
  echo "</select></td></tr>";

  // Birth date & age
  echo "<tr><td><strong>Birth Date:</strong></td>
            <td><input type=date name='birthDate' required value='" .
                ($isNew ? "" : $player->getBirthDate()) . "'></td></tr>";
  if (!$isNew) {
    echo "<tr><td><strong>Age:</strong></td><td>" . $player->getAge() . "</td></tr>";
  }

  // Sportsline ID
  echo "<tr><td><strong>Sportsline ID:</strong></td>
            <td><input type=text name='sportslineId' required " .
                "value='" . ($isNew ? "0" : $player->getSportslineId()) . "'></td></tr>";

  // Positions
  echo "<tr><td><strong>Position(s):</strong></td>
            <td>" . ($isNew ? "" : $player->getPositionString()) .
                " <select name='positions[]' required multiple>";
  $positions = PositionDao::getPositions();
  foreach ($positions as $position) {
    $isSelected = (!$isNew && $player->playsPosition($position));
    echo "<option value='" . $position->getId() . "'" . ($isSelected ? "selected" : "") . ">" .
         $position->getAbbreviation() . "</option>";
  }
  echo "</select></td></tr>";
  echo "</table><br/>";

  // Buttons
  if ($isNew) {
    echo "<input class='button' type=submit name='create' value='Create Player'>";
  } else {
    echo "<input class='button' type=submit name='update' value='Update Player'>";
    echo "&nbsp&nbsp<a href='../displayPlayer.php?player_id=" . $player->getId() .
         "'>View player</a>";
  }
?>

</body>
</html>
