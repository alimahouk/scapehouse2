<?php

class Tipbox_Model_Profile {

    // Define properties.
    public $fullname;
    public $email;
    public $username;
    public $password;
    public $website;
    public $location;
    public $bio;
    public $userid;
    public $profileErrors;

    function editProfile() {

        $this->standardizeInput()
                ->validateName()
                ->validateEmail()
                ->validateBio()
                ->validateLocation()
                ->validateWebsite()
                ->validateUsername();

        $tbuserTable = new Tipbox_Model_DbTable_Tbuser();

        if (!$this->profileErrors) {
            
            $this->success = true;
            $tbuserTable->updateProfile($this->userid, $this->bio, $this->location, $this->username, $this->website, $this->fullname, $this->email);

            return true;
        } else {
            return false;
        }
    }

    function validateBio() {

        if (mb_strlen($this->bio) > 160) {// Bio length error
            $this->profileErrors["bioLenError"] = true;
        }

        return $this;
    }

    function validateLocation() {

        if (mb_strlen($this->location) > 160) {// Location length error
            $this->profileErrors["locationLenError"] = true;
        }
        return $this;
    }

    function validateWebsite() {

        if (mb_strlen($this->website) > 100) {// Website length error
            $this->profileErrors["websiteLenError"] = true;
        }
        
        return $this;
    }

    function validateName() {

        $validateName = new Zend_Validate_Alpha();
        $validateName->setAllowWhiteSpace(true);
        $lengthCheck = new Zend_Validate_StringLength();

        $lengthCheck->setMin(2)->setMax(50);
        if (!$validateName->isValid($this->fullname)) {
            $this->profileErrors["fullnameErr"] = true;
        }

        if (!$lengthCheck->isValid($this->fullname)) {
            $this->profileErrors["fullnameLenErr"] = true;
        }

        return $this;
    }

    function validateEmail() {

        $validateEmail = new Zend_Validate_EmailAddress();
        $tbuserTable = new Tipbox_Model_DbTable_Tbuser();

        if (!$validateEmail->isValid($this->email) || !Model_Lib_Func::verifyEmailDomain($this->email)) {
            $this->profileErrors["emailErr"] = true;
        } else {
            if ($tbuserTable->getUserByEmail($this->email, $this->userid)) {
                $this->profileErrors["emailExistsErr"] = true;
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

            $this->profileErrors["usernameErr"] = true;
        } else {
            if ($tbuserTable->getUserByUsername($this->username, $this->userid)) {
                $this->profileErrors["usernameExistsErr"] = true;
            }
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
        $this->website = trim($this->website);
        $this->location = trim($this->location);
        $this->bio = trim($this->bio);

        return $this;
    }

}