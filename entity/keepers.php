<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'ballDao.php');
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');
CommonEntity::requireFileIn('/../entity/', 'ball.php');
CommonEntity::requireFileIn('/../util/', 'sessions.php');
CommonEntity::requireFileIn('/../util/', 'time.php');

/**
 * Represents a set of contracts and ping pong balls to be kept.
 */
class Keepers {
  private $team;
  private $contracts;
  private $pingPongBalls;

  public function parseKeepersFromPost() {
  	$this->team = TeamDao::getTeamById($_POST['teamid']);
  	// TODO $this->contracts = $this->parseContracts($_POST, true);
    $this->pingPongBalls = $this->parsePingPongBalls($_POST, true);
  	
    SessionUtil::updateSession('teamid', $_POST, true);
  }

  public function parseKeepersFromSession() {
    $this->team = TeamDao::getTeamById($_SESSION['teamid']);
  	// TODO $this->contracts = $this->parseContracts($_SESSION, false);
    $this->pingPongBalls = $this->parsePingPongBalls($_SESSION, false);
    
    SessionUtil::updateSession('teamid', $_SESSION, false);
  }

  private function parsePingPongBalls($assocArray, $isPost) {
  	$savedppString = 'savedppballcount';
  	$newppString = 'newppballcount';
  	$ppSavedCount = $assocArray[$savedppString];
  	$ppNewCount = $assocArray[$newppString];
  	
  	$pingPongBalls = array();
  	$currentYear = TimeUtil::getCurrentYear() + 1; // TDOO should be currentYear
  	for ($i = ($ppSavedCount + 1); $i <= ($ppSavedCount + $ppNewCount); $i++) {
  	  $ppKey = 'pp' . $i;
  	  $pingPongBalls[] = new PingPongBall(
  	      -1, $currentYear, $assocArray[$ppKey], $this->team->getId(), null);
      SessionUtil::updateSession($ppKey, $assocArray, $isPost);
  	}

  	SessionUtil::updateSession($savedppString, $assocArray, $isPost);
  	SessionUtil::updateSession($newppString, $assocArray, $isPost);
  	return $pingPongBalls;
  }
  
  public function showKeepersSummary() {
    echo "<h2>Keepers Summary</h2>";
    echo "<h3>" . $this->team->getName() . " (" . $this->team->getOwnersString() . ")</h3>";

    // TODO display contracts
    
    // display ping pong balls
    if ($this->pingPongBalls && (count($this->pingPongBalls) > 0)) {
      echo "<strong>Ping Pong Balls: </strong>";
      $isFirst = true;
      foreach ($this->pingPongBalls as $pingPongBall) {
    	if ($isFirst) {
    	  $isFirst = false;
    	} else {
    	  echo ", ";
    	}
    	echo $pingPongBall->toString();
      }
      echo "<br/><br/>";
    }
  }

  public function validateKeepers() {
  	$totalBrognasSpent = 0;
  	
	// TODO validate contracts

  	// validate ping pong ball values
  	if ($this->pingPongBalls && (count($this->pingPongBalls) > 0)) {
  	  foreach ($this->pingPongBalls as $pingPongBall) {
  	  	$cost = $pingPongBall->getCost();
  	  	if (!is_numeric($cost) || $cost < 100) {
    	  echo "Error: cannot spend an invalid number of brognas on a ball: " . $cost . "<br>";
  	  	  return false;
  	  	}
  	  	$totalBrognasSpent += $cost;
  	  }
  	}
  	
  	// confirm team has enough money to spend on contracts & balls.
  	$currentYear = TimeUtil::getCurrentYear() + 1; // TODO currentYear
  	$brognas = BrognaDao::getBrognasByTeamAndYear($this->team->getId(), $currentYear);
  	if ($brognas->getTotalPoints() < $totalBrognasSpent) {
  	  echo "Error: " . $this->team->getName() . " cannot spend " . $totalBrognasSpent .
  	      " brognas on contracts & balls; only has " . $brognas->getTotalPoints() .
  		  " total points.<br>";
  	  return false;
  	}
  	return true;
  }

  public function saveKeepers() {
  	echo "<h2>Keepers Saved!</h2>";
  	echo "<h3>" . $this->team->getName() . " (" . $this->team->getOwnersString() . ")</h3>";

  	$totalBrognasSpent = 0;
  	
  	// TODO save contracts
    
    // save ping pong balls
    if ($this->pingPongBalls && (count($this->pingPongBalls) > 0)) {
      foreach ($this->pingPongBalls as $pingPongBall) {
      	BallDao::createPingPongBall($pingPongBall);
      	echo "<strong>Purchased ball:</strong> " . $pingPongBall->toString() . "<br>";
        $totalBrognasSpent += $pingPongBall->getCost();
      }
    }

    // update brognas
  	$currentYear = TimeUtil::getCurrentYear() + 1; // TODO currentYear
  	$brognas = BrognaDao::getBrognasByTeamAndYear($this->team->getId(), $currentYear);
	$originalTotalPoints = $brognas->getTotalPoints();
  	$brognas->setTotalPoints($originalTotalPoints - $totalBrognasSpent);
  	BrognaDao::updateBrognas($brognas);
	echo "<strong>" . $currentYear . " Brognas:</strong> reduced by " . $totalBrognasSpent . 
	    ", from " . $originalTotalPoints . " to " . $brognas->getTotalPoints() . "<br>";

	// TODO update changelog
  }
}
?>