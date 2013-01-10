<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'tradedAsset.php');
CommonDao::requireFileIn('/../entity/', 'tradeResult.php');

/**
 * DAO for handling trades, specifically in the 'trade' and 'traded_asset' tables.
 */
class TradeDao {

  /**
   * Returns the trade with the specified Id.
   */
  public static function getTradeById($tradeId) {
    CommonDao::connectToDb();
    $query = "select t.*
              from trade t
              where t.trade_id = $tradeId";
    return TradeDao::createTradeFromQuery($query);
  }

  /**
   * Returns the assets traded by the specified team in the specified trade.
   */
  public static function getTradedAssetsByTradeAndTeam($tradeId, $teamId) {
    CommonDao::connectToDb();
    $query = "select a.*
              from traded_asset a
              where a.trade_id = " . $tradeId . "
              and a.trading_team_id = " . $teamId;
    return TradeDao::createTradedAssetsFromQuery($query);
  }

  private static function createTradeFromQuery($query) {
    $tradeArray = TradeDao::createTradesFromQuery($query);
    if (count($tradeArray) == 1) {
      return $tradeArray[0];
    }
    return null;
  }

  private static function createTradesFromQuery($query) {
    $res = mysql_query($query);

    $trades = array();
    while ($tradeDb = mysql_fetch_assoc($res)) {
      $trades[] = new TradeResult($tradeDb["trade_id"], $tradeDb["team1_id"], $tradeDb["team2_id"],
          $tradeDb["timestamp"]);
    }
    return $trades;
  }

  private static function createTradedAssetFromQuery($query) {
    $assetArray = TradeDao::createTradedAssetsFromQuery($query);
    if (count($assetArray) == 1) {
      return $assetArray[0];
    }
    return null;
  }

  private static function createTradedAssetsFromQuery($query) {
    $res = mysql_query($query);

    $assets = array();
    while ($assetDb = mysql_fetch_assoc($res)) {
      $assets[] = new TradedAsset($assetDb["traded_asset_id"], $assetDb["trade_id"],
          $assetDb["trading_team_id"], $assetDb["asset_type"], $assetDb["asset_id"]);
    }
    return $assets;
  }

  /**
   * Creates a new trade result in the 'trade' table.
   */
  public static function createTradeResult(TradeResult $trade) {
    CommonDao::connectToDb();
    $query = "insert into trade(team1_id, team2_id, timestamp) values (" .
        $trade->getTeam1Id() . ", " .
        $trade->getTeam2Id() . ", '" .
        $trade->getTimestamp() . "')";
    $result = mysql_query($query);
    if (!$result) {
  	  echo "Error creating trade in DB: " . $trade . "<br/>";
      return null;
    }

    $idQuery = "select trade_id from trade where team1_id = " . $trade->getTeam1Id() .
        " and team2_id = " . $trade->getTeam2Id() . " and timestamp = '" .
        $trade->getTimestamp() . "'";
    $result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
    $row = mysql_fetch_assoc($result);
    $trade->setId($row["trade_id"]);
    return $trade;
  }

  /**
   * Creates a new traded asset in the 'traded_asset' table.
   */
  public static function createTradedAsset(TradedAsset $asset) {
    CommonDao::connectToDb();
    $query = "insert into traded_asset(trade_id, trading_team_id, asset_type, asset_id) values (" .
        $asset->getTradeId() . ", " .
        $asset->getTradingTeamId() . ", '" .
        $asset->getAssetType() . "', " .
        $asset->getAssetId() . ")";
    $result = mysql_query($query);
    if (!$result) {
      echo "Error creating traded asset in DB: " . $asset . "<br/>";
      return null;
    }

    $idQuery = "select traded_asset_id from traded_asset where trade_id = " . $asset->getTradeId() .
        " and trading_team_id = " . $asset->getTradingTeamId() . " and asset_type = '" .
        $asset->getAssetType() . "' and asset_id = " . $asset->getAssetId();
    $result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
    $row = mysql_fetch_assoc($result);
    $asset->setId($row["traded_asset_id"]);
    return $asset;
  }
}