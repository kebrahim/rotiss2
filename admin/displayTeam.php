<?php
  require_once '../dao/changelogDao.php';
  require_once '../dao/teamDao.php';
  require_once '../util/sessions.php';
  require_once '../util/teamManager.php';
  require_once '../util/time.php';

  /**
   * Returns a Team based on the ID specified in the GET/POST, or null if none exist.
   */
  function getTeamByParam($param) {
  	if (isset($_REQUEST[$param])) {
      $teamId = $_REQUEST[$param];
  	} else {
  	  $teamId = 0;
  	}
    return TeamDao::getTeamById($teamId);
  }

  /**
   * Display team information [points, picks, players], allowing user to select those which should
   * be traded.
   */
  function displayTeamForTrade(Team $team, $position) {
  	echo "<hr class='bothr'/>";

  	// Team info
  	$team->displayTeamInfo();
  	echo "<hr/>";

    // Contracts
    $contractSeason = TimeUtil::getYearByEvent(Event::AUCTION);
    $team->displayContractsForTrade($contractSeason, 3000);

    // Brognas
    $keeperSeason = TimeUtil::getYearByEvent(Event::KEEPER_NIGHT);
    $team->displayBrognasForTrade($keeperSeason + 1, $keeperSeason + 1, $position);

    // Picks
    $draftSeason = TimeUtil::getYearByEvent(Event::SEASON_START);
    $team->displayDraftPicksForTrade($draftSeason + 1, 3000);

    echo "<input type='hidden' name='trade_team". $position . "id' value='" . $team->getId() . "'>";
  }

  /**
   * Display team information for auction.
   */
  function displayTeamForAuction(Team $team) {
  	echo "<hr class='bothr'/>";

  	// Team info
  	$team->displayTeamInfo();
  	echo "<hr/>";

  	// Brognas - auctions always happen in january, so current year is sufficient.
  	$currentYear = TimeUtil::getCurrentYear();
  	$team->displayBrognasForAuction($currentYear);

  	echo "<input type='hidden' name='auction_teamid' value='" . $team->getId() . "'>";
  }

  /**
   * Display team information for selecting keepers, including buttons to add more keepers if not
   * in read-only mode.
   */
  function displayTeamForKeepers(Team $team) {
    echo "<hr class='bothr'/>
          <h4>Keepers for " . $team->getName() . "</h4>";

    // Team info
    echo "<div class='row-fluid'>
            <div class='span4'>";
    echo "<br/>" . $team->getSportslineImg(72);
    echo "<br/><p class='ptop'>" . $team->getOwnersString() . "</p>";
    echo "  </div>"; // span4

    // Brognas - keepers always happen in march, so current year is sufficient.
    echo "  <div class='span8'>";
    $currentYear = TimeUtil::getCurrentYear();
    $team->displayBrognas($currentYear, $currentYear + 1, false, 0, 'center smallfonttable');
    echo "  </div>"; // span8
    echo "</div>";   // row-fluid

    // Provide ability to add contracts and balls if brognas exist and have not been banked for the
    // current year.
    $readOnly = count(BrognaDao::getBrognasByTeamFilteredByYears(
        $team->getId(), $currentYear, $currentYear + 1)) != 1;

    // Contracts (with ability to add a keeper & buy out a contract)
    // TODO show old contracts and new contracts
    echo "<div class='row-fluid'>
            <div class='span9 center'>";
    $team->displayContractsForKeepers($currentYear, $currentYear, $readOnly);
    if (!$readOnly) {
      echo "<p><button id='addContractButton' class='btn' name='addcontract'
                       type='button' onclick='addContract(". $team->getId() . ")'>
                 Add Keeper Contract
               </button></p>";
    }
    echo "  </div>"; // span9

    // Ping pong balls (with ability to add more)
    echo "  <div class='span3 center'>";
    $team->displayPingPongBalls($currentYear, $currentYear);
    if (!$readOnly) {
      echo "<p><button id='addpp' class='btn' name='addpp'
                       type='button' onclick='addBall()'>
                 Add Ball
               </button></p>";
    }
    echo "  </div>"; // span3
    echo "</div>"; // row-fluid

    if ($readOnly) {
      echo "<div id='bankMessageDiv' class='alert alert-error'>
              <strong>Cannot add keepers as this team has already banked!</strong>
            </div>";
    } else {
      echo "<p>
              <button class='btn btn-primary' name='save' type='submit'>Save Changes</button>&nbsp
              <button class='btn btn-inverse' name='bank' type='submit'>Bank Money</button>&nbsp
              <button class='btn' name='cancel' type='submit'>Cancel</button>
            </p>";
    }
    echo "<input type='hidden' name='keeper_teamid' value='" . $team->getId() . "'>";
  }

  /**
   * Displays a drop-down of players eligible to be kept.
   */
  function displayEligibleKeeperPlayers(Team $team, $rowNumber) {
    $currentYear = TimeUtil::getCurrentYear();
    $eligiblePlayers = PlayerDao::getEligibleKeepers($team, $currentYear);
    echo "<select class='input-large' id='keeper_player" . $rowNumber . "'
                  name='keeper_player" . $rowNumber . "' onchange='selectPlayer(this.value, " .
                 "document.getElementById(\"keeper_year" . $rowNumber . "\").value, " .
                  $rowNumber . ")'><option value='0'>-- Select Player --</option>";
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
  function displayTeam(Team $team, $isLoggedInAdmin) {
    echo "<div class='row-fluid'>";

    // team logo
    echo "<div class='span2 center teamlogo'>" .
            $team->getSportslineImg(72) .
         "</div>";

    // team name
    echo "<div class='span10 center'>
            <h3>" . $team->getName() . "</h3>
            <div class='bookmarks'>
              <a href='#summary'>Summary</a>&nbsp&nbsp
              <a href='#brognas'>Brognas</a>&nbsp&nbsp
              <a href='#contracts'>Contracts</a>&nbsp&nbsp
              <a href='#draft'>Draft Picks</a>&nbsp&nbsp
              <a href='#roster'>Roster</a>
            </div>
          </div>";
    echo "</div>"; // row-fluid

    echo "<div class='row-fluid'>";

    // Team Summary - Owners, Abbreviation, Division
    echo "<div class='span5'>
          <a id='summary'></a><h4>Team Summary</h4>
          <table class='table vertmiddle table-striped table-condensed table-bordered'>
            <tr><td><strong>Owner(s):</strong></td>
                  <td>" . $team->getOwnersString() . "</td></tr>
            <tr><td><strong>Abbreviation:</strong></td>
                  <td>" . $team->getAbbreviation() . "</td></tr>
            <tr><td><strong>Division:</strong></td>
                  <td>" . $team->getLeague() . " " . $team->getDivision() . "</td></tr>
          </table>";

    // if admin user, show management links
    if ($isLoggedInAdmin) {
      echo "<div class='managelink'>
              <a class='btn btn-primary' href='admin/manageTeam.php?team_id=" . $team->getId() .
                  "'>Manage team</a>&nbsp
              <a class='btn btn-primary' href='admin/manageContracts.php?team_id=" .
                  $team->getId() . "'>Manage contracts</a>
            </div>";
    }
    echo "</div>"; // span6

    // brognas
    echo "<div class='span7'>";

    // if upcoming year's brognas are negative, show warning.
    foreach ($team->getBrognas() as $brogna) {
      if (($brogna->getYear() == (TimeUtil::getYearByEvent(Event::KEEPER_NIGHT) + 1))
          && ($brogna->getTotalPoints() < 0)) {
        echo "<br/><div class='alert alert-error'>
                <strong>Warning!</strong> Over-budget for the upcoming year!
              </div>";
      }
    }

    // TODO show brognas from this year & next year; allow user to select years
    $team->displayAllBrognas();
    echo "</div>"; // span6

    echo "</div>"; // row-fluid

    // Display active contracts.
    if ($team->hasContracts()) {
      echo "<div class='row-fluid'>
              <div class='span12'>";
      $team->displayAllContracts();
      echo "</div>"; // span12
      echo "</div>"; // row-fluid
    }

    // Display draft pick information
    echo "<div class='row-fluid'>
            <div class='span12'>";

    // if team has too many extra draft picks for upcoming draft, then show warning.
    $upcomingDraftYear = TimeUtil::getYearByEvent(Event::DRAFT) + 1;
    $extraDraftPicks =
        DraftPickDao::getNumberPicksByTeamByRound(
            $upcomingDraftYear, $team->getId(), DraftPick::EXTRA_PICK_ROUND_CUTOFF)
        - DraftPick::EXTRA_PICK_ROUND_CUTOFF;
    $numBalls = BallDao::getNumPingPongBallsByTeamYear($upcomingDraftYear, $team->getId());
    $extraPicks = $extraDraftPicks + $numBalls;
    if ($extraPicks > DraftPick::MAX_EXTRA_PICKS) {
      echo "<br/>
        <div class='alert alert-error'>
          <strong>Warning!</strong> Too many extra draft picks for $upcomingDraftYear: $extraPicks
        </div>";
    }

    // TODO show draft picks from this year; allow user to select year
    $team->displayAllDraftPicks();
    echo "</div>"; // span12
    echo "</div>"; // row-fluid

    // Display current team
    echo "<div class='row-fluid'>
            <div class='span12'>";
    $team->displayPlayers();
    echo "</div>"; // span12
    echo "</div>"; // row-fluid
  }

  /**
   * Display team on manage team page.
   */
  function displayTeamForManagement(Team $team) {
  	echo "<div class='row-fluid'>";

  	// team logo
  	echo "<div class='span2 center teamlogo'>" .
    	    $team->getSportslineImg(72) .
  	     "</div>";

  	// team name
  	echo "  <div class='span10 center teamlogo'>
  	          <h3 class='nexttologo'>Manage: " . $team->getName() . "</h3>
  	        </div>
  	      </div>"; // row-fluid

  	echo "<div class='row-fluid'>
  	        <div class='span12 center'>";

    // ID
    echo "<br/><table class='table vertmiddle table-striped table-condensed table-bordered'>";
    echo "<tr><td><label>Team Id:</label></td><td>" . $team->getId() . "</td></tr>";
    echo "<input type=hidden name='teamId' value='" . $team->getId() . "'>";

    // Name
    echo "<tr><td><label for='teamName'>Name:</label></td><td>
             <input type=text name='teamName' id='teamName' maxLength=50 class='input-xxlarge'
             required placeholder='Team Name' value=\"" . $team->getName() . "\"></td></tr>";

    // League/Division
    echo "<tr><td><label for='league'>Division:</label></td>
              <td><select name='league' id='league' class='input-small' required>";
    $leagues = array("AL", "NL");
    foreach ($leagues as $league) {
      $isSelected = ($league == $team->getLeague());
      echo "<option value='" . $league . "'" . ($isSelected ? " selected" : "") .
             ">" . $league . "</option>";
    }
    echo "</select> <select name='division' class='input-small' required>";
    $divisions = array("East", "West");
    foreach ($divisions as $division) {
      $isSelected = ($division == $team->getDivision());
      echo "<option value='" . $division . "'" . ($isSelected ? " selected" : "") .
               ">" . $division . "</option>";
    }
    echo "</select></td></tr>";

    // Abbreviation
    echo "<tr><td><label for='abbr'>Abbreviation:</label></td><td>
             <input type=text name='abbreviation' id='abbr' maxLength=10 class='input-medium'
             required placeholder='Team Abbreviation' value='" . $team->getAbbreviation() . "'>
          </td></tr>";

    // Sportsline Image Name
    echo "<tr><td><label for='sportslineImage'>Sportsline Image Name:</label></td><td>
             <input type=text name='sportslineImage' id='sportslineImage'" .
           " maxLength=100 class='input-xxlarge' value='" . $team->getSportslineImageName() . "'
             required>
              </td></tr>";

    // Owners
    echo "<tr><td><label>Owner(s):</label></td><td>" . $team->getOwnersString() . "</td></tr>";

    echo "</table>";

    // Buttons
    echo "<p><button class=\"btn btn-primary\" name='update' type=\"submit\">Update Team</button>";
    echo "&nbsp&nbsp" . $team->getIdLink(false, "Return to Team");

    echo "  </div>"; // span12
    echo "</div>";   // row-fluid
  }

  /**
   * Display team on budget page.
   */
  function displayTeamForBudget(Team $team) {
  	echo "<div class='row-fluid'>";

  	// team logo
  	echo "<div class='span2 center teamlogo'>" .
      	    $team->getSportslineImg(72) .
  	     "</div>";

  	// team name w/ bookmarks for each year
  	echo "<div class='span10 center'>
  	        <h3>Budget: " . $team->getName() . "</h3>
  	        <div class='bookmarks'>";
  	$brognas = BrognaDao::getBrognasByTeamId($team->getId());
  	foreach ($brognas as $brogna) {
  	  echo "<a href='#" . $brogna->getYear() . "'>" . $brogna->getYear() . "</a>&nbsp&nbsp";
  	}
    echo "  </div>
  	      </div>";
  	echo "</div>"; // row-fluid

  	// Display brognas and contracts for each specified year.
  	// TODO budget: allow user to adjust years
  	displayBreakdown($team->getId(), $brognas);
  }

  /**
   * Show breakdown of brognas per year w/ contract info for specified team
   */
  function displayBreakdown($teamId, $brognas) {
  	// get all contracts
  	$contracts = ContractDao::getContractsByTeamId($teamId);

  	// for ever brogna record,
  	$currentYear = TimeUtil::getYearByEvent(Event::KEEPER_NIGHT);
  	foreach ($brognas as $brogna) {
  	  if ($brogna->getYear() < $currentYear) {
  	    continue;
  	  }

  	  echo "<div class='row-fluid'>
  	          <div class='span12'>";

  	  // show brogna info
  	  echo "<a id='" . $brogna->getYear() . "'></a><h3>" . $brogna->getYear() . "</h3>";
  	  echo "<h4>Brognas</h4>";
  	  echo "<table border class='table vertmiddle table-striped table-condensed table-bordered center'>
  		      <thead><tr><th>Alotted</th>
  		          <th>" . ($brogna->getYear() - 1) . " Bank</th>
  		          <th>Received in Trade</th>
  		          <th>Given in Trade</th><th>Total</th></tr></thead>";
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
  		  echo "<table border class='table vertmiddle table-striped table-condensed table-bordered center'>
  		          <thead><tr><th colspan='2'>Player</th><th>Position</th><th>Team</th><th>Age</th>
  				      <th>Years Remaining</th><th>Price</th></tr></thead>";
  		  $hasContracts = true;
  	    }
  		$player = $contract->getPlayer();
  		echo "<tr><td>" . $player->getMiniHeadshotImg() . "</td>
  			      <td>" . $player->getNameLink(true) . "</td>
  			      <td>" . $player->getPositionString() . "</td>
  			      <td>" . $player->getMlbTeam()->getImageTag(32) . "</td>
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
  	  echo "<label>Cash Balance for " . ($brogna->getYear() + 1) . ": </label>" .
          ($brogna->getTotalPoints() - $contractTotal) . "<br/><br/>";
      // TODO budget: should bank from previous year be calculated?

  	  echo "</div>"; // span 12
  	  echo "</div>"; // row-fluid
  	}
  }

  function displayTeamForChanges($team) {
    echo "<div class='row-fluid'>
            <div class='span12 center'>";

    if ($team == null) {
      // show all changes
      echo "<h3>All Changes</h3>";
      $changes = ChangelogDao::getAllChanges();
    } else {
      // show changes for team
      echo "<h3>Changes for " . $team->getName() . "</h3>";
      $changes = ChangelogDao::getChangesByTeam($team->getId());
    }

    echo "<table class='table center vertmiddle table-striped table-condensed table-bordered'>
            <thead><tr>
              <th>Date/Time</th><th>User</th><th>Type</th><th colspan=2>Team</th><th>Details</th>
            </tr></thead>";
    $tradeSecond = false;
    for ($i = 0; $i < count($changes); $i++) {
      $change = $changes[$i];
      $tradeFirst = ($change->getType() == Changelog::TRADE_TYPE) &&
          ($i != (count($changes) - 1)) &&
          ($changes[$i + 1]->getType() == Changelog::TRADE_TYPE) &&
          ($change->getChangeId() == $changes[$i + 1]->getChangeId());
      if ($tradeFirst) {
        echo "<tr><td rowspan=2>" . $change->getTimestamp() . "</td>
                  <td rowspan=2>" . $change->getUser()->getFullName() . "</td>
                  <td rowspan=2>" . $change->getType() . "</td>" .
                  TeamManager::getAbbreviationAndLogoRowAtLevel($change->getTeam(), false) .
                 "<td>" . $change->getDetails() . "</td>
              </tr>";
        $tradeSecond = true;
      } else if ($tradeSecond) {
        echo "<tr>" .
                  TeamManager::getAbbreviationAndLogoRowAtLevel($change->getTeam(), false) .
                 "<td>" . $change->getDetails() . "</td>
              </tr>";
        $tradeSecond = false;
      } else {
        echo "<tr><td>" . $change->getTimestamp() . "</td>
                  <td>" . $change->getUser()->getFullName() . "</td>
                  <td>" . $change->getType() . "</td>" .
                  TeamManager::getAbbreviationAndLogoRowAtLevel($change->getTeam(), false) .
                 "<td>" . $change->getDetails() . "</td>
              </tr>";
      }
    }
    echo "</table>
          </div>
          </div>";
  }

  function displayTeamForContracts(Team $team) {
    echo "<div class='row-fluid'>";

    // team logo
    echo "<div class='span2 center teamlogo'>" .
        $team->getSportslineImg(72) .
        "</div>";

    // team name
    echo "  <div class='span10 center teamlogo'>
              <h3 class='nexttologo'>Contracts: " . $team->getName() . "</h3>
            </div>
          </div>"; // row-fluid

    echo "<div class='row-fluid'>
            <div class='span12 center'>";

    // show all existing contracts
    $currentYear = TimeUtil::getYearByEvent(Event::AUCTION);
    $team->displayContractsForManagement($currentYear, Contract::MAX_YEAR);

    // Buttons
    echo "<p><button class=\"btn btn-primary\" name='update'
                     type=\"submit\">Update Contracts</button>";
    // TODO seltzer player
    //echo "&nbsp&nbsp<a href='manageSeltzer.php' class='btn btn-inverse'>
    //      Offer Seltzer Contract</a>";
    echo "&nbsp&nbsp" . $team->getIdLink(false, "Return to Team");

    echo "<input type='hidden' name='contract_teamid' value='" . $team->getId() . "'>";

    echo "  </div>"; // span12
    echo "</div>";   // row-fluid
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
    displayTeam($team, SessionUtil::isLoggedInAdmin());
  } else if ($displayType == "manage") {
    displayTeamForManagement($team);
  } else if ($displayType == "budget") {
    displayTeamForBudget($team);
  } else if ($displayType == "changes") {
    displayTeamForChanges($team);
  } else if ($displayType == "contracts") {
    displayTeamForContracts($team);
  }
?>