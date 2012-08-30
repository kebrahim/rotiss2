<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'ballDao.php');
CommonEntity::requireFileIn('/../dao/', 'brognaDao.php');
CommonEntity::requireFileIn('/../dao/', 'contractDao.php');
CommonEntity::requireFileIn('/../dao/', 'draftPickDao.php');
CommonEntity::requireFileIn('/../dao/', 'userDao.php');
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
  }

  public function showTradeSummary() {
    echo "<h2>Trade Summary:</h2>";
    $this->tradePartner1->showSummary();
    $this->tradePartner2->showSummary();
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

  private function parseTradePartnerFromPost($teamId) {
    $tradePartner = new TradePartner($teamId);

    // process contracts
    $contractStr = 't' . $teamId . 'c';
    if (isset($_POST[$contractStr]) && is_array($_POST[$contractStr])) {
      $contracts = array();
      foreach ($_POST[$contractStr] as $contractId) {
        $contracts[] = ContractDao::getContractById($contractId);
      }
      $tradePartner->setContracts($contracts);
    }

    // brognas
    $brognaStr = 't' . $teamId . 'b';
    if (isset($_POST[$brognaStr])) {
      $brognaYear = $_POST[$brognaStr];
      // Ensure year is next year
      $currentYear = TimeUtil::getCurrentSeasonYear();
      if ($brognaYear != ($currentYear + 1)) {
        die("Invalid brogna year to trade " . $brognaYear);
      }

      // Get # of brognas traded.
      $numBrognasStr = 't' . $teamId . 'bv';
      if (($_POST[$numBrognasStr] != null) && ($_POST[$numBrognasStr] != "")) {
        $numBrognas = $_POST[$numBrognasStr];
      } else {
        $numBrognas = "0";
      }
      $tradePartner->setBrognas($numBrognas);
    }

    // draft picks
    $draftPickStr = 't' . $teamId . 'dpp';
    if (isset($_POST[$draftPickStr]) && is_array($_POST[$draftPickStr])) {
      $draftPicks = array();
      foreach ($_POST[$draftPickStr] as $draftPickId) {
        // Retrieve draft pick from DB.
        $draftPicks[] = DraftPickDao::getDraftPickById($draftPickId);
      }
      $tradePartner->setDraftPicks($draftPicks);
    }

    // pingpong balls
    $pingPongStr = 't' . $teamId . 'dpb';
    if (isset($_POST[$pingPongStr]) && is_array($_POST[$pingPongStr])) {
      $pingPongBalls = array();
      foreach ($_POST[$pingPongStr] as $pingPongBallId) {
        // Retrieve pingpong ball from DB.
        $pingPongBalls[] = BallDao::getPingPongBallById($pingPongBallId);
      }
      $tradePartner->setPingPongBalls($pingPongBalls);
    }
    return $tradePartner;
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
    echo "<h3>" . $this->team->getName() . " trades:</h3><ul>";

    // display contracts
    if ($this->contracts) {
      echo "<li>Contracts<ul>";
      foreach ($this->contracts as $contract) {
        echo "<li>" . $contract->getPlayer()->getFullName() . "</li>";
      }
      echo "</ul></li>";
    }

    // display brognas
    if ($this->brognas != null) {
      echo "<li>Brognas - " . $this->brognas . "</li>";
    }

    // display picks
    if ($this->draftPicks) {
      echo "<li>Draft Picks<ul>";
      foreach ($this->draftPicks as $draftPick) {
        echo "<li>" . $draftPick->toString() . "</li>";
      }
      echo "</ul></li>";
    }

    // display ping pong balls
    if ($this->pingPongBalls) {
      echo "<li>Ping Pong Balls<ul>";
      foreach ($this->pingPongBalls as $pingPongBall) {
        echo "<li>" . $pingPongBall->toString() . "</li>";
      }
      echo "</ul></li>";
    }
    echo "</ul>";
  }

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

      $nextYear = TimeUtil::getCurrentSeasonYear() + 1;
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
      $nextYear = TimeUtil::getCurrentSeasonYear() + 1;
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