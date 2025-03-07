<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function indexAction() {

        $this->view->pageClass = "homepage public";
        $this->view->pageTitle = "Scapehouse";
        $this->view->pageName = "mainPage";

        $URI = $_SERVER["REQUEST_URI"];

        // /ali redirectors to cover up for the link on current business cards.
        if ($URI == "/ali") {
            $this->_redirect("http://twitter.com/MachOSX");
        }

        if ($_POST) {

            $email = trim($_POST["email"]);

            $validateEmail = new Zend_Validate_EmailAddress();

            if (!$validateEmail->isValid($email) || !Model_Lib_Func::verifyEmailDomain($email)) {
                
                echo "emailErr";
                die;
                
            } else {
                
                $TbmailinglistTable = new Model_DbTable_Tbmailinglist;
                $TbmailinglistTable->insertEmail($email);
                
                echo "done";
                die;
            }
        }
    }

}

?>
