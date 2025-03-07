<?php

/**
 * Hashtag alert table management class.
 *
 * @copyright  2015 Scapehouse
 */

class Scapes_Model_DbTable_Shhashtag extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_scapes_hashtag_alert"; // The table name.
    protected $_schema = "scapes"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }

    /**
     * Registers a user for an alert on a hashtag.
     *
     * @param string $userID The ID of the user.
     * @param string $hashtag The hashtag.
     *
     * @return void
     */
	function addHashtag($userID, $hashtag)
    {
        $userIDQ = $this->_db->quoteInto("?", $userID);
        $hashtagQ = $this->_db->quoteInto("?", $hashtag);

        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_hashtag_alert
                    WHERE user_id = {$userIDQ} AND hashtag = {$hashtagQ}");
        
        $result = $query->fetch();

        if ( !$result )
        {
            $this->_db->insert($this->_name, array(
                "user_id" => $userID,
                "hashtag" => $hashtag
            ));
        }
 	}

    /**
     * Unregisters a user for an alert on a hashtag.
     *
     * @param string $userID The ID of the user.
     * @param string $hashtag The hashtag.
     *
     * @return void
     */
    function removeHashtagForUser($userID, $hashtag)
    {
        $userIDQ = $this->_db->quoteInto("user_id = ?", $userID);
        $hashtagQ = $this->_db->quoteInto("hashtag = ?", $hashtag);
        
        $this->_db->delete($this->_name, "{$userIDQ} AND {$hashtagQ}");
    }

    /**
     * Unregisters a user for an alert on a hashtag.
     *
     * @param string $userID The ID of the user.
     *
     * @return void
     */
    function removeAllHashtagsForUser($userID)
    {
        $where = $this->_db->quoteInto("user_id = ?", $userID);
        
        $this->_db->delete($this->_name, $where);
    }

    /**
     * Gets a list of users to alert for a hashtag.
     *
     * @param string $hashtag The hashtag.
     *
     * @return array The list of users.
     */
    function usersForHashtag($hashtag)
    {
        $where = $this->_db->quoteInto("hashtag = ?", $hashtag);

        $query = $this->_db->select()->from($this->_name)
                ->where($where);

        return $this->_db->fetchAll($query);
    }

    /**
     * Gets a list of users to alert for a hashtag.
     *
     * @param string $hashtag The hashtag.
     *
     * @return array The list of users.
     */
    function hashtagsForUser($userID)
    {
        $where = $this->_db->quoteInto("user_id = ?", $userID);

        $query = $this->_db->select()->from($this->_name)
                ->where($where);
        
        return $this->_db->fetchAll($query);
    }
}
