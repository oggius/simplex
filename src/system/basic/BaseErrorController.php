<?php
namespace system\basic;

use system\basic\exceptions\BaseException;

class BaseErrorController extends BaseController
{
    protected $_exception;

    public function renderErrorPageAction(BaseException $exception)
    {
        if ($errorCode = $exception->getCode()) {
            $this->getResponse()->setErrorHeader($errorCode);
            if (is_file($this->view->getViewsPath() . '_errors/' . $errorCode . '.html')) {
                $this->view->render('_errors/' . $errorCode . '.html');
            }
        } else {
            $this->getResponse()->setErrorHeader(503);
            if (DEBUG && is_file($this->view->getViewsPath() . '_errors/unknown.html')) {
                $this->view->assignValue('stacktrace', $exception->getTraceAsString() );
                $this->view->assignValue('errorMessage', $exception->getMessage());
                $this->view->render('_errors/unknown.html');
            }
        }
    }
}
