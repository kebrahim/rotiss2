<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'auctionDao.php');
CommonEntity::requireFileIn('/../dao/', 'brognaDao.php');
CommonEntity::requireFileIn('/../dao/', 'changelogDao.php');
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
  	$this->team = TeamDao::getTeamById($_REQUEST['auction_teamid']);
  	$this->player = PlayerDao::getPlayerById($_REQUEST['auction_playerid']);
  	$this->amount = $_REQUEST['auction_amount'];

  	$_SESSION['auction_teamid'] = $_REQUEST['auction_teamid'];
  	$_SESSION['auction_playerid'] = $_REQUEST['auction_playerid'];
  	$_SESSION['auction_amount'] = $_REQUEST['auction_amount'];
  }

  public function parseAuctionFromSession() {
    $this->team = TeamDao::getTeamById($_SESSION['auction_teamid']);
    $this->player = PlayerDao::getPlayerById($_SESSION['auction_playerid']);
    $this->amount = $_SESSION['auction_amount'];

    unset($_SESSION['auction_teamid']);
    unset($_SESSION['auction_playerid']);
    unset($_SESSION['auction_amount']);
  }

  public function showAuctionSummary() {
    echo "<h3>Auction Summary</h3>";
    echo "<div class='row-fluid'>
            <div class='span12 center'><br/>";
    echo "    <div class='row-fluid'>
                <div class='span6 center auctionsummary'>";
    $this->player->displayPlayerInfo();
    echo "        <br/>";
    echo "<p class='ptop'>" . $this->player->getPositionString() . " (" .
         $this->player->getMlbTeam()->getAbbreviation() . ")</p>";
    echo "      </div>
                <div class='span6 center auctionsummary'>";
    $this->team->displayTeamInfo();
    echo "      </div>
              </div>";
    echo "<hr class='bothr'/>";
    echo "<label>Auction amount:</label> $" . $this->amount . "<br/><br/>";
    echo "  </div>
          </div>";
  }

  public function validateAuction() {
  	// confirm amount is a valid numeric value > 0.
  	if (!is_numeric($this->amount) || $this->amount <= 0) {
  	  $this->printError($this->team->getName() .
  	      " cannot spend an invalid number of brognas: " . $this->amount);
  	  return false;
  	}

  	return true;
  }

  public function initiateAuction() {
  	echo "<h3 class='conf_msg'>Auction Completed!</h3>";
  	echo "<div class='row-fluid'>
  	        <div class='span12 center'><br/>";
  	echo "    <div class='row-fluid'>
  	            <div class='span6 center auctionsummary'>";
  	$this->player->displayPlayerInfo();
  	echo "        <br/>";
  	echo "<p class='ptop'>" . $this->player->getPositionString() . " (" .
  			$this->player->getMlbTeam()->getAbbreviation() . ")</p>";
  	echo "      </div>
  	            <div class='span6 center auctionsummary'>";
  	$this->team->displayTeamInfo();
  	echo "      </div>
  	          </div>";
  	echo "<hr class='bothr'/>";

  	$auction = $this->saveAuctionResult();
  	$this->saveAuctionContract();
  	$this->updateBrognas();

  	echo "<br/></div>
  	      </div>";

  	// Update changelog
  	$change = new Changelog(-1, Changelog::AUCTION_TYPE, SessionUtil::getLoggedInUser()->getId(),
        TimeUtil::getTimestampString(), $auction->getId(), $this->team->getId(), null);
  	ChangelogDao::createChange($change);
  }

  /**
   * Creates and inserts AuctionResult into db.
   */
  private function saveAuctionResult() {
    $currentYear = TimeUtil::getCurrentYear();
    $auctionResult = new AuctionResult(-1, $currentYear, $this->team->getId(),
        $this->player->getId(), $this->amount);
    $auctionResult = AuctionResultDao::createAuctionResult($auctionResult);
    echo "<strong>Auctioned:</strong> " . $this->player->getFullName() . " for " .
        $this->amount . " brognas<br>";
    return $auctionResult;
  }

  /**
   * Creates and inserts a 1-year auction contract for team/player/amount.
   */
  private function saveAuctionContract() {
  	$currentYear = TimeUtil::getCurrentYear();
  	$todayString = TimeUtil::getTodayString();
  	$contract = new Contract(-1, $this->player->getId(), $this->team->getId(), 1, $this->amount,
  	    $todayString, $currentYear, $currentYear, false, Contract::AUCTION_TYPE);
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

  private function printError($errorMsg) {
  	echo "<br/><div class='alert alert-error'><strong>Error: </strong>" . $errorMsg . "</div>";
  }
}
?>