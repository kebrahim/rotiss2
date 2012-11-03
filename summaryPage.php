<?php
  require_once 'util/sessions.php';
  SessionUtil::checkUserIsLoggedIn();
?>

<html>
<head>
<title>Rotiss.com - Team Summary</title>
<link href='css/style.css' rel='stylesheet' type='text/css'>
</head>

<body>
<?php
  require_once 'dao/contractDao.php';
  require_once 'dao/teamDao.php';
  require_once 'util/navigation.php';
  require_once 'util/time.php';

  // Display header.
  NavigationUtil::printHeader(true, true, NavigationUtil::MY_TEAM_BUTTON);

  echo "<div class='bodyleft'>";

  // Get team from REQUEST; otherwise, use logged-in user's team.
  if (isset($_REQUEST["team_id"])) {
    $teamId = $_REQUEST["team_id"];
  } else {
    $teamId = SessionUtil::getLoggedInTeam()->getId();
  }
  $team = TeamDao::getTeamById($teamId);

  // Allow user to choose from list of teams to see corresponding summary page.
  $allTeams = TeamDao::getAllTeams();
  echo "<form action='summaryPage.php' method=post>";
  echo "<br/><label for='team_id'>Change team: </label>";
  echo "<select id='team_id' name='team_id'>";
  foreach ($allTeams as $selectTeam) {
    echo "<option value='" . $selectTeam->getId() . "'";
    if ($selectTeam->getId() == $teamId) {
      echo " selected";
    }
    echo ">" . $selectTeam->getName() . " (" . $selectTeam->getAbbreviation() . ")</option>";
  }
  echo "</select>&nbsp&nbsp<input type='submit' name='submit' value='Choose team'><br/></form>";

  // TODO add bookmarks to various sections of page

  echo "<h1>Team Summary: " . $team->getName() . "</h1>";
  echo "<img src='" . $team->getSportslineImageUrl() . "'><br/><br/>";

  // Owners, Abbreviation, Division
  echo "<table>";
  echo "  <tr><td><strong>Owner(s):</strong></td>
              <td>" . $team->getOwnersString() . "</td></tr>";
  echo "  <tr><td><strong>Abbreviation:</strong></td>
              <td>" . $team->getAbbreviation() . "</td></tr>";
  echo "  <tr><td><strong>Division:</strong></td>
              <td>" . $team->getLeague() . " " . $team->getDivision() . "</td></tr>";
  echo "</table>";

  // if admin user, show edit link
  if (SessionUtil::isLoggedInAdmin()) {
    echo "<br/><a href='admin/manageTeam.php?team_id=" . $team->getId() . "'>Manage team</a><br/>";
  }

  // Display contracts.
  $team->displayAllContracts();

  // Display points information
  $team->displayAllBrognas();

  // Display draft pick information
  $team->displayAllDraftPicks();

  // Display current team
  $team->displayPlayers();

  echo "</div>";

  // Display footer
  NavigationUtil::printFooter();
?>

</body>
</html>
