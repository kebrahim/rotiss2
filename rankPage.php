<?php
  require_once 'util/sessions.php';
  SessionUtil::logoutUserIfNotLoggedIn("rankPage.php");
?>

<!DOCTYPE html>
<html>
<head>
<title>St Pete's Rotiss - My Ranks</title>
<link href='css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='css/stpetes.css' rel='stylesheet' type='text/css'>
<link rel="shortcut icon" href="img/background-tiles-01.png" />
</head>

<body>
<?php
  require_once 'dao/rankDao.php';
  require_once 'dao/statDao.php';
  require_once 'util/layout.php';
  require_once 'util/time.php';

  /**
   * Creates a select tag for the ranking of the specified player, showing the specified rank as
   * selected.
   */
  function displaySelectForPlayer(Player $player, $selectedRank) {
  	echo "<select class='input-micro' name='pk" . $player->getId() . "' size=1>";
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
  function displayPlayersByRank($teamId, $rankYear, $rank, $isReadOnly) {
  	$lastYear = $rankYear - 1;
  	$ranks = RankDao::getRanksByTeamYearRank($teamId, $rankYear, $rank);
  	echo "<h5>" . $rank . "'s</h5>";
  	if (count($ranks) > 15) {
  	  echo "<div class=\"alert alert-error\">Too many " . $rank . "s!</div>";
  	}
  	echo "<table class='table vertmiddle table-striped table-condensed table-bordered center
  	                    smallfonttable'>";
  	echo "<thead><tr><th>Player</th><th>FPTS</th><th>Rank</th></tr></thead>";

  	foreach ($ranks as $rank) {
      // if rank is a placeholder, update style
  	  echo "<tr";
  	  if ($rank->isPlaceholder()) {
  	  	echo " class='placeholder_row'";
  	  }
  	  $fantasyPts = ($rank->getPlayer()->getStatLine($lastYear) != null) ?
          $rank->getPlayer()->getStatLine($lastYear)->getFantasyPoints() : "--";
  	  echo "><td>" . $rank->getPlayer()->getIdNewTabLink(true, $rank->getPlayer()->getFullName()) .
  	        "</td>
  	         <td>" . $fantasyPts . "</td>";
  	  echo "<td>";
  	  if ($rank->isPlaceholder() || $isReadOnly) {
  	  	// placeholder; show read-only value
  	  	echo $rank->getRank();
  	  } else {
  	  	// not a placeholder; show drop-down for selecting rank
  	  	displaySelectForPlayer($rank->getPlayer(), $rank->getRank());
  	  }
  	  echo "</td></tr>";
  	}
  	echo "</table>";
  }

  function displayTotalRanks($teamId, $rankYear) {
  	$numRanks = RankDao::getTotalRankCount($teamId, $rankYear);
  	echo "<h4>Total Ranks: " . $numRanks . " / 150</h4>";
  	displayProgressBar($numRanks, 150, "");
  }

  function displayProgressBar($currentValue, $maxValue, $extraClass) {
  	echo "<div class='progress progress-striped $extraClass'>
  	        <div class=\"bar ";
  	if ($currentValue < $maxValue) {
  	  echo "bar";
  	} else if ($currentValue > $maxValue) {
  	  echo "bar-danger";
  	} else {
  	  echo "bar-success";
  	}
  	echo "\" style=\"width: " . (($currentValue / $maxValue) * 100) . "%;\"></div>
  	     </div>";
  }

  function displayRankSummary($teamId, $rankYear) {
  	$count = 10;
  	for ($i=0; $i<2; $i++) {
  	  echo "<div class='row-fluid'>";
  	  for ($j=0; $j<5; $j++) {
  	  	if ($j == 0) {
  	  	echo "<div class='span2 offset1'>";
  	  	} else {
   	      echo "<div class='span2'>";
  	  	}
  	  	$numRanks = count(RankDao::getRanksByTeamYearRank($teamId, $rankYear, $count));
  	  	echo "<label>$count:</label> ($numRanks / 15)<br/>";
        displayProgressBar($numRanks, 15, "smallprogress");
  	  	$count--;
  		echo "</div>";
  	  }
  	  echo "</div>"; // row-fluid
  	}
  }

  // Display nav bar.
  LayoutUtil::displayNavBar(true, LayoutUtil::MY_RANKS_BUTTON);

  // Get selected team from logged-in user.
  $teamId = SessionUtil::getLoggedInTeam()->getId();

  echo "<div class='row-fluid'>
          <div class='span12 center'>";
  echo "<h3>My Ranks</h3>";
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
  echo "  </div>
        </div>
        <div class='row-fluid'>
          <div class='span12 center'>";

  displayTotalRanks($teamId, $rankYear);
  displayRankSummary($teamId, $rankYear);
  echo "</div>"; // span12
  echo "</div>"; // row-fluid

  // if any cumulative ranks exist for the rank year, then display page in read-only mode.
  $isReadOnly = CumulativeRankDao::hasCumulativeRanks($rankYear);

  echo "<div class='row-fluid'>
          <div class='span3 center'>";

  // display unranked players
  echo "<h4>Unranked Players</h4>";
  $rankablePlayers = PlayerDao::getPlayersForRanking($teamId, $lastYear);
  echo "<table id='unranked'
               class='table vertmiddle table-striped table-condensed table-bordered
                      center smallfonttable'>
          <thead><tr><th>Player</th><th>FPTS</th><th>Rank</th></tr></thead>";
  foreach ($rankablePlayers as $player) {
  	$fantasyPts = ($player->getStatLine($lastYear) != null) ?
  	    $player->getStatLine($lastYear)->getFantasyPoints() : "--";
  	echo "<tr><td>" . $player->getIdNewTabLink(true, $player->getFullName()) . "</td>
  	          <td>" . $fantasyPts . "</td><td>";
  	if ($isReadOnly) {
      echo "0";
  	} else {
  	  displaySelectForPlayer($player, 0);
  	}
  	echo "</td></tr>";
  }

  // display all 0 placeholders
  $zeroPlaceholders = RankDao::getRanksByTeamYearRank($teamId, $rankYear, 0);
  foreach ($zeroPlaceholders as $zeroPlaceholder) {
  	$placeholderPlayer = $zeroPlaceholder->getPlayer();
  	$fantasyPts = ($placeholderPlayer->getStatLine($lastYear) != null) ?
   	    $placeholderPlayer->getStatLine($lastYear)->getFantasyPoints() : "--";
  	echo "<tr class='placeholder_row'>
  	        <td>" . $placeholderPlayer->getIdNewTabLink(
  	                  true, $placeholderPlayer->getFullName()) . "</td>
  	        <td>" . $fantasyPts . "</td>
  	        <td>0</td>
  	      </tr>";
  }
  echo "</table>";
  echo "</div>"; //span2

  echo "<div class='span9 center'>";

  // display ranked players
  echo "<h4>Ranked Players</h4>";

  if (!$isReadOnly) {
    echo "<p><button class='btn btn-primary' name='save' type='submit'>Save my changes</button>";
    echo "&nbsp&nbsp<button class=\"btn\" name='cancel' type=\"submit\">Reset</button></p>";
    echo "<input type='hidden' name='team_id' value='" . $teamId . "'>";
  }
  $count = 10;
  for ($i=0; $i<4; $i++) {
  	echo "<div class='row-fluid'>";
  	for ($j=0; $j<3; $j++) {
  	  if ($count > 0) {
    	echo "<div class='span4'>";
        displayPlayersByRank($teamId, $rankYear, $count--, $isReadOnly);
        echo "</div>";
  	  }
  	}
  	echo "</div>"; // row-fluid
  }

  if (!$isReadOnly) {
    echo "<p><button class='btn btn-primary' name='save' type='submit'>Save my changes</button>";
    echo "&nbsp&nbsp<button class='btn' name='cancel' type='submit'>Reset</button></p>";
  }

  echo "</FORM>";
  echo "</div>"; // span10
  echo "</div>"; // row-fluid

  // Footer
  LayoutUtil::displayFooter();
?>
</body>
</html>