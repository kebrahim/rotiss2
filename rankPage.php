<?php session_start(); ?>
<html>
<head>
<title>My Ranks</title>
</head>

<style type="text/css">
html {height:100%;}
body {text-align:center;}
table {text-align:center;}
table.center {margin-left:auto; margin-right:auto;}
#column_container {padding:0; margin:0 0 0 50%; width:50%; float:right;}
#left_col {float:left; width:100%; margin-left:-100%; text-align:center;}
#left_col_inner {padding:10px;}
#right_col {float:right; width:100%; text-align:center;}
#right_col_inner {padding:10px;}
#placeholder_row {background-color:#E3F2F9;}
#vert_td {vertical-align:top;}
</style>

<script>
</script>

<body>
<?php 
  require_once 'dao/rankDao.php';

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
  function displayPlayersForRank($teamId, $rankYear, $rank) {
  	$ranks = RankDao::getRanksByTeamYearRank($teamId, $rankYear, $rank);
  	echo "<br/><strong>" . $rank . "'s</strong> ";
  	echo "<meter min='0' max='15' low='15' optimum='15' value='" . count($ranks) . "'></meter>
  	      (" . count($ranks) . "/15)
  	      <br/><br/>";
  	echo "<table border class='center'>";
  	echo "<tr><th>Player</th><th>Pos</th><th>Team</th><th>Age</th><th>Rank</th></tr>";

  	foreach ($ranks as $rank) {
      // if rank is a placeholder, update style
  	  echo "<tr";
  	  if ($rank->isPlaceholder()) {
  	  	echo " id='placeholder_row'";
  	  }
  	  echo "><td><a href='displayPlayer.php?player_id=" . $rank->getPlayerId() . "'>" . 
  	             $rank->getPlayer()->getFullName() . "</a></td>
  	         <td>" . $rank->getPlayer()->getPositionString() . "</td>
  	         <td>" . $rank->getPlayer()->getMlbTeam()->getAbbreviation() . "</td>
  	         <td>" . $rank->getPlayer()->getAge() . "</td>";
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
  
  echo "<h1>My Ranks</h1>";
  echo "<FORM ACTION='rankPage.php' METHOD=POST>";
  $rankYear = TimeUtil::getYearBasedOnEndOfSeason();
  $teamId = $_REQUEST["team_id"];
  
  if (isset($_POST['save'])) {
    // for every unranked player, check if a player was ranked.
  	$unrankedPlayers = PlayerDao::getPlayersForRanking($teamId);
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
  
  // display ranked players
  echo "<h2>Ranked Players</h2>";
  $numRanks = count(RankDao::getRanksByTeamYear($teamId, $rankYear));
  echo "<meter min='0' max='150' low='150' optimum='150' value='" . $numRanks . "'></meter> 
        (" . $numRanks . "/150)<br/><br/>";
  
  echo "<table border id='ranked' class='center'>";
  $count = 10;
  for ($i=0; $i<2; $i++) {
  	echo "<tr>";
  	for ($j=0; $j<5; $j++) {
      echo "<td id='vert_td'>";
      displayPlayersForRank($teamId, $rankYear, $count--);
      echo "</td>";
  	}
  	echo "</tr>";
  }
  echo "</table><br/><br/>";

  echo "<input type='submit' name='save' value='Save my changes'>";
  echo "<input type='submit' name='cancel' value='Cancel'>";
  echo "<input type='hidden' name='team_id' value='" . $teamId . "'>";
  
  // display unranked players
  // TODO show fantasy points for previous year
  echo "<h2>Unranked Players</h2>";
  $rankablePlayers = PlayerDao::getPlayersForRanking($teamId);
  echo "<table border id='unranked' class='center'>
          <tr><th>Player</th><th>Pos</th><th>Team</th><th>Age</th><th>Rank</th></tr>";
  foreach ($rankablePlayers as $player) {
  	echo "<tr><td><a href='displayPlayer.php?player_id=" . $player->getId() . "'>" . 
  	              $player->getFullName() . "</a></td>
  	          <td>" . $player->getPositionString() . "</td>
  	          <td>" . $player->getMlbTeam()->getAbbreviation() . "</td>
  	          <td>" . $player->getAge() . "</td><td>";
  	displaySelectForPlayer($player, 0);
  	echo "</td></tr>";
  }
  echo "</table>";
  
  echo "</FORM>";
?>
</body>
</html>