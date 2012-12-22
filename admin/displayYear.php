<?php
  require_once '../dao/auctionDao.php';
  require_once '../dao/ballDao.php';
  require_once '../dao/brognaDao.php';
  require_once '../dao/draftPickDao.php';
  require_once '../util/playerManager.php';
  require_once '../util/teamManager.php';
  require_once '../util/sessions.php';
  require_once '../util/time.php';

  /**
   * Displays draft results from specified year.
   */
  function displayDraftYear($year) {
  	echo "<div class='row-fluid'>
  	        <div class='span6 offset3 center'>
  	          <h3>Draft Results " . $year . "</h3>";
  	echo "  </div>
  	      </div>
  	      <div class='row-fluid'>
  	        <div class='span12 center'><br/>";
  	
  	// TODO allow filtering by round
  	
  	// display table of draft picks for selected year, highlighting row for logged-in team
  	$loggedInTeamId = SessionUtil::getLoggedInTeam()->getId();
  	echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
  	        <thead><tr>
  	          <th>Round</th><th>Pick</th><th colspan=2>Team</th><th colspan=2>Player</th>
  	        </tr></thead>";
  	
  	$pingPongBalls = BallDao::getPingPongBallsByYear($year);
  	foreach ($pingPongBalls as $pingPongBall) {
  	  echo "<tr";
  	  if ($pingPongBall->getTeam()->getId() == $loggedInTeamId) {
  		echo " class='selected_team_row'";
  	  }
  	  echo "><td>Ping Pong</td>
  	         <td>" . $pingPongBall->getCost() . "</td>" .
  	         TeamManager::getNameAndLogoRow($pingPongBall->getTeam()) .
  		     PlayerManager::getNameAndHeadshotRow($pingPongBall->getPlayer()) . "</tr>";
  	  }
  	
  	  $draftPicks = DraftPickDao::getDraftPicksByYear($year);
  	  foreach ($draftPicks as $draftPick) {
  		echo "<tr";
  		if ($draftPick->getTeam()->getId() == $loggedInTeamId) {
  		  echo " class='selected_team_row'";
  	    }
  	    echo "><td>" . $draftPick->getRound() . "</td>
  		       <td>" . $draftPick->getPick() . "</td>" .
  		       TeamManager::getNameAndLogoRow($draftPick->getTeam()) .
  		       PlayerManager::getNameAndHeadshotRow($draftPick->getPlayer()) . "</tr>";
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

  function displayDraftYearForManagement($year, $round) {
  	if ($round == 0) {
  	  echo "<h4>$year Draft - Ping Pong Round</h4>";
  	} else {
      echo "<h4>$year Draft - Round $round</h4>";
  	}
  	// allow user to select players who have not yet been drafted during the specified year.
  	$undraftedPlayers = PlayerDao::getUndraftedPlayers($year);
  	 
  	// display table of draft picks for selected year, highlighting row for logged-in team
  	$loggedInTeamId = SessionUtil::getLoggedInTeam()->getId();
  	echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
  	      <thead><tr><th>Pick</th><th>Team</th><th>Player</th></tr></thead>";

  	if ($round == 0) {
      $pingPongBalls = BallDao::getPingPongBallsByYear($year);
  	  foreach ($pingPongBalls as $pingPongBall) {
  	    echo "<tr class='tdselect'>
  		       <td>" . $pingPongBall->getCost() . "</td>
  		       <td>" . $pingPongBall->getTeam()->getNameLink(false) . "</td>
  		       <td>" . getPlayerDropdown(
  		           $pingPongBall->getPlayer(), $pingPongBall->getId(), $undraftedPlayers) . "</td>
  	          </tr>";
  	  }
  	} else {
      $draftPicks = DraftPickDao::getDraftPicksByYearRound($year, $round);
  	  foreach ($draftPicks as $draftPick) {
  		echo "<tr class='tdselect'>
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
  function displayAuctionYear($year) {
  	echo "<div class='row-fluid'>
  	        <div class='span6 offset3 center'>
  	          <h3>Auction Results " . $year . "</h3>";
  	echo "  </div>
  	      </div>
  	      <div class='row-fluid'>
  	        <div class='span12 center'><br/>";

  	// display table of auction results for specified year, highlighting row for specified team
  	$loggedinTeamId = SessionUtil::getLoggedInTeam()->getId();
  	echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
  	        <thead><tr>
  	          <th colspan=2>Player</th><th colspan=2>Team</th><th>Cost</th>
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
  	             TeamManager::getNameAndLogoRow($team) .
                 "<td>" . $auctionResult->getCost() . "</td></tr>";
  	}
  	echo "</table>";
  	echo "</div>"; // span12
  	echo "</div>"; // row-fluid
  }
  
  /**
   * Displays the brogna breakdown for all teams in the specified year.
   */
  function displayBrognaYear($year) {
  	echo "<div class='row-fluid'>
   	        <div class='span6 offset3 center'>
  	          <h3>Manage $year Brognas</h3>";
  	echo "  </div>
  	      </div>
  	      <div class='row-fluid'>
  	        <div class='span12 center'><br/>";
    echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
            <thead><tr><th colspan=2>Team</th><th>Total</th><th>Banked</th><th>Traded In</th>
  		        <th>Traded Out</th><th>Tradeable</th></tr></thead>";

    $brognas = BrognaDao::getBrognasByYear($year, $year);
    foreach ($brognas as $brogna) {
      echo "<tr><td>" . $brogna->getTeam()->getSportslineImg(36,36) . "</td>
                <td>" . $brogna->getTeam()->getNameLink(false) . "</td>
                <td><strong>" . $brogna->getTotalPoints() . "</strong></td>
       	        <td>" . $brogna->getBankedPoints() . "</td>
           	    <td>" . $brogna->getTradedInPoints() . "</td>
               	<td>" . $brogna->getTradedOutPoints() . "</td>
               	<td>" . $brogna->getTradeablePoints() . "</td>
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

  if ($displayType == "draft") {
    displayDraftYear($year);
  } else if ($displayType == "auction") {
  	displayAuctionYear($year);
  } else if ($displayType == "brognas") {
  	displayBrognaYear($year);
  } else if ($displayType == "managedraft") {
  	if (isset($_REQUEST["round"])) {
  	  $round = $_REQUEST["round"];
  	}
  	displayDraftYearForManagement($year, $round);
  } else if ($displayType == "draftround") {
  	if (isset($_REQUEST["round"])) {
  	  $round = $_REQUEST["round"];
  	}
  	displayRoundDropdown($year, $round);
  }
?>