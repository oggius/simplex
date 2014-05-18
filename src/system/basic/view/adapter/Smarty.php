<?php
namespace system\basic\view\adapter;

use system\basic\view\TemplateEngineAdapter;

require_once SYSTEM_PATH . 'ext' . DS . 'Smarty' . DS . 'Smarty.class.php';

/**
 * Class Smarty
 * @package system\basic\view\adapter
 */
class Smarty extends TemplateEngineAdapter {

    public function configure(array $config)
    {
        $smarty = new \Smarty();

        if (isset($config['smarty'])) {
            $cfg = $config['smarty'];
            if (isset($cfg['cache_dir'])) {
                $this->prepareTemplateCacheDirectory(ROOT . $cfg['cache_dir']);
                $smarty->setCacheDir(ROOT . $cfg['cache_dir']);
            }

            if (isset($cfg['compiled_dir'])) {
                $this->prepareTemplateCacheDirectory(ROOT . $cfg['compiled_dir']);
                $smarty->setCompileDir(ROOT . $cfg['compiled_dir']);
            }

            if (isset($cfg['templates_dir'])) {
                $smarty->setTemplateDir(ROOT . $cfg['templates_dir']);
            }

            if (isset($cfg['plugins_dir'])) {
                $smarty->addPluginsDir(ROOT . $cfg['plugins_dir']);
            }
        } else {
            $smarty->setTemplateDir(APP_PATH . 'views');
            $this->prepareTemplateCacheDirectory(ROOT . 'cache' . DS . 'smarty' . DS . 'cache' . DS);
            $smarty->setCacheDir(ROOT . 'cache' . DS . 'smarty' . DS . 'cache' . DS);
            $smarty->setCompileDir(ROOT . 'cache' . DS . 'smarty' . DS . 'compiled' . DS);
        }
        //$smarty->caching = 1;
        $this->_engine = $smarty;
    }

    public function assign(array $data)
    {
        foreach ($data as $key => $value) {
            $this->_engine->assign($key, $value);
        }
    }

    public function render($template)
    {
        return $this->_engine->fetch($template, null, null, null, false);
    }
}
