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
  private $buyoutContracts;
  private $newContracts;
  private $pingPongBalls;

  public function parseKeepersFromPost() {
  	$this->team = TeamDao::getTeamById($_POST['teamid']);
  	$this->buyoutContracts = $this->parseBuyoutContracts($_POST, true);
  	$this->newContracts = $this->parseNewContracts($_POST, true);
  	$this->pingPongBalls = $this->parsePingPongBalls($_POST, true);
  	
    SessionUtil::updateSession('teamid', $_POST, true);
  }

  public function parseKeepersFromSession() {
    $this->team = TeamDao::getTeamById($_SESSION['teamid']);
  	$this->buyoutContracts = $this->parseBuyoutContracts($_SESSION, false);
  	$this->newContracts = $this->parseNewContracts($_SESSION, false);
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
  
  private function parseBuyoutContracts($assocArray, $isPost) {
  	$buyoutString = 'buyout';
    $buyoutContracts = array();
  	if (isset($assocArray[$buyoutString]) && is_array($assocArray[$buyoutString])) {
      foreach ($assocArray[$buyoutString] as $contractId) {
      	$buyoutContracts[] = ContractDao::getContractById($contractId);
      }
  	  SessionUtil::updateSession($buyoutString, $assocArray, $isPost);
    }
  	return $buyoutContracts;
  }
  
  private function parseNewContracts($assocArray, $isPost) {
  	
  }
  
  public function showKeepersSummary() {
    echo "<h2>Keepers Summary</h2>";
    echo "<h3>" . $this->team->getName() . " (" . $this->team->getOwnersString() . ")</h3>";
    
    // display buyout contracts
    $buyoutBrognas = 0;
    if ($this->buyoutContracts) {
      echo "<h4>Buyout Contract(s):</h4>";
      foreach ($this->buyoutContracts as $contract) {
      	echo $contract->getBuyoutContractString() . "<br/>";
      	$buyoutBrognas += $contract->getBuyoutPrice();
      }
    }

    // TODO display new contracts
    $newContractBrognas = 0;
    
    // display ping pong balls
    $pingPongBrognas = 0;
    if ($this->pingPongBalls && (count($this->pingPongBalls) > 0)) {
      echo "<h4>Ping Pong Ball(s):</h4>";
      foreach ($this->pingPongBalls as $pingPongBall) {
    	echo "$" . $pingPongBall->getCost() . "<br/>";
    	$pingPongBrognas += $pingPongBall->getCost();
      }
    }
    
    // display summary
    $currentYear = TimeUtil::getCurrentYear();
    $brognas = BrognaDao::getBrognasByTeamAndYear($this->team->getId(), $currentYear);
    echo "<h4>Brognas</h4>";
    echo "<table class='center' border><tr><th></th><th>Price</th></tr>";
    echo "<tr><td><strong>" . $currentYear . " Brognas</strong></td>
              <td><strong>" . $brognas->getTotalPoints() . "</strong></td></tr>";
    echo "<tr><td>Buyout Contracts</td><td>" . $buyoutBrognas . "</td></tr>";
    echo "<tr><td>New Contracts</td><td>" . $newContractBrognas . "</td></tr>";
    echo "<tr><td>Ping Pong Balls</td><td>" . $pingPongBrognas . "</td></tr>";
    
    $totalBrognas = $brognas->getTotalPoints() -
        ($buyoutBrognas + $newContractBrognas + $pingPongBrognas);
    echo "<tr><td><strong>Leftover Brognas</strong></td><td><strong>" . $totalBrognas .
        "</strong></td></tr></table><br/>";
  }

  public function validateKeepers() {
  	$totalBrognasSpent = 0;
  	
	// validate buyout contracts
  	if ($this->buyoutContracts) {
	  foreach ($this->buyoutContracts as $contract) {
	  	if ($contract->isAuction() || $contract->isBoughtOut()) {
		  echo "Error: cannot buy out contract: " . $contract->getBuyoutContractString() . "<br>";
	  	  return false;
	  	}	  	
	  	$totalBrognasSpent += $contract->getBuyoutPrice();
	  }
  	}
  	 
	// TODO validate new contracts
  	  
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
  	$currentYear = TimeUtil::getCurrentYear();
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
  	
  	// buyout contracts
  	if ($this->buyoutContracts) {
  	  foreach ($this->buyoutContracts as $contract) {
  	    $contract->buyOut();
  	    ContractDao::updateContract($contract);
  	    echo "<strong>Bought out:</strong> " . $contract->getBuyoutContractString() . "<br/>";
  	  	$totalBrognasSpent += $contract->getBuyoutPrice();  	  	
  	  }
  	}
  	
  	// TODO create new contracts
  	  
    // save ping pong balls
    if ($this->pingPongBalls && (count($this->pingPongBalls) > 0)) {
      foreach ($this->pingPongBalls as $pingPongBall) {
      	BallDao::createPingPongBall($pingPongBall);
      	echo "<strong>Purchased ball:</strong> " . $pingPongBall->toString() . "<br>";
        $totalBrognasSpent += $pingPongBall->getCost();
      }
    }

    // update brognas
  	$currentYear = TimeUtil::getCurrentYear();
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