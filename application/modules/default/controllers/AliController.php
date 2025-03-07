<?php

class AliController extends Zend_Controller_Action
{
    public function init()
    {
        
    }

    public function indexAction()
    {

        $this->view->pageClass = "portfolio ali";
        $this->view->pageTitle = "Ali Mahouk";
        $this->view->pageName = "AliPortfolio";

    }
}

?>
