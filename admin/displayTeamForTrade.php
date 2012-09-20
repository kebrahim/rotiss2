<?php
  require_once '../dao/teamDao.php';
  require_once '../util/time.php';
  // TODO rename to displayTeamForTransaction.php
  /**
   * Returns a Team based on the ID specified in the GET/POST.
   */
  function getTeamByParam($param) {
  	if (isset($_REQUEST[$param])) {
      $teamId = $_REQUEST[$param];
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
  function displayTeamForTrade($team, $position) {
  	// Team info
    echo "<img src='" . $team->getSportslineImageUrl() . "'><br/><br/>";
    echo "<strong>" . $team->getName() . "</strong><br/>";
    echo $team->getOwnersString() . "<br/>";

    // Contracts
    $contractSeason = TimeUtil::getYearBasedOnEndOfSeason();
    $team->displayContracts($contractSeason, 3000, true);

    // Brognas
    $keeperSeason = TimeUtil::getYearBasedOnKeeperNight();
    $team->displayBrognas($keeperSeason + 1, $keeperSeason + 1, true, $position);

    // Picks
    $draftSeason = TimeUtil::getYearBasedOnStartOfSeason();
    $team->displayDraftPicks($draftSeason + 1, 3000, true);

    echo "<input type='hidden' name='team". $position . "id' value='" . $team->getId() . "'>";
    echo "<br/><br/>";
  }
  
  /**
   * Display team information for auction.
   */
  function displayTeamForAuction($team) {
  	// Team info
  	echo "<h2>" . $team->getName() . "</h2>";
  	echo "<img src='" . $team->getSportslineImageUrl() . "'><br/><br/>";
  	echo $team->getOwnersString() . "<br/>";

  	// Brognas
  	$keeperSeason = TimeUtil::getYearBasedOnKeeperNight();
  	$team->displayBrognas($keeperSeason + 1, $keeperSeason + 1, false, 0);

  	echo "<input type='hidden' name='teamid' value='" . $team->getId() . "'>";
    echo "<br/><br/>";
  }

  if (isset($_REQUEST["type"])) {
  	$displayType = $_REQUEST["type"];
  } else {
  	die("<h1>Invalid display type for team</h1>");
  }
  $team = getTeamByParam("team_id");
  
  if ($displayType == "trade") {
    if (isset($_REQUEST["position"])) {
      $position = $_REQUEST["position"];
    }
    displayTeamForTrade($team, $position);
  } else if ($displayType == "auction") {
  	displayTeamForAuction($team);
  }
?>