<?php
namespace application\controllers;

use application\helpers\Feed;
use application\helpers\SyncHelper;
use application\helpers\Url;
use application\models\customer\CustomerManager;
use system\basic\BaseController;
use system\basic\Config;
use system\basic\exceptions\AccessException;
use system\basic\Redirector;
use system\basic\Registry;
use system\basic\Session;
use system\cache\CacheFactory;
use system\ftp\Ftp;
use system\logger\LoggerFactory;

class AdminController extends BaseController
{
    private function _checkAdminLogged()
    {
        return Session::get('isAdminLogged');
    }

    /**
     * clear all cache (templates + memcached)
     */
    public function clearcacheAction()
    {
        $cache = CacheFactory::factory('memcache');
        $dir = opendir(ROOT . '_cache/smarty/compiled/');
        while(false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                unlink(ROOT . '_cache/smarty/compiled/' . $file);
            }
        }
        rmdir(ROOT . '_cache/smarty/compiled/');
        echo $cache->flush() ? $this->_app->getResponse()->setOutput('ok') : 'failed';
    }

    /**
     * clear only memcached
     */
    public function clearmemcacheAction()
    {
        $cache = CacheFactory::factory('memcache');
        echo $cache->flush() ? $this->_app->getResponse()->setOutput('ok') : 'failed';
    }

    /**
     * sanitize aliases of all products
     */
    public function sanitizealiasAction()
    {
        $db = Registry::get('dbdriver');
        $products = $db->fetchAll("SELECT id, alias, title FROM products");
        foreach ($products as $product) {
            $db->update('products', array('alias' => preg_replace('/[^a-zA-Z0-9_-]/', '', $product['alias'])), array('id' => $product['id']));
        }
    }

    /**
     * sync database with PoweredTemplate db
     */
    public function synchronizeAction()
    {
        $syncHelper = new SyncHelper();
        $result = $syncHelper->syncWithPowered();
        $cache = CacheFactory::factory('memcache');
        $cache->flush();
        $this->view->assignValue('syncProducts', $result);
        $this->view->render('admin/sync.html');
    }

    /**
     * update description action
     */
    public function updatedescriptionsAction()
    {
        $syncHelper = new SyncHelper();
        $result = $syncHelper->syncWithDescriptionsFile(ROOT . 'uploads/pptstar_descriptions.csv');
        $cache = CacheFactory::factory('memcache');
        $cache->flush();
        $this->view->assignValue('syncProducts', $result['products']);
        $this->view->assignValue('syncDuplicates', $result['duplicates']);
        $this->view->render('admin/sync.html');
    }

    /**
     * get products feed action
     */
    public function getproductsfeedAction()
    {
        if ($this->_checkAdminLogged() || $this->getRequest()->isCli()) {
            $feed = new Feed();
            $fName = 'shareasale_pptstar.csv';
            $fPath = ROOT . 'uploads/' . $fName;
            $feed->getProducts($fPath);
            $response = $this->getResponse();
            if ($this->getRequest()->isCli()) {
                // if it is a cron job, then we should put the file to the shareasale ftp
                if (is_readable($fPath)) {
                    $config = Config::getSection('shareasale');
                    if (!empty($config)) {
                        $ftp = new Ftp($config['ftphost']);
                        $ftp->login($config['ftplogin'], $config['ftppassword']);
                        $ftp->setPassiveMode(true);
                        //$ftp->setDirectory($config['folder']);
                        if (!$ftp->putFile($fPath)) {
                            $logger = LoggerFactory::factory('file');
                            $logger->log(error_get_last(), 'shareasale');
                        }
                    }
                }
            } else {
                $response->setOutput(file_get_contents($fPath), 'text');
                $response->setHeader("Cache-Control: private");
                $response->setHeader("Content-Type: text/csv");
                $response->setHeader("Content-Disposition: attachment; filename=" . $fName);
                $response->setHeader("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                $response->setHeader("Accept-Ranges: bytes");
            }
        }
    }

    /**
     * action to perform customer login from admin panel
     * @throws \system\basic\exceptions\AccessException
     */
    public function customerloginAction()
    {
        if (!$this->_checkAdminLogged()) {
            throw new AccessException('No direct access to customerlogin action');
        }

        $request = $this->getRequest();
        $email = $request->getParam('customerEmail', '', 'GET');
        $customerManager = new CustomerManager();
        $customer = $customerManager->getCustomerByEmail($email);
        if (!$customer->isVoid()) {
            $customerManager->login($customer);
            Redirector::redirect(Url::getProfileUrl());
        } else {
            Redirector::redirect('/administrator/customerlogin/index/errorlogin');
        }
    }
}