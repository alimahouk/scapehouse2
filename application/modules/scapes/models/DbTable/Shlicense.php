<?php

/**
 * Scapes license management class.
 *
 * @copyright  2014 Scapehouse
 */

class Scapes_Model_DbTable_Shlicense extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_scapes_purchase"; // The table name.
    protected $_schema = "scapes"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs a passcode
     *
     * @param int $userID ID of user.
     * @param string $licenseType The license type.
     *
     * @return void
     */
    function addLicense($userID, $licenseType)
    {
        $this->_db->insert($this->_name, array(
            "user_id" => $userID,
            "license_type" => $licenseType,
            "timestamp" => gmdate("Y-m-d H:i:s", time()))
        );
    }

    /**
     * Gets the status of a license by looking up the user ID.
     *
     * @param int $userID ID of the user.
     *
     * @return License row data. If the user is still in trial, nothing will be returned.
     */
    function getLicenseForUserID($userID)
    {
        if ( !$userID ) // Just a failsafe against NULL values.
        {
            $userID = "";
        }

        $query = $this->_db->select()->from($this->_name)->where("user_id = ?", $userID);
        $result = $this->_db->fetchRow($query);
        
        if ( $result["purchase_id"] ) 
        {
            return $result;
        }
    }
}