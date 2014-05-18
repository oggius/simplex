<?php
namespace application\controllers;

use application\helpers\DateHelper;
use application\helpers\Url;
use application\libs\Twocheckout\Api\TwocheckoutSale;
use application\libs\Twocheckout;
use application\models\customer\CustomerManager;
use application\models\order\Order;
use application\models\product\ProductFactory;
use application\models\subscription\DownloadManager;
use application\models\subscription\Subscription;
use application\models\subscription\SubscriptionManager;
use system\basic\BaseController;
use system\basic\Registry;
use system\basic\exceptions\WrongInputParamsException;
use system\db\Driver\MysqliDbDriver;
use system\mailer\Mailer;

class TestController extends BaseController
{
    public function testAction()
    {
        $name = $this->getParam('name', 'World');
        $this->view->assignValue('name', $name);
        $this->view->render('test/test.html');
    }


}