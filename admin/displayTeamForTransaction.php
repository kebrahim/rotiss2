<?php
  require_once '../dao/teamDao.php';
  require_once '../util/sessions.php';
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
  	$team->displayTeamInfo();

    // Contracts
    $contractSeason = TimeUtil::getYearBasedOnEndOfSeason();
    $team->displayContracts($contractSeason, 3000, true, 'center smallfonttable');

    // Brognas
    $keeperSeason = TimeUtil::getYearBasedOnKeeperNight();
    $team->displayBrognas($keeperSeason + 1, $keeperSeason + 1, true, $position,
        'center smallfonttable');

    // Picks
    $draftSeason = TimeUtil::getYearBasedOnStartOfSeason();
    $team->displayDraftPicks($draftSeason + 1, 3000, true, 'center smallfonttable');

    echo "<input type='hidden' name='trade_team". $position . "id' value='" . $team->getId() . "'>";
    echo "<br/>";
  }

  /**
   * Display team information for auction.
   */
  function displayTeamForAuction(Team $team) {
  	// Team info
  	$team->displayTeamInfo();

  	// Brognas - auctions always happen in january, so current year is sufficient.
  	$currentYear = TimeUtil::getCurrentYear();
  	$team->displayBrognas($currentYear, $currentYear, false, 0, 'center smallfonttable');

  	echo "<input type='hidden' name='auction_teamid' value='" . $team->getId() . "'>";
    echo "<br/>";
  }

  /**
   * Display team information for selecting keepers, including buttons to add more keepers if not
   * in read-only mode.
   */
  function displayTeamForKeepers(Team $team) {
    // Team info
    $team->displayTeamInfo();

    // Brognas - keepers always happen in march, so current year is sufficient.
    $currentYear = TimeUtil::getCurrentYear();
    $team->displayBrognas($currentYear, $currentYear + 1, false, 0, 'center smallfonttable');

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
             value='Add keeper' onclick='addContract(". $team->getId() . ")'><br/>";
    }
    echo "</div></div>";

    // Ping pong balls (with ability to add more)
    echo "<div id='right_col'><div id='right_col_inner'>";
    $team->displayPingPongBalls($currentYear, $currentYear);
    if (!$readOnly) {
      echo "<input class='button' type='button' name='addpp' value='Add ball' onclick='addBall()'>
            <br/>";
    }
    echo "</div></div></div>";
    echo "<input type='hidden' name='keeper_teamid' value='" . $team->getId() . "'>";
  }

  /**
   * Displays a drop-down of players eligible to be kept.
   */
  function displayEligibleKeeperPlayers(Team $team, $rowNumber) {
    $currentYear = TimeUtil::getCurrentYear();
    $eligiblePlayers = PlayerDao::getEligibleKeepers($team, $currentYear);
    echo "<select name='keeper_player" . $rowNumber . "' onchange='selectPlayer(this.value, "
        . $rowNumber . ")'><option value='0'></option>";
    foreach ($eligiblePlayers as $player) {
      echo "<option value='" . $player->getId() . "'" . ">" . $player->getFullName() .
          " (" . $player->getPositionString() . ") - " . $player->getMlbTeam()->getAbbreviation() .
          "</option>";
    }
    echo "</select>";
  }

  /**
   * Display specified team on team summary page.
   */
  function displayTeam(Team $team) {
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
  }

  /**
   * Display team on manage team page.
   */
  function displayTeamForManagement(Team $team) {
    echo "<h1>Manage: " . $team->getName() . "</h1>";

    // Sportsline Image
    echo "<img src='" . $team->getSportslineImageUrl() . "'><br/><br/>";

    // ID
    echo "<table>";
    echo "<tr><td><strong>Team Id:</strong></td><td>" . $team->getId() . "</td></tr>";
    echo "<input type=hidden name='teamId' value='" . $team->getId() . "'>";

    // Name
    // TODO parse quotes of team name field
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
             <input type=text name='abbreviation' maxLength=10 size=10 required " .
           "placeholder='Team Abbreviation' value='" . $team->getAbbreviation() . "'></td></tr>";

    // Sportsline Image Name
    echo "<tr><td><strong>Sportsline Image Name:</strong></td><td>
             <input type=text name='sportslineImage'" .
           " maxLength=65 size=65 value='" . $team->getSportslineImageName() . "' required>
              </td></tr>";

    // Owners
    echo "<tr><td><strong>Owner(s):</strong></td><td>" . $team->getOwnersString() . "</td></tr>";

    echo "</table><br/>";

    // Buttons
    echo "<input class='button' type=submit name='update' value='Update Team'>&nbsp&nbsp
          <a href='../summaryPage.php?team_id=" . $team->getId() . "'>Back to Summary</a>";
  }
  
  /**
   * Display team on budget page.
   */
  function displayTeamForBudget(Team $team) {
  	// Show budget information for selected team
  	echo "<h1>Budget: " . $team->getName() . "</h1>";
  	echo "<img src='" . $team->getSportslineImageUrl() . "'><br/><br/>";
  	
  	echo "<table>";
  	echo "  <tr><td><strong>Owner(s):</strong></td>
  	<td>" . $team->getOwnersString() . "</td></tr>";
  	echo "</table>";
  	
  	// Display contracts.
  	displayBreakdown($team->getId());
  }
  
  /**
   * Show breakdown of brognas per year w/ contract info for specified team
   */
  function displayBreakdown($teamId) {
  	// get all points
  	$brognas = BrognaDao::getBrognasByTeamId($teamId);
  
  	// get all contracts
  	$contracts = ContractDao::getContractsByTeamId($teamId);
  
  	// for ever brogna record,
  	$currentYear = TimeUtil::getYearBasedOnKeeperNight();
  	// TODO budget: allow user to adjust start year
  	foreach ($brognas as $brogna) {
  		if ($brogna->getYear() < $currentYear) {
  			continue;
  		}
  		// show brogna info
  		echo "<h3>" . $brogna->getYear() . "</h3>";
  		echo "<h4>Brognas</h4>";
  		echo "<table border class='left'>
  		<tr><th>Alotted</th>
  		<th>" . ($brogna->getYear() - 1) . " Bank</th>
  		<th>Received in Trade</th>
  		<th>Given in Trade</th><th>Total</th></tr>";
  		echo "<tr><td>450</td>
  		<td>" . $brogna->getBankedPoints() . "</td>
  		<td>" . $brogna->getTradedInPoints() . "</td>
  		<td>" . $brogna->getTradedOutPoints() . "</td>
  		<td><strong>" . $brogna->getTotalPoints() . "</td></tr></table>";
  
  		// show contracts for that year
  		$contractTotal = 0;
  		$hasContracts = false;
  		foreach ($contracts as $contract) {
  			if (($contract->getStartYear() > $brogna->getYear()) ||
  					($contract->getEndYear() < $brogna->getYear())) {
  				continue;
  			}
  			if ($hasContracts == false) {
  				echo "<h4>Contracts</h4>";
  				echo "<table border class='left'>
  				<tr><th colspan='2'>Player</th><th>Position</th><th>Team</th><th>Age</th>
  				<th>Years Remaining</th><th>Price</th></tr>";
  				$hasContracts = true;
  			}
  			$player = $contract->getPlayer();
  			echo "<tr><td>" . $player->getMiniHeadshotImg() . "</td>
  			<td>" . $player->getNameLink(true) . "</td>
  			<td>" . $player->getPositionString() . "</td>
  			<td>" . $player->getMlbTeam()->getImageTag(30, 30) . "</td>
  			<td>" . $player->getAge() . "</td>
  			<td>" . ($contract->getEndYear() - $brogna->getYear() + 1) . "</td>
  			<td>" . $contract->getPrice() . "</td></tr>";
  			$contractTotal += $contract->getPrice();
  		}
  
  		if ($hasContracts == true) {
  			echo "<tr><td colspan='6'></td>
  			<td><strong>" . $contractTotal . "</strong></td></tr>";
  			echo "</table>";
  		}
  
  		// show leftover brognas
  		echo "<br/><strong>Bank for " . ($brogna->getYear() + 1) . ": </strong>" .
  				($brogna->getTotalPoints() - $contractTotal);
  		// TODO budget: should bank from previous year be calculated?
  	}
  }

  // direct to corresponding function, depending on type of display
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
  } else if ($displayType == "keepercontracts") {
    if (isset($_REQUEST["row"])) {
      $rowNumber = $_REQUEST["row"];
    }
    displayEligibleKeeperPlayers($team, $rowNumber);
  } else if ($displayType == "display") {
    displayTeam($team);
  } else if ($displayType == "manage") {
    displayTeamForManagement($team);
  } else if ($displayType == "budget") {
    displayTeamForBudget($team);
  }
?>