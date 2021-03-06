<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'auctionDao.php');
CommonEntity::requireFileIn('/../dao/', 'ballDao.php');
CommonEntity::requireFileIn('/../dao/', 'brognaDao.php');
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
  const CONTRACT_SIGNED_TYPE = 'Contract Signed';
  const BUYOUT_CONTRACT_TYPE = 'Buyout Contract';
  const PING_PONG_BALL_TYPE = 'Ping Pong Ball';
  const BANK_TYPE = 'Bank Money';
  const CONTRACT_PICKUP_TYPE = 'Contract Pickup';
  const CONTRACT_DROP_TYPE = 'Contract Drop';
  const CONTRACT_PAID_TYPE = 'Contract Paid';

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
  	    case Changelog::CONTRACT_SIGNED_TYPE:
  	    case Changelog::CONTRACT_PICKUP_TYPE:
  	    case Changelog::CONTRACT_DROP_TYPE:
  	    case Changelog::CONTRACT_PAID_TYPE: {
  	      $this->change = ContractDao::getContractById($this->changeId);
  	      break;
  	    }
  	    case Changelog::PING_PONG_BALL_TYPE: {
  	      $this->change = BallDao::getPingPongBallById($this->changeId);
  	      break;
  	    }
  	    case Changelog::BANK_TYPE: {
  	      $this->change = BrognaDao::getBrognasByTeamAndYear($this->teamId, $this->changeId);
  	      break;
  	    }
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
      case Changelog::CONTRACT_SIGNED_TYPE:
      case Changelog::CONTRACT_PICKUP_TYPE:
      case Changelog::CONTRACT_DROP_TYPE:
      case Changelog::CONTRACT_PAID_TYPE: {
        return $change->getDetails();
      }
      case Changelog::PING_PONG_BALL_TYPE: {
        return $change->getDetails();
      }
      case Changelog::BANK_TYPE: {
        return $change->getBankedDetails();
      }
      default: {
        return null;
      }
    }
  }

  public function getEmailDetails() {
    $change = $this->getChange();
    switch($this->changeType) {
      case Changelog::AUCTION_TYPE: {
        return $change->getPlayer()->getAbsoluteNameLink(true) . " - $" . $change->getCost();
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
          $tradeDetails .= $asset->toStringForEmail();
        }
        return $tradeDetails;
      }
      case Changelog::BUYOUT_CONTRACT_TYPE: {
        return $change->getPlayer()->getAbsoluteNameLink(true) . ": " . $change->getYearsLeft() .
            " year(s) remaining @ $" . $change->getPrice() . " - Buyout price: $" .
            $change->getBuyoutPrice();
      }
      case Changelog::CONTRACT_SIGNED_TYPE:
      case Changelog::CONTRACT_PICKUP_TYPE:
      case Changelog::CONTRACT_DROP_TYPE:
      case Changelog::CONTRACT_PAID_TYPE: {
        return "<strong>" . $change->getType() . ": </strong>" .
            $change->getPlayer()->getAbsoluteNameLink(true) .
            " - " . $change->getYearsLeft() . " year(s) @ $" . $change->getPrice() . " [" .
            $change->getStartYear() . " - " . $change->getEndYear() . "]";
      }
      case Changelog::PING_PONG_BALL_TYPE: {
        return $change->getDetails();
      }
      case Changelog::BANK_TYPE: {
        return $change->getBankedDetails();
      }
      default: {
        return null;
      }
    }
  }

  public function getKeeperDetails() {
    $change = $this->getChange();
    switch($this->changeType) {
      case Changelog::BUYOUT_CONTRACT_TYPE: {
        return $change->getPlayer()->getAbsoluteNameLink(true) . " - $" . $change->getBuyoutPrice();
      }
      case Changelog::CONTRACT_SIGNED_TYPE:
      case Changelog::CONTRACT_PAID_TYPE: {
        return $change->getPlayer()->getAbsoluteNameLink(true) .
            " - " . $change->getTotalYears() . "yr/$" . $change->getPrice();
      }
      case Changelog::PING_PONG_BALL_TYPE: {
        return "$" . $change->getCost();
      }
      case Changelog::BANK_TYPE: {
        return "$" . $change->getBankedPoints();
      }
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