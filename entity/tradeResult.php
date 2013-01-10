<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');

/**
 * Represents an executed trade.
 */
class TradeResult {
  private $tradeId;

  private $team1Id;
  private $team1Loaded = false;
  private $team1;

  private $team2Id;
  private $team2Loaded = false;
  private $team2;

  private $timestamp;

  public function __construct($tradeId, $team1Id, $team2Id, $timestamp) {
    $this->tradeId = $tradeId;
    $this->team1Id = $team1Id;
    $this->team2Id = $team2Id;
    $this->timestamp = $timestamp;
  }

  public function getId() {
    return $this->tradeId;
  }

  public function setId($tradeId) {
    $this->tradeId = $tradeId;
  }

  public function getTeam1Id() {
    return $this->team1Id;
  }

  public function getTeam1() {
    if ($this->team1Loaded == false) {
      $this->team1 = TeamDao::getTeamById($this->team1Id);
      $this->team1Loaded = true;
    }
    return $this->team1;
  }

  public function getTeam2Id() {
    return $this->team2Id;
  }

  public function getTeam2() {
    if ($this->team2Loaded == false) {
      $this->team2 = TeamDao::getTeamById($this->team2Id);
      $this->team2Loaded = true;
    }
    return $this->team2;
  }

  public function getTimestamp() {
    return $this->timestamp;
  }

  public function __toString() {
    return "Trade: " . $this->team1Id . ", " . $this->team2Id . "; " . $this->getTimestamp();
  }

  public function toString() {
    return $this->__toString();
  }
}