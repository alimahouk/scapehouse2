<?php

/**
 * Scapes passcode management class.
 *
 * @copyright  2014 Scapehouse
 */

class Scapes_Model_DbTable_Shpasscode extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_scapes_passcode"; // The table name.
    protected $_schema = "scapes"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs a passcode
     *
     * @param int $userID ID of user.
     * @param string $tokenID ID of the token.
     * @param string $passcode The new passcode.
     * @return ID of the newly-created token.
     */
    function addPasscode($userID, $tokenID, $passcode)
    {
        $this->_db->insert($this->_name, array(
            "user_id" => $userID,
            "token_id" => $tokenID,
            "passcode" => $passcode,
            "timestamp" => gmdate("Y-m-d H:i:s", time()))
        );

        $passcodeID = $this->_db->lastInsertId($this->_name);

        return $passcodeID;
    }

    /**
     * Gets a passcode's row by looking up the passcode.
     *
     * @param int $tokenID The token ID.
     * @param string $passcode The new passcode string.
     * @return Passcode row data.
     */
    function changePasscodeForTokenID($tokenID, $passcode)
    {
        $tokenID = $this->_db->quoteInto("?", $tokenID);
        $this->_db->update($this->_name, array("passcode" => $passcode), "token_id = {$tokenID}");
    }

    /**
     * Gets a passcode's row by looking up the passcode.
     *
     * @param string $passcode The passcode string.
     * @return Passcode row data.
     */
    function getPasscodeByPasscode($passcode)
    {
        if ( !$passcode ) // Just a failsafe against NULL values.
        {
            $passcode = "";
        }

        $query = $this->_db->select()->from($this->_name)->where("passcode = ?", $passcode);
        
        return $this->_db->fetchRow($query);
    }

    /**
     * Gets a passcode's row by looking up the token ID.
     *
     * @param string $token The token ID.
     * @return Passcode row data.
     */
    function getPasscodeByTokenID($tokenID)
    {
        if ( !$tokenID )
        {
            $tokenID = "";
        }

        $query = $this->_db->select()->from($this->_name)->where("token_id = ?", $tokenID);
        
        return $this->_db->fetchRow($query);
    }

    /**
     * Gets a passcode's row by looking up the user ID.
     *
     * @param int $userID ID of the user holding the passcode.
     * @return Passcode row data.
     */
    function getPasscodesByUserID($userID)
    {
        if ( !$userID )
        {
            $userID = "";
        }

        $query = $this->_db->select()->from($this->_name)->where("user_id = ?", $userID);

        return $this->_db->fetchAll($query);
    }

    /**
     * Deletes a passcode entry.
     *
     * @param int $userID ID of the user holding the passcode.
     * @return void
     */
    function deletePasscodesByUserID($userID)
    {
        $userID = $this->_db->quoteInto("?", $userID);
        $this->_db->delete($this->_name, "user_id = {$userID}");
    }

    /**
     * Deletes a passcode entry.
     *
     * @param int $passcode Passcode to be deleted.
     * @param int $userID ID of the user.
     * @return int The number of passcodes left that are associated with the user.
     */
    function deletePasscodeByPasscode($passcode, $userID)
    {
        $passcode = $this->_db->quoteInto("?", $passcode);
        $this->_db->delete($this->_name, "passcode = {$passcode} AND user_id = {$userID}");

        $query = $this->_db->select()->from($this->_name, "COUNT(passcode_id)")->where("user_id = ?", $userID);

        return $this->_db->fetchOne($query);
    }

    /**
     * Deletes a passcode entry.
     *
     * @param int $tokenID ID of the token to which this passcode was assigned.
     * @param int $userID ID of the user.
     * @return int The number of passcodes left that are associated with the user.
     */
    function deletePasscodeByTokenID($tokenID, $userID)
    {
        if ( !$tokenID ) // Just a failsafe to against NULL values.
        {
            $tokenID = "";
        }
        
        $tokenID = $this->_db->quoteInto("?", $tokenID);
        $this->_db->delete($this->_name, "token_id = {$tokenID}");

        $query = $this->_db->select()->from($this->_name, "COUNT(passcode_id)")->where("user_id = ?", $userID);

        return $this->_db->fetchOne($query);
    }
}