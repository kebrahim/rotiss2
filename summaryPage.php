<?php
  require_once 'util/sessions.php';

  SessionUtil::checkLogin();
  // TODO teamId should be pulled from request, not session
  $teamId = SessionUtil::getLoggedInTeam()->getId();
  //var_dump($_SESSION);
?>
<html>
<head>
<title>Rotiss 2012</title>
</head>

<body>
<?php
  error_reporting(E_ALL);

  require_once 'dao/contractDao.php';
  require_once 'dao/teamDao.php';
  require_once 'util/time.php';

  // Display general team information.
  //$teamId = $_GET["team_id"];
  $team = TeamDao::getTeamById($teamId);
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
?>

</body>
</html>
