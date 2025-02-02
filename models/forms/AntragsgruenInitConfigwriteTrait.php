<?php

namespace app\models\forms;

use app\models\settings\AntragsgruenApp;

/**
 * @method protected setConfigValues(AntragsgruenApp $config)
 */
trait AntragsgruenInitConfigwriteTrait
{
    protected ?string $configFile = null;

    public function readConfigFromFile(?string $configFile = null): AntragsgruenApp
    {
        if ($configFile) {
            $this->configFile = $configFile;
        }

        if (file_exists($this->configFile)) {
            $configJson = file_get_contents($this->configFile);
            try {
                $config = new AntragsgruenApp($configJson);
            } catch (\Exception $e) {
                $config = new AntragsgruenApp('');
            }
        } else {
            $config = new AntragsgruenApp('');
        }

        if ($config->randomSeed == '') {
            $config->randomSeed = \Yii::$app->getSecurity()->generateRandomString();
        }

        return $config;
    }

    public function saveConfig(): void
    {
        $config = $this->readConfigFromFile();
        $this->setConfigValues($config);
        $file = fopen($this->configFile, 'w');
        fwrite($file, json_encode($config, JSON_PRETTY_PRINT));
        fclose($file);
    }
}
