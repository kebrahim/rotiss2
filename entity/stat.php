<?php
require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'playerDao.php');

/**
 * Represents a set of fantasy statistics for a single player in a single year.
 */
// TODO add rest of stats
class Stat {
  private $statId;
  private $year;
  private $playerId;
  private $player;
  private $playerLoaded = false;
  private $fantasyPts;
  
  public function __construct($statId, $year, $playerId, $fantasyPts) {
  	$this->statId = $statId;
  	$this->year = $year;
  	$this->playerId = $playerId;
  	$this->fantasyPts = $fantasyPts;
  }
  
  public function getId() {
  	return $this->statId;
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
  
  public function getFantasyPoints() {
  	return $this->fantasyPts;
  }
}
?>