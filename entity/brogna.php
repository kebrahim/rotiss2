<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');

/**
 * Brogna record - includes team, year, total points, tradeable points, banked points from
 * previous year & traded points [in & out].
 */
class Brogna {
  private $teamId;
  private $teamLoaded;
  private $team;

  private $year;
  private $totalPoints;
  private $bankedPoints;
  private $tradedInPoints;
  private $tradedOutPoints;
  private $tradeablePoints;

  public function __construct($teamId, $year, $totalPoints, $bankedPoints, $tradedInPoints,
      $tradedOutPoints, $tradeablePoints) {
    $this->teamId = $teamId;
    $this->teamLoaded = false;
    $this->year = $year;
    $this->totalPoints = $totalPoints;
    $this->bankedPoints = $bankedPoints;
    $this->tradedInPoints = $tradedInPoints;
    $this->tradedOutPoints = $tradedOutPoints;
    $this->tradeablePoints = $tradeablePoints;
  }

  public function getTeam() {
    if ($this->teamLoaded != true) {
      $this->team = TeamDao::getTeamById($this->teamId);
      $this->teamLoaded = true;
    }
    return $this->team;
  }

  public function getYear() {
    return $this->year;
  }

  public function getTotalPoints() {
    return $this->totalPoints;
  }

  public function setTotalPoints($totalPoints) {
    $this->totalPoints = $totalPoints;
  }

  public function getBankedPoints() {
    return $this->bankedPoints;
  }

  public function setBankedPoints($bankedPoints) {
    $this->bankedPoints = $bankedPoints;
  }

  public function getTradedInPoints() {
    return $this->tradedInPoints;
  }

  public function setTradedInPoints($tradedInPoints) {
    $this->tradedInPoints = $tradedInPoints;
  }

  public function getTradedOutPoints() {
    return $this->tradedOutPoints;
  }

  public function setTradedOutPoints($tradedOutPoints) {
    $this->tradedOutPoints = $tradedOutPoints;
  }

  public function getTradeablePoints() {
    return $this->tradeablePoints;
  }

  public function setTradeablePoints($tradeablePoints) {
    $this->tradeablePoints = $tradeablePoints;
  }

  public function toString() {
    return $this->year . ": tp: " . $this->totalPoints . ", b: " . $this->bankedPoints .
           ", tip: " . $this->tradedInPoints . ", top: " . $this->tradedOutPoints .
           ", trp: " . $this->tradeablePoints;
  }
}
?>