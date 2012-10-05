<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'contract.php');

class ContractDao {
  /**
   * Returns all of the non-bought-out contracts for the specified team ID.
   */
  static function getContractsByTeamId($teamId) {
    CommonDao::connectToDb();
    $query = "select * from contract
              where team_id = " . $teamId . " and is_bought_out = 0" .
              " order by sign_date, end_year, price DESC";
    return ContractDao::createContractsFromQuery($query);
  }

  /**
   * Returns the contract by the specified id.
   */
  public static function getContractById($contractId) {
    CommonDao::connectToDb();
    $query = "select * from contract
              where contract_id = " . $contractId;
    $contracts = ContractDao::createContractsFromQuery($query);
    return $contracts[0];
  }

  private static function createContractsFromQuery($query) {
  	$res = mysql_query($query);
    $contracts = array();
    while ($contractDb = mysql_fetch_assoc($res)) {
      $contracts[] = new Contract($contractDb["contract_id"], $contractDb["player_id"],
          $contractDb["team_id"], $contractDb["num_years"], $contractDb["price"],
          $contractDb["sign_date"], $contractDb["start_year"], $contractDb["end_year"],
          $contractDb["is_auction"], $contractDb["is_bought_out"]);
    }
    return $contracts;
  }

  /**
   * Creates a new contract.
   */
  public static function createContract(Contract $contract) {
    CommonDao::connectToDb();
    $query = "insert into contract(player_id, team_id, num_years, price, sign_date, start_year,
              end_year, is_auction, is_bought_out) values (" .
              $contract->getPlayer()->getId() . ", " . $contract->getTeam()->getId() . ", " .
              $contract->getTotalYears() . ", " . $contract->getPrice() . ", '" .
              $contract->getSignDate() . "', " . $contract->getStartYear() . ", " .
              $contract->getEndYear() . ", " . ($contract->isAuction() ? "1" : "0") .
              ($contract->isBoughtOut() ? "1" : "0") . ")";
    $result = mysql_query($query);
    if (!$result) {
      echo "Contract " . $contract->toString() . " already exists in DB. Try again.";
      return null;
    }

    $idQuery = "select contract_id from contract where player_id = " .
        $contract->getPlayer()->getId() . " and team_id = " . $contract->getTeam()->getId() . 
        " and start_year = " . $contract->getStartYear();
    $result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
    $row = mysql_fetch_assoc($result);
    $contract->setId($row["contract_id"]);
    return $contract;
  }

  /**
   * Updates the specified contract.
   */
  public static function updateContract(Contract $contract) {
    CommonDao::connectToDb();
    $query = "update contract set player_id = '" . $contract->getPlayer()->getId() . "',
                                  team_id = '" . $contract->getTeam()->getId() . "',
                                  num_years = " . $contract->getTotalYears() . ",
                                  price = " . $contract->getPrice() . ",
                                  sign_date = '" . $contract->getSignDate() . "',
                                  start_year = " . $contract->getStartYear() . ",
                                  end_year = " . $contract->getEndYear() . ",
                                  is_auction = " . ($contract->isAuction() ? "1" : "0") . ", 
                                  is_bought_out = " . ($contract->isBoughtOut() ? "1" : "0") .
             " where contract_id = " . $contract->getId();
    $result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }
}
?>