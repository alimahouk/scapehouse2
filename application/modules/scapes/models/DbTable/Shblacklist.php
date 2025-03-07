<?php

/**
 * Scapes user blacklist table management class.
 *
 * @copyright  2014 Scapehouse
 */
class Scapes_Model_DbTable_Shblacklist extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_blacklist"; // The table name.
    protected $_schema = "scapes"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Blacklists a user.
     *
     * @param int $badUserID The ID of the bad user.
     * @return void
     */
    function blacklist($badUserID)
    {
        $this->_db->insert($this->_name, array(
            "user_id" => $badUserID,
            "timestamp" => gmdate("Y-m-d H:i:s", time()))
        );
    }

    /**
     * Removes a user from the blacklist.
     *
     * @param int $goodUserID The ID of the good guy user.
     * @return void
     */
    function removeFromList($goodUserID)
    {
        $db->delete($this->_name, array(
            "user_id = ?" => $goodUserID
        ));
    }

    /**
     * Checks if a user is blacklisted.
     *
     * @param int $userID The ID of the user.
     * @return void
     */
    function isBadUser($userID)
    {
        $query = $this->_db->query("
                    SELECT COUNT(*)
                    FROM sh_blacklist WHERE user_id = {$userID}");
        
        $query->setFetchMode(Zend_Db::FETCH_NUM);
        $result = $query->fetch();

        if ( $result[0] == 0 ) // User not in blacklist.
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}