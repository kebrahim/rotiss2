<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'playerDao.php');
CommonEntity::requireFileIn('/../util/', 'time.php');

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
  private $isBoughtOut;

  private $playerId;
  private $playerLoaded;
  private $player;

  private $teamId;
  private $teamLoaded;
  private $team;

  public function __construct($contractId, $playerId, $teamId, $totalYears, $price, $signDate,
      $startYear, $endYear, $isAuction, $isBoughtOut) {
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
    $this->isBoughtOut = $isBoughtOut;
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

  public function getYearsLeft() {
  	$currentYear = TimeUtil::getCurrentYear();
  	$yearsLeft = ($this->getEndYear() - $currentYear) + 1;
  	return ($yearsLeft > 0) ? $yearsLeft : 0;
  }

  public function getPrice() {
    return $this->price;
  }

  public function getSignDate() {
    return $this->signDate;
  }

  public function getPlayerId() {
    return $this->playerId;
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

  public function isBoughtOut() {
  	return $this->isBoughtOut;
  }

  public function buyOut() {
  	$this->isBoughtOut = true;
  }

  /**
   * Returns the cost for buying out this contract.
   */
  public function getBuyoutPrice() {
  	return intval(($this->getPrice() * $this->getYearsLeft()) / 2);
  }

  /**
   * Return a string representation of a bought out contract.
   */
  public function getBuyoutContractString() {
  	return $this->getPlayer()->getFullName() . ": " . $this->getYearsLeft() .
  	    " year(s) remaining at $" . $this->getPrice() . " - Buyout price: $" .
  	    $this->getBuyoutPrice();
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

  public function getKeeperString() {
    return $this->getPlayer()->getFullName() . " (" . $this->getPlayer()->getPositionString() .
        ") " . $this->getPlayer()->getMlbTeam()->getAbbreviation() . ": " . $this->getYearsLeft() .
        " year(s) at $" . $this->getPrice() . " [" . $this->getStartYear() . " - " .
        $this->getEndYear() . "]";
  }

  public function toString() {
  	return $this->getPlayer()->getFullName() . ": " . $this->getYearsLeft() .
        " year(s) at $" . $this->getPrice() . " [" . $this->getStartYear() . " - " .
        $this->getEndYear() . "]";
  }
}
?>