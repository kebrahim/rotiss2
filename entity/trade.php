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
    $this->tradePartner1 = $this->parseTradePartnerFromPost($_POST['team1id']);
    $this->tradePartner2 = $this->parseTradePartnerFromPost($_POST['team2id']);

    $_SESSION['team1id'] = $_POST['team1id'];
    $_SESSION['team2id'] = $_POST['team2id'];
  }

  public function parseTradeFromSession() {
    $this->tradePartner1 = $this->parseTradePartnerFromSession($_SESSION['team1id']);
    $this->tradePartner2 = $this->parseTradePartnerFromSession($_SESSION['team2id']);

    unset($_SESSION['team1id']);
    unset($_SESSION['team2id']);
  }

  public function showTradeSummary() {
    echo "<h2>Trade Summary:</h2>";
    echo "<div id='column_container'>
            <div id='left_col'>
              <div id='left_col_inner'>";
    $this->tradePartner1->showSummary();
    echo "    </div>
            </div>
            <div id='right_col'>
              <div id='right_col_inner'>";
    $this->tradePartner2->showSummary();
    echo "    </div>
            </div>
          </div>";
  }

  public function validateTrade() {
    return $this->tradePartner1->validate($this->tradePartner2->getTeam()) &&
        $this->tradePartner2->validate($this->tradePartner1->getTeam());
  }

  public function initiateTrade() {
    // Move stuff to other team & display results.
    $this->tradePartner1->trade($this->tradePartner2->getTeam());
    $this->tradePartner2->trade($this->tradePartner1->getTeam());
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
    $contractStr = 't' . $teamId . 'c';
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
    $brognaStr = 't' . $teamId . 'b';
    if (isset($assocArray[$brognaStr])) {
      $brognaYear = $assocArray[$brognaStr];
      // Ensure year is next year
      $currentYear = TimeUtil::getYearBasedOnKeeperNight();
      if ($brognaYear != ($currentYear + 1)) {
        die("Invalid brogna year to trade " . $brognaYear);
      }

      // Get # of brognas traded.
      $numBrognasStr = 't' . $teamId . 'bv';
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
    $draftPickStr = 't' . $teamId . 'dpp';
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
    $pingPongStr = 't' . $teamId . 'dpb';
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
    echo "<h3>" . $this->team->getName() . " trades:</h3>";

    // display contracts
    if ($this->contracts) {
      echo "<strong>Contracts: </strong>";
      $isFirst = true;
      foreach ($this->contracts as $contract) {
        if ($isFirst) {
          $isFirst = false;
        } else {
          echo ", ";
        }
        echo $contract->getPlayer()->getFullName();
      }
      echo "<br/><br/>";
    }

    // display brognas
    if ($this->brognas != null) {
      echo "<strong>Brognas: </strong>" . $this->brognas . "<br/><br/>";
    }

    // display picks
    if ($this->draftPicks) {
      echo "<strong>Draft Picks: </strong>";
      $isFirst = true;
      foreach ($this->draftPicks as $draftPick) {
        if ($isFirst) {
          $isFirst = false;
        } else {
          echo ", ";
        }
        echo $draftPick->toString();
      }
      echo "<br/><br/>";
    }

    // display ping pong balls
    if ($this->pingPongBalls) {
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
          echo "Error: " . $this->team->getName() . " cannot trade contract for " .
               $contract->getPlayer()->getFullName() . "; contract now belongs to " .
               $contractFromDb->getTeam()->getName() . "<br>";
          return false;
        }
      }
    }

    // validate brognas
    if ($this->brognas != null) {
      // is brognas value numeric and > 0?
      if (!is_numeric($this->brognas) || $this->brognas <= 0) {
        echo "Error: " . $this->team->getName() . " cannot trade an invalid number of brognas: "
             . $this->brognas . "<br>";
        return false;
      }

      $nextYear = TimeUtil::getYearBasedOnKeeperNight() + 1;
      $myBrognas = BrognaDao::getBrognasByTeamAndYear($this->team->getId(), $nextYear);

      // do i have enough total points?
      if ($myBrognas->getTotalPoints() < $this->brognas) {
        echo "Error: " . $this->team->getName() . " cannot trade " . $this->brognas .
             " brognas; only has " . $myBrognas->getTotalPoints() . " total points.<br>";
        return false;
      }
      // do i have enough tradeable points?
      if ($myBrognas->getTradeablePoints() < $this->brognas) {
        echo "Error: " . $this->team->getName() . " cannot trade " . $this->brognas .
             " brognas; only has " . $myBrognas->getTradeablePoints() . " tradeable points.<br>";
        return false;
      }
    }

    // validate draft picks
    if ($this->draftPicks) {
      // do i still own these draft picks?
      foreach ($this->draftPicks as $draftPick) {
        $draftPickFromDb = DraftPickDao::getDraftPickById($draftPick->getId());
        if ($draftPickFromDb->getTeam()->getId() != $this->team->getId()) {
          echo "Error: " . $this->team->getName() . " cannot trade draft pick " .
              $draftPick->toString() . "; pick now belongs to " .
              $draftPickFromDb->getTeam()->getName() . "<br>";
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
          echo "Error: " . $this->team->getName() . " cannot trade pingpong ball " .
              $pingPongBall->toString() . "; ball now belongs to " .
              $pingPongBallFromDb->getTeam()->getName() . "<br>";
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
    // Trade contracts
    if ($this->contracts) {
      foreach ($this->contracts as $contract) {
        $contract->setTeam($otherTeam);
        ContractDao::updateContract($contract);
        echo $this->team->getName() . ": trades " . $contract->getPlayer()->getFullName() . " to "
             . $contract->getTeam()->getName() . "<br>";
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

      echo $this->team->getName() . ": trades " . $this->brognas . " brognas to " .
           $otherTeam->getName() . "<br>";
    }

    // Trade draft picks
    if ($this->draftPicks) {
      foreach ($this->draftPicks as $draftPick) {
        $draftPick->setTeam($otherTeam);
        $draftPick->setOriginalTeam($this->team);
        DraftPickDao::updateDraftPick($draftPick);
        echo $this->team->getName() . ": trades draft pick " . $draftPick->toString() . " to "
             . $draftPick->getTeam()->getName() . "<br>";
      }
    }

    // Trade ping pong balls
    if ($this->pingPongBalls) {
      foreach ($this->pingPongBalls as $pingPongBall) {
        $pingPongBall->setTeam($otherTeam);
        BallDao::updatePingPongBall($pingPongBall);
        echo $this->team->getName() . ": trades ball " . $pingPongBall->toString() . " to "
            . $pingPongBall->getTeam()->getName() . "<br>";
      }
    }

    // TODO update changelog
  }
}
?>