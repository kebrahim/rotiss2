<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');
CommonEntity::requireFileIn('/../dao/', 'playerDao.php');

/**
 * Represents an offseason ranking [1-10] of a player by a fantasy team, or a placeholder ranking
 * if that player has or is coming off a contract. 
 */
class Rank {
  private $rankId;
  private $year;
  private $teamId;
  private $playerId;
  private $teamLoaded;
  private $team;
  private $playerLoaded;
  private $player;
  private $rank;
  private $isPlaceholder;
  
  public function __construct($rankId, $year, $teamId, $playerId, $rank, $isPlaceholder) {
    $this->rankId = $rankId;
    $this->year = $year;
    $this->teamId = $teamId;
    $this->playerId = $playerId;
    $this->teamLoaded = false;
    $this->playerLoaded = false;
    $this->rank = $rank;
    $this->isPlaceholder = $isPlaceholder;
  }

  public function getId() {
    return $this->rankId;
  }
  
  public function setId($id) {
  	$this->rankId = $id;
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
  
  public function getTeamId() {
  	return $this->teamId;
  }

  public function setTeam(Team $team) {
    $this->team = $team;
    $this->teamId = $team->getId();
    $this->teamLoaded = true;
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
  
  public function toString() {
    return $this->year . "/" . $this->getTeam()->getAbbreviation() . "/" .
        $this->getPlayer()->getFullName() . "/" . $this->rank;
  }
}
?>