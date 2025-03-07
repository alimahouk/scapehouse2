<?php

class Tipbox_Model_Signup {

    // Define properties.
    public $fullname;
    public $email;
    public $username;
    public $password;
    public $signupErrors;
    public $fbid;
    public $fbToken;
    public $fbTokenExp;
    public $location;
    public $website;
    public $timezone;
    public $accessToken;
    public $signupType;
    public $twtToken;
    public $twtid;
    public $twtSecret;
    public $twtBio;
    public $twtUsername;

    function signup() {


        $this->standardizeInput()
                ->validateName()
                ->validateEmail()
                ->validateUsername()
                ->validatePassword();

        if ($this->signupType == "fb") {
            $this->validateFacebook();
        } elseif ($this->signupType == "twt") {
            //$this->validateTwitter();
        }


        $tbuserTable = new Tipbox_Model_DbTable_Tbuser();

        if (!$this->signupErrors) {

            $this->success = true;

            $passwordHash = Model_Lib_Func::saltedSha1($this->password);
            return $tbuserTable->createUser(
                            $this->username, $this->fullname, $this->email, $passwordHash, $this->location, $this->website, $this->timezone, $this->fbToken, $this->fbid, $this->fbTokenExp, $this->accessToken, $this->signupType, $this->twtToken, $this->twtid, $this->twtSecret, $this->twtBio, $this->twtUsername);
        } else {

            return false;
        }
    }

    function validateTwitter() {

        $tb_twitter = new Tipbox_Model_DbTable_Tbtwitter();
        $twtOutput = $tb_twitter->getTokenByTwtid($this->twtid);

        if ($twtOutput) {
            $this->signupErrors["twtExsitsErr"] = true;
        }

        return $this;
    }

    function validateFacebook() {

        $tb_facebook = new Tipbox_Model_DbTable_Tbfacebook();
        $fbOutput = $tb_facebook->getTokenByFbid($this->fbid);

        if ($fbOutput) {
            $this->signupErrors["fbExsitsErr"] = true;
        }

        return $this;
    }

    function validateName() {

        $validateName = new Zend_Validate_Alpha();
        $validateName->setAllowWhiteSpace(true);
        $lengthCheck = new Zend_Validate_StringLength();

        $lengthCheck->setMin(2)->setMax(50);
        if (!$validateName->isValid($this->fullname)) {
            $this->signupErrors["fullnameErr"] = true;
        }

        if (!$lengthCheck->isValid($this->fullname)) {
            $this->signupErrors["fullnameLenErr"] = true;
        }

        return $this;
    }

    function validateEmail() {

        $validateEmail = new Zend_Validate_EmailAddress();
        $tbuserTable = new Tipbox_Model_DbTable_Tbuser();

        if (!$validateEmail->isValid($this->email) || !Model_Lib_Func::verifyEmailDomain($this->email)) {
            $this->signupErrors["emailErr"] = true;
        } else {
            if ($tbuserTable->getUserByEmail($this->email)) {
                $this->signupErrors["emailExistsErr"] = true;
            }
        }

        return $this;
    }

    function validateUsername() {

        $lengthCheck = new Zend_Validate_StringLength();
        $tbuserTable = new Tipbox_Model_DbTable_Tbuser();

        $restricted = array(
            "index",
            "signup",
            "login",
            "about",
            "contact",
            "privacy",
            "terms",
            "test",
            "verify",
            "mobile",
            "all",
            "careers",
            "notifs",
            "lr",
            "inbox",
            "invite",
            "settings",
            "logout",
            "search",
            "extern",
            "forgotpassword",
            "verifyemail",
            "help");

        if (array_search(strtolower($this->username), $restricted) !== FALSE ||
                !preg_match("/^[a-zA-Z0-9\._-]{2,15}$/", $this->username) ||
                !$lengthCheck->isValid($this->username)) {

            $this->signupErrors["usernameErr"] = true;
        } else {
            if ($tbuserTable->getUserByUsername($this->username)) {
                $this->signupErrors["usernameExistsErr"] = true;
            }
        }

        return $this;
    }

    function validatePassword() {

        $lengthCheck = new Zend_Validate_StringLength();

        $lengthCheck->setMin(6);

        if (!$lengthCheck->isValid($this->password)) {
            $this->signupErrors["passwordErr"] = true;
        }

        if ($this->password != $this->passwordConfirm) {
            $this->signupErrors["passwordConfirmErr"] = true;
        }

        return $this;
    }

    function standardizeInput() {

        $this->fullname = trim($this->fullname);
        $this->fullname = strtolower($this->fullname);
        $this->fullname = ucwords($this->fullname);
        $this->fullname = Model_Lib_Func::stripExtraSpace($this->fullname);

        $this->email = trim($this->email);
        $this->username = trim($this->username);

        return $this;
    }

}