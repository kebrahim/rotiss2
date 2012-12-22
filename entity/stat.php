<?php
require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'playerDao.php');
CommonEntity::requireFileIn('/../entity/', 'statline.php');

/**
 * Represents a set of fantasy statistics for a single player in a single year.
 */
class Stat {
  private $statId;
  private $year;
  private $playerId;
  private $player;
  private $playerLoaded = false;
  private $statLine;
  
  public function __construct($statId, $year, $playerId, StatLine $statLine) {
  	$this->statId = $statId;
  	$this->year = $year;
  	$this->playerId = $playerId;
  	$this->statLine = $statLine;
  }
  
  public function getId() {
  	return $this->statId;
  }
  
  public function setId($statId) {
  	$this->statId = $statId;
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
  
  public function getStatLine() {
  	return $this->statLine;
  }
  
  public function toString() {
  	return $this->getYear() . ": " . $this->getPlayerId() . " - " . 
  	    $this->getStatLine()->toString();
  }
}
?>