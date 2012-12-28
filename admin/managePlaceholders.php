<?php
  require_once '../util/sessions.php';

  SessionUtil::checkUserIsLoggedInSuperAdmin();
  SessionUtil::logoutUserIfNotLoggedIn("admin/managePlaceholders.php");
?>

<!DOCTYPE html>
<html>
<head>
<title>St Pete's Rotiss - Manage Placeholders</title>
<link href='../css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='../css/stpetes.css' rel='stylesheet' type='text/css'>
<link rel="shortcut icon" href="../img/background-tiles-01.png" />
</head>

<body>

<?php
  require_once '../dao/playerDao.php';
  require_once '../dao/rankDao.php';
  require_once '../dao/statDao.php';
  require_once '../entity/rank.php';
  require_once '../entity/stat.php';
  require_once '../util/layout.php';
  require_once '../util/time.php';

  // Nav bar
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_PLACEHOLDERS_BUTTON);

  echo "<div class='row-fluid'>
          <div class='span12 center'>";
  echo "<FORM ACTION='managePlaceholders.php' METHOD=POST>";
  $rankYear = TimeUtil::getYearByEvent(TimeUtil::SEASON_END_EVENT);
  $lastYear = $rankYear - 1;

  // Save placeholders
  if(isset($_POST['save'])) {
  	$stats = StatDao::getStatsForRankingByYear($lastYear);
  	$ct = 1;
  	$teams = TeamDao::getAllTeams();
  	foreach($stats as $stat) {
      $ranking = floor(11.0 - ($ct++ / 15.0));
      if ($ranking < 0) {
      	$ranking = 0;
      }
  	  $isPlaceholder = PlayerDao::hasContractForPlaceholders($stat->getPlayerId(), $lastYear);
  	  if ($isPlaceholder &&
  	      !RankDao::hasAllPlaceholderRanks($stat->getPlayerId(), $rankYear)) {
  	  	// insert placeholder ranks into db if they haven't already been inserted
  	  	foreach ($teams as $team) {
  	  	  if ($stat->getPlayer()->getFantasyTeam()->getId() != $team->getId()) {
  	  	  	$rank = new Rank(-1, $rankYear, $team->getId(), $stat->getPlayerId(), $ranking, true);
  	  	  	RankDao::createRank($rank);
  	  	  }
  	  	}
  	  }
  	}
  }

  // show stats for players belonging to teams for last season
  echo "<h3>Placeholders " . $rankYear . "</h3>";
  $stats = StatDao::getStatsForRankingByYear($lastYear);

  echo "<p><button class='btn btn-primary' name='save' type='submit'>Save placeholders</button>
        &nbsp&nbsp";
  if (isset($_REQUEST["filter"])) {
  	echo "<a href='managePlaceholders.php'>Show all</a>";
  	echo "<input type=hidden name='filter'>";
  	$filter = true;
  } else {
  	echo "<a href='managePlaceholders.php?filter'>Show placeholders</a>";
  	$filter = false;
  }
  echo "</p></div></div>"; // span12, row-fluid
  echo "<div class='row-fluid'>
            <div class='span12 center'>";

  echo "<br/><table class='center smallfonttable table vertmiddle table-striped table-condensed
                           table-bordered'>
  	      <thead><tr><th></th><th>Player</th><th>Team</th><th>$lastYear Fantasy Pts</th>
  	        <th>Rank</th><th>Is Placeholder</th><th>Saved In DB</th></tr></thead>";
  $ct = 0;
  foreach($stats as $stat) {
  	$ct++;
  	$rank = floor(11.0 - ($ct / 15.0));
    if ($rank < 0) {
      $rank = 0;
    }
  	$isPlaceholder = PlayerDao::hasContractForPlaceholders($stat->getPlayerId(), $lastYear);
  	$placeholderInDb = ($isPlaceholder &&
  	    RankDao::hasAllPlaceholderRanks($stat->getPlayerId(), $rankYear));
  	if ($filter && !$isPlaceholder) {
  	  continue;
  	}
  	echo "<tr";
  	if ($isPlaceholder) {
      echo " class='row";
      if (!$placeholderInDb) {
      	echo "_missing";
      } else {
        echo "_indb";
      }
      echo "'";
  	}
  	echo ">";
  	$fantasyTeam = $stat->getPlayer()->getFantasyTeam();
  	echo "    <td>" . $ct . "</td>
  	          <td>" . $stat->getPlayer()->getNameLink(false) . "</td>
  	          <td>" . $fantasyTeam->getIdLink(false, $fantasyTeam->getAbbreviation()) . "</td>
  	          <td>" . $stat->getStatLine()->getFantasyPoints() . "</td>
  	          <td>" . $rank . "</td>
  	          <td>" . ($isPlaceholder ? 'Y' : "") . "</td>
  	          <td>" . ($placeholderInDb ? "Y" : "") . "</td>
  	      </tr>";
  }
  echo "</table>";
  echo "</form>";
  echo "</div>"; // span12
  echo "</div>"; // row-fluid

  // Footer
  LayoutUtil::displayAdminFooter();
?>

</body>
</html>
