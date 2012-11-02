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

  // TODO choose from list of teams to see corresponding summary page.

  // Display general team information.
  if (isset($_REQUEST["team_id"])) {
    $teamId = $_REQUEST["team_id"];
  } else {
    // if no team is selected, then show the logged-in user's team.
    $teamId = SessionUtil::getLoggedInTeam()->getId();
  }
  $team = TeamDao::getTeamById($teamId);

  echo "<div class='bodyleft'>";
  echo "<h1>" . $team->getName() . "</h1>";
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

  // TODO display current team
  echo "</div>";

  // Display footer
  NavigationUtil::printFooter();
?>

</body>
</html>
