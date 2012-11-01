<?php
  require_once 'util/sessions.php';
  SessionUtil::checkUserIsLoggedIn();
?>
<html>
<head>
<title>Rotiss - My Team</title>
<link href='css/style.css' rel='stylesheet' type='text/css'>
</head>

<body>
<?php
  require_once 'dao/contractDao.php';
  require_once 'dao/teamDao.php';
  require_once 'util/navigation.php';
  require_once 'util/time.php';

  // Display header.
  NavigationUtil::printHeader();

  // Display general team information.
  if (isset($_REQUEST["team_id"])) {
    $teamId = $_REQUEST["team_id"];
  } else {
    // if no team is selected, then show the user's team.
    $teamId = SessionUtil::getLoggedInTeam()->getId();
  }
  $team = TeamDao::getTeamById($teamId);

  echo "<div id='bodyleft'>";
  echo "<h1>" . $team->getName() . "</h1>";
  echo "<img src='" . $team->getSportslineImageUrl() . "'><br/><br/>";

  // Owners, Division
  echo "<table>";
  echo "  <tr><td><strong>Owner(s):</strong></td>
              <td>" . $team->getOwnersString() . "</td></tr>";
  echo "  <tr><td><strong>Division:</strong></td>
              <td>" . $team->getLeague() . " " . $team->getDivision() . "</td></tr>";
  echo "</table><br/>";

  // if admin user, show edit link
  if (SessionUtil::isLoggedInAdmin()) {
    echo "<a href='admin/manageTeam.php?team_id=" . $team->getId() . "'>Edit team</a><br/>";
  }

  // Display contracts.
  $team->displayAllContracts();

  // Display points information
  $team->displayAllBrognas();

  // Display draft pick information
  $team->displayAllDraftPicks();
  echo "</div>";

  // Display footer
  NavigationUtil::printFooter();
?>

</body>
</html>
