<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'playerDao.php');

/**
 * Represents a contract.
 */
class Contract {
  private $contractId;
  private $totalYears;
  private $price;
  private $signDate;
  private $startYear;
  private $endYear;
  private $isAuction;

  private $playerId;
  private $playerLoaded;
  private $player;

  private $teamId;
  private $teamLoaded;
  private $team;

  public function __construct($contractId, $playerId, $teamId, $totalYears, $price, $signDate,
      $startYear, $endYear, $isAuction) {
    $this->contractId = $contractId;
    $this->playerId = $playerId;
    $this->playerLoaded = false;
    $this->teamId = $teamId;
    $this->teamLoaded = false;
    $this->totalYears = $totalYears;
    $this->price = $price;
    $this->signDate = $signDate;
    $this->startYear = $startYear;
    $this->endYear = $endYear;
    $this->isAuction = $isAuction;
  }

  public function getId() {
    return $this->contractId;
  }

  public function setId($contractId) {
  	$this->contractId = $contractId;
  }
  
  public function getTotalYears() {
    return $this->totalYears;
  }

  public function getPrice() {
    return $this->price;
  }

  public function getSignDate() {
    return $this->signDate;
  }

  public function getPlayer() {
    if ($this->playerLoaded != true) {
      $this->player = PlayerDao::getPlayerById($this->playerId);
      $this->playerLoaded = true;
    }
    return $this->player;
  }

  public function getStartYear() {
    return $this->startYear;
  }

  public function getEndYear() {
    return $this->endYear;
  }

  public function isAuction() {
  	return $this->isAuction;
  }
  
  public function getTeam() {
    if ($this->teamLoaded != true) {
      $this->team = TeamDao::getTeamById($this->teamId);
      $this->teamLoaded = true;
    }
    return $this->team;
  }

  public function setTeam(Team $team) {
    $this->team = $team;
    $this->teamLoaded = true;
  }
  
  public function toString() {
  	return $this->player->getFullName() . ": " . $this->team->getName() . " [" . $this->startYear .
  	    ":" . $this->endYear . "] - $" . $this->price . " (" . $this->signDate . ")";
  }
}
?>