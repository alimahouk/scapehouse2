<?php

/**
 * Nightboard phone number management class.
 *
 * @copyright  2014 Scapehouse
 */

class Theboard_Model_DbTable_Shphonenumber extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_phone_number"; // The table name.
    protected $_schema = "theboard"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Creates a phone number entry.
     *
     * @param int $userID ID of user.
     * @param string $countryCallingCode The international calling code.
     * @param string $prefix The prefix of the number.
     * @param string $phoneNumber The phone number.
     *
     * @return int ID of the newly-created phone number.
     */
    function createPhoneNumber($userID, $countryID, $countryCallingCode, $prefix, $phoneNumber)
    {
        $countryCodeID = $countryID;
        
        if ( !$countryID )
        {
            $countryCodeID = $this->getCountryID($countryCallingCode);
        }

        $this->_db->insert($this->_name, array(
            "user_id" => $userID,
            "prefix" => $prefix,
            "country_calling_code_id" => $countryCodeID,
            "number" => $phoneNumber,
            "timestamp" => gmdate("Y-m-d H:i:s", time()))
        );

        $numberID = $this->_db->lastInsertId($this->_name);

        return $numberID;
    }

    /**
     * Gets the ID of a country from a calling code.
     *
     * @param string $countryCallingCode The international calling code.
     *
     * @return int ID of the country.
     */
    function getCountryID($countryCallingCode)
    {
        $query = $this->_db->query("
            SELECT *
            FROM sh_country
            WHERE calling_code = {$countryCallingCode}");
        
        $result = $query->fetch();

        return $result["country_id"];
    }

    /**
     * Gets the ID of a user for a certain phone number.
     *
     * @param string $countryCallingCode The international calling code.
     * @param string $prefix The prefix of the number.
     *
     * @return ID of the user.
     */
    function getUserIDForNumber($countryCallingCode, $prefix, $phoneNumber)
    {
        $countryCodeID = $this->getCountryID($countryCallingCode);
        
        $query = $this->_db->query("
                SELECT *
                FROM sh_scapes_phone_number
                WHERE number = {$phoneNumber} AND prefix = {$prefix} AND country_calling_code_id = {$countryCodeID}");
        
        $result = $query->fetch();

        return $result["user_id"];
    }

    /**
     * Gets the phone numbers of a certain user ID.
     *
     * @param int $userID The ID.
     *
     * @return The user array.
     */
    function getNumbersForUserID($userID)
    {
        $query = $this->_db->query("
                SELECT *
                FROM sh_scapes_phone_number
                WHERE user_id = {$userID}
                ORDER BY timestamp DESC");
            
        $results = $query->fetchAll();

        return $results;
    }

    /**
     * Gets a list of all countries.
     *
     * @return The list of countries.
     */
    function getCountryList()
    {
        $query = $this->_db->query("
                SELECT * 
                FROM sh_country");
        
        $results = $query->fetchAll();
        
        return $results;
    }

    /**
     * Gets the country details for a given calling code.
     *
     * @param string $countryCallingCode The international calling code.
     *
     * @return The country.
     */
    function getCountryByCallingCode($countryCallingCode)
    {
        $query = $this->_db->query("
            SELECT *
            FROM sh_country
            WHERE calling_code = {$countryCallingCode}");
        
        $result = $query->fetch();
        
        return $result;
    }

    /**
     * Gets the given calling code of a country ID.
     *
     * @param string $countryCodeID The country ID.
     *
     * @return int The calling code.
     */
    function getCountryCallingCode($countryCodeID)
    {
        $query = $this->_db->query("
            SELECT *
            FROM sh_country
            WHERE country_id = {$countryCodeID}");
        
        $result = $query->fetch();
        
        return $result["calling_code"];
    }
}