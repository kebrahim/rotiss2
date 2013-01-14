<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'ballDao.php');
CommonEntity::requireFileIn('/../dao/', 'changelogDao.php');
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
  	$this->team = TeamDao::getTeamById($_POST['keeper_teamid']);
  	$this->buyoutContracts = $this->parseBuyoutContracts($_POST, true);
  	$this->newContracts = $this->parseNewContracts($_POST, true);
  	$this->pingPongBalls = $this->parsePingPongBalls($_POST, true);

    SessionUtil::updateSession('keeper_teamid', $_POST, true);
  }

  public function parseKeepersFromSession() {
    $this->team = TeamDao::getTeamById($_SESSION['keeper_teamid']);
  	$this->buyoutContracts = $this->parseBuyoutContracts($_SESSION, false);
  	$this->newContracts = $this->parseNewContracts($_SESSION, false);
  	$this->pingPongBalls = $this->parsePingPongBalls($_SESSION, false);

    SessionUtil::updateSession('keeper_teamid', $_SESSION, false);
  }

  private function parsePingPongBalls($assocArray, $isPost) {
  	$savedppString = 'keeper_savedppballcount';
  	$newppString = 'keeper_newppballcount';
  	$ppSavedCount = $assocArray[$savedppString];
  	$ppNewCount = $assocArray[$newppString];

  	$pingPongBalls = array();
  	$currentYear = TimeUtil::getCurrentYear();
  	for ($i = ($ppSavedCount + 1); $i <= ($ppSavedCount + $ppNewCount); $i++) {
  	  $ppKey = 'keeper_pp' . $i;
  	  $pingPongBalls[] = new PingPongBall(
  	      -1, $currentYear, $assocArray[$ppKey], $this->team->getId(), null);
      SessionUtil::updateSession($ppKey, $assocArray, $isPost);
  	}

  	SessionUtil::updateSession($savedppString, $assocArray, $isPost);
  	SessionUtil::updateSession($newppString, $assocArray, $isPost);
  	return $pingPongBalls;
  }

  private function parseBuyoutContracts($assocArray, $isPost) {
  	$buyoutString = 'keeper_buyout';
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
    $savedKeeperString = 'keeper_savedkeepercount';
    $newKeeperString = 'keeper_newkeepercount';
    $keeperSavedCount = $assocArray[$savedKeeperString];
    $keeperNewCount = $assocArray[$newKeeperString];

    $newContracts = array();
    $currentYear = TimeUtil::getCurrentYear();
    for ($i = ($keeperSavedCount + 1); $i <= ($keeperSavedCount + $keeperNewCount); $i++) {
      $playerKey = 'keeper_player' . $i;
      $yearKey = 'keeper_year' . $i;
      $priceKey = 'keeper_price' . $i;

      // If length of contract is "0", then use free 1-year minor league contract.
      if (intval($assocArray[$yearKey]) == 0) {
        $numYears = 1;
        $contractType = Contract::MINOR_KEEPER_TYPE;
      } else {
        $numYears = intval($assocArray[$yearKey]);
        $contractType = Contract::KEEPER_TYPE;
      }
      $newContracts[] = new Contract(-1, $assocArray[$playerKey], $this->team->getId(), $numYears,
          $assocArray[$priceKey], TimeUtil::getTodayString(), $currentYear, ($currentYear + $numYears) - 1, false,
          $contractType);

      SessionUtil::updateSession($playerKey, $assocArray, $isPost);
      SessionUtil::updateSession($yearKey, $assocArray, $isPost);
      SessionUtil::updateSession($priceKey, $assocArray, $isPost);
    }

    SessionUtil::updateSession($savedKeeperString, $assocArray, $isPost);
    SessionUtil::updateSession($newKeeperString, $assocArray, $isPost);
    return $newContracts;
  }

  public function showKeepersSummary() {
    echo "<h4>Keepers Summary for " . $this->team->getName() . "</h4><hr class='bothr'/>";

    // Team info
    echo "<div class='row-fluid'>
            <div class='span4 center'>";
    echo "<br/>" . $this->team->getSportslineImg(72);
    echo "<br/><p class='ptop'>" . $this->team->getOwnersString() . "</p>";
    echo "  </div>"; // span4

    echo " <div class='span8 center'>";
    // display buyout contracts
    $buyoutBrognas = 0;
    if ($this->buyoutContracts) {
      echo "<h4>Buyout Contract(s):</h4>";
      foreach ($this->buyoutContracts as $contract) {
      	echo $contract->getBuyoutDetails() . "<br/>";
      	$buyoutBrognas += $contract->getBuyoutPrice();
      }
    }

    // display new contracts
    $newContractBrognas = 0;
    if ($this->newContracts) {
      echo "<h4>New Contract(s):</h4>";
      foreach ($this->newContracts as $contract) {
        echo $contract->getDetails() . "<br/>";
        $newContractBrognas += $contract->getPrice();
      }
    }

    // display ping pong balls
    $pingPongBrognas = 0;
    if ($this->pingPongBalls && (count($this->pingPongBalls) > 0)) {
      echo "<h4>Ping Pong Ball(s):</h4>";
      foreach ($this->pingPongBalls as $pingPongBall) {
    	echo "$" . $pingPongBall->getCost() . "<br/>";
    	$pingPongBrognas += $pingPongBall->getCost();
      }
    }
    echo "<br/></div>"; // span8
    echo "</div>"; // row-fluid

    // display summary
    echo "<div class='row-fluid'>
            <div class='span12 center'>";
    $currentYear = TimeUtil::getCurrentYear();
    $brognas = BrognaDao::getBrognasByTeamAndYear($this->team->getId(), $currentYear);
    echo "<h4>Brognas Breakdown</h4>";
    echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
          <thead><tr><th></th><th>Price</th></tr></thead>";
    echo "<tr><td><strong>" . $currentYear . " Brognas</strong></td>
              <td><strong>" . $brognas->getTotalPoints() . "</strong></td></tr>";
    echo "<tr><td>Buyout Contracts</td><td>" . $buyoutBrognas . "</td></tr>";
    echo "<tr><td>New Contracts</td><td>" . $newContractBrognas . "</td></tr>";
    echo "<tr><td>Ping Pong Balls</td><td>" . $pingPongBrognas . "</td></tr>";

    $totalBrognas = $brognas->getTotalPoints() -
        ($buyoutBrognas + $newContractBrognas + $pingPongBrognas);
    echo "<tr><td><strong>Leftover Brognas</strong></td><td><strong>" . $totalBrognas .
        "</strong></td></tr></table>";
    echo "  </div>"; // span12
    echo "</div>";   // row-fluid
  }

  public function validateKeepers() {
  	$totalBrognasSpent = 0;

	// validate buyout contracts
  	if ($this->buyoutContracts) {
	  foreach ($this->buyoutContracts as $contract) {
	  	if (($contract->getType() == Contract::AUCTION_TYPE) || $contract->isBoughtOut()) {
		  $this->printError("Cannot buy out contract: " . $contract->getBuyoutDetails());
	  	  return false;
	  	}
	  	$totalBrognasSpent += $contract->getBuyoutPrice();
	  }
  	}

	// validate new contracts
  	if ($this->newContracts) {
	  foreach ($this->newContracts as $contract) {
	  	if ($contract->getPlayer() == null) {
		  $this->printError("Invalid player id for contract: " . $contract->getPlayerId());
	  	  return false;
	  	} else if (!is_numeric($contract->getTotalYears()) || $contract->getTotalYears() < 1
	  	    || $contract->getTotalYears() > 2) {
		  $this->printError(
		      "Invalid length of contract: " . $contract->getTotalYears() . " years");
	  	  return false;
	  	} else if (!is_numeric($contract->getPrice()) ||
	  	    ($contract->getPrice() < 30 && $contract->getType() == Contract::KEEPER_TYPE) ||
	  	    ($contract->getPrice() != 0 && $contract->getType() == Contract::MINOR_KEEPER_TYPE)) {
	  	  $this->printError("Invalid price for " . $contract->getType() . " contract: $" .
	  	      $contract->getPrice());
	  	  return false;
	  	}
	  	$totalBrognasSpent += $contract->getPrice();
	  }
  	}

  	// validate ping pong ball values
  	if ($this->pingPongBalls && (count($this->pingPongBalls) > 0)) {
  	  foreach ($this->pingPongBalls as $pingPongBall) {
  	  	$cost = $pingPongBall->getCost();
  	  	if (!is_numeric($cost) || $cost < 100) {
    	  $this->printError("Cannot spend an invalid number of brognas on a ball: " .
    	      $cost);
  	  	  return false;
  	  	}
  	  	$totalBrognasSpent += $cost;
  	  }
  	}

  	// confirm team has enough money to spend on contracts & balls.
  	$currentYear = TimeUtil::getCurrentYear();
  	$brognas = BrognaDao::getBrognasByTeamAndYear($this->team->getId(), $currentYear);
  	if ($brognas->getTotalPoints() < $totalBrognasSpent) {
  	  $this->printError($this->team->getName() . " cannot spend " . $totalBrognasSpent .
  	      " brognas on contracts & balls; only has " . $brognas->getTotalPoints() .
  		  " total brognas");
  	  return false;
  	}
  	return true;
  }

  public function saveKeepers() {
    echo "<h4>Keepers Saved for " . $this->team->getName() . "</h4><hr class='bothr'/>";

    // Team info
    echo "<div class='row-fluid'>
            <div class='span4 center'>";
    echo "<br/>" . $this->team->getSportslineImg(72);
    echo "<br/><p class='ptop'>" . $this->team->getOwnersString() . "</p>";
    echo "  </div>"; // span4

    echo " <div class='span8 center'>
           <h4>Summary</h4>";
  	$totalBrognasSpent = 0;

  	// buyout contracts
  	if ($this->buyoutContracts) {
  	  foreach ($this->buyoutContracts as $contract) {
  	    $contract->buyOut();
  	    ContractDao::updateContract($contract);
  	    echo "<strong>Bought out:</strong> " . $contract->getBuyoutDetails() . "<br/>";
  	  	$totalBrognasSpent += $contract->getBuyoutPrice();

  	  	// update changelog
  	  	ChangelogDao::createChange(new Changelog(-1, Changelog::BUYOUT_CONTRACT_TYPE,
  	  	    SessionUtil::getLoggedInUser()->getId(), TimeUtil::getTimestampString(),
  	  	    $contract->getId(), $this->team->getId(), null));
  	  }
  	}

  	// create new contracts
  	if ($this->newContracts) {
  	  foreach ($this->newContracts as $contract) {
  	    if (ContractDao::createContract($contract) != null) {
     	  echo "<strong>Signed:</strong> " . $contract->getDetails() . "<br/>";
  	      $totalBrognasSpent += $contract->getPrice();

  	      // update changelog
  	      ChangelogDao::createChange(new Changelog(-1, Changelog::CONTRACT_TYPE,
  	          SessionUtil::getLoggedInUser()->getId(), TimeUtil::getTimestampString(),
  	          $contract->getId(), $this->team->getId(), null));
  	    }
  	  }
  	}

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
	    ", from " . $originalTotalPoints . " to " . $brognas->getTotalPoints() . "<br/><br/>";

	echo "</div>"; // span8
    echo "</div>"; // row-fluid

	// TODO update changelog
  }

  private function printError($errorString) {
    echo "<br/><div class='alert alert-error'><strong>Error: </strong>" . $errorString . "</div>";
  }
}
?>