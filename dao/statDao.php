<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'stat.php');

class StatDao {
	
  /**
   * Return all of the stats for the specified year, only returning stats for players which
   * currently belong to a team.
   */
  public static function getStatsForRankingByYear($year) {
  	CommonDao::connectToDb();
  	$query = "select s.*
  	          from stat s, team_player tp
  	          where s.player_id = tp.player_id and s.year = " . $year .
  	        " order by s.fantasy_pts DESC";
  	return StatDao::createStatsFromQuery($query);
  }
  
  /**
   * Returns a single stat line for the specified player during the specified year and null if
   * it doesn't exist.
   */
  public static function getStatByPlayerYear($playerId, $year) {
  	CommonDao::connectToDb();
  	$query = "select s.*
  	          from stat s
  	          where s.player_id = " . $playerId . " and s.year = " . $year;
  	return StatDao::createStatFromQuery($query);
  }
  
  private static function createStatFromQuery($query) {
  	$statArray = StatDao::createStatsFromQuery($query);
  	if (count($statArray) == 1) {
      return $statArray[0];
    }
    return null;
  }
  
  private static function createStatsFromQuery($query) {
  	$res = mysql_query($query);
  	$statsDb = array();
  	while($statDb = mysql_fetch_assoc($res)) {
  	  $statsDb[] = new Stat($statDb["stat_id"], $statDb["year"], $statDb["player_id"],
  	      StatDao::populateStatLine($statDb));
  	}
  	return $statsDb;
  }
  
  /**
   * Creates and returns a StatLine with stats from the specified db result, which contains
   * references to all of the stat fields in the 'stat' table.
   */
  // TODO add rest of stats
  public static function populateStatLine($statDb) {
    return new StatLine($statDb["fantasy_pts"]);
  }
}