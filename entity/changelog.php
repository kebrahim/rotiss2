<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'auctionDao.php');
CommonEntity::requireFileIn('/../dao/', 'contractDao.php');
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');
CommonEntity::requireFileIn('/../dao/', 'tradeDao.php');
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

  private $secondaryTeamId;
  private $secondaryTeamLoaded = false;
  private $secondaryTeam;

  const AUCTION_TYPE = 'Auction';
  const TRADE_TYPE = 'Trade';
  const CONTRACT_TYPE = 'Contract';
  const BUYOUT_CONTRACT_TYPE = 'Buyout Contract';
  const PING_PONG_BALL_TYPE = 'Ping Pong Ball';
  const BANK_TYPE = 'Bank Money';

  public function __construct($changelogId, $changeType, $userId, $timestamp, $changeId,
      $teamId, $secondaryTeamId) {
  	$this->changelogId = $changelogId;
  	$this->changeType = $changeType;
  	$this->userId = $userId;
  	$this->timestamp = $timestamp;
  	$this->changeId = $changeId;
  	$this->teamId = $teamId;
  	$this->secondaryTeamId = $secondaryTeamId;
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
  	      break;
  	    }
  	    case Changelog::TRADE_TYPE: {
  	      $this->change = TradeDao::getTradeById($this->changeId);
  	      break;
  	    }
  	    case Changelog::BUYOUT_CONTRACT_TYPE:
  	    case Changelog::CONTRACT_TYPE: {
  	      $this->change = ContractDao::getContractById($this->changeId);
  	      break;
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

  public function getSecondaryTeamId() {
    return $this->secondaryTeamId;
  }

  public function getSecondaryTeam() {
    if ($this->secondaryTeamId == null) {
      return null;
    } else if ($this->secondaryTeamLoaded != true) {
      $this->secondaryTeam = TeamDao::getTeamById($this->secondaryTeamId);
      $this->secondaryTeamLoaded = true;
    }
    return $this->secondaryTeam;
  }

  public function getDetails() {
    $change = $this->getChange();
    switch($this->changeType) {
      case Changelog::AUCTION_TYPE: {
        return $change->getPlayer()->getNameLink(false) . " - $" . $change->getCost();
      }
      case Changelog::TRADE_TYPE: {
        $assets = TradeDao::getTradedAssetsByTradeAndTeam($change->getId(), $this->teamId);
        $tradeDetails = "<strong>Trades: </strong>";
        $firstAsset = true;
        foreach ($assets as $asset) {
          if ($firstAsset) {
            $firstAsset = false;
          } else {
            $tradeDetails .= ", ";
          }
          $tradeDetails .= $asset;
        }
        return $tradeDetails;
      }
      case Changelog::BUYOUT_CONTRACT_TYPE: {
        return $change->getBuyoutDetails();
      }
      case Changelog::CONTRACT_TYPE: {
        return $change->getDetails();
      }
      // TODO add support for other change types
      default: {
        return null;
      }
    }
  }

  public function toString() {
  	return $this->changeType . " by " . $this->getUser()->getUsername() . " at " .
  	    $this->timestamp . " - " . $this->getChange()->toString();
  }
}
?>