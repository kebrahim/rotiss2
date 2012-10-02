<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'auctionDao.php');
CommonEntity::requireFileIn('/../dao/', 'brognaDao.php');
CommonEntity::requireFileIn('/../dao/', 'contractDao.php');
CommonEntity::requireFileIn('/../entity/', 'auctionResult.php');
CommonEntity::requireFileIn('/../entity/', 'contract.php');
CommonEntity::requireFileIn('/../util/', 'time.php');

/**
 * Represents a rotiss auction.
 */
class Auction {
  private $team;
  private $player;
  private $amount;

  public function parseAuctionFromPost() {
  	$this->team = TeamDao::getTeamById($_REQUEST['teamid']);
  	$this->player = PlayerDao::getPlayerById($_REQUEST['playerid']);
  	$this->amount = $_REQUEST['amount'];

  	$_SESSION['teamid'] = $_REQUEST['teamid'];
  	$_SESSION['playerid'] = $_REQUEST['playerid'];
  	$_SESSION['amount'] = $_REQUEST['amount'];
  }

  public function parseAuctionFromSession() {
    $this->team = TeamDao::getTeamById($_SESSION['teamid']);
    $this->player = PlayerDao::getPlayerById($_SESSION['playerid']);
    $this->amount = $_SESSION['amount'];

    unset($_SESSION['teamid']);
    unset($_SESSION['playerid']);
    unset($_SESSION['amount']);
  }

  public function showAuctionSummary() {
    echo "<h2>Auction Summary</h2>";
    echo "<h3>" . $this->team->getName() . " (" . $this->team->getOwnersString() . ")</h3>";
    echo "<strong>Player: </strong>" . $this->player->getFullName() . ", " .
          $this->player->getPositionString() . " (" .
          $this->player->getMlbTeam()->getAbbreviation() . ")<br/>";
    echo "<strong>Amount: </strong>" . $this->amount . "</br>";
  }

  public function validateAuction() {
  	// confirm amount is a valid numeric value > 0.
  	if (!is_numeric($this->amount) || $this->amount <= 0) {
  	  echo "Error: " . $this->team->getName() . " cannot spend an invalid number of brognas: "
     	  . $this->amount . "<br>";
  	  return false;
  	}

  	// confirm team has enough brognas to cover auction.
  	$currentYear = TimeUtil::getCurrentYear();
  	$brognas = BrognaDao::getBrognasByTeamAndYear($this->team->getId(), $currentYear);
  	if ($brognas->getTotalPoints() < $this->amount) {
  	  echo "Error: " . $this->team->getName() . " cannot spend " . $this->amount .
  	  	  " brognas; only has " . $brognas->getTotalPoints() . " total points.<br>";
  	  return false;
  	}
  	return true;
  }

  public function initiateAuction() {
  	echo "<h2>Auction Confirmed!</h2>";
  	echo "<h3>" . $this->team->getName() . " (" . $this->team->getOwnersString() . ")</h3>";
    $this->saveAuctionResult();
    $this->saveAuctionContract();
  	$this->updateBrognas();

  	// TODO update changelog
  }

  /**
   * Creates and inserts AuctionResult into db.
   */
  private function saveAuctionResult() {
    $currentYear = TimeUtil::getCurrentYear();
    $auctionResult = new AuctionResult(-1, $currentYear, $this->team->getId(),
        $this->player->getId(), $this->amount);
    AuctionResultDao::createAuctionResult($auctionResult);
    echo "<strong>Auctioned:</strong> " . $this->player->getFullName() . " for " .
        $this->amount . " brognas<br>";
  }

  /**
   * Creates and inserts a 1-year auction contract for team/player/amount.
   */
  private function saveAuctionContract() {
  	$currentYear = TimeUtil::getCurrentYear();
  	$todayString = TimeUtil::getTodayString();
  	$contract = new Contract(-1, $this->player->getId(), $this->team->getId(), 1, $this->amount,
  	    $todayString, $currentYear, $currentYear, true);
	ContractDao::createContract($contract);
	echo "<strong>Signed:</strong> a 1-year auction " .
		 "contract [" . $contract->getStartYear() . ":" . $contract->getEndYear() . "] for " .
		 $this->amount . " brognas<br>";
  }

  /**
   * Deduct amount from team's brognas for current year in db
   */
  private function updateBrognas() {
  	$currentYear = TimeUtil::getCurrentYear();
  	$brognas = BrognaDao::getBrognasByTeamAndYear($this->team->getId(), $currentYear);

	// subtract amount from total points and save brognas
	$originalTotalPoints = $brognas->getTotalPoints();
  	$brognas->setTotalPoints($originalTotalPoints - intval($this->amount));
  	BrognaDao::updateBrognas($brognas);
	echo "<strong>" . $currentYear . " Brognas:</strong> reduced by " . $this->amount . ", from " .
		$originalTotalPoints . " to " . $brognas->getTotalPoints() . "<br>";
  }
}
?>