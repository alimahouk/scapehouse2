<?php

/**
 * Scapehouse Scapes signup handling.
 *
 * @copyright  2014 Scapehouse
 */

class Scapes_Model_Signup
{
    // Define properties.
    public $firstName;
    public $lastName;
    public $countryID;
    public $countryCallingCode;
    public $prefix;
    public $phoneNumber;
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
             ->validateName();

        $userTable = new Scapes_Model_DbTable_Shuser();
        $phoneNumberTable = new Scapes_Model_DbTable_Shphonenumber();

        if ( !$this->signupErrors )
        {
            $userID = $userTable->createUser($this->firstName, $this->lastName, $this->locale, $this->timezone, $this->accessToken, $this->osName, $this->osVersion, $this->deviceName, $this->deviceType);
            
            if ( $userID )
            {
                $phoneNumberID = $phoneNumberTable->createPhoneNumber($userID, $this->countryID, $this->countryCallingCode, $this->prefix, $this->phoneNumber);
                
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

        if ( !$lengthCheck->isValid($this->firstName) )
        {
            $this->signupErrors["firstNameLengthError"] = true;
        }

        if ( !$lengthCheck->isValid($this->lastName) )
        {
            $this->signupErrors["lastNameLengthError"] = true;
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

        $this->phoneNumber = trim($this->phoneNumber);

        return $this;
    }

}