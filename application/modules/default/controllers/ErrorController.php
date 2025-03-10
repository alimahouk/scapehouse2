<?php
// application/controllers/ErrorController.php

class ErrorController extends Zend_Controller_Action {

    public function errorAction() {
        $errors = $this->_getParam('error_handler');

        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

            // 404 error -- controller or action not found

                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                
                    $this->view->pageClass = "utilPage public pageNotFound";
                    $this->view->pageTitle = 'Page not found!';

                break;
            default:
            // application error
                // No layout
                $this->_helper->layout->disableLayout();

                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';

                
                break;
        }


        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->crit($this->view->message, $errors->exception);
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request = $errors->request;
    }

    public function notfoundAction() {

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->view->pageClass = "utilPage loggedIn pageNotFound";

        }else {
            $this->view->pageClass = "utilPage public pageNotFound";
        }

        $this->view->pageTitle = "Page not found";

        $this->getResponse()->setHttpResponseCode(404);
    }
    
    public function brbAction(){
        
        // No layout
        $this->_helper->layout->disableLayout();
    }
    
    public function getLog() {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasPluginResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }
}

?>
