<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../dao/', 'statDao.php');
CommonDao::requireFileIn('/../entity/', 'player.php');
CommonDao::requireFileIn('/../util/', 'time.php');

class PlayerDao {

  /**
   * Returns player information for the specified player ID and null if the player ID is not found.
   */
  static function getPlayerById($playerId) {
    CommonDao::connectToDb();
    $query = "select * from player
              where player_id = $playerId";
    return PlayerDao::createPlayerFromQuery($query);
  }

  /**
   * Returns an array of all players.
   */
  public static function getAllPlayers() {
    CommonDao::connectToDb();
    $query = "select p.*
              from player p
              order by p.last_name, p.first_name";
    return PlayerDao::createPlayersFromQuery($query);
  }

  /**
   * Returns all of the players eligible for auction in the specified year.
   */
  public static function getPlayersForAuction($year) {
  	CommonDao::connectToDb();

  	// get all players whose contract ended last year.
  	$lastYear = $year - 1;
  	$query = "SELECT P.*
  	          FROM player P, contract C
  	          WHERE P.player_id = C.player_id
  	            AND C.contract_type != 'Auction'
  	            AND C.end_year = " . $lastYear
  	      . " ORDER BY P.last_name, P.first_name";
  	$eligiblePlayers = PlayerDao::createPlayersFromQuery($query);

  	// filter out players who already have an auction contract for this year.
  	$playersForAuction = array();
  	foreach ($eligiblePlayers as $eligiblePlayer) {
  	  if (!PlayerDao::hasAuctionContract($eligiblePlayer->getId(), $year)) {
  	  	$playersForAuction[] = $eligiblePlayer;
  	  }
  	}
  	return $playersForAuction;
  }

  /**
   * Returns true if the player with the specified id has an auction contract starting in the
   * specified year.
   */
  private static function hasAuctionContract($playerId, $startYear) {
  	CommonDao::connectToDb();
  	$query = "SELECT C.*
  	          FROM contract C
  	          WHERE C.player_id = " . $playerId . " AND C.contract_type = 'Auction'
  	          AND C.start_year = " . $startYear . " AND C.is_bought_out = 0";
  	$res = mysql_query($query);
  	return (mysql_num_rows($res) > 0);
  }

  /**
   * Returns true if the player w/ the specified id has a non-auction contract that either ended in
   * the specified year or is still active after the specified year.
   */
  public static function hasContractForPlaceholders($playerId, $year) {
  	CommonDao::connectToDb();
  	$query = "SELECT c.*
  	          FROM contract c
  	          WHERE c.player_id = " . $playerId . "
  	          AND c.is_bought_out = 0
  	          AND c.contract_type != 'Auction'
  	          AND c.end_year >= " . $year;
  	$res = mysql_query($query);
  	return (mysql_num_rows($res) > 0);
  }

  /**
   * Returns true if the player has any contract that either ended in the specified year or is still
   * active after the specified year.
   */
  public static function hasContract($playerId, $year) {
    CommonDao::connectToDb();
    $query = "SELECT c.*
              FROM contract c
              WHERE c.player_id = " . $playerId . "
              AND c.is_bought_out = 0
              AND c.end_year >= " . $year;
    $res = mysql_query($query);
    return (mysql_num_rows($res) > 0);
  }

  /**
   * Returns true if the player has any non-zero contract that either ended in the specified year
   * or is still active after the specified year.
   */
  public static function hasNonZeroContract($playerId, $year) {
    return CommonDao::hasAnyRowsMatchingQuery(
             "SELECT c.*
              FROM contract c
              WHERE c.player_id = " . $playerId . "
              AND c.is_bought_out = 0
              AND c.price > 0
              AND c.end_year >= " . $year);
  }

  /**
   * Return all players eligible to be ranked by the specified team sorted by fantasy points in the
   * specified year.
   */
  public static function getPlayersForRanking($teamId, $year) {
  	CommonDao::connectToDb();

  	// get all players in team_player who aren't assigned to specified team & haven't been ranked
  	// by specified team
  	$query = "select p.*, s.*
              from player p, team_player tp, stat s
              where p.player_id = tp.player_id
              and p.player_id = s.player_id
              and s.year = " . $year .
            " and tp.team_id <> " . $teamId .
              " and p.player_id not in (
                  select player_id
                  from rank
                  where team_id = " . $teamId . ")
  	          order by s.fantasy_pts DESC";
  	$res = mysql_query($query);
  	$playersDb = array();
  	while($playerDb = mysql_fetch_assoc($res)) {
  	  $player = PlayerDao::populatePlayer($playerDb);
  	  $player->setStatLine($year, StatDao::populateStatLine($playerDb));
  	  $playersDb[] = $player;
  	}
  	return $playersDb;
  }

  /**
   * Returns an array of players unranked by anyone in the specified year.
   */
  public static function getUnrankedPlayers($year) {
    CommonDao::connectToDb();
    $query = "select p.*
              from player p, team_player tp
              where p.player_id = tp.player_id
              and p.player_id not in (
                select distinct r.player_id
                from rank r
                where r.year = $year)";
    return PlayerDao::createPlayersFromQuery($query);
  }

  /**
   * Returns an array of players, belonging to the specified team, eligible to be kept in the
   * specified year.
   */
  public static function getEligibleKeepers(Team $team, $year) {
    // first, get all players on specified team
    $allPlayers = PlayerDao::getPlayersByTeam($team);

    // filter out players who currently have a contract or a contract ended for them last year.
    $eligibleKeepers = array();
    foreach ($allPlayers as $player) {
      if (!PlayerDao::hasContractForPlaceholders($player->getId(), $year - 1)) {
        $eligibleKeepers[] = $player;
      }
    }
    return $eligibleKeepers;
  }

  /**
   * Returns an array of players, belonging to the specified team who will be dropped when money
   * is banked for the specified keeper year.
   */
  public static function getPlayersToBeDroppedForKeepers(Team $team, $year) {
  	// first, get all players on specified team
  	$allPlayers = PlayerDao::getPlayersByTeam($team);

  	// filter out players who currently have a contract.
  	$playersToBeDropped = array();
  	foreach ($allPlayers as $player) {
  	  if (!PlayerDao::hasContract($player->getId(), $year)) {
  	    $playersToBeDropped[] = $player;
  	  }
  	}
  	return $playersToBeDropped;
  }

  /**
   * Returns an array of players, belonging to the specified team who will be kept when money
   * is banked for the specified keeper year.
   */
  public static function getPlayersToBeKeptForKeepers(Team $team, $year) {
    // first, get all players on specified team
    $allPlayers = PlayerDao::getPlayersByTeam($team);

    // filter out players who do not have a contract.
    $playersToBeKept = array();
    foreach ($allPlayers as $player) {
      if (PlayerDao::hasContract($player->getId(), $year)) {
        $playersToBeKept[] = $player;
      }
    }
    return $playersToBeKept;
  }

  /**
   * Returns an array of players belonging to the specified fantasy team, including fantasy points
   * from the previous year, if they exist.
   */
  public static function getPlayersByTeam(Team $team) {
    CommonDao::connectToDb();
    $lastYear = TimeUtil::getYearByEvent(Event::OFFSEASON_START) - 1;
    $query = "select p.*, s.fantasy_pts
          	  from player p
          	  left outer join (select * from stat where year = $lastYear) s
                  on s.player_id = p.player_id
          	  inner join team_player tp on p.player_id = tp.player_id
          	  where tp.team_id = " . $team->getId() .
          	" order by p.last_name, p.first_name";
    $res = mysql_query($query);
    $playersDb = array();
    while($playerDb = mysql_fetch_assoc($res)) {
    	$player = PlayerDao::populatePlayer($playerDb);
    	$player->setStatLine($lastYear, StatDao::populateStatLine($playerDb));
    	$playersDb[] = $player;
    }
    return $playersDb;
  }

  /**
   * Returns an array of players who do not belong to any fantasy team.
   */
  public static function getUnassignedPlayers() {
    CommonDao::connectToDb();
    $query = "select p.*
              from player p left outer join team_player tp
              on p.player_id = tp.player_id
              where tp.team_id is null
              order by p.last_name, p.first_name";
    return PlayerDao::createPlayersFromQuery($query);
  }

  /**
   * Returns an array of players who have not yet been drafted in the specified year.
   */
  public static function getUndraftedPlayers($year) {
    CommonDao::connectToDb();
    $query = "select p.* from player p where p.player_id not in (
                  select player_id from (select distinct b.player_id from ping_pong b
                      where b.year = $year and b.player_id is not null) as t1
                  union
                  select player_id from (select distinct dp.player_id from draft_pick dp
                      where dp.year = $year and dp.player_id is not null) as t2)
              order by p.last_name, p.first_name";
    return PlayerDao::createPlayersFromQuery($query);
  }

  /**
   * Returns an array of players whose first or last name contains the specified string.
   */
  public static function getPlayersByName($searchString) {
  	CommonDao::connectToDb();
  	$query = "select * from player
              where first_name like '%$searchString%'
  	          or last_name like '%$searchString%'
  	          order by last_name, first_name";
  	return PlayerDao::createPlayersFromQuery($query);
  }

  /**
   * Returns an array of players whose first and last names match the specified strings.
   */
  public static function getPlayersByFullName($firstName, $lastName) {
    CommonDao::connectToDb();
    $query = "select * from player
              where first_name = \"$firstName\"
              and last_name = \"$lastName\"
              order by last_name, first_name";
    return PlayerDao::createPlayersFromQuery($query);
  }

  /**
   * Return all players belonging to the specified team without a contract during the specified
   * year, who were either drafted after the seltzer cutoff or undrafted.
   */
  public static function getPlayersForSeltzerContracts($teamId, $year) {
    CommonDao::connectToDb();
    $query = "select p.*
              from player p, team_player tp
              where tp.player_id = p.player_id
              and tp.team_id = $teamId";
    $playersOnTeam = PlayerDao::createPlayersFromQuery($query);

    // Filter out players under contract or those that were drafted before the seltzer cutoff
    $seltzerPlayers = array();
    foreach ($playersOnTeam as $player) {
      if (!PlayerDao::hasNonZeroContract($player->getId(), $year) &&
          !PlayerDao::draftedBeforeSeltzerCutoff($player->getId(), $year)) {
        $seltzerPlayers[] = $player;
      }
    }

    return $seltzerPlayers;
  }

  /**
   * Returns true if the specified player was drafted before the seltzer cutoff in the specified
   * year.
   *
   * WARNING: This will break if the cutoff pick is in the ping pong round, but it's doubtful that
   * it ever will be.
   */
  public static function draftedBeforeSeltzerCutoff($playerId, $year) {
    $ppQuery = "select count(*) from ping_pong where year = $year and player_id = $playerId";
    if (CommonDao::getIntegerValueFromQuery($ppQuery) > 0) {
      return true;
    }

    $draftPick = DraftPickDao::getDraftPickByPlayer($playerId, $year);
    if ($draftPick == null) {
      return false;
    } else {
      $cutoffPick = DraftPickDao::getSeltzerCutoffPick($year);
      if (($cutoffPick == null) ||                                // no cutoff pick
          ($cutoffPick->getRound() < $draftPick->getRound()) ||   // cutoff in earlier round
          (($cutoffPick->getRound() == $draftPick->getRound()) && // cutoff same round, earlier pick
           ($cutoffPick->getPick() <= $draftPick->getPick()))) {
        return false;
      }
    }
    return true;
  }

  private static function createPlayerFromQuery($query) {
    $playerArray = PlayerDao::createPlayersFromQuery($query);
    if (count($playerArray) == 1) {
      return $playerArray[0];
    }
    return null;
  }

  private static function createPlayersFromQuery($query) {
    $res = mysql_query($query);
    $playersDb = array();
    while($playerDb = mysql_fetch_assoc($res)) {
      $playersDb[] = PlayerDao::populatePlayer($playerDb);
    }
    return $playersDb;
  }

  /**
   * Creates and returns a Player with data from the specified db result, which contains
   * references to all of the fields in the 'player' table.
   */
  public static function populatePlayer($playerDb) {
  	return new Player($playerDb["player_id"], $playerDb["first_name"], $playerDb["last_name"],
  	    $playerDb["birth_date"], $playerDb["mlb_team_id"], $playerDb["sportsline_id"]);
  }

  /**
   * Inserts the specified player in the 'player' table and returns the same Player with its id set.
   */
  static function createPlayer(Player $player) {
    CommonDao::connectToDb();
    $query = "insert into player(first_name, last_name, mlb_team_id, birth_date, sportsline_id)
                 values (\"" .
             $player->getFirstName() . "\", \"" . $player->getLastName() . "\", " .
             $player->getMlbTeam()->getId() . ", '" . $player->getBirthDate() . "', " .
             $player->getSportslineId() . ")";
    $result = mysql_query($query);
    if (!$result) {
      echo "Player " . $player->getFullName() . " already exists in DB. Try again.";
      return null;
    }

    $idQuery = "select player_id from player where first_name = \"" . $player->getFirstName() .
        "\" and last_name = \"" . $player->getLastName() . "\" and birth_date = '" .
        $player->getBirthDate() . "'";
    $result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
    $row = mysql_fetch_assoc($result);
    $player->setId($row["player_id"]);
    return $player;
  }

  /**
   * Updates the specified player in the 'player' table.
   */
  static function updatePlayer($player) {
    CommonDao::connectToDb();
    $query = "update player set first_name = \"" . $player->getFirstName() . "\",
                                last_name = \"" . $player->getLastName() . "\",
                                mlb_team_id = " . $player->getMlbTeam()->getId() . ",
                                birth_date = '" . $player->getBirthDate() . "',
                                sportsline_id = " . $player->getSportslineId() .
             " where player_id = " . $player->getId();
    $result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }

  /**
   * Deletes all of the players.
   */
  public static function deleteAllPlayers() {
  	CommonDao::connectToDb();
  	$query = "delete from player where player_id > 0";
  	mysql_query($query);
  }
}
?>