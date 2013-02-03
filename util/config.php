<?php

/**
 * Reads settings from configuration files.
 */
class ConfigUtil {

  const CONFIG_FILE = 'config.ini';
  const VERSION_FILE = 'version.ini';

  // config keys
  const ENVIRONMENT = 'environment';
  const RELEASE_DATE = 'release_date';
  const VERSION = 'version';
  const CODENAME = 'codename';

  // features
  const KEEPER_FEATURE = 'keeper_feature';

  /**
   * Returns the version number.
   */
  public static function getVersion() {
    return ConfigUtil::getValueFromVersionFile(ConfigUtil::VERSION);
  }

  /**
   * Returns the version codename.
   */
  public static function getCodename() {
    return ConfigUtil::getValueFromVersionFile(ConfigUtil::CODENAME);
  }

  /**
   * Returns the release date.
   */
  public static function getReleaseDate() {
    return ConfigUtil::getValueFromVersionFile(ConfigUtil::RELEASE_DATE);
  }

  /**
   * Returns the value with the specified key from the version file.
   */
  private static function getValueFromVersionFile($key) {
    return ConfigUtil::getValueFromFile(ConfigUtil::VERSION_FILE, $key);
  }

  /**
   * Returns true if this is the production environment.
   */
  public static function isProduction() {
    return (ConfigUtil::getValueFromConfigFile(ConfigUtil::ENVIRONMENT) == "PROD");
  }

  /**
   * Returns true if the specified feature is enabled.
   */
  public static function isFeatureEnabled($feature) {
    return (ConfigUtil::getValueFromConfigFile($feature) == true);
  }

  /**
   * Returns the config value with the specified key from the config file.
   */
  private static function getValueFromConfigFile($key) {
    return ConfigUtil::getValueFromFile(ConfigUtil::CONFIG_FILE, $key);
  }

  /**
   * Returns the value with the specified key from the specified file.
   */
  private static function getValueFromFile($file, $key) {
    $configs = ConfigUtil::parseConfigFile($file);
    return $configs[$key];
  }

  private static function parseConfigFile($configFile) {
    if (file_exists($configFile)) {
      $configPath = $configFile;
    } else if (file_exists("../" . $configFile)) {
      $configPath = "../" . $configFile;
    } else if (file_exists("../../" . $configFile)) {
      $configPath = "../../" . $configFile;
    } else {
      die("<h1>Cannot find config file $configFile!</h1>");
    }

    return parse_ini_file($configPath);
  }
}

?>
