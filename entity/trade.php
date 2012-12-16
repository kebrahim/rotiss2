<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'ballDao.php');
CommonEntity::requireFileIn('/../dao/', 'brognaDao.php');
CommonEntity::requireFileIn('/../dao/', 'contractDao.php');
CommonEntity::requireFileIn('/../dao/', 'draftPickDao.php');
CommonEntity::requireFileIn('/../util/', 'time.php');

/**
 * Represents a rotiss trade.
 */
class Trade {
  private $tradePartner1;
  private $tradePartner2;

  public function parseTradeFromPost() {
    $this->tradePartner1 = $this->parseTradePartnerFromPost($_POST['trade_team1id']);
    $this->tradePartner2 = $this->parseTradePartnerFromPost($_POST['trade_team2id']);

    $_SESSION['trade_team1id'] = $_POST['trade_team1id'];
    $_SESSION['trade_team2id'] = $_POST['trade_team2id'];
  }

  public function parseTradeFromSession() {
    $this->tradePartner1 = $this->parseTradePartnerFromSession($_SESSION['trade_team1id']);
    $this->tradePartner2 = $this->parseTradePartnerFromSession($_SESSION['trade_team2id']);

    unset($_SESSION['trade_team1id']);
    unset($_SESSION['trade_team2id']);
  }

  public function showTradeSummary() {
    echo "<h3>Trade Summary</h3>";
    echo "<div class='row-fluid'>
            <div class='span6 center'>";
    $this->tradePartner1->showSummary();
    echo "  </div>
            <div class='span6 center'>";
    $this->tradePartner2->showSummary();
    echo "  </div>
          </div>";
  }

  public function validateTrade() {
    return $this->tradePartner1->validate($this->tradePartner2->getTeam()) &&
        $this->tradePartner2->validate($this->tradePartner1->getTeam());
  }

  public function initiateTrade() {
    // Move stuff to other team & display results.
    echo "<h3 class='conf_msg'>Trade completed!</h3>";
    echo "<div class='row-fluid'>
            <div class='span6 center'>";
    $this->tradePartner1->trade($this->tradePartner2->getTeam());
    echo "  </div>
          <div class='span6 center'>";
    $this->tradePartner2->trade($this->tradePartner1->getTeam());
    echo "  </div>
          </div>";
  }

  /**
   * Parses a TradePartner with the specified team ID from the $_POST array.
   */
  private function parseTradePartnerFromPost($teamId) {
    $tradePartner = new TradePartner($teamId);

    $this->parseContracts($teamId, $_POST, $tradePartner, true);
    $this->parseBrognas($teamId, $_POST, $tradePartner, true);
    $this->parseDraftPicks($teamId, $_POST, $tradePartner, true);
    $this->parsePingPongBalls($teamId, $_POST, $tradePartner, true);

    return $tradePartner;
  }

  /**
   * Parses a TradePartner with the specified team ID from the $_SESSION array.
   */
  private function parseTradePartnerFromSession($teamId) {
    $tradePartner = new TradePartner($teamId);

    $this->parseContracts($teamId, $_SESSION, $tradePartner, false);
    $this->parseBrognas($teamId, $_SESSION, $tradePartner, false);
    $this->parseDraftPicks($teamId, $_SESSION, $tradePartner, false);
    $this->parsePingPongBalls($teamId, $_SESSION, $tradePartner, false);

    return $tradePartner;
  }

  /**
   * Parses contract information for the specified team ID, out of the specified associative array
   * & populates it in the specified trade partner.
   */
  private function parseContracts($teamId, $assocArray, TradePartner $tradePartner, $isPost) {
    $contractStr = 'trade_t' . $teamId . 'c';
    if (isset($assocArray[$contractStr]) && is_array($assocArray[$contractStr])) {
      $contracts = array();
      foreach ($assocArray[$contractStr] as $contractId) {
        $contracts[] = ContractDao::getContractById($contractId);
      }
      $tradePartner->setContracts($contracts);
      if ($isPost) {
        $_SESSION[$contractStr] = $assocArray[$contractStr];
      } else {
        unset($_SESSION[$contractStr]);
      }
    }
  }

  /**
   * Parses brogna information for the specified team ID, out of the specified associative array
   * & populates it in the specified trade partner.
   */
  private function parseBrognas($teamId, $assocArray, TradePartner $tradePartner, $isPost) {
    $brognaStr = 'trade_t' . $teamId . 'b';
    if (isset($assocArray[$brognaStr])) {
      $brognaYear = $assocArray[$brognaStr];
      // Ensure year is next year
      $nextYear = TimeUtil::getYearBasedOnKeeperNight() + 1;
      if ($brognaYear != $nextYear) {
        die("Invalid brogna year to trade " . $brognaYear);
      }

      // Get # of brognas traded.
      $numBrognasStr = 'trade_t' . $teamId . 'bv';
      if (($assocArray[$numBrognasStr] != null) && ($assocArray[$numBrognasStr] != "")) {
        $numBrognas = $assocArray[$numBrognasStr];
      } else {
        $numBrognas = "0";
      }
      $tradePartner->setBrognas($numBrognas);
      if ($isPost) {
        $_SESSION[$brognaStr] = $assocArray[$brognaStr];
        $_SESSION[$numBrognasStr] = $numBrognas;
      } else {
        unset($_SESSION[$brognaStr]);
        unset($_SESSION[$numBrognasStr]);
      }
    }
  }

  /**
   * Parses draft pick information for the specified team ID, out of the specified associative
   * array & populates it in the specified trade partner.
   */
  private function parseDraftPicks($teamId, $assocArray, TradePartner $tradePartner, $isPost) {
    $draftPickStr = 'trade_t' . $teamId . 'dpp';
    if (isset($assocArray[$draftPickStr]) && is_array($assocArray[$draftPickStr])) {
      $draftPicks = array();
      foreach ($assocArray[$draftPickStr] as $draftPickId) {
        // Retrieve draft pick from DB.
        $draftPicks[] = DraftPickDao::getDraftPickById($draftPickId);
      }
      $tradePartner->setDraftPicks($draftPicks);
      if ($isPost) {
        $_SESSION[$draftPickStr] = $assocArray[$draftPickStr];
      } else {
        unset($_SESSION[$draftPickStr]);
      }
    }
  }

  /**
   * Parses ping pong ball information for the specified team ID, out of the specified associative
   * array & populates it in the specified trade partner.
   */
  private function parsePingPongBalls($teamId, $assocArray, TradePartner $tradePartner, $isPost) {
    $pingPongStr = 'trade_t' . $teamId . 'dpb';
    if (isset($assocArray[$pingPongStr]) && is_array($assocArray[$pingPongStr])) {
      $pingPongBalls = array();
      foreach ($assocArray[$pingPongStr] as $pingPongBallId) {
        // Retrieve pingpong ball from DB.
        $pingPongBalls[] = BallDao::getPingPongBallById($pingPongBallId);
      }
      $tradePartner->setPingPongBalls($pingPongBalls);
      if ($isPost) {
        $_SESSION[$pingPongStr] = $assocArray[$pingPongStr];
      } else {
        unset($_SESSION[$pingPongStr]);
      }
    }
  }
}

/**
 * Represents a trade partner, including everything that they will be trading.
 */
class TradePartner {
  private $team;
  private $contracts;
  private $brognas;
  private $draftPicks;
  private $pingPongBalls;

  public function __construct($teamId) {
    $this->team = TeamDao::getTeamById($teamId);
  }

  public function getTeam() {
    return $this->team;
  }

  public function getContracts() {
    return $this->contracts;
  }

  public function setContracts($contracts) {
    $this->contracts = $contracts;
  }

  public function getBrognas() {
    return $this->brognas;
  }

  public function setBrognas($brognas) {
    $this->brognas = $brognas;
  }

  public function getDraftPicks() {
    return $this->draftPicks;
  }

  public function setDraftPicks($draftPicks) {
    $this->draftPicks = $draftPicks;
  }

  public function getPingPongBalls() {
    return $this->pingPongBalls;
  }

  public function setPingPongBalls($pingPongBalls) {
    $this->pingPongBalls = $pingPongBalls;
  }

  public function showSummary() {
    $this->team->displayTeamInfo();
    echo "<hr class='bothr'/><h5>will trade:</h5>";

    // display contracts
    if ($this->contracts) {
      foreach ($this->contracts as $contract) {
        echo "<strong>Contract:</strong> " . $contract->toString() . "<br/>";
      }
    }

    // display brognas
    if ($this->brognas != null) {
      echo "<strong>Brognas:</strong> $" . $this->brognas . "<br/>";
    }

    // display picks
    if ($this->draftPicks) {
  	  foreach ($this->draftPicks as $draftPick) {
        echo "<strong>Draft pick:</strong> " . $draftPick->toString() . "<br/>";
      }
    }

    // display ping pong balls
    if ($this->pingPongBalls) {
  	  foreach ($this->pingPongBalls as $pingPongBall) {
    	echo "<strong>Ping pong ball:</strong> " . $pingPongBall->toString() . "<br/>";
  	  }
    }
    echo "<br/>";
  }

  /**
   * Returns whether a trade with the specified team is valid.
   */
  public function validate(Team $otherTeam) {
    // validate contracts
    if ($this->contracts) {
      // do i still own these contracts?
      foreach ($this->contracts as $contract) {
        $contractFromDb = ContractDao::getContractById($contract->getId());
        if ($contractFromDb->getTeam()->getId() != $this->team->getId()) {
          $this->printError($this->team->getName() . " cannot trade contract for " .
               $contract->getPlayer()->getFullName() . "; contract now belongs to " .
               $contractFromDb->getTeam()->getName());
          return false;
        }
      }
    }

    // validate brognas
    if ($this->brognas != null) {
      // is brognas value numeric and > 0?
      if (!is_numeric($this->brognas) || $this->brognas <= 0) {
        $this->printError($this->team->getName() . 
             " cannot trade an invalid number of brognas: "
             . $this->brognas);
        return false;
      }

      $nextYear = TimeUtil::getYearBasedOnKeeperNight() + 1;
      $myBrognas = BrognaDao::getBrognasByTeamAndYear($this->team->getId(), $nextYear);

      // do i have enough total points?
      if ($myBrognas->getTotalPoints() < $this->brognas) {
        $this->printError($this->team->getName() . " cannot trade " . 
            $this->brognas . " brognas; only has " . $myBrognas->getTotalPoints() . 
        	" total brognas.");
        return false;
      }
      // do i have enough tradeable points?
      if ($myBrognas->getTradeablePoints() < $this->brognas) {
        $this->printError($this->team->getName() . " cannot trade " . 
            $this->brognas . " brognas; only has " . $myBrognas->getTradeablePoints() . 
        	" tradeable brognas.");
        return false;
      }
    }

    // validate draft picks
    if ($this->draftPicks) {
      // do i still own these draft picks?
      foreach ($this->draftPicks as $draftPick) {
        $draftPickFromDb = DraftPickDao::getDraftPickById($draftPick->getId());
        if ($draftPickFromDb->getTeam()->getId() != $this->team->getId()) {
          $this->printError($this->team->getName() . 
              " cannot trade draft pick " . $draftPick->toString() . "; pick now belongs to " .
              $draftPickFromDb->getTeam()->getName());
          return false;
        }
      }
    }

    // validate pingpong balls
    if ($this->pingPongBalls) {
      // do i still own these pingpong balls?
      foreach ($this->pingPongBalls as $pingPongBall) {
        $pingPongBallFromDb = BallDao::getPingPongBallById($pingPongBall->getId());
        if ($pingPongBallFromDb->getTeam()->getId() != $this->team->getId()) {
          $this->printError($this->team->getName() . 
              " cannot trade pingpong ball " . $pingPongBall->toString() . 
          	  "; ball now belongs to " . $pingPongBallFromDb->getTeam()->getName());
          return false;
        }
      }
    }

    return true;
  }

  /**
   * Executes trade with specified team.
   */
  public function trade(Team $otherTeam) {
    $this->team->displayTeamInfo();
    echo "<hr class='bothr'/><h5>traded:</h5>";

    // Trade contracts
    if ($this->contracts) {
      foreach ($this->contracts as $contract) {
        // update team in contract
        $contract->setTeam($otherTeam);
        ContractDao::updateContract($contract);

        // move player to new team
        TeamDao::assignPlayerToTeam($contract->getPlayer(), $otherTeam->getId());

        echo "<strong>Contract: </strong>" . $contract->toString() . "<br>";
      }
    }

    // Trade brognas
    if ($this->brognas != null) {
      $nextYear = TimeUtil::getYearBasedOnKeeperNight() + 1;
      $myBrognas = BrognaDao::getBrognasByTeamAndYear($this->team->getId(), $nextYear);
      // subtract brognas from total points, tradeable_points
      $myBrognas->setTotalPoints($myBrognas->getTotalPoints() - $this->brognas);
      $myBrognas->setTradeablePoints($myBrognas->getTradeablePoints() - $this->brognas);
      // add brognas to traded_out_points
      $myBrognas->setTradedOutPoints($myBrognas->getTradedOutPoints() + $this->brognas);
      // save myBrognas
      BrognaDao::updateBrognas($myBrognas);

      $otherBrognas = BrognaDao::getBrognasByTeamAndYear($otherTeam->getId(), $nextYear);
      // add brognas to total points, tradeable_points, traded_in_points
      $otherBrognas->setTotalPoints($otherBrognas->getTotalPoints() + $this->brognas);
      $otherBrognas->setTradeablePoints($otherBrognas->getTradeablePoints() + $this->brognas);
      $otherBrognas->setTradedInPoints($otherBrognas->getTradedInPoints() + $this->brognas);
      // save otherBrognas
      BrognaDao::updateBrognas($otherBrognas);

      echo "<strong>Brognas:</strong> $" . $this->brognas . "<br>";
    }

    // Trade draft picks
    if ($this->draftPicks) {
      foreach ($this->draftPicks as $draftPick) {
        $draftPick->setTeam($otherTeam);
        $draftPick->setOriginalTeam($this->team);
        DraftPickDao::updateDraftPick($draftPick);
        echo "<strong>Draft pick:</strong> " . $draftPick->toString() . "<br>";
      }
    }

    // Trade ping pong balls
    if ($this->pingPongBalls) {
      foreach ($this->pingPongBalls as $pingPongBall) {
        $pingPongBall->setTeam($otherTeam);
        BallDao::updatePingPongBall($pingPongBall);
        echo "<strong>Ping pong ball:</strong> " . $pingPongBall->toString() . "<br>";
      }
    }
    echo "<br/>";
    // TODO update changelog
  }
  
  private function printError($errorMsg) {
    echo "<br/><div class='alert alert-error'>
            <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button> . 
            <strong>Error: </strong>" . $errorMsg .
         "</div>";
  }
}
?>