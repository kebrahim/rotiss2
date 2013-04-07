<?php

require_once 'commonDao.php';
require_once 'playerDao.php';
CommonDao::requireFileIn('/../entity/', 'contract.php');

class ContractDao {

  /**
   * Returns the contract by the specified id.
   */
  public static function getContractById($contractId) {
    return ContractDao::createContractFromQuery(
        "select * from contract
         where contract_id = " . $contractId);
  }

  /**
   * Returns all of the non-bought-out contracts for the specified team ID.
   */
  public static function getContractsByTeamId($teamId) {
    return ContractDao::createContractsFromQuery(
        "select * from contract
         where team_id = " . $teamId . " and is_bought_out = 0" .
       " order by end_year, start_year, price DESC");
  }

  /**
   * Returns all of the non-bought-out contracts for the specified team in the specified year.
   */
  public static function getContractsByTeamYear($teamId, $year) {
    return ContractDao::createContractsFromQuery(
        "select *
         from contract
         where team_id = " . $teamId . "
         and is_bought_out = 0
         and start_year <= $year
         and end_year >= $year
         order by end_year, start_year, price DESC");
  }

  /**
   * Returns the total cost of non-bought-out contracts for the specified team in the specified
   * year.
   */
  public static function getTotalPriceByTeamYear($teamId, $year) {
    return CommonDao::getIntegerValueFromQuery(
             "select sum(price)
              from contract
              where team_id = " . $teamId . "
              and is_bought_out = 0
              and start_year <= $year
              and end_year >= $year
              order by end_year, start_year, price DESC");
  }

  /**
   * Returns the number of non-zero contracts for the specified year.
   */
  public static function getNumberNonZeroContracts($year) {
    return CommonDao::getIntegerValueFromQuery(
        "select count(*)
         from contract
         where is_bought_out = 0
         and price > 0
         and start_year <= $year
         and end_year >= $year");
  }

  /**
   * Returns the number of non-zero contracts for the specified team, during the specified year.
   */
  public static function getNumberNonZeroContractsByTeam($year, $teamId) {
    return CommonDao::getIntegerValueFromQuery(
        "select count(*)
        from contract
        where is_bought_out = 0
        and price > 0
        and start_year <= $year
        and end_year >= $year
        and team_id = $teamId");
  }

  /**
   * Returns a list of contracts for players currently not on a fantasy team.
   */
  public static function getAvailableContracts($year) {
    CommonDao::connectToDb();
    $query = "select c.*, p.*
              from contract c, player p
              where c.player_id not in
                (select tp.player_id from team_player tp)
              and c.player_id = p.player_id
              and c.is_bought_out = 0
              and c.end_year >= " . $year;
    $res = mysql_query($query);
    $contractsDb = array();
    while ($contractDb = mysql_fetch_assoc($res)) {
      $contract = ContractDao::populateContract($contractDb);
      $contract->setPlayer(PlayerDao::populatePlayer($contractDb));
      $contractsDb[] = $contract;
    }
    return $contractsDb;
  }

  /**
   * Returns all of the contracts for the specified player ID.
   */
  public static function getContractsByPlayerId($playerId) {
    return ContractDao::createContractsFromQuery(
        "select * from contract
        where player_id = " . $playerId .
        " order by sign_date DESC, num_years DESC, price DESC");
  }

  private static function createContractFromQuery($query) {
    $contracts = ContractDao::createContractsFromQuery($query);
    if (count($contracts) == 1) {
      return $contracts[0];
    }
    return null;
  }

  private static function createContractsFromQuery($query) {
    CommonDao::connectToDb();
  	$res = mysql_query($query);
    $contracts = array();
    while ($contractDb = mysql_fetch_assoc($res)) {
      $contracts[] = ContractDao::populateContract($contractDb);
    }
    return $contracts;
  }

  private static function populateContract($contractDb) {
    return new Contract($contractDb["contract_id"], $contractDb["player_id"],
        $contractDb["team_id"], $contractDb["num_years"], $contractDb["price"],
        $contractDb["sign_date"], $contractDb["start_year"], $contractDb["end_year"],
        $contractDb["is_bought_out"], $contractDb["contract_type"]);
  }

  /**
   * Creates a new contract.
   */
  public static function createContract(Contract $contract) {
    CommonDao::connectToDb();
    $query = "insert into contract(player_id, team_id, num_years, price, sign_date, start_year,
              end_year, is_bought_out, contract_type) values (" .
              $contract->getPlayer()->getId() . ", " . $contract->getTeam()->getId() . ", " .
              $contract->getTotalYears() . ", " . $contract->getPrice() . ", '" .
              $contract->getSignDate() . "', " . $contract->getStartYear() . ", " .
              $contract->getEndYear() . ", " . ($contract->isBoughtOut() ? "1" : "0") . ", '" .
              $contract->getType() . "')";
    $result = mysql_query($query);
    if (!$result) {
      echo "<div class='error_msg'>DB Error: Contract (" . $contract->toString() .
          ") already exists in DB. Try again.<br/><br/></div>";
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
                                  is_bought_out = " . ($contract->isBoughtOut() ? "1" : "0") . ",
                                  contract_type = '" . $contract->getType() . "'
              where contract_id = " . $contract->getId();
    $result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }

  /**
   * Deletes all of the contracts.
   */
  public static function deleteAllContracts() {
  	CommonDao::connectToDb();
  	$query = "delete from contract where contract_id > 0";
  	mysql_query($query);
  }
}
?>