<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'auctionDao.php');
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');
CommonEntity::requireFileIn('/../dao/', 'userDao.php');

/**
 * Represents an individual change made by a user for a specific team. The type of the referenced
 * change depends on the change type.
 */
class Changelog {
  private $changelogId;
  private $changeType;

  private $userId;
  private $userLoaded = false;
  private $user;
  
  private $timestamp;

  private $changeId;
  private $changeLoaded = false;
  private $change;
  
  private $teamId;
  private $teamLoaded = false;
  private $team;
  
  const AUCTION_TYPE = 'Auction';
  const TRADE_TYPE = 'Trade';
  const KEEPER_TYPE = 'Keeper';
	
  public function __construct($changelogId, $changeType, $userId, $timestamp, $changeId,
      $teamId) {
  	$this->changelogId = $changelogId;
  	$this->changeType = $changeType;
  	$this->userId = $userId;
  	$this->timestamp = $timestamp;
  	$this->changeId = $changeId;
  	$this->teamId = $teamId;
  }
  
  public function getId() {
  	return $this->changelogId;
  }
  
  public function setId($changelogId) {
  	$this->changelogId = $changelogId;
  }
  
  public function getType() {
  	return $this->changeType;
  }
  
  public function getUserId() {
  	return $this->userId;
  }
  
  public function getUser() {
  	if ($this->userLoaded != true) {
  	  $this->user = UserDao::getUserById($this->userId);
  	  $this->userLoaded = true;
  	}
  	return $this->user;
  }
  
  public function getTimestamp() {
  	return $this->timestamp;
  }
  
  public function getChangeId() {
  	return $this->changeId;
  }
  
  public function getChange() {
  	if ($this->changeLoaded != true) {
   	  switch($this->changeType) {
  	    case Changelog::AUCTION_TYPE: {
  	  	  $this->change = AuctionResultDao::getAuctionResultById($this->changeId);
  	    }
  	    // TODO add support for other change types
  	    default: {
  	      return null;
  	    }
  	  }
  	  $this->changeLoaded = true;
    }
    return $this->change;
  }
  
  public function getTeamId() {
  	return $this->teamId;
  }
  
  public function getTeam() {
  	if ($this->teamLoaded != true) {
  	  $this->team = TeamDao::getTeamById($this->teamId);
  	  $this->teamLoaded = true;
  	}
  	return $this->team;
  }
  
  public function toString() {
  	return $this->changeType . " by " . $this->getUser()->getUsername() . " at " .
  	    $this->timestamp . " - " . $this->getChange()->toString();
  }
}
?>