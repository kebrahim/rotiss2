<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');
CommonEntity::requireFileIn('/../dao/', 'playerDao.php');

/**
 * Auction result
 */
class AuctionResult {
  private $auctionResultId;
  private $year;
  private $teamId;
  private $teamLoaded;
  private $team;
  private $playerId;
  private $playerLoaded;
  private $player;
  private $cost;
  private $isMatch;

  public function __construct($auctionResultId, $year, $teamId, $playerId, $cost, $isMatch) {
    $this->auctionResultId = $auctionResultId;
    $this->year = $year;
    $this->teamId = $teamId;
    $this->playerId = $playerId;
    $this->cost = $cost;
    $this->isMatch = $isMatch;
  }

  public function getYear() {
    return $this->year;
  }

  public function getTeam() {
    if ($this->teamLoaded != true) {
      $this->team = TeamDao::getTeamById($this->teamId);
      $this->teamLoaded = true;
    }
    return $this->team;
  }

  public function getPlayer() {
    if ($this->playerLoaded != true) {
      $this->player = PlayerDao::getPlayerById($this->playerId);
      $this->playerLoaded = true;
    }
    return $this->player;
  }

  public function getCost() {
    return $this->cost;
  }

  public function isMatch() {
    return $this->isMatch;
  }
}
?>