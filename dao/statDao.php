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
  
  private static function createStatsFromQuery($query) {
  	$res = mysql_query($query);
  	$statsDb = array();
  	while($statDb = mysql_fetch_assoc($res)) {
  		$statsDb[] = new Stat($statDb["stat_id"], $statDb["year"], $statDb["player_id"],
  				$statDb["fantasy_pts"]);
  	}
  	return $statsDb;
  }
}