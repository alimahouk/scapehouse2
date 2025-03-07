<?php

/**
 * Scapehouse user presence management class.
 *
 * @copyright  2014 Scapehouse
 */

class Scapes_Model_DbTable_Shuseronlinestatus extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_user_online_status"; // The table name.
    protected $_schema = "scapes"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    function createFreshPresence($userID)
    {
        $this->_db->insert($this->_name, array(
            "user_id" => $userID,
            "status" => 1,
            "target_id" => -1,
            "audience" => 3,
            "timestamp" => gmdate("Y-m-d H:i:s", time()))
        );
    }

    /**
     * Set's a user's online presence.
     *
     * @param int $userID The user's ID.
     * @param int $targetID The target of the user's action. e.g. the ID of the person they're talking to.
     * @param string $timezone The status type.
     * 
     * @return void.
     */
    function setStatus($userID, $status, $masked, $targetID = -1, $audience = 1)
    {
        $userIDQ = $this->_db->quoteInto("?", $userID);
        $actualPresence = $status;

        if ( $masked )
        {
            $masked = 1;
        }
        else
        {
            $masked = 0;
        }

        // Special cases where we might have multiple connected instances of the same person.
        // We only set presence to the highest common denominator.
        if ( $status == 1 || $status == 4 || $status == 14 )
        {
            $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
            $activeTokens = $accessTokenTable->getTokensByUserID($userID);
            
            foreach ( $activeTokens as $key => $session )
            {
                if ( $session["session_presence"] == 1 )
                {
                    $actualPresence == 1;

                    break;
                }
                else if ( $session["session_presence"] == 4 )
                {
                    $actualPresence == 4;

                    break;
                }
                else if ( $session["session_presence"] == 14 )
                {
                    $actualPresence == 14;

                    break;
                }
            }
        }
        
        $this->_db->update($this->_name, array(
            "status" => $actualPresence,
            "target_id" => $targetID,
            "audience" => $audience,
            "masked" => $masked,
            "timestamp" => gmdate("Y-m-d H:i:s", time())),
            array("user_id = {$userIDQ}")
        );
    }

    /**
     * Get's a user's online presence.
     *
     * KEY:
     * 1 = offline
     * 2 = online
     * 3 = online - masked
     * 4 = away
     * 5 = typing
     * 6 = stopped typing
     * 7 = sending photo
     * 8 = sending video
     * 9 = sending audio
     * 10 = sending location
     * 11 = sending contact
     * 12 = sending file
     * 13 = checking link
     * 14 = offline - masked
     *
     * Audience:
     * 1 = recipient
     * 2 = contacts
     * 3 = everyone
     *
     * @param int $userID The user's ID for whom you want the presence.
     * 
     * @return Presence data.
     */
    function getStatus($userID)
    {
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_user_online_status
                    WHERE user_id = {$userID}");
        
        $result = $query->fetch();

        if ( $result["user_id"] )
        {
            return $result;
        }
    }

    /**
     * @param int $userID The ID of the user requesting the presence data.
     * 
     * @return Presence data.
     */
    function getStatusAll($userID)
    {
        $query = $this->_db->select()->from($this->_name)
                ->join("sh_scapes_follow", "sh_scapes_follow.followed_userid <> {$userID} AND user_id = sh_scapes_follow.followed_userid AND sh_scapes_follow.follower_userid = {$userID} AND sh_scapes_follow.removed_by_user = 0 AND sh_scapes_follow.followed_userid NOT IN (SELECT blocker_id FROM sh_blocklist WHERE sh_blocklist.blocker_id = sh_scapes_follow.followed_userid AND sh_blocklist.blockee_id = {$userID})", array());

        return $this->_db->fetchAll($query);
    }

    /**
     * Sets everyone as offline.
     * 
     * @return void.
     */
    function resetAll()
    {
        $this->_db->update($this->_name, array(
            "status" => 1,
            "target_id" => -1,
            "audience" => -1,
            "timestamp" => gmdate("Y-m-d H:i:s", time())),
            array("masked = 0 AND status <> 1")
        );

        // Masked users.
        $this->_db->update($this->_name, array(
            "status" => 14,
            "target_id" => -1,
            "audience" => -1,
            "timestamp" => gmdate("Y-m-d H:i:s", time())),
            array("masked = 1 AND status <> 14")
        );
    }
}