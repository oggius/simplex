<?php
namespace application\controllers;

use system\basic\BaseController;
use system\basic\Redirector;
use system\url\Url;

class IndexController extends BaseController
{
    public function indexAction()
    {
        $url = Url::_('fakecontroller', 'fakeaction', array('name' => 'Simplex'));
        Redirector::redirect($url);
    }
}