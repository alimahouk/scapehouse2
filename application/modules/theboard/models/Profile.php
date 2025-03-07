<?php

/**
 * Scapehouse user profile management class.
 *
 * @copyright  2015 Scapehouse
 */

class Theboard_Model_Profile
{
    // Define properties.
    public $userID;
    public $name;
    public $username; 
    public $gender;
    public $birthday;
    public $location_country;
    public $location_state;
    public $location_city;
    public $bio;
    public $website;

    function editProfile()
    {
        $this->standardizeInput()
             ->validateName()
             ->validateUsername()
             ->validateBio()
             ->validateLocationCity()
             ->validateWebsite();

        $userTable = new Theboard_Model_DbTable_Shuser();

        if ( !$this->profileErrors )
        {    
            $this->success = true;
            $userTable->updateProfile($this->userID, $this->name, $this->username, $this->gender, $this->birthday, $this->location_country, $this->location_state, $this->location_city, $this->bio, $this->website);

            return true;
        }
        else
        {
            return false;
        }
    }

    function validateBio()
    {
        if ( mb_strlen($this->bio) > 140 ) // Bio length error.
        {
            $this->profileErrors[] = "error_bioLen";
        }

        return $this;
    }

    function validateLocationCity()
    {
        if ( mb_strlen($this->location) > 160 ) // Location length error.
        {
            $this->profileErrors[] = "error_locationLen";
        }

        return $this;
    }

    function validateWebsite()
    {
        if ( mb_strlen($this->website) > 100 ) // Website length error.
        {
            $this->profileErrors[] = "error_websiteLen";
        }
        
        return $this;
    }

    function validateName()
    {
        $validateName = new Zend_Validate_Alpha();
        $validateName->setAllowWhiteSpace(true);
        $lengthCheck = new Zend_Validate_StringLength();

        $lengthCheck->setMin(2)->setMax(50);

        if ( !$validateName->isValid($this->name) )
        {
            $this->profileErrors[] = "error_name";
        }

        if ( !$lengthCheck->isValid($this->name) )
        {
            $this->profileErrors[] = "error_nameLen";
        }

        return $this;
    }

    function validateEmail()
    {
        if ( mb_strlen($this->email) > 0 )
        {
            $validateEmail = new Zend_Validate_EmailAddress();
            $userTable = new Theboard_Model_DbTable_Shuser();
            
            if ( !$validateEmail->isValid($this->email) || !Model_Lib_Func::verifyEmailDomain($this->email) )
            {
                $this->profileErrors[] = "error_email";
            }
            else
            {
                if ( $userTable->getUserByEmail($this->email, $this->userID) )
                {
                    $this->profileErrors[] = "error_emailExists";
                }
            }
        }

        return $this;
    }

    function validateUsername()
    {
        if ( mb_strlen($this->username) > 0 )
        {
            $lengthCheck = new Zend_Validate_StringLength();
            $userTable = new Theboard_Model_DbTable_Shuser();
            
            $restricted = array(
                "index",
                "signup",
                "login",
                "about",
                "contact",
                "privacy",
                "products",
                "terms",
                "test",
                "verify",
                "mobile",
                "web",
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
            
            if ( array_search(strtolower($this->username), $restricted) !== FALSE ||
                    !preg_match("/^[a-zA-Z0-9\._-]{2,15}$/", $this->username) ||
                    !$lengthCheck->isValid($this->username) )
            {
                $this->profileErrors[] = "error_username";
            }
            else
            {
                if ( $userTable->getUserByUsername($this->username, $this->userID) )
                {
                    $this->profileErrors[] = "error_usernameExists";
                }
            }
        }

        return $this;
    }

    function standardizeInput()
    {
        $this->name = trim($this->name);
        $this->name = strtolower($this->name);
        $this->name = ucwords($this->name);
        $this->name = Model_Lib_Func::stripExtraSpace($this->name);
        
        $this->username = trim($this->username);
        $this->website = trim($this->website);
        $this->location_country = trim($this->location_country);
        $this->location_state = trim($this->location_state);
        $this->location_city = trim($this->location_city);
        $this->bio = trim($this->bio);

        return $this;
    }
}