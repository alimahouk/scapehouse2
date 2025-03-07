<?php

/**
 * Nightboard Apple token table management class.
 *
 * @copyright  2015 Scapehouse
 */

class Theboard_Model_DbTable_Shappletoken extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_device_token"; // The table name.
    protected $_schema = "theboard"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs an access token.
     *
     * @param int $sessionID The ID of the current user's session.
     * @param int $userID The user ID of token holder.
     * @param string $deviceToken  The Apple token.
     *
     * @return int The ID of the newly-created token.
     */
    function logToken($sessionID, $userID, $deviceToken)
    {
        // Prevent duplicate same device token logs.
        $deviceTokenQ = $this->_db->quoteInto("?", $deviceToken);
        $this->_db->delete($this->_name, "token = {$deviceTokenQ}");
        
        // Prevent duplicate same session token logs.
        $sessionIDQ = $this->_db->quoteInto("?", $sessionID);
        $this->_db->delete($this->_name, "session_id = {$sessionIDQ}");
        
        $this->_db->insert($this->_name, array(
            "session_id" => $sessionID,
            "user_id" => $userID,
            "token" => $deviceToken,
            "badge_count" => 0,
            "timestamp" => gmdate("Y-m-d H:i:s", time()))
        );

        $tokenID = $this->_db->lastInsertId($this->_name);

        return $tokenID;
    }

    /**
     * Updates an existing access token.
     *
     * @param int $sessionID The ID of the current user's session.
     * @param string $deviceToken  The Apple token.
     *
     * @return void
     */
    function updateToken($sessionID, $userID, $deviceToken)
    {
        $sessionIDQ = $this->_db->quoteInto("?", $sessionID);
        
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_device_token
                    WHERE session_id = {$sessionIDQ}");
        
        $result = $query->fetch();
        
        if ( $result["session_id"] )
        {
            $this->_db->update($this->_name, array("token" => $deviceToken), "session_id = {$sessionIDQ}");
        }
        else
        {
            $this->logToken($sessionID, $userID, $deviceToken);
        }
    }

    /**
     * Gets token's log's row by looking up the user ID.
     *
     * @param string $userID The user ID of token holder.
     * @return Token row data.
     */
    function getTokensByUserID($userID)
    {
        if ( !$userID ) // Just a failsafe against NULL values.
        {
            $userID = "";
        }

        $query = $this->_db->select()->from($this->_name)->where("user_id = ?", $userID);

        return $this->_db->fetchAll($query);
    }
}

