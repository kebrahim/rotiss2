<?php
  require_once '../dao/teamDao.php';
  require_once '../util/time.php';

  /**
   * Returns a Team based on the ID specified in the GET/POST.
   */
  function getTeamByParam($param) {
    if (isset($_GET[$param])) {
      $teamId = $_GET[$param];
    } else if (isset($_POST[$param])) {
      $teamId = $_POST[$param];
    } else {
      $teamId = 0;
    }
    $team = TeamDao::getTeamById($teamId);
    if ($team == null) {
      die("<h1>team id " . $teamId . " does not exist for param " . $param . "!</h1>");
    }
    return $team;
  }

  /**
   * Display team information [points, picks, players], allowing user to select those which should
   * be traded.
   */
  function displayTeam($team, $position) {
    // Team info
    echo "<center><img src='" . $team->getSportslineImageUrl() . "'><br/>";
    echo "<strong>" . $team->getName() . "</strong><br/>";
    echo $team->getOwnersString() . "<br/></center>";

    $currentYear = TimeUtil::getCurrentSeasonYear();

    // Contracts
    $team->displayContracts($currentYear, 3000, true);

    // Points
    $team->displayBrognas($currentYear + 1, $currentYear + 1, true);

    // Picks
    $team->displayDraftPicks($currentYear + 1, 3000, true);

    echo "<input type='hidden' name='team". $position . "id' value='" . $team->getId() . "'>";
    echo "<br/><br/>";
  }

  $team = getTeamByParam("team_id");
  displayTeam($team);
?>