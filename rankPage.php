<?php session_start(); ?>
<html>
<head>
<title>Rotiss.com - My Ranks</title>
<link href='css/style.css' rel='stylesheet' type='text/css'>
</head>

<body>
<?php
  require_once 'dao/rankDao.php';
  require_once 'dao/statDao.php';
  require_once 'util/navigation.php';
  require_once 'util/time.php';

  /**
   * Creates a select tag for the ranking of the specified player, showing the specified rank as
   * selected.
   */
  function displaySelectForPlayer(Player $player, $selectedRank) {
  	echo "<select name='pk" . $player->getId() . "' size=1>";
  	for ($i=0; $i<=10; $i++) {
  	  echo "<option";
  	  if ($i == $selectedRank) {
  		echo " selected";
  	  }
  	  echo ">" . $i . "</option>";
  	}
  	echo "</select>";
  }

  /**
   * Displays a table of ranked players for the specified team, during the specified year, for the
   * specified rank value.
   */
  function displayPlayersByRank($teamId, $rankYear, $rank) {
  	$lastYear = $rankYear - 1;
  	$ranks = RankDao::getRanksByTeamYearRank($teamId, $rankYear, $rank);
  	echo "<br/><strong>" . $rank . "'s</strong> ";
  	echo "<meter min='0' max='15' low='15' optimum='15' value='" . count($ranks) . "'></meter>
  	      (" . count($ranks) . "/15)
  	      <br/><br/>";
  	if (count($ranks) > 15) {
  	  echo "<div class='error_msg'>Too many " . $rank . "s!</div><br/>";
  	}
  	echo "<table border class='center ranktable'>";
  	echo "<tr><th>Player</th><th>Pos</th><th>Team</th><th>FPTS</th><th>Rank</th></tr>";

  	foreach ($ranks as $rank) {
      // if rank is a placeholder, update style
  	  echo "<tr";
  	  if ($rank->isPlaceholder()) {
  	  	echo " class='placeholder_row'";
  	  }
  	  $fantasyPts = ($rank->getPlayer()->getStatLine($lastYear) != null) ?
          $rank->getPlayer()->getStatLine($lastYear)->getFantasyPoints() : "--";
  	  echo "><td><a href='displayPlayer.php?player_id=" . $rank->getPlayerId() . "'>" .
  	             $rank->getPlayer()->getFullName() . "</a></td>
  	         <td>" . $rank->getPlayer()->getPositionString() . "</td>
  	         <td>" . $rank->getPlayer()->getMlbTeam()->getAbbreviation() . "</td>
  	         <td>" . $fantasyPts . "</td>";
  	  echo "<td>";
  	  if ($rank->isPlaceholder()) {
  	  	// placeholder; show read-only value
  	  	echo $rank->getRank();
  	  } else {
  	  	// not a placeholder; show drop-down for selecting rank
  	  	displaySelectForPlayer($rank->getPlayer(), $rank->getRank());
  	  }
  	  echo "</td></tr>";
  	}
  	echo "</table><br/>";
  }

  // Display header.
  NavigationUtil::printNoWidthHeader(true, true, NavigationUtil::RANKING_BUTTON);

  // Get selected team from logged-in user.
  $teamId = SessionUtil::getLoggedInTeam()->getId();

  echo "<div class='bodycenter'><h1>My Ranks</h1>";
  echo "<FORM ACTION='rankPage.php' METHOD=POST>";
  $rankYear = TimeUtil::getYearBasedOnEndOfSeason();
  $lastYear = $rankYear - 1;

  if (isset($_POST['save'])) {
    // for every unranked player, check if a player was ranked.
  	$unrankedPlayers = PlayerDao::getPlayersForRanking($teamId, $lastYear);
    foreach ($unrankedPlayers as $unrankedPlayer) {
      $rankSelection = 'pk' . $unrankedPlayer->getId();
      // if non-zero value was provided, create a new ranking
      if (isset($_POST[$rankSelection]) && intval($_POST[$rankSelection]) > 0) {
      	$newRank = new Rank(-1, $rankYear, $teamId, $unrankedPlayer->getId(),
            intval($_POST[$rankSelection]), false);
      	RankDao::createRank($newRank);
      }
    }

    // for every ranked player, check if ranking has changed.
    $ranks = RankDao::getRanksByTeamYear($teamId, $rankYear);
    foreach ($ranks as $rank) {
      $rankSelection = 'pk' . $rank->getPlayerId();
      if (isset($_POST[$rankSelection]) && (intval($_POST[$rankSelection]) != $rank->getRank())) {
      	$newRank = intval($_POST[$rankSelection]);
      	if ($newRank == 0) {
      	  // rank is changed to 0; delete rank.
      	  RankDao::deleteRank($rank);
      	} else {
      	  // update rank
      	  $rank->setRank($newRank);
      	  RankDao::updateRank($rank);
      	}
      }
    }
  }

  // navigation links
  echo "<a href='rankPage.php'>My Ranks</a>&nbsp
        <a href='allRanksPage.php'>All Ranks</a>";

  // display ranked players
  echo "<h3>Ranked Players</h3>";
  $numRanks = RankDao::getTotalRankCount($teamId, $rankYear);
  echo "<strong>Total Ranks:</strong>
        <meter min='0' max='150' low='150' optimum='150' value='" . $numRanks . "'></meter>
        (" . $numRanks . "/150)<br/><br/>";

  echo "<table border id='ranked' class='center'>";
  $count = 10;
  for ($i=0; $i<2; $i++) {
  	echo "<tr>";
  	for ($j=0; $j<5; $j++) {
      echo "<td class='vert_td_top'>";
      displayPlayersByRank($teamId, $rankYear, $count--);
      echo "</td>";
  	}
  	echo "</tr>";
  }
  echo "</table><br/><br/>";

  echo "<input type='submit' name='save' value='Save my changes'>&nbsp";
  echo "<input type='submit' name='cancel' value='Reset'>";
  echo "<input type='hidden' name='team_id' value='" . $teamId . "'>";

  // display unranked players
  echo "<h3>Unranked Players</h3>";
  $rankablePlayers = PlayerDao::getPlayersForRanking($teamId, $lastYear);
  echo "<table border id='unranked' class='center ranktable'>
          <tr><th>Player</th><th>Pos</th><th>Team</th><th>FPTS</th><th>Rank</th></tr>";
  foreach ($rankablePlayers as $player) {
  	$fantasyPts = ($player->getStatLine($lastYear) != null) ?
  	    $player->getStatLine($lastYear)->getFantasyPoints() : "--";
  	echo "<tr><td><a href='displayPlayer.php?player_id=" . $player->getId() . "'>" .
  	              $player->getFullName() . "</a></td>
  	          <td>" . $player->getPositionString() . "</td>
  	          <td>" . $player->getMlbTeam()->getAbbreviation() . "</td>
  	          <td>" . $fantasyPts . "</td><td>";
  	displaySelectForPlayer($player, 0);
  	echo "</td></tr>";
  }
  echo "</table>";

  echo "</FORM></div>";

  // Footer
  NavigationUtil::printFooter();
?>
</body>
</html>