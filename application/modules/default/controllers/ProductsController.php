<?php

class ProductsController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }
    
    public function scapesAction()
    {
        $this->view->pageClass = "productPage scapes";
        $this->view->pageTitle = "Scapes Messenger, by Scapehouse.";
        $this->view->pageName = "mainPage";
    }

    public function fingereaderAction()
    {
        $this->view->pageClass = "productPage fingereader";
        $this->view->pageTitle = "Fingereader, by Scapehouse.";
        $this->view->pageName = "mainPage";
    }
    
    public function metromateAction()
    {
        $this->view->pageClass = "productPage metromate";
        $this->view->pageTitle = "MetroMate, by Scapehouse.";
        $this->view->pageName = "mainPage";
    }
    
    public function shelfAction()
    {
        $this->view->pageClass = "productPage shelf";
        $this->view->pageTitle = "Shelf, by Scapehouse.";
        $this->view->pageName = "mainPage";
    }
}

?>