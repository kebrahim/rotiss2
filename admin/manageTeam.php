<html>
<head>
<title>Rotiss 2012</title>
</head>

<body>

<?php
  require_once '../dao/teamDao.php';
  require_once '../entity/team.php';

  if (isset($_POST['update'])) {
    // Update team.
    $teamToUpdate = new Team($_POST['teamId'], $_POST['teamName'], $_POST['league'],
        $_POST['division'], $_POST['abbreviation'], $_POST['sportslineImage']);
    TeamDao::updateTeam($teamToUpdate);
    $teamId = $teamToUpdate->getId();
  } else if (isset($_GET["team_id"])) {
    $teamId = $_GET["team_id"];
  } else if (isset($_POST["team_id"])) {
    $teamId = $_POST["team_id"];
  } else {
    die("<h1>Missing team id!</h1>");
  }

  echo "<FORM ACTION='manageTeam.php' METHOD=POST>";

  $team = TeamDao::getTeamById($teamId);
  if ($team == null) {
    die("<h1>team id " . $teamId . " does not exist!</h1>");
  }

  echo "<h1>Edit " . $team->getName() . "</h1>";

  // Sportsline Image
  echo "<img src='" . $team->getSportslineImageUrl() . "'><br/><br/>";

  // ID
  echo "<table>";
  echo "<tr><td><strong>Team Id:</strong></td><td>" . $team->getId() . "</td></tr>";
  echo "<input type=hidden name='teamId' value='" . $team->getId() . "'>";

  // Name
  // TODO manageTeam: parse quotes
  echo "<tr><td><strong>Name:</strong></td><td>
         <input type=text name='teamName' maxLength=50 size=50 required " .
       "placeholder='Team Name' value='" . $team->getName() . "'></td></tr>";

  // League/Division
  echo "<tr><td><strong>Division:</strong></td><td><select name='league' required>";
  $leagues = array("AL", "NL");
  foreach ($leagues as $league) {
    $isSelected = ($league == $team->getLeague());
    echo "<option value='" . $league . "'" . ($isSelected ? " selected" : "") .
         ">" . $league . "</option>";
  }
  echo "</select> <select name='division' required>";
  $divisions = array("East", "West");
  foreach ($divisions as $division) {
    $isSelected = ($division == $team->getDivision());
    echo "<option value='" . $division . "'" . ($isSelected ? " selected" : "") .
           ">" . $division . "</option>";
  }
  echo "</select></td></tr>";

  // Abbreviation
  echo "<tr><td><strong>Abbreviation:</strong></td><td>
         <input type=text name='abbreviation' maxLength=5 size=5 required " .
       "placeholder='Team Abbreviation' value='" . $team->getAbbreviation() . "'></td></tr>";

  // Sportsline Image Name
  echo "<tr><td><strong>Sportsline Image Name:</strong></td><td>
         <input type=text name='sportslineImage'" .
       " maxLength=45 size=45 value='" . $team->getSportslineImageName() . "'></td></tr>";

  // Owners
  echo "<tr><td><strong>Owner(s):</strong></td><td>" . $team->getOwnersString() . "</td></tr>";

  echo "</table><br/>";

  // Buttons
  echo "<input class='button' type=submit name='update' value='Update Team'>
        <a href='../summaryPage.php?team_id=" . $team->getId() . "'>Back to Summary</a>";
?>

</body>
</html>
