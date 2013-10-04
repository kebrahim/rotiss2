<?php
  require_once 'commonScript.php';
  CommonScript::requireFileIn('/../../dao/', 'playerDao.php');
  CommonScript::requireFileIn('/../../dao/', 'statDao.php');
  CommonScript::requireFileIn('/../', 'time.php');
      
  $rankYear = TimeUtil::getYearByEvent(Event::OFFSEASON_START);
  $lastYear = $rankYear - 1;

  // open players file & display stats
  $fh = fopen("2013_players.csv", 'r');
  $header = fgetcsv($fh);

  $data = array();
  while ($line = fgetcsv($fh)) {
    try {
      $data[] = array_combine($header, $line);
    } catch(Exception $e) {
      print_r("error: " + $line);
    }
  }
  fclose($fh);

  echo "<h1>MLB Players w/ $lastYear stats</h1>";
  $numPlayers = 0;
  $numFound = 0;
  $numStats = 0;
  echo "<table border>
          <thead><tr>
            <th>#</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>FPTS</th>
            <th>CBS ID</th>
            <th>Rotiss ID</th>
            <th>Stat</th>
          </tr></thead>";
  foreach ($data as $line) {
	$numPlayers++;
  	echo "<tr><td>" . $numPlayers . "</td>";

    // first name
    echo "<td>" . $line["fname"] . "</td>";

    // last name
    echo "<td>" . $line["lname"] . "</td>";

	// fpts  	
    echo "<td>" . $line["points"] . "</td>";

    $players = PlayerDao::getPlayersByFullName($line["fname"], $line["lname"]);
    $player = null;
    if (count($players) == 1) {
      $numFound++;
      $player = $players[0];
      echo "<td>" . $player->getSportslineId() . "</td>
	        <td>" . $player->getId() . "</td>";
    
      // get 2013 stat from db
	  $stat = StatDao::getStatByPlayerYear($player->getId(), 2013);
	  if ($stat != null) {
	  	$numStats++;
        echo "<td>" . $stat->getStatLine()->getFantasyPoints() . "</td>";
	  } elseif (isset($_POST['save'])) {
	  	// stat doesn't exist, user wants to create
     	$fantasyPts = $line["points"];
  	    $statLine = new StatLine($fantasyPts);
  	    $stat = new Stat(-1, $lastYear, $player->getId(), $statLine);
  	    StatDao::createStat($stat);
        echo "<td><strong>". $stat->getStatLine()->getFantasyPoints() ."</strong></td>";
	  	$numStats++;
	  } else {
	  	echo "<td>--</td>";
	  }
    } elseif (count($players) > 1) {
      echo "<td>Multi</td>
	        <td>" . count($players) . "</td>";
    } else {
      echo "<td>--</td>
	        <td>--</td>";
    }
  	echo "</tr>";
  }
  echo "</table>";

  echo "<h3>Stats</h3>
        Players: " . $numPlayers . "<br/>
        Found in DB: " . $numFound . "<br/>
        $lastYear Stats: " . $numStats . "<br/><br/>";  

  echo "<form action='fantasyPointUpload.php' method='post'>
          <input type='submit' name='save' value='Save Stats'/>
        </form>";
?>