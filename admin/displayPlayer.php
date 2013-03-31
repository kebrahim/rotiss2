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
    return PlayerDao::getPlayerById($playerId);
  }

  /**
   * Display player information for auction.
   */
  function displayPlayerForAuction(Player $player) {
  	// Display player attributes.
  	echo "<hr class='bothr'/>";
    $player->displayPlayerInfo();
  	echo "<hr/>";

  	// MLB Team
  	echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>";
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
  	echo "</table>";

  	echo "<input type='hidden' name='auction_playerid' value='" . $player->getId() . "'>";
  }

  /**
   * Displays the cumulative rank for the specified player for the current ranking year, and the
   * type of contract. If either player or contract type are not selected, then an empty ranking
   * is returned. If the contract type is a minor league contract, then the cumulative rank/price
   * is always zero.
   */
  function displayCumulativeRankForPlayer($player, $contractType) {
    if (($player == null) || ($contractType == -1)) {
      echo "";
    } else if ($contractType == 0) {
      echo "0";
    } else {
      $rankYear = TimeUtil::getYearByEvent(Event::OFFSEASON_START);
  	  $rank = CumulativeRankDao::getCumulativeRankByPlayerYear($player->getId(), $rankYear);
  	  if ($rank != null) {
  	    echo $rank->getRank();
  	  } else {
        echo "error";
  	  }
    }
  }

  /**
   * Displays an IMG link with the headshot of the specified player.
   */
  function displayHeadShotForPlayer($player) {
    if ($player == null) {
      echo "";
    } else {
      echo $player->getMiniHeadshotImg();
    }
  }

  function displayPlayerForSeltzer(Player $player) {
    // Display player attributes.
    echo "<hr class='bothr'/>";
    $player->displayPlayerInfo();
    echo "<hr/>";

    // MLB Team
    echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>";
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

    // Other site links
    echo "<tr><td><strong>Other Sites:</strong></td>
              <td><a href='" . $player->getStPetesUrl() . "' target='_blank'>CBS</a>&nbsp&nbsp
                  <a href='" . $player->getBaseballReferenceUrl() . "' target='_blank'>BBRef</a>
              </td></tr>";

    echo "</table>";
    echo "<input type='hidden' name='seltzer_playerid' value='" . $player->getId() . "'>";
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
    if (isset($_REQUEST["contracttype"])) {
      $contractType = $_REQUEST["contracttype"];
    }
  	displayCumulativeRankForPlayer($player, $contractType);
  } else if ($displayType == "headshot") {
  	displayHeadShotForPlayer($player);
  } else if ($displayType == "seltzer") {
    displayPlayerForSeltzer($player);
  }
?>