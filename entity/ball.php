<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');
CommonEntity::requireFileIn('/../dao/', 'playerDao.php');

/**
 * Ping pong ball - represents a draft pick in the 0th round of a draft, which was auctioned at a
 * particular price.
 */
class PingPongBall {
  private $ballId;
  private $year;
  private $cost;
  private $teamId;
  private $playerId;
  private $teamLoaded;
  private $team;
  private $playerLoaded;
  private $player;

  public function __construct($ballId, $year, $cost,  $teamId, $playerId) {
    $this->ballId = $ballId;
    $this->year = $year;
    $this->cost = $cost;
    $this->teamId = $teamId;
    $this->playerId = $playerId;
    $this->teamLoaded = false;
    $this->playerLoaded = false;
  }

  public function getId() {
    return $this->ballId;
  }

  public function setId($id) {
  	$this->ballId = $id;
  }

  public function getYear() {
    return $this->year;
  }

  public function getCost() {
    return $this->cost;
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
    if ($this->playerId == null) {
      return null;
    }
    if ($this->playerLoaded != true) {
      $this->player = PlayerDao::getPlayerById($this->playerId);
      $this->playerLoaded = true;
    }
    return $this->player;
  }

  public function setPlayer(Player $player) {
    $this->player = $player;
    $this->playerId = $player->getId();
    $this->playerLoaded = true;
  }

  public function getPlayerName() {
    if ($this->playerId == null) {
      return "--";
    }
    return $this->getPlayer()->getNameLink(true);
  }

  public function getPlayerId() {
    if ($this->playerId == null) {
      return "null";
    }
    return $this->getPlayer()->getId();
  }

  public function setPlayerId($playerId) {
  	$this->playerId = $playerId;
  	$this->playerLoaded = false;
  	$this->player = null;
  }

  public function __toString() {
    return $this->year . ":" . $this->cost;
  }

  public function toString() {
    return $this->__toString();
  }
}
?>