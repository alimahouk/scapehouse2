<?php

/**
 * Nightboard access token table management class.
 *
 * @copyright  2015 Scapehouse
 */

class Theboard_Model_DbTable_Shaccesstoken extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_access_token"; // The table name.
    protected $_schema = "theboard"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs an access token.
     *
     * @param int $userID ID of user.
     * @param string $token Token string.
     * @param string $osName The name of the OS running on the device of the user.
     * @param string $osVersion The OS version running on the device of the user.
     * @param string $deviceName The name given to the device by the user.
     * @param string $deviceType The device the user used to log in.
     *
     * @return int ID of the newly-created token.
     */
    function logToken($userID, $token, $osName, $osVersion, $deviceName, $deviceType)
    {
        $this->_db->insert($this->_name, array(
            "user_id" => $userID,
            "token" => $token,
            "os_name" => $osName,
            "os_version" => $osVersion,
            "device_name" => $deviceName,
            "device_type_id" => $deviceType,
            "timestamp" => gmdate("Y-m-d H:i:s", time()))
        );

        $tokenID = $this->_db->lastInsertId($this->_name);

        return $tokenID;
    }

    /**
     * Updates the device name associated with a session.
     *
     * @param int $tokenID The ID of the session.
     * @param string $deviceName The name given to the device by the user.
     *
     * @return void
     */
    function updateDeviceName($tokenID, $deviceName)
    {
        $tokenIDQ = $this->_db->quoteInto("?", $tokenID);
        
        $this->_db->update($this->_name, array("device_name" => $deviceName), "token_id = {$tokenIDQ}");
    }

    /**
     * Updates the device OS info associated with a session.
     *
     * @param int $tokenID The ID of the session.
     * @param string $osName The name of the OS running on the device of the user.
     * @param string $osVersion The OS version running on the device of the user.
     *
     * @return void
     */
    function updateOsInfo($tokenID, $osName, $osVersion)
    {
        $tokenIDQ = $this->_db->quoteInto("?", $tokenID);
        
        $this->_db->update($this->_name, array("os_name" => $osName, "os_version" => $osVersion), "token_id = {$tokenIDQ}");
    }

    /**
     * Gets a token log's row by looking up the token.
     *
     * @param string $token The token string.
     *
     * @return array Token row data.
     */
    function getTokenByToken($token)
    {
        if ( !$token ) // Just a failsafe to against NULL values.
        {
            $token = "";
        }

        $query = $this->_db->select()->from($this->_name)->where("token = ?", $token);
        
        return $this->_db->fetchRow($query);
    }

    /**
     * Gets a token log's row by looking up the token ID.
     *
     * @param string $tokenID The token ID.
     *
     * @return array Token row data.
     */
    function getTokenByTokenID($tokenID)
    {
        if ( !$tokenID ) // Just a failsafe to against NULL values.
        {
            $tokenID = "";
        }

        $query = $this->_db->select()->from($this->_name)->where("token_id = ?", $tokenID);
        
        return $this->_db->fetchRow($query);
    }

    /**
     * Gets a token log's row by looking up the user ID.
     *
     * @param int $userID ID of the user holding the token.
     *
     * @return array Token row data.
     */
    function getTokensByUserID($userID)
    {
        if ( !$userID ) // Just a failsafe to against NULL values.
        {
            $userID = "";
        }

        $query = $this->_db->select()->from($this->_name)->where("user_id = ?", $userID);

        return $this->_db->fetchAll($query);
    }

    /**
     * Deletes a token entry.
     *
     * @param int $userID ID of the user holding the token.
     *
     * @return void
     */
    function deleteTokensByUserID($userID)
    {
        $userID = $this->_db->quoteInto("?", $userID);
        $this->_db->delete($this->_name, "user_id = {$userID}");
    }

    /**
     * Deletes a token entry.
     *
     * @param int $token Token to be deleted.
     *
     * @return void
     */
    function deleteTokenByToken($token)
    {
        $token = $this->_db->quoteInto("?", $token);
        $this->_db->delete($this->_name, "token = {$token}");
    }

    /**
     * Deletes a token entry.
     *
     * @param int $tokenID ID of the token to be deleted.
     *
     * @return void
     */
    function deleteTokenByTokenID($tokenID)
    {
        $tokenID = $this->_db->quoteInto("?", $tokenID);
        $this->_db->delete($this->_name, "token_id = {$tokenID}");
    }
}