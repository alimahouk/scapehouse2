<?php

/**
 * Nightboard user blocklist table management class.
 *
 * @copyright  2015 Scapehouse
 */
class Theboard_Model_DbTable_Shblocklist extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_blocklist"; // The table name.
    protected $_schema = "theboard"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Blocks a user.
     *
     * @param int $blocker The ID of the user.
     * @param int $blockee The ID of the target user.
     *
     * @return void
     */
    function block($blockerID, $blockeeID)
    {
        $this->_db->insert($this->_name, array(
            "blocker_id" => $blockerID,
            "blockee_id" => $blockeeID,
            "timestamp" => gmdate("Y-m-d H:i:s", time()))
        );
    }

    /**
     * Removes a user from the blocklist.
     *
     * @param int $blocker The ID of the user.
     * @param int $blockee The ID of the target user.
     *
     * @return void
     */
    function unblock($blockerID, $blockeeID)
    {
        $this->_db->delete($this->_name, array(
            "blocker_id = ?" => $blockerID,
            "blockee_id = ?" => $blockeeID
        ));
    }

    /**
     * Checks if a user is blocked by another user.
     *
     * @param int $blocker The ID of the user.
     * @param int $blockee The ID of the target user.
     *
     * @return void
     */
    function isBlocked($blockerID, $blockeeID)
    {
        $query = $this->_db->query("
                    SELECT COUNT(*)
                    FROM sh_blocklist WHERE blocker_id = {$blockerID} AND blockee_id = {$blockeeID}");
        
        $query->setFetchMode(Zend_Db::FETCH_NUM);
        $result = $query->fetch();

        if ( $result[0] == 0 ) // User not in blocklist.
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Gets a list of all people blocked by the given user.
     *
     * @param int $blocker The ID of the user.
     *
     * @return void
     */
    function getPeopleBlockedByUser($blockerID)
    {
        $query = $this->_db->query("
                SELECT *
                FROM sh_blocklist
                WHERE blocker_id = {$blockerID}");
            
        $results = $query->fetchAll();

        return $results;
    }
}