<?php
  require_once '../dao/auctionDao.php';
  require_once '../dao/ballDao.php';
  require_once '../dao/brognaDao.php';
  require_once '../dao/contractDao.php';
  require_once '../dao/draftPickDao.php';
  require_once '../dao/playerDao.php';
  require_once '../util/playerManager.php';
  require_once '../util/teamManager.php';
  require_once '../util/sessions.php';
  require_once '../util/time.php';

  /**
   * Displays draft results from specified year.
   */
  function displayDraftYear($year, $loggedInTeamId) {
  	echo "<div class='row-fluid'>
  	        <div class='span12 center'>
  	          <h3>Draft Results " . $year . "</h3>";
  	echo "  </div>
  	      </div>
  	      <div class='row-fluid'>
  	        <div class='span12 center'><br/>";

  	// TODO allow filtering by round

  	// display table of draft picks for selected year, highlighting row for logged-in team
  	echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
  	        <thead><tr>
  	          <th>Overall</th><th>Round</th><th>Pick</th><th colspan=2>Team</th>
  	          <th colspan=2>Player</th>
  	          <th colspan=2>Original Team</th>
  	        </tr></thead>";

  	$pingPongBalls = BallDao::getPingPongBallsByYear($year);
  	foreach ($pingPongBalls as $pingPongBall) {
  	  echo "<tr";
  	  if ($pingPongBall->getTeam()->getId() == $loggedInTeamId) {
  		echo " class='selected_team_row'";
  	  }
  	  echo "><td>" . $pingPongBall->getOrdinal() . "</td>
  	         <td>Ping Pong</td>
  	         <td>" . $pingPongBall->getCost() . "</td>" .
  	         TeamManager::getNameAndLogoRow($pingPongBall->getTeam()) .
  		     PlayerManager::getNameAndHeadshotRow($pingPongBall->getPlayer()) .
  		     "<td colspan=2>--</td>" .
  	    "</tr>";
  	}

  	$draftPicks = DraftPickDao::getDraftPicksByYear($year);
  	foreach ($draftPicks as $draftPick) {
  	  echo "<tr";
  	  if ($draftPick->isSeltzerCutoff()) {
  	    echo " class='warning'";
  	  } else if ($draftPick->getTeam()->getId() == $loggedInTeamId) {
  	    echo " class='selected_team_row'";
  	  }
  	  echo "><td>" . $draftPick->getOverallPick(count($pingPongBalls)) . "</td>
  	         <td>" . $draftPick->getRound() . "</td>
  		     <td>" . $draftPick->getPick() . "</td>" .
  		     TeamManager::getNameAndLogoRow($draftPick->getTeam()) .
  		     PlayerManager::getNameAndHeadshotRow($draftPick->getPlayer()) .
             TeamManager::getAbbreviationAndLogoRow($draftPick->getOriginalTeam()) .
  	      "</tr>";
  	}
    echo "</table>";
    echo "</div>"; // span12
    echo "</div>"; // row-fluid
  }

  /**
   * Returns a dropdown of all of the undrafted players with the specified player selected.
   */
  function getPlayerDropdown($selectedPlayer, $pickId, $undraftedPlayers) {
  	$playerDropdown = "<select name='player" . $pickId . "' class='input-xlarge'>
  	    <option value='0'";
  	if ($selectedPlayer == null) {
  	  $playerDropdown .= " selected";
  	}
  	$playerDropdown .= "></option>";

  	if ($selectedPlayer != null) {
  	  $playerDropdown .= "<option value='" . $selectedPlayer->getId() . "' selected>" .
  	      $selectedPlayer->getAttributes() . "</option>";
  	}
  	foreach ($undraftedPlayers as $player) {
  	  $playerDropdown .= "<option value='" . $player->getId() . "'";
  	  if ($selectedPlayer != null && $selectedPlayer->getId() == $player->getId()) {
  	  	$playerDropdown .= " selected";
  	  }
  	  $playerDropdown .= ">" . $player->getAttributes() . "</option>";
  	}
  	$playerDropdown .= "</select>";
  	return $playerDropdown;
  }

  /**
   * Returns a dropdown of all of the picks with the specified pick selected.
   */
  function getPickDropdown($selectedPick, $pickId, $numPicks) {
  	$pickDropdown = "<select class='input-mini' name='pick" . $pickId . "'>
  	                   <option value='0'";
  	if ($selectedPick == null) {
  	  $pickDropdown .= " selected";
  	}
  	$pickDropdown .= "></option>";
  	for ($pk = 1; $pk <= $numPicks; $pk++) {
  	  $pickDropdown .= "<option value='" . $pk . "'";
  	  if ($selectedPick != null && $selectedPick == $pk) {
  	    $pickDropdown .= " selected";
  	  }
  	  $pickDropdown .= ">" . $pk . "</option>";
  	}
  	$pickDropdown .= "</select>";
  	return $pickDropdown;
  }

  function displayDraftYearForManagement($year, $round, $loggedInTeamId) {
  	if ($round == 0) {
  	  echo "<h4>$year Draft - Ping Pong Round</h4>";
  	} else {
      echo "<h4>$year Draft - Round $round</h4>";
  	}
  	// allow user to select players who are not currently assigned to a team.
  	$undraftedPlayers = PlayerDao::getUnassignedPlayers();

  	// display table of draft picks for selected year, highlighting row for logged-in team
  	echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
  	      <thead><tr><th>Overall</th><th>Pick</th><th>Team</th><th>Player</th></tr></thead>";

  	if ($round == 0) {
      $pingPongBalls = BallDao::getPingPongBallsByYear($year);
  	  foreach ($pingPongBalls as $pingPongBall) {
  	    echo "<tr class='tdselect'>
  	           <td><input type='number' class='input-mini center'
  	                      name='ppOrd" . $pingPongBall->getId() . "'
  	                      value='" . $pingPongBall->getOrdinal() . "'
  	                      min='0' max='" . count($pingPongBalls) . "'></td>
  		       <td>" . $pingPongBall->getCost() . "</td>
  		       <td>" . $pingPongBall->getTeam()->getNameLink(false) . "</td>
  		       <td>" . getPlayerDropdown(
  		           $pingPongBall->getPlayer(), $pingPongBall->getId(), $undraftedPlayers) . "</td>
  	          </tr>";
  	  }
  	} else {
      $draftPicks = DraftPickDao::getDraftPicksByYearRound($year, $round);
      $numBalls = BallDao::getNumPingPongBallsByYear($year);
  	  foreach ($draftPicks as $draftPick) {
  		echo "<tr class='tdselect";
        if ($draftPick->isSeltzerCutoff()) {
          echo " warning";
        }
  		echo "'>
  		       <td>" . $draftPick->getOverallPick($numBalls) . "
  		       <td>" . getPickDropdown(
  		           $draftPick->getPick(), $draftPick->getId(), count($draftPicks)) . "</td>
  		       <td>" . $draftPick->getTeam()->getNameLink(false) . "</td>
  		       <td>" . getPlayerDropdown(
  		           $draftPick->getPlayer(), $draftPick->getId(), $undraftedPlayers) . "</td></tr>";
  	  }
  	}
  	echo "</table>";
  	echo "<input type=hidden name='year' value='$year'>
  	      <input type=hidden name='round' value='$round'>";
  }

  /**
   * Display list of auctioned players for specified year
   */
  function displayAuctionYear($year, $loggedinTeamId) {
  	echo "<div class='row-fluid'>
  	        <div class='span12 center'>
  	          <h3>Auction Results " . $year . "</h3>";
  	echo "  </div>
  	      </div>
  	      <div class='row-fluid'>";

  	// display table of players eligible to be auctioned
  	echo "  <div class='span6 center'>
  	          <h4>To Be Auctioned</h4>";
  	echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
  	        <thead><tr>
  	          <th colspan=4>Player</th><th colspan=2>Fantasy Team</th>
  	        </tr></thead>";
  	$players = PlayerDao::getPlayersForAuction($year);
    foreach ($players as $player) {
      $team = $player->getFantasyTeam();
      echo "<tr";
  	  if ($team != null && $team->getId() == $loggedinTeamId) {
  	    echo " class='selected_team_row'";
  	  }
      echo   ">" . PlayerManager::getNameAndHeadshotRow($player) .
                   "<td>" . $player->getMlbTeam()->getImageTag(32) . "</td>
                   <td>" . $player->getPositionString() . "</td>" .
                   TeamManager::getAbbreviationAndLogoRow($team) .
             "</tr>";
    }
  	echo "    </table>
  	        </div>";

  	// display table of auction results for specified year, highlighting row for specified team
  	echo "  <div class='span6 center'>
  	          <h4>The Auction</h4>";
  	echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
  	        <thead><tr>
  	          <th colspan=2>Player</th><th colspan=2>Fantasy Team</th><th>Cost</th>
  	        </tr></thead>";
  	$auctionResults = AuctionResultDao::getAuctionResultsByYear($year);
  	foreach ($auctionResults as $auctionResult) {
  	  $team = $auctionResult->getTeam();
  	  $player = $auctionResult->getPlayer();
  	  echo "<tr";
  	  if ($team->getId() == $loggedinTeamId) {
  	    echo " class='selected_team_row'";
  	  }
      echo ">" . PlayerManager::getNameAndHeadshotRow($player) .
                 TeamManager::getAbbreviationAndLogoRow($team) .
                "<td>" . $auctionResult->getCost() . "</td></tr>";
  	}
  	echo "</table>";
  	echo "</div>"; // span6
  	echo "</div>"; // row-fluid
  }

  /**
   * Displays the brogna breakdown for all teams in the specified year.
   */
  function displayBrognaYear($year, $isAdmin) {
  	echo "<div class='row-fluid'>
   	        <div class='span6 offset3 center'>
  	          <h3>" . ($isAdmin ? "Manage " : "") . "$year Team Budgets</h3>";
  	echo "  </div>
  	      </div>
  	      <div class='row-fluid'>
  	        <div class='span12 center'><br/>";
    echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
            <thead><tr><th rowspan=2 colspan=2>Team</th>
                       <th colspan=7>Brognas</th><th colspan=3>Draft Picks</th></tr>
                   <tr><th>Total</th><th>Contracts</th><th>Available</th><th>Banked</th>
                   <th>Traded In</th><th>Traded Out</th><th>Tradeable</th>
                   <th><abbr title='In first ". DraftPick::EXTRA_PICK_ROUND_CUTOFF ." rounds'>
                       Extra Draft Picks</abbr></th>
                   <th>Ping Pong Balls</th>
                   <th><abbr title='Cannot be more than 3'>Extra Picks</abbr></th>
            </tr></thead>";

    $brognas = BrognaDao::getBrognasByYear($year, $year);
    foreach ($brognas as $brogna) {
      // display number of extra draft picks before the extra pick cutoff, and ping pong balls; show
      // warning if team has more than 3 extra picks.
      $extraDraftPicks = DraftPickDao::getNumberPicksByTeamByRound(
              $brogna->getYear(), $brogna->getTeam()->getId(), DraftPick::EXTRA_PICK_ROUND_CUTOFF)
          - DraftPick::EXTRA_PICK_ROUND_CUTOFF;
      $numBalls = BallDao::getNumPingPongBallsByTeamYear(
          $brogna->getYear(), $brogna->getTeam()->getId());
      $extraPicks = ($extraDraftPicks + $numBalls);

      echo "<tr>" .
                TeamManager::getAbbreviationAndLogoRowAtLevel($brogna->getTeam(), true) .
                "<td";
      if ($brogna->getTotalPoints() < 0) {
        echo " class ='warning'";
      }
      $contractBrognas =
          ContractDao::getTotalPriceByTeamYear($brogna->getTeam()->getId(), $brogna->getYear());
      if (!$contractBrognas) {
        $contractBrognas = 0;
      }
      echo          "><strong>" . $brogna->getTotalPoints() . "</strong></td>
                <td>" . $contractBrognas . "</td>
                <td class='conf_msg'><strong>" . ($brogna->getTotalPoints() - $contractBrognas) .
                    "</strong></td>
       	        <td>" . $brogna->getBankedPoints() . "</td>
           	    <td>" . $brogna->getTradedInPoints() . "</td>
               	<td>" . $brogna->getTradedOutPoints() . "</td>
               	<td>" . $brogna->getTradeablePoints() . "</td>
               	<td>" . $extraDraftPicks . "</td>
               	<td>" . $numBalls . "</td>
               	<td";
      if ($extraPicks > DraftPick::MAX_EXTRA_PICKS) {
        echo " class='warning'";
      }
      echo        ">" . $extraPicks . "</td>
      </tr>";
    }
    echo "</table>";
    echo "</div>"; // span12
    echo "</div>"; // row-fluid
  }

  function displayRoundDropdown($year, $selectedRound) {
  	$minRound = DraftPickDao::getMinimumRound($year);
  	$maxRound = DraftPickDao::getMaximumRound($year);
  	echo "<option value='0'";
  	if (($selectedRound == 0) || ($selectedRound > $maxRound)) {
  	  echo " selected";
  	}
  	echo ">PP</option>";
  	for ($rd = $minRound; $rd <= $maxRound; $rd++) {
  	  echo "<option value='" . $rd . "'";
  	  if ($rd == $selectedRound) {
  	    echo " selected";
  	  }
  	  echo ">$rd</option>";
  	}
  }

  /**
   * Displays a table of events for the specified year, providing the ability to change the date.
   */
  function displayEventYear($year) {
  	echo "<h3>$year Events</h3>";

  	echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
  	      <thead><tr><th>Event Type</th><th>Event Date</th></tr></thead>";

  	$events = EventDao::getEventsByYear($year);
  	foreach ($events as $event) {
  	  echo "<tr class='tdselect'>
  	          <td>" . $event->getEventTypeName() . "</td>
  	          <td><input type=date name='eventDate" . $event->getEventType() .
  	              "' required value='" . $event->getEventDate() . "'></td>
    	    </tr>";
  	}
  	echo "</table>";
  	echo "<input type=hidden name='year' value='$year'>";
  }

  // direct to corresponding function, depending on type of display
  if (isset($_REQUEST["type"])) {
  	$displayType = $_REQUEST["type"];
  } else {
  	die("<h1>Invalid display type for year</h1>");
  }
  if (isset($_REQUEST["year"])) {
  	$year = $_REQUEST["year"];
  } else {
  	die("<h1>Invalid year param for year</h1>");
  }

  $loggedInTeamId = SessionUtil::getLoggedInTeam()->getId();
  if ($displayType == "draft") {
    displayDraftYear($year, $loggedInTeamId);
  } else if ($displayType == "auction") {
  	displayAuctionYear($year, $loggedInTeamId);
  } else if ($displayType == "brognas") {
    if (isset($_REQUEST["admin"])) {
  	  $isAdmin = true;
  	} else {
  	  $isAdmin = false;
  	}
    displayBrognaYear($year, $isAdmin);
  } else if ($displayType == "managedraft") {
  	if (isset($_REQUEST["round"])) {
  	  $round = $_REQUEST["round"];
  	}
  	displayDraftYearForManagement($year, $round, $loggedInTeamId);
  } else if ($displayType == "draftround") {
  	if (isset($_REQUEST["round"])) {
  	  $round = $_REQUEST["round"];
  	}
  	displayRoundDropdown($year, $round);
  } else if ($displayType == "events") {
  	displayEventYear($year);
  }
?>