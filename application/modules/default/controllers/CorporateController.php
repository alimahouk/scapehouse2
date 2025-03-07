<?php

class CorporateController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }

    public function contactAction()
    {
        $this->view->pageClass = "corporate contact ";
        $this->view->pageTitle = "Scapehouse | Contact Us";
        $this->view->pageName = "contactPage";

        if ( $_POST )
        {
            $email = trim($_POST["email"]);
            $fullName = trim($_POST["fullName"]);
            $messsage = trim($_POST["messsage"]);

            $validateEmail = new Zend_Validate_EmailAddress();

            if ( !$validateEmail->isValid($email) || !Model_Lib_Func::verifyEmailDomain($email) )
            {
                echo "emailError";
                die;
            }

            if ( empty($fullName) )
            {
                echo "fullNameError";
                die;
            }

            if ( empty($messsage) )
            {
                echo "messageError";
                die;
            }
			
            $messsage = nl2br($messsage);
            $bodyHTML = <<<MAIL

{$fullName} ({$email}) sent us a message:<br><br>{$messsage}<br><br>- Scapehouse One, out.

MAIL;
            Model_Lib_Func::shMailer("amrazzouk@gmail.com", "Scapehouse Support", "Scapehouse Feedback", $bodyHTML, $fullName, $email);
            
            echo "done";
            die;
        }
    }

    public function privacyAction()
    {
        $this->view->pageClass = "corporate privacy";
        $this->view->pageTitle = "Scapehouse | Privacy Policy";
        $this->view->pageName = "privacyPage";
    }

    public function tosAction()
    {
        $this->view->pageClass = "corporate terms";
        $this->view->pageTitle = "Scapehouse | Terms &amp; Conditions";
        $this->view->pageName = "tosPage";
    }

    public function aboutAction()
    {
        $this->view->pageClass = "corporate portfolio";
        $this->view->pageTitle = "Scapehouse | A Little More About Us";
        $this->view->pageName = "hirePage";
    }
}

?>