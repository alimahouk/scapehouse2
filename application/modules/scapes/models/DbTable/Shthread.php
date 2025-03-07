<?php

/**
 * Scapes message table management class
 *
 * @copyright  2014 Scapehouse
 */

class Scapes_Model_DbTable_Shthread extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_scapes_thread"; // The table name.
    protected $_schema = "scapes"; // The DB name.

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

        $ownerID = $messageData['owner_id'];
        $timestampSent = $messageData['timestamp_sent'];
        $messageQ = $this->_db->quoteInto("message = ?", $messageData['message']);

        $this->_db->insert($this->_name, array(
            "thread_type" => 1,
            "root_item_id" => $messageData['root_item_id'],
            "owner_id" => $messageData['owner_id'],
            "owner_type" => $messageData['owner_type'],
            "group_id" => $groupID,
            "privacy" => $messageData['privacy'],
            "timestamp_sent" => $messageData['timestamp_sent'],
            "message" => $messageData['message'],
            "location_longitude" => $messageData['location_longitude'],
            "location_latitude" => $messageData['location_latitude'],
            "media_type" => $messageData['media_type'],
            "media_file_size" => $messageData['media_file_size'],
            "media_hash" => $messageData['media_hash'],
            "media_extra" => $messageData['media_extra']
        ));
        
        $threadID = $this->_db->lastInsertId($this->_name);
        
        $dispatchTable = new Scapes_Model_DbTable_Shmessagedispatch();
        $dispatchTable->dispatchMessage($threadID, $messageData['owner_id'], $messageData['owner_type'], $messageData['recipient_id']);
        
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
        $dispatchTable = new Scapes_Model_DbTable_Shmessagedispatch();
        $recipientIDs = $dispatchTable->recipientsForThread($threadID);

        if ( is_array($recipientIDs) )
        {
            foreach ( $recipientIDs as $key => $recipientID )
            {
                $badgeCount = $this->getTotalUnreadThreadCount($recipientID);
                $appletokenTable = new Scapes_Model_DbTable_Shappletoken();
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
            $photoProcessor = new Scapes_Model_Photo();

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
            "owner_type" => 1,
            "group_id" => new Zend_Db_Expr('NULL'),
            "privacy" => 2,
            "timestamp_sent" => gmdate("Y-m-d H:i:s", time()),
            "timestamp_delivered" => gmdate("Y-m-d H:i:s", time()),
            "timestamp_read" => gmdate("Y-m-d H:i:s", time()),
            "group_id" => new Zend_Db_Expr('NULL'),
            "media_file_size" => -1,
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
                    FROM sh_scapes_thread
                    WHERE owner_id = {$userID} AND thread_type IN (2, 3, 4)
                    ORDER BY thread_id DESC LIMIT 1");
        
        $result = $query->fetch();

        $result["status_sent"] = 1;
        $result["media_extra"] = json_decode($result["media_extra"]);

        if ( !$result["group_id"] )
        {
            $result["group_id"] = -1;
        }

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
                    FROM sh_scapes_thread
                    WHERE owner_id = {$userID} AND thread_type NOT IN (1, 8)
                    ORDER BY thread_id DESC LIMIT 1");
        
        $result = $query->fetch();

        $result["status_sent"] = 1;
        $result["media_extra"] = json_decode($result["media_extra"]);
        
        if ( !$result["group_id"] )
        {
            $result["group_id"] = -1;
        }

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
                ->join("sh_scapes_follow", "sh_scapes_follow.followed_userid = sh_scapes_thread.owner_id AND sh_scapes_follow.follower_userid = {$userID} AND sh_scapes_follow.removed_by_user = 0 AND thread_type NOT IN (1, 8) AND sh_scapes_thread.owner_id NOT IN (SELECT blockee_id FROM sh_blocklist WHERE sh_blocklist.blocker_id = {$userID} AND sh_blocklist.blockee_id = sh_scapes_thread.owner_id) AND sh_scapes_thread.owner_id NOT IN (SELECT blocker_id FROM sh_blocklist WHERE sh_blocklist.blocker_id = sh_scapes_thread.owner_id AND sh_blocklist.blockee_id = {$userID})", array())
                ->limit($GLOBALS["batch_size"], $batch * $GLOBALS["batch_size"])
                ->order("sh_scapes_thread.timestamp_sent DESC");

        return $this->_db->fetchAll($query);
    }

    /**
     * Gets the ID of the last message sent to or received by this user.
     *
     * @param int $userID The user's ID.
     * 
     * @return int The last thread's data.
     */
    function getLastMessageForUserID($userID)
    {
        $query = $this->_db->select()->from($this->_name)
                ->join("sh_scapes_message_dispatch", "sh_scapes_message_dispatch.thread_id = sh_scapes_thread.thread_id AND (sh_scapes_message_dispatch.sender_id = {$userID} OR sh_scapes_message_dispatch.recipient_id = {$userID})", array())
                ->order("sh_scapes_thread.timestamp_sent DESC")
                ->limit(1);
        
        $result = $this->_db->fetchAll($query);

        if ( $result )
        {
            return $result;
        }
    }

    /**
     * Gets the latest 20 messages from this sender to this recipient. Includes status updates shared by both.
     *
     * @param int $senderID The sender's ID.
     * @param int $recipientID The recipients's ID.
     * 
     * @return array The threads.
     */
    function getLastMessagesBetweenUsers($user1, $user2)
    {
        $query = $this->_db->query("
            SELECT * FROM (
                SELECT sh_scapes_thread.*, sh_scapes_message_dispatch.recipient_id
                FROM sh_scapes_thread
                INNER JOIN sh_scapes_message_dispatch ON sh_scapes_message_dispatch.thread_id = sh_scapes_thread.thread_id AND ((sh_scapes_message_dispatch.sender_id = {$user1} AND sh_scapes_message_dispatch.recipient_id = {$user2}) OR (sh_scapes_message_dispatch.sender_id = {$user2} AND sh_scapes_message_dispatch.recipient_id = {$user1}))
                ORDER BY sh_scapes_thread.timestamp_sent DESC
                LIMIT 20) AS result
            ORDER BY result.timestamp_sent ASC");

        $results = $query->fetchAll();

        return $results;
    }

    /**
     * Gets all undelivered messages.
     *
     * @param int $userID The user's ID.
     * 
     * @return array The threads.
     */
    function getAllUnreadMessagesForUserID($userID)
    {
        $query = $this->_db->select()->from($this->_name)
                ->join("sh_scapes_message_dispatch", "sh_scapes_message_dispatch.thread_id = sh_scapes_thread.thread_id AND sh_scapes_thread.status_read = 0 AND sh_scapes_message_dispatch.recipient_id = {$userID} AND sh_scapes_thread.owner_id NOT IN (SELECT blockee_id FROM sh_blocklist WHERE sh_blocklist.blocker_id = {$userID} AND sh_blocklist.blockee_id = sh_scapes_thread.owner_id)", array())
                ->order("sh_scapes_thread.timestamp_sent ASC");

        return $this->_db->fetchAll($query);
    }

    /**
     * Gets the number of unread threads (not messages).
     *
     * @param int threadID The thread's ID.
     * 
     * @return int Unread count.
     */
    function getTotalUnreadThreadCount($userID)
    {
        $query = $this->_db->query("
                    SELECT COUNT(*)
                    FROM (
                        SELECT sh_scapes_thread.thread_id
                        FROM sh_scapes_thread
                        INNER JOIN sh_scapes_message_dispatch ON sh_scapes_message_dispatch.thread_id = sh_scapes_thread.thread_id AND sh_scapes_thread.status_read = 0 AND sh_scapes_message_dispatch.recipient_id = {$userID} AND sh_scapes_thread.owner_id NOT IN (SELECT blockee_id FROM sh_blocklist WHERE sh_blocklist.blocker_id = {$userID} AND sh_blocklist.blockee_id = sh_scapes_thread.owner_id)
                        GROUP BY sh_scapes_thread.owner_id
                    ) AS t");
        
        $query->setFetchMode(Zend_Db::FETCH_NUM);
        $result = $query->fetch();

        return $result[0];
    }

    /**
     * Updates the privacy level for the given parties. You either update $user1 AND $user2, OR $groupID, not both.
     *
     * @param int $privacy The privacy level.
     * @param int $senderID The first user's ID.
     * @param int $recipientID The second user's ID.
     * @param int $groupID The group ID.
     * 
     * @return void
     */
    function changePrivacyBetweenUsers($privacy, $user1, $user2, $groupID = NULL)
    {
        $user_1 = min($user1, $user2);
        $user_2 = max($user1, $user2);

        if ( $privacy == 1 )
        {
            if ( $groupID )
            {
                $user1 = new Zend_Db_Expr('NULL');
                $user2 = new Zend_Db_Expr('NULL');
            }
            else
            {
                $groupID = new Zend_Db_Expr('NULL');
            }

            $this->_db->insert("sh_scapes_private_conversation", array(
                "party_1_id" => $user_1,
                "party_2_id" => $user_2,
                "group_id" => $groupID
            ));
        }
        else
        {
            if ( $groupID )
            {
                $groupIDQ = $this->_db->quoteInto("group_id = ?", $groupID);
                
                $where = $groupIDQ;
                
                $this->_db->delete("sh_scapes_private_conversation", $where);
            }
            else
            {
                $user1IDQ = $this->_db->quoteInto("party_1_id = ?", $user_1);
                $user2IDQ = $this->_db->quoteInto("party_2_id = ?", $user_2);
                
                $where = $user1IDQ . " AND " . $user2IDQ;
                
                $this->_db->delete("sh_scapes_private_conversation", $where);
            }
        }
    }

    /**
     * Gets the privacy level for the given parties. You ask for $user1 AND $user2, OR $groupID, not both.
     *
     * @param int $senderID The first user's ID.
     * @param int $recipientID The second user's ID.
     * @param int $groupID The group ID.
     * 
     * @return int The privacy level.
     */ 
    function getPrivacyBetweenUsers($user1, $user2, $groupID = NULL)
    {
        if ( $groupID )
        {
            $query = $this->_db->query("
                    SELECT COUNT(*)
                    FROM sh_scapes_private_conversation
                    WHERE group_id = {$groupID}");
            
            $query->setFetchMode(Zend_Db::FETCH_NUM);
            $result = $query->fetch();
            
            if ( $result[0] == 0 )
            {
                return 2;
            }
            else
            {
                return 1;
            }
        }
        else
        {
            $user_1 = min($user1, $user2);
            $user_2 = max($user1, $user2);

            $query = $this->_db->query("
                    SELECT COUNT(*)
                    FROM sh_scapes_private_conversation
                    WHERE party_1_id = {$user_1} AND party_2_id = {$user_2}");
            
            $query->setFetchMode(Zend_Db::FETCH_NUM);
            $result = $query->fetch();
            
            if ( $result[0] == 0 )
            {
                return 2;
            }
            else
            {
                return 1;
            }
        }
    }

    /**
     * Marks a thread as delivered.
     *
     * @param int    $threadID The ID of the thread.
     *
     * @return void
     */
    function markDelivered($threadID)
    {
        $threadIDQ = $this->_db->quoteInto("?", $threadID);
        $this->_db->update($this->_name, array("status_delivered" => 1, "timestamp_delivered" => gmdate("Y-m-d H:i:s", time())), "thread_id = {$threadIDQ}");
    }

    /**
     * Marks a thread as read.
     *
     * @param int    $threadID The ID of the thread.
     *
     * @return void
     */
    function markRead($threadID)
    {
        $threadIDQ = $this->_db->quoteInto("?", $threadID);
        $this->_db->update($this->_name, array("status_read" => 1, "timestamp_read" => gmdate("Y-m-d H:i:s", time())), "thread_id = {$threadIDQ}");
    }

    /**
     * Gets the date & time that a thread was delivered.
     *
     * @param int    $threadID The ID of the thread.
     *
     * @return The date string. 
     */
    function getDeliveryDate($threadID)
    {
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_thread
                    WHERE thread_id = {$threadID}");
        
        $result = $query->fetch();
        
        if ( $result["thread_id"] )
        {
            return $result["timestamp_delivered"];
        }
        else // Thread was probably deleted.
        {
            return gmdate("Y-m-d H:i:s", time());
        }
    }

    /**
     * Gets the date & time that a thread was read.
     *
     * @param int $threadID The ID of the thread.
     *
     * @return The date string. 
     */
    function getReadDate($threadID)
    {
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_thread
                    WHERE thread_id = {$threadID}");
        
        $result = $query->fetch();
        
        if ( $result["thread_id"] )
        {
            return $result["timestamp_read"];
        }
        else // Thread was probably deleted.
        {
            return gmdate("Y-m-d H:i:s", time());
        }
    }

    /**
     * Gets the date & time that a thread was read.
     *
     * @param float $latitude The current user's latitude.
     * @param float $longitude The current user's longitude.
     *
     * @return Array All nearby conversations that are less than an hour old. 
     */
    function getNearbyThreads($latitude, $longitude)
    {
        $query = $this->_db->query("
                    SELECT sh_scapes_thread.*, sh_scapes_message_dispatch.recipient_id
                    FROM sh_scapes_thread
                    INNER JOIN sh_scapes_message_dispatch ON sh_scapes_message_dispatch.thread_id = sh_scapes_thread.thread_id AND sh_scapes_thread.privacy = 2 AND (sh_scapes_thread.timestamp_sent > NOW() - INTERVAL 1 HOUR)
                    ORDER BY sh_scapes_thread.timestamp_sent DESC");

        $results = $query->fetchAll();

        foreach ( $results as $key => $thread ) // Weed out anything beyond a 1km radius.
        {
            if ( $thread["location_latitude"] && $thread["location_longitude"] )
            {
                $distance = Model_Lib_Func::vincentyGreatCircleDistance($latitude, $longitude, $thread["location_latitude"], $thread["location_longitude"]); // Distance returned is in meters.

                if ( $distance > 1000 )
                {
                    unset($results[$key]);
                }
                else
                {
                    $adHocTable = new Scapes_Model_DbTable_Shadhoc();
                    $thread["participants"] = $adHocTable->participantsForConversation($thread["owner_id"], $thread["recipient_id"]);
                    $thread["distance"] = $distance;
                    $results[$key] = $thread;
                }
            }
            else
            {
                unset($results[$key]);
            }
        }
        
        return $results;
    }
}
