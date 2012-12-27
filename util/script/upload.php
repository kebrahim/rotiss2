<?php
  require_once 'commonUtil.php';
  CommonUtil::requireFileIn('/../dao/', 'auctionDao.php');
  CommonUtil::requireFileIn('/../dao/', 'ballDao.php');
  CommonUtil::requireFileIn('/../dao/', 'contractDao.php');
  CommonUtil::requireFileIn('/../dao/', 'cumulativeRankDao.php');
  CommonUtil::requireFileIn('/../dao/', 'draftPickDao.php');
  CommonUtil::requireFileIn('/../dao/', 'mlbTeamDao.php');
  CommonUtil::requireFileIn('/../dao/', 'playerDao.php');
  CommonUtil::requireFileIn('/../dao/', 'positionDao.php');
  CommonUtil::requireFileIn('/../dao/', 'rankDao.php');
  CommonUtil::requireFileIn('/../dao/', 'statDao.php');
  CommonUtil::requireFileIn('/../dao/', 'teamDao.php');
  CommonUtil::requireFileIn('/../util/', 'time.php');
  
  // TODO provide ability to upload file
  //$fh = fopen("players.csv", 'r');
  $header = fgetcsv($fh);

  $data = array();
  while ($line = fgetcsv($fh)) {
    $data[] = array_combine($header, $line);
  }
  fclose($fh);
    
  // clear out all player-related tables
  $currentYear = TimeUtil::getCurrentYear();
  /*StatDao::deleteAllStats();
  TeamDao::deleteAllPlayerAssociations();
  PositionDao::deleteAllPlayerAssociations();
  RankDao::deleteAllRanks();
  CumulativeRankDao::deleteAllCumulativeRanks();
  AuctionResultDao::deleteAllAuctionResults();
  ContractDao::deleteAllContracts();
  DraftPickDao::deleteAllDraftPicks();
  BallDao::deleteAllPingPongBalls();
  PlayerDao::deleteAllPlayers();*/

  echo "<h1>players</h1>";
  $numPlayers = 0;
  foreach ($data as $line) {
	print_r($line);
  	echo "<br/>";
  	
  	// get mlb team id
  	$mlbTeamAbbr = $line["MLB Team"];
  	$mlbTeam = MlbTeamDao::getMlbTeamByAbbreviation(trim($mlbTeamAbbr));
  	$mlbTeamId = $mlbTeam->getId();
  	
  	// birth date
    $dob = $line["Date of Birth"];
    list($month,$day,$year) = explode("-", $dob);
    $dateOfBirth = $year . "-" . $month . "-" . $day;

  	$player = new Player(-1, $line["First Name"], $line["Last Name"], $dateOfBirth,
  			$mlbTeamId, $line["Sportsline ID"]);
	echo $player->toString();
  	echo "<br/>";
  	
  	// create player
  	$player = PlayerDao::createPlayer($player);
  	
  	// assign positions
  	$positionString = $line["Position(s)"];
  	$positionAbbrs = explode("/", $positionString);
  	$positions = array();
  	foreach ($positionAbbrs as $positionAbbr) {
  	  $position = PositionDao::getPositionByAbbreviation($positionAbbr);
  	  echo $position->toString() . "<br/>";
  	  $positions[] = $position;
  	}
  	PositionDao::assignPositionsToPlayer($positions, $player);
  	 
  	// assign to fantasy team
  	$teamName = $line["Team"];
  	$team = TeamDao::getTeamByName($teamName);
  	echo "Team: " . $team->getAbbreviation() . "<br/>";
  	TeamDao::assignPlayerToTeam($player, $team->getId());
  	
  	// create fantasy stat line
  	$fantasyPts = $line["FPTS"];
  	$statLine = new StatLine($fantasyPts);
  	$stat = new Stat(-1, $currentYear, $player->getId(), $statLine);
  	echo "Stat: " . $stat->toString() . "<br/>";
  	StatDao::createStat($stat);
  	
  	echo "<br/>";
  	$numPlayers++;
  }
  echo "players processed - $numPlayers";
?>