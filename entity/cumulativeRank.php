<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'playerDao.php');

/**
 * Represents a sum of ranks for a particular player in a given year;
 * corresponds to the 'cumulative_rank' table.
 */
class CumulativeRank {
  const MINIMUM_RANK = 30;

  private $rankId;
  private $year;
  private $playerId;
  private $playerLoaded;
  private $player;
  private $rank;
  private $isPlaceholder;
  private $rankCount;

  public function __construct($rankId, $year, $playerId, $rank, $isPlaceholder) {
    $this->rankId = $rankId;
  	$this->year = $year;
    $this->playerId = $playerId;
    $this->playerLoaded = false;
    $this->rank = $rank;
    $this->isPlaceholder = $isPlaceholder;
  }

  public function getId() {
  	return $this->rankId;
  }

  public function setId($rankId) {
  	$this->rankId = $rankId;
  }

  public function getYear() {
    return $this->year;
  }

  public function getPlayer() {
    if ($this->playerLoaded != true) {
      $this->player = PlayerDao::getPlayerById($this->playerId);
      $this->playerLoaded = true;
    }
    return $this->player;
  }

  public function getPlayerId() {
    return $this->playerId;
  }

  public function setPlayer(Player $player) {
    $this->player = $player;
    $this->playerId = $player->getId();
    $this->playerLoaded = true;
  }

  public function getRank() {
  	return $this->rank;
  }

  public function setRank($rank) {
  	$this->rank = $rank;
  }

  public function isPlaceholder() {
  	return $this->isPlaceholder;
  }

  public function setRankCount($rankCount) {
    $this->rankCount = $rankCount;
  }

  public function getRankCount() {
    return $this->rankCount;
  }

  public function toString() {
    return $this->year . "/" . $this->getPlayer()->getFullName() . "/" . $this->rank;
  }
}
?>