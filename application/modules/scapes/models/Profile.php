<?php

/**
 * Scapehouse user profile management class.
 *
 * @copyright  2014 Scapehouse
 */

class Scapes_Model_Profile
{
    // Define properties.
    public $userID;
    public $firstName;
    public $lastName; 
    public $username; 
    public $gender;
    public $email;
    public $birthday;
    public $location_country;
    public $location_state;
    public $location_city;
    public $bio;
    public $website;
    public $facebookID;
    public $twitterID;
    public $instagramID;

    function editProfile()
    {
        $this->standardizeInput()
             ->validateFirstName()
             ->validateLastName()
             ->validateUsername()
             ->validateEmail()
             ->validateBio()
             ->validateLocationCity()
             ->validateWebsite();

        $userTable = new Scapes_Model_DbTable_Shuser();

        if ( !$this->profileErrors )
        {    
            $this->success = true;
            $userTable->updateProfile($this->userID, $this->firstName, $this->lastName, $this->username, $this->gender, $this->email, $this->birthday, $this->location_country, $this->location_state, $this->location_city, $this->bio, $this->website, $this->facebookID, $this->twitterID, $this->instagramID);

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

    function validateFirstName()
    {
        $validateName = new Zend_Validate_Alpha();
        $validateName->setAllowWhiteSpace(true);
        $lengthCheck = new Zend_Validate_StringLength();

        $lengthCheck->setMin(2)->setMax(50);

        if ( !$validateName->isValid($this->firstName) )
        {
            $this->profileErrors[] = "error_firstName";
        }

        if ( !$lengthCheck->isValid($this->firstName) )
        {
            $this->profileErrors[] = "error_firstNameLen";
        }

        return $this;
    }

    function validateLastName()
    {
        $validateName = new Zend_Validate_Alpha();
        $validateName->setAllowWhiteSpace(true);
        $lengthCheck = new Zend_Validate_StringLength();

        $lengthCheck->setMin(2)->setMax(50);

        if ( !$validateName->isValid($this->lastName) )
        {
            $this->profileErrors[] = "error_lastName";
        }

        if ( !$lengthCheck->isValid($this->lastName) )
        {
            $this->profileErrors[] = "error_lastNameLen";
        }

        return $this;
    }

    function validateEmail()
    {
        if ( mb_strlen($this->email) > 0 )
        {
            $validateEmail = new Zend_Validate_EmailAddress();
            $userTable = new Scapes_Model_DbTable_Shuser();
            
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
            $userTable = new Scapes_Model_DbTable_Shuser();
            
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
        $this->firstName = trim($this->firstName);
        $this->firstName = strtolower($this->firstName);
        $this->firstName = ucwords($this->firstName);
        $this->firstName = Model_Lib_Func::stripExtraSpace($this->firstName);

        $this->lastName = trim($this->lastName);
        $this->lastName = strtolower($this->lastName);
        $this->lastName = ucwords($this->lastName);
        $this->lastName = Model_Lib_Func::stripExtraSpace($this->lastName);

        $this->email = trim($this->email);
        $this->username = trim($this->username);
        $this->website = trim($this->website);
        $this->location_country = trim($this->location_country);
        $this->location_state = trim($this->location_state);
        $this->location_city = trim($this->location_city);
        $this->bio = trim($this->bio);

        return $this;
    }
}