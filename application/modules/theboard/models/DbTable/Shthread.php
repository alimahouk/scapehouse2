<?php

/**
 * Nightboard thread table management class
 *
 * @copyright  2015 Scapehouse
 */

class Theboard_Model_DbTable_Shthread extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_thread"; // The table name.
    protected $_schema = "theboard"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }

    /**
     * Creates a new thread.
     *
     * THREAD TYPE KEY:
     * 1 = message
     * 2 = status/text
     * 3 = status/location
     * 4 = status/song
     * 5 = status/dp
     * 6 = status/profile change
     * 7 = status/join
     * 8 = message/location
     *
     * OWNER TYPE KEY:
     * 1 = user
     * 2 = bot
     * 
     * THREAD PRIVACY KEY:
     * 1 = private
     * 2 = public
     *
     * @param array    $messageData The message data chunk.
     *
     * @return ID of the newly-created thread.
     */
	function spawnThread($messageData)
    {
        $groupID = $messageData['group_id'];

        if ( $groupID == -1 ) // Blank group ID.
        {
            $groupID = new Zend_Db_Expr('NULL');
        }

        $this->_db->insert($this->_name, array(
            "thread_type" => 1,
            "root_item_id" => $messageData['root_item_id'],
            "owner_id" => $messageData['owner_id'],
            "timestamp_sent" => $messageData['timestamp_sent'],
            "message" => $messageData['message'],
            "location_longitude" => $messageData['location_longitude'],
            "location_latitude" => $messageData['location_latitude'],
            "media_type" => $messageData['media_type'],
            "media_hash" => $messageData['media_hash'],
            "media_extra" => $messageData['media_extra']
        ));
        
        $threadID = $this->_db->lastInsertId($this->_name);
        
        return $threadID;
 	}

    /**
     * Deletes a thread along with all its children.
     *
     * @param int $userID The ID of the user issuing the delete command. You can only delete threads for which you have deletion privileges.
     * @param int $threadID The thread's ID.
     * 
     * @return void
     */
    function deleteThread($userID, $threadID)
    {
        // Update the badge count for all recipients to decrement it BEFORE deleting the thread.
        $dispatchTable = new Theboard_Model_DbTable_Shmessagedispatch();
        $recipientIDs = $dispatchTable->recipientsForThread($threadID);

        if ( is_array($recipientIDs) )
        {
            foreach ( $recipientIDs as $key => $recipientID )
            {
                $badgeCount = $this->getTotalUnreadThreadCount($recipientID);
                $appletokenTable = new Theboard_Model_DbTable_Shappletoken();
                $appletokenTable->updateBadgeCount($recipientID, $badgeCount);
            }
        }
        
        $userIDQ = $this->_db->quoteInto("owner_id = ?", $userID);
        $threadIDQ = $this->_db->quoteInto("thread_id = ?", $threadID);

        $where = $userIDQ . " AND " . $threadIDQ;

        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_thread
                    WHERE {$where}");
        
        $result = $query->fetch();
        
        if ( $result["media_hash"] != -1 ) // Delete the media from storage.
        {
            $photoProcessor = new Theboard_Model_Photo();

            if ( $result["media_type"] == 1 )
            {
                $photoProcessor->deleteMediaPhoto($userID, $result["media_hash"]);
            }
        }

        $this->_db->delete($this->_name, $where);
    }

    /**
     * Publishes a new status update.
     *
     * @param int $userID The user's ID.
     * @param string $status The status text.
     * @param string $statusType The status type.
     * 
     * @return The newly-created status' ID.
     */
    function publishStatus($userID, $status, $statusType)
    {
        if ( $statusType == 5 ) // Delete any previous photo change status.
        {
            $userIDQ = $this->_db->quoteInto("owner_id = ?", $userID);
            $this->_db->delete($this->_name, "{$userIDQ} AND thread_type = 5");
        }

        $mediaExtra["attachment_type"] = "null";
        $mediaExtraJSON = json_encode($mediaExtra);
        
        $this->_db->insert($this->_name, array(
            "thread_type" => $statusType,
            "owner_id" => $userID,
            "timestamp_sent" => gmdate("Y-m-d H:i:s", time()),
            "media_hash" => -1,
            "media_extra" => $mediaExtraJSON,
            "message" => $status
        ));

        $statusID = $this->_db->lastInsertId($this->_name);

        return $statusID;
    }

    /**
     * Delete's a status update.
     *
     * @param int $followerID The ID of the status update.
     *
     * @return void
     */
    function deleteStatus($statusID)
    {
        $threadIDQ = $this->_db->quoteInto("thread_id = ?", $statusID);
        
        $this->_db->delete($this->_name, $threadIDQ);
    }

    /**
     * Delete's all status updates of a particular type belonging to a particular user.
     *
     * @param int $userID The user's ID.
     * @param int $statusType The type of status update.
     *
     * @return void
     */
    function deleteStatusesOfType($userID, $statusType)
    {
        $userIDQ = $this->_db->quoteInto("owner_id = ?", $userID);
        $threadTypeQ = $this->_db->quoteInto("thread_type = ?", $statusType);
        
        $this->_db->delete($this->_name, "{$userIDQ} AND {$threadTypeQ}");
    }

    /**
     * Gets a user's latest generic status update (doesn't include DP changes, profile changes, etc.)
     *
     * @param int $userID The user's ID.
     * 
     * @return The status update.
     */
    function getLatestGenericStatusUpdate($userID)
    {
        // Only get statuses of type text, location, or song.
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_thread
                    WHERE owner_id = {$userID} AND thread_type IN (2, 3, 4)
                    ORDER BY thread_id DESC LIMIT 1");
        
        $result = $query->fetch();

        $result["media_extra"] = json_decode($result["media_extra"]);

        return $result;
    }

    /**
     * Gets a user's latest status update.
     *
     * @param int $userID The user's ID.
     * 
     * @return The status update.
     */
    function getLatestStatusUpdate($userID)
    {
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_thread
                    WHERE owner_id = {$userID} AND thread_type NOT IN (1, 8)
                    ORDER BY thread_id DESC LIMIT 1");
        
        $result = $query->fetch();

        $result["media_extra"] = json_decode($result["media_extra"]);

        return $result;
    }

    /**
     * Gets a user's Mini Feed.
     *
     * @param int $userID The user's ID.
     * 
     * @return The list of status updates.
     */
    function getMiniFeed($userID, $batch = 0)
    {
        $query = $this->_db->select()->from($this->_name)
                ->join("sh_follow", "sh_follow.followed_userid = sh_thread.owner_id AND sh_follow.follower_userid = {$userID} AND sh_follow.removed_by_user = 0 AND thread_type NOT IN (1, 8) AND sh_thread.owner_id NOT IN (SELECT blockee_id FROM sh_blocklist WHERE sh_blocklist.blocker_id = {$userID} AND sh_blocklist.blockee_id = sh_thread.owner_id) AND sh_thread.owner_id NOT IN (SELECT blocker_id FROM sh_blocklist WHERE sh_blocklist.blocker_id = sh_thread.owner_id AND sh_blocklist.blockee_id = {$userID})", array())
                ->limit($GLOBALS["batch_size"], $batch * $GLOBALS["batch_size"])
                ->order("sh_thread.timestamp_sent DESC");

        return $this->_db->fetchAll($query);
    }
}
