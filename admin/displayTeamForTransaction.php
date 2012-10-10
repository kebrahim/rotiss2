<?php
  require_once '../dao/teamDao.php';
  require_once '../util/time.php';

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
  function displayTeamForTrade(Team $team, $position) {
  	// Team info
    echo "<h2>" . $team->getName() . "</h2>";
    echo "<img src='" . $team->getSportslineImageUrl() . "'><br/><br/>";
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
    echo "<br/>";
  }

  /**
   * Display team information for auction.
   */
  function displayTeamForAuction(Team $team) {
  	// Team info
  	echo "<h2>" . $team->getName() . "</h2>";
  	echo "<img src='" . $team->getSportslineImageUrl() . "'><br/><br/>";
  	echo $team->getOwnersString() . "<br/>";

  	// Brognas - auctions always happen in january, so current year is sufficient.
  	$currentYear = TimeUtil::getCurrentYear();
  	$team->displayBrognas($currentYear, $currentYear, false, 0);

  	echo "<input type='hidden' name='teamid' value='" . $team->getId() . "'>";
    echo "<br/>";
  }

  /**
   * Display team information for selecting keepers, including buttons to add more keepers if not
   * in read-only mode.
   */
  function displayTeamForKeepers(Team $team) {
    // Team info
    echo "<h2>" . $team->getName() . "</h2>";
    echo "<img src='" . $team->getSportslineImageUrl() . "'><br/><br/>";
    echo $team->getOwnersString() . "<br/>";

    // Brognas - keepers always happen in march, so current year is sufficient.
    $currentYear = TimeUtil::getCurrentYear();
    $team->displayBrognas($currentYear, $currentYear + 1, false, 0);

    // Provide ability to add contracts and balls if brognas exist and have not been banked for the
    // current year.
    $readOnly = count(BrognaDao::getBrognasByTeamFilteredByYears(
        $team->getId(), $currentYear, $currentYear + 1)) != 1;
    
    if ($readOnly) {
      echo "<br/>
            <div id='bankMessageDiv' style='color:red; font-weight:bold'>
              Cannot add keepers as this team has already banked!
    	    </div>";
    } else {
      echo "<br/>
            <input class='button' type=submit name='save' value='Save changes'>
    	    <input class='button' type=submit name='bank' value='Bank money'>
    	    <input class='button' type=submit name='cancel' value='Cancel'>";
    }
    
    // Contracts (with ability to add a keeper & buy out a contract)
    echo "<div id='column_container'>";
    echo "<div id='left_col'><div id='left_col_inner'>";
    $team->displayContractsForKeepers($currentYear, $currentYear);
    if (!$readOnly) {
      echo "<input id='addContractButton' class='button' type='button' name='addcontract'
             value='Add contract' onclick='addContract()'><br/>";
    }
    echo "</div></div>";
    
    // Ping pong balls (with ability to add more)
    echo "<div id='right_col'><div id='right_col_inner'>";
    $team->displayPingPongBalls($currentYear, $currentYear);
    if (!$readOnly) {
      echo "<input id='addBallButton' class='button' type='button' name='addpp' value='Add ball'
           onclick='addBall()'><br/>";
    }
    echo "</div></div></div>";    
    echo "<input type='hidden' name='teamid' value='" . $team->getId() . "'>";  
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
  } else if ($displayType == "keepers") {
    displayTeamForKeepers($team);
  }
?>