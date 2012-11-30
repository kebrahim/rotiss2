<?php
  require_once '../util/sessions.php';
  SessionUtil::checkUserIsLoggedInSuperAdmin();
?>

<html>
<head>
<title>Rotiss.com - Manage Ranks</title>
<link href='../css/style.css' rel='stylesheet' type='text/css'>
</head>

<body>

<?php
  require_once '../dao/cumulativeRankDao.php';
  require_once '../dao/playerDao.php';
  require_once '../dao/rankDao.php';
  require_once '../dao/statDao.php';
  require_once '../entity/rank.php';
  require_once '../entity/stat.php';
  require_once '../util/navigation.php';
  require_once '../util/time.php';

  // Display header.
  NavigationUtil::printHeader(true, false, NavigationUtil::MANAGE_RANKS_BUTTON);
  echo "<div class='bodycenter'>";

  echo "<FORM ACTION='manageRanks.php' METHOD=POST>";
  $rankYear = TimeUtil::getYearBasedOnEndOfSeason();
  $lastYear = $rankYear - 1;

  // Save ranks
  if(isset($_POST['save'])) {
    // create cumulative rank for ranked players
  	$ranks = RankDao::calculateCumulativeRanksByYear($rankYear);
  	foreach ($ranks as $rank) {
  	  if (!CumulativeRankDao::hasCumulativeRank($rank->getPlayerId(), $rankYear)) {
  	  	CumulativeRankDao::createCumulativeRank($rank);
  	  }
  	}

  	// create minimum cumulative rank for unranked players
  	$unrankedPlayers = PlayerDao::getUnrankedPlayers($rankYear);
  	foreach ($unrankedPlayers as $unrankedPlayer) {
  	  if (!CumulativeRankDao::hasCumulativeRank($unrankedPlayer->getId(), $rankYear)) {
        $rank = new CumulativeRank(-1, $rankYear, $unrankedPlayer->getId(),
  	        CumulativeRank::MINIMUM_RANK, false);
  	    CumulativeRankDao::createCumulativeRank($rank);
  	  }
  	}
  }

  // show cumulative ranks for all players, including placeholders & whether the ranks have
  // been saved
  echo "<h1>Ranks " . $rankYear . "</h1><hr/>";
  $ranks = RankDao::calculateCumulativeRanksByYear($rankYear);

  echo "<input class='button' type=submit name='save' value='Save ranks'>&nbsp&nbsp";
  if (isset($_REQUEST["filter"])) {
  	echo "<a href='manageRanks.php'>Show placeholders</a>";
  	echo "<input type=hidden name='filter'>";
  	$filter = true;
  } else {
  	echo "<a href='manageRanks.php?filter'>Hide placeholders</a>";
  	$filter = false;
  }
  echo "<br/><br/>";

  echo "<table class='center smallfonttable' border>
  	          <th>Player</th><th>Team</th><th>Rank</th><th>Is Placeholder</th>
              <th>Saved In DB</th></tr>";
  foreach($ranks as $rank) {
  	if ($filter && $rank->isPlaceholder()) {
  	  continue;
  	}

  	// determine if cumulative rank is already in db
  	$hasCumulativeRank = CumulativeRankDao::hasCumulativeRank($rank->getPlayerId(), $rankYear);

  	// highlight rows based on placeholders/rank-in-db
  	echo "<tr class='";
  	if ($rank->isPlaceholder()) {
      echo "placeholder";
  	} else if ($hasCumulativeRank) {
  	  echo "row_indb";
  	} else {
  	  echo "row_missing";
  	}
  	echo "'>";

  	$fantasyTeam = $rank->getPlayer()->getFantasyTeam();
  	$teamLink = ($fantasyTeam == null) ?
  	    "" : $fantasyTeam->getIdLink(false, $fantasyTeam->getAbbreviation());
  	echo "    <td>" . $rank->getPlayer()->getNameLink(false) . "</td>
  	          <td>" . $teamLink . "</td>
  	          <td>" . $rank->getRank() . "</td>
  	          <td>" . ($rank->isPlaceholder() ? "Y" : "") . "</td>
  	          <td>" . ($hasCumulativeRank ? "Y" : "") . "</td>
  	      </tr>";
  }

  // Add unranked players at bottom of table.
  $unrankedPlayers = PlayerDao::getUnrankedPlayers($rankYear);
  foreach ($unrankedPlayers as $unrankedPlayer) {
    $hasCumulativeRank = CumulativeRankDao::hasCumulativeRank($unrankedPlayer->getId(), $rankYear);
    echo "<tr class='";
    if ($hasCumulativeRank) {
      echo "row_indb";
    } else {
      echo "row_missing";
    }
    echo "'>";

    $fantasyTeam = $unrankedPlayer->getFantasyTeam();
    echo "<td>" . $unrankedPlayer->getNameLink(false) . "</td>
          <td>" . $fantasyTeam->getIdLink(false, $fantasyTeam->getAbbreviation()) . "</td>
  	      <td>0</td>
  	      <td></td>
  	      <td>" . ($hasCumulativeRank ? "Y" : "") . "</td>
  	    </tr>";
  }

  echo "</table><br/>";
  echo "</form></div>";

  // Footer
  NavigationUtil::printFooter();
?>

</body>
</html>
