<?php
  require_once '../dao/auctionDao.php';
  require_once '../dao/ballDao.php';
  require_once '../dao/draftPickDao.php';
  require_once '../util/sessions.php';
  require_once '../util/time.php';
  
  /**
   * Displays draft results from specified year.
   */
  function displayDraftYear($year) {
  	echo "<h1>Draft " . $year . "</h1><hr/>";
  	
  	// display table of draft picks for selected year, highlighting row for logged-in team
  	$loggedInTeamId = SessionUtil::getLoggedInTeam()->getId();
  	echo "<br/><br/><table border class='center'>
  	        <th>Round</th><th>Pick</th><th>Team</th><th>Player</th></tr>";
  	
  	$pingPongBalls = BallDao::getPingPongBallsByYear($year);
  	foreach ($pingPongBalls as $pingPongBall) {
  	  echo "<tr";
  	  if ($pingPongBall->getTeam()->getId() == $loggedInTeamId) {
  		echo " class='selected_team_row'";
  	  }
  	  echo "><td>Ping Pong</td>
  	         <td>" . $pingPongBall->getCost() . "</td>
  		     <td>" . $pingPongBall->getTeam()->getNameLink(true) . "</td>
  		     <td>" . displayPlayerLink($pingPongBall->getPlayer()) . "</td></tr>";
  	  }
  	
  	  $draftPicks = DraftPickDao::getDraftPicksByYear($year);
  	  foreach ($draftPicks as $draftPick) {
  		echo "<tr";
  		if ($draftPick->getTeam()->getId() == $loggedInTeamId) {
  		  echo " class='selected_team_row'";
  	    }
  	    echo "><td>" . $draftPick->getRound() . "</td>
  		       <td>" . $draftPick->getPick() . "</td>
  	    	   <td>" . $draftPick->getTeam()->getNameLink(true) . "</td>
  		       <td>" . displayPlayerLink($draftPick->getPlayer()) . "</td></tr>";
  	  }
    echo "</table>";
  }
  
  function displayPlayerLink($player) {
  	if ($player != null) {
  	  return $player->getNameLink(true);
  	} else {
  	  return "--";
  	}
  }

  /**
   * Display list of auctioned players for specified year
   */
  function displayAuctionYear($year) {
 	echo "<h1>Auction " . $year . "</h1><hr/><br/><br/>";
    
  	// display table of auction results for specified year, highlighting row for specified team
  	$loggedinTeamId = SessionUtil::getLoggedInTeam()->getId();
  	echo "<table border class='center'>
  	        <th>Team</th><th>Player</th><th>Cost</th></tr>";
  	$auctionResults = AuctionResultDao::getAuctionResultsByYear($year);
  	foreach ($auctionResults as $auctionResult) {
  	  $team = $auctionResult->getTeam();
  	  $player = $auctionResult->getPlayer();
  	  echo "<tr";
  	  if ($team->getId() == $loggedinTeamId) {
  	    echo " class='selected_team_row'";
  	  }
      echo "><td>" . $team->getNameLink(true) . "</td>
  	         <td>" . $player->getNameLink(true) . "</td>
  	         <td>" . $auctionResult->getCost() . "</td></tr>";
  	}
  	echo "</table>";
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
  }
?>