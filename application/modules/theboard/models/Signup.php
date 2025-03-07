<?php

/**
 * Scapehouse signup handling.
 *
 * @copyright  2015 Scapehouse
 */

class Theboard_Model_Signup
{
    // Define properties.
    public $name;
    public $email;
    public $locale;
    public $timezone;
    public $accessToken;
    public $osName;
    public $osVersion;
    public $deviceName;
    public $deviceType;
    public $signupErrors;

    function signup()
    {
        $this->standardizeInput()
             ->validateName()
             ->validateEmail();

        $userTable = new Theboard_Model_DbTable_Shuser();
        $phoneNumberTable = new Theboard_Model_DbTable_Shphonenumber();

        if ( !$this->signupErrors )
        {
            $userID = $userTable->createUser($this->name, $this->email, $this->locale, $this->timezone, $this->accessToken, $this->osName, $this->osVersion, $this->deviceName, $this->deviceType);
            
            if ( $userID )
            {
                return $userID;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    function validateName()
    {
        $lengthCheck = new Zend_Validate_StringLength();
        $lengthCheck->setMin(1)->setMax(45);

        if ( !$lengthCheck->isValid($this->name) )
        {
            $this->signupErrors["nameLengthError"] = true;
        }

        if ( !$lengthCheck->isValid($this->name) )
        {
            $this->signupErrors["nameLengthError"] = true;
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
                if ( $userTable->getUserByEmail($this->email) )
                {
                    $this->profileErrors[] = "error_emailExists";
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

        $this->email = trim($this->email);
        $this->email = Model_Lib_Func::stripExtraSpace($this->email);

        return $this;
    }

}