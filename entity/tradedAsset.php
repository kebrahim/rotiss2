<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'ballDao.php');
CommonEntity::requireFileIn('/../dao/', 'contractDao.php');
CommonEntity::requireFileIn('/../dao/', 'draftPickDao.php');
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');

/**
 * Represents an asset traded between two teams, which can be either a contract, a
 * non-zero amount of brognas, a draft pick or a ping pong ball.
 */
class TradedAsset {
  private $tradedAssetId;
  private $tradeId;

  private $tradingTeamId;
  private $tradingTeamLoaded = false;
  private $tradingTeam;

  private $assetType;

  private $assetId;
  private $assetLoaded = false;
  private $asset;

  const BROGNAS = 'Brognas';
  const CONTRACT = 'Contract';
  const DRAFT_PICK = 'Draft Pick';
  const PING_PONG_BALL = 'Ping Pong Ball';

  public function __construct($tradedAssetId, $tradeId, $tradingTeamId, $assetType, $assetId) {
    $this->tradedAssetId = $tradedAssetId;
    $this->tradeId = $tradeId;
    $this->tradingTeamId = $tradingTeamId;
    $this->assetType = $assetType;
    $this->assetId = $assetId;
  }

  public function getId() {
    return $this->tradedAssetId;
  }

  public function setId($tradedAssetId) {
    $this->tradedAssetId = $tradedAssetId;
  }

  public function getTradeId() {
    return $this->tradeId;
  }

  public function getTradingTeamId() {
    return $this->tradingTeamId;
  }

  public function getTradingTeam() {
    if ($this->tradingTeamLoaded != true) {
      $this->tradingTeam = TeamDao::getTeamById($this->tradingTeamId);
      $this->tradingTeamLoaded = true;
    }
    return $this->tradingTeam;
  }

  public function getAssetType() {
    return $this->assetType;
  }

  public function getAssetId() {
    return $this->assetId;
  }

  public function getAsset() {
    if ($this->assetLoaded != true) {
      switch ($this->assetType) {
        case TradedAsset::BROGNAS: {
          // brognas are just a simple number stored in assetId.
          $this->asset = $this->assetId;
          break;
        }
        case TradedAsset::CONTRACT: {
          $this->asset = ContractDao::getContractById($this->assetId);
          break;
        }
        case TradedAsset::DRAFT_PICK: {
          $this->asset = DraftPickDao::getDraftPickById($this->assetId);
          break;
        }
        case TradedAsset::PING_PONG_BALL: {
          $this->asset = BallDao::getPingPongBallById($this->assetId);
          break;
        }
        default: {
          return null;
        }
      }
      $this->assetLoaded = true;
    }
    return $this->asset;
  }

  public function __toString() {
    $asset = $this->getAsset();
    switch($this->assetType) {
      case TradedAsset::BROGNAS: {
        return $this->asset . " " . TradedAsset::BROGNAS;
      }
      case TradedAsset::CONTRACT: {
        return $this->asset->getPlayer()->getNameLink(false);
      }
      case TradedAsset::DRAFT_PICK: {
        return TradedAsset::DRAFT_PICK . " " . $this->asset;
      }
      case TradedAsset::PING_PONG_BALL: {
        return TradedAsset::PING_PONG_BALL . " " . $this->asset;
      }
      default: {
        return null;
      }
    }
  }
}