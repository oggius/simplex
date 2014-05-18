<?php
namespace system\basic\view;

use \system\basic\exceptions\WrongConfigException;
use \system\basic\exceptions\MissingClassException;

class TemplateEngineFactory {

    /**
     * Creates TemplateEngineAdapter instance corresponding to the engine set in the templateengine config
     * @param array $tplConfig
     * @return TemplateEngineAdapter
     * @throws \system\basic\exceptions\WrongConfigException
     */
    public static function factory(array $tplConfig)
    {
        if (!array_key_exists('engine', $tplConfig)) {
            throw new WrongConfigException('Template engine not set');
        }

        $templateEngineAdapterName = __NAMESPACE__ . '\\adapter\\' . ucfirst($tplConfig['engine']);
        $templateEngine = new $templateEngineAdapterName();
        $templateEngine->configure($tplConfig);

        return $templateEngine;
    }
}