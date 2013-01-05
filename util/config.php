<?php

/**
 * Reads settings from the configuration file.
 */
class ConfigUtil {

  const CONFIG_FILE = 'config.ini';

  // config keys
  // TODO move version to separate file
  const VERSION = 'version';
  const ENVIRONMENT = 'environment';

  // features
  const KEEPER_FEATURE = 'keeper_feature';

  /**
   * Returns the config value with the specified key.
   */
  public static function getValue($key) {
    $configs = ConfigUtil::parseConfigFile();
    return $configs[$key];
  }

  /**
   * Returns true if this is the production environment.
   */
  public static function isProduction() {
    return (ConfigUtil::getValue(ConfigUtil::ENVIRONMENT) == "PROD");
  }

  /**
   * Returns true if the specified feature is enabled.
   */
  public static function isFeatureEnabled($feature) {
    return (ConfigUtil::getValue($feature) == true);
  }

  private static function parseConfigFile() {
    if (file_exists("../" . ConfigUtil::CONFIG_FILE)) {
      $configFile = "../" . ConfigUtil::CONFIG_FILE;
    } else if (file_exists(ConfigUtil::CONFIG_FILE)) {
      $configFile = ConfigUtil::CONFIG_FILE;
    } else {
      die("<h1>Cannot find config file!</h1>");
    }

    return parse_ini_file($configFile);
  }
}

?>
