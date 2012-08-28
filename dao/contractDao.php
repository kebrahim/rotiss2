<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'contract.php');

class ContractDao {
  /**
   * Returns all of the contracts for the specified team ID.
   */
  static function getContractsByTeamId($teamId) {
    CommonDao::connectToDb();
    $query = "select C.contract_id, C.num_years, C.price, C.sign_date, C.player_id, C.start_year,
                     C.end_year, C.team_id
    	      from contract C
              where C.team_id = " . $teamId;
    return ContractDao::createContracts($query);
  }

  /**
   * Returns the contract by the specified id.
   */
  public static function getContractById($contractId) {
    CommonDao::connectToDb();
    $query = "select contract_id, player_id, team_id, num_years, price, sign_date, start_year,
                     end_year
              from contract
              where contract_id = " . $contractId;
    $contracts = ContractDao::createContracts($query);
    return $contracts[0];
  }

  private static function createContracts($query) {
  	$res = mysql_query($query);
    $contracts = array();
    while ($contractDb = mysql_fetch_assoc($res)) {
      $contracts[] = new Contract($contractDb["contract_id"], $contractDb["player_id"],
          $contractDb["team_id"], $contractDb["num_years"], $contractDb["price"],
          $contractDb["sign_date"], $contractDb["start_year"], $contractDb["end_year"]);
    }
    return $contracts;
  }

  /**
   * Creates a new contract.
   */
  public static function createContract(Contract $contract) {
    // TODO
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
                                  end_year = " . $contract->getEndYear() .
             " where contract_id = " . $contract->getId();
    $result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }
}
?>