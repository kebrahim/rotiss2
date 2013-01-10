<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');
CommonEntity::requireFileIn('/../dao/', 'playerDao.php');

/**
 * Draft pick
 */
class DraftPick {
  private $draftPickId;
  private $teamId;
  private $teamLoaded;
  private $team;
  private $year;
  private $round;
  private $pick;
  private $originalTeamId;
  private $originalTeamLoaded;
  private $originalTeam;
  private $playerId;
  private $playerLoaded;
  private $player;

  const EXTRA_PICK_ROUND_CUTOFF = 5;
  const MAX_EXTRA_PICKS = 3;

  public function __construct($draftPickId, $teamId, $year, $round, $pick, $originalTeamId,
      $playerId) {
    $this->draftPickId = $draftPickId;
    $this->teamId = $teamId;
    $this->year = $year;
    $this->round = $round;
    $this->pick = $pick;
    $this->originalTeamId = $originalTeamId;
    $this->playerId = $playerId;
  }

  public function getId() {
    return $this->draftPickId;
  }

  public function setId($draftPickId) {
    $this->draftPickId = $draftPickId;
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

  public function getYear() {
    return $this->year;
  }

  public function getRound() {
    return $this->round;
  }

  public function getPick() {
    return $this->pick;
  }

  public function setPick($pick) {
  	if ($this->pick == 0) {
  	  $this->pick = null;
  	}
  	$this->pick = $pick;
  }

  public function getOriginalTeamName() {
    if ($this->originalTeamId == null) {
      return "--";
    }
    $this->originalTeam = $this->getOriginalTeam();
    return $this->originalTeam->getAbbreviation();
  }

  public function getOriginalTeam() {
    if ($this->originalTeamId == null) {
      return null;
    }
    if ($this->originalTeamLoaded != true) {
      $this->originalTeam = TeamDao::getTeamById($this->originalTeamId);
      $this->originalTeamLoaded = true;
    }
    return $this->originalTeam;
  }

  public function getOriginalTeamId() {
    if ($this->originalTeamId == null) {
      return "null";
    }
    return $this->getOriginalTeam()->getId();
  }

  public function setOriginalTeam(Team $team) {
    $this->originalTeam = $team;
    $this->originalTeamId = $team->getId();
    $this->originalTeamLoaded = true;
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

  public function getPlayerName() {
    if ($this->playerId == null) {
      return "--";
    }
    return $this->getPlayer()->getNameLink(true);
  }

  public function __toString() {
    return $this->year . ":" . $this->round . ":" . $this->pick;
  }

  public function toString() {
    return $this->__toString();
  }
}
?>