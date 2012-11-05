<?php
  require_once '../dao/cumulativeRankDao.php';
  require_once '../dao/playerDao.php';

  /**
   * Returns a Player based on the ID specified in the GET/POST.
   */
  function getPlayerByParam($param) {
  	if (isset($_REQUEST[$param])) {
      $playerId = $_REQUEST[$param];
  	} else {
  	  $playerId = 0;
  	}
    $player = PlayerDao::getPlayerById($playerId);
    if ($player == null) {
      die("<h1>player id " . $playerId . " does not exist for param " . $param . "!</h1>");
    }
    return $player;
  }

  /**
   * Display player information for auction.
   */
  function displayPlayerForAuction(Player $player) {
  	// Display player attributes.
  	echo "<h3>" . $player->getFullName() . "</h3>";

  	// Headshot
  	if ($player->hasSportslineId()) {
  		echo "<a href='" . $player->getStPetesUrl() . "'>
  		<img src='" . $player->getHeadshotUrl() . "'></a><br/><br/>";
  	}

  	// MLB Team
  	echo "<table class='center'>";
  	$mlbTeam = $player->getMlbTeam();
  	echo "<tr><td><strong>Team:</strong></td>
  	<td>" . $mlbTeam->getCity() . " " . $mlbTeam->getName() . "</td></tr>";

  	// Birth date & age
  	echo "<tr><td><strong>Birth Date:</strong></td>
  	<td>" . $player->getBirthDate() . "</td></tr>";
  	echo "<tr><td><strong>Age:</strong></td>
  	<td>" . $player->getAge() . "</td></tr>";

  	// Positions
  	echo "<tr><td><strong>Position(s):</strong></td>
  	<td>" . $player->getPositionString() . "</td></tr>";
  	echo "</table><br/>";

  	echo "<input type='hidden' name='auction_playerid' value='" . $player->getId() . "'>";
  }

  /**
   * Displays the cumulative rank for the specified player for the current ranking year.
   */
  function displayCumulativeRankForPlayer(Player $player) {
  	$rankYear = TimeUtil::getYearBasedOnEndOfSeason();
  	$rank = CumulativeRankDao::getCumulativeRankByPlayerYear($player->getId(), $rankYear);
  	if ($rank != null) {
  	  echo $rank->getRank();
  	} else {
      echo "error";
  	}
  }
  
  if (isset($_REQUEST["type"])) {
  	$displayType = $_REQUEST["type"];
  } else {
  	die("<h1>Invalid display type for player</h1>");
  }
  $player = getPlayerByParam("player_id");

  if ($displayType == "auction") {
  	displayPlayerForAuction($player);
  } else if ($displayType == "cumulativerank") {
  	displayCumulativeRankForPlayer($player);
  }
?>