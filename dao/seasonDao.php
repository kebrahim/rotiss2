<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'season.php');

class SeasonDao {

  /**
   * Returns all of the seasons of the specified sport.
   */
  public static function getSeasonsBySport($sport) {
    return SeasonDao::createSeasonsFromQuery(
        "select * from season
         where sport = '" . $sport . "'" .
       " order by year DESC");
  }

  private static function createSeasonFromQuery($query) {
    $seasons = SeasonDao::createSeasonsFromQuery($query);
    if (count($seasons) == 1) {
      return $seasons[0];
    }
    return null;
  }

  private static function createSeasonsFromQuery($query) {
    CommonDao::connectToDb();
  	$res = mysql_query($query);
    $seasons = array();
    while ($seasonDb = mysql_fetch_assoc($res)) {
      $seasons[] = SeasonDao::populateSeason($seasonDb);
    }
    return $seasons;
  }

  private static function populateSeason($seasonDb) {
    return new Season($seasonDb["season_id"], $seasonDb["sport"],
        $seasonDb["year"], $seasonDb["winning_owner"], $seasonDb["winning_team"],
        $seasonDb["runner_up_owner"], $seasonDb["runner_up_team"], $seasonDb["result"]);
  }
}
