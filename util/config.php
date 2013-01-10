<?php

/**
 * Reads settings from configuration files.
 */
class ConfigUtil {

  const CONFIG_FILE = 'config.ini';
  const VERSION_FILE = 'version.ini';

  // config keys
  const VERSION = 'version';
  const ENVIRONMENT = 'environment';

  // features
  const KEEPER_FEATURE = 'keeper_feature';

  /**
   * Returns the version number.
   */
  public static function getVersion() {
    $configs = ConfigUtil::parseConfigFile(ConfigUtil::VERSION_FILE);
    return $configs[ConfigUtil::VERSION];
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

  /**
   * Returns the config value with the specified key.
   */
  private static function getValue($key) {
    $configs = ConfigUtil::parseConfigFile(ConfigUtil::CONFIG_FILE);
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
