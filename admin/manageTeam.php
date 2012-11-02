<?php
  require_once '../util/sessions.php';
  SessionUtil::checkUserIsLoggedInAdmin();
?>

<html>
<head>
<title>Rotiss.com - Manage Team</title>
<link href='../css/style.css' rel='stylesheet' type='text/css'>
</head>

<body>

<?php
  require_once '../dao/teamDao.php';
  require_once '../entity/team.php';
  require_once '../util/navigation.php';

  // Display header.
  NavigationUtil::printHeader(true, false, NavigationUtil::ADMIN_BUTTON);
  echo "<div class='bodyleft'>";
  if (isset($_POST['update'])) {
    // Update team.
    $teamToUpdate = new Team($_POST['teamId'], $_POST['teamName'], $_POST['league'],
        $_POST['division'], $_POST['abbreviation'], $_POST['sportslineImage']);
    TeamDao::updateTeam($teamToUpdate);
    $teamId = $_POST['teamId'];
    echo "<div class='alert_msg'>Team successfully updated!</div>";
  } else if (isset($_REQUEST["team_id"])) {
    $teamId = $_REQUEST["team_id"];
  } else {
    die("<h1>Missing team id!</h1>");
  }

  echo "<FORM ACTION='manageTeam.php' METHOD=POST>";

  $team = TeamDao::getTeamById($teamId);
  if ($team == null) {
    die("<h1>team id " . $teamId . " does not exist!</h1>");
  }

  echo "<h1>Manage: " . $team->getName() . "</h1>";

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
       " maxLength=65 size=65 value='" . $team->getSportslineImageName() . "' required></td></tr>";

  // Owners
  echo "<tr><td><strong>Owner(s):</strong></td><td>" . $team->getOwnersString() . "</td></tr>";

  echo "</table><br/>";

  // Buttons
  echo "<input class='button' type=submit name='update' value='Update Team'>
        &nbsp&nbsp<a href='../summaryPage.php?team_id=" . $team->getId() . "'>Back to Summary</a>";
  echo "</div>";

  // TODO add/delete contracts from this page
  // TODO seltzer player?

  // Footer
  NavigationUtil::printFooter();
?>

</body>
</html>
