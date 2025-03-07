<?php

/**
 * Scapes contact followership table management class.
 *
 * @copyright  2014 Scapehouse
 */

class Scapes_Model_DbTable_Shfollow extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_scapes_follow"; // The table name.
    protected $_schema = "scapes"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Subscribes a user to a contact's updates, readding them if they were previously removed.
     *
     * @param int $followerID The ID of the follower.
     * @param int $followedID The ID of the person followed.
     * @return void
     */
    function follow($followerID, $followedID)
    {
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_follow
                    WHERE follower_userid = {$followerID} AND followed_userid = {$followedID}");
        
        $result = $query->fetch();
        
        if ( !$result["follow_id"] )
        {
            $this->_db->insert($this->_name, array(
                "follower_userid" => $followerID,
                "followed_userid" => $followedID,
                "timestamp" => gmdate("Y-m-d H:i:s", time()))
            );
        }
        else
        {
            $this->_db->update($this->_name, array("removed_by_user" => 0), "follower_userid = {$followerID} AND followed_userid = {$followedID}");
        }
    }

    /**
     * Unsubscribes a user to a contact's updates.
     *
     * @param int $followerID The ID of the follower.
     * @param int $followedID The ID of the person followed.
     * @return void
     */
    function unfollow($followerID, $followedID)
    {
        // Note: this function does not actually delete the follow.
        // It merely sets a flag, otherwise the user would
        // be followed again on the next address book scan.
        /*$db->delete($this->_name, array(
            "follower_userid = ?" => $followerID,
            "followed_userid = ?" => $followedID
        ));*/

        $this->_db->update($this->_name, array("removed_by_user" => 1), "follower_userid = {$followerID} AND followed_userid = {$followedID}");
    }

    /**
     * Unsubscribes a user to a contact's updates.
     *
     * @param int $followerID The ID of the follower.
     * @param int $followedID The ID of the person followed.
     * @return void
     */
    function isRemovedByFollower($followerID, $followedID)
    {
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_follow
                    WHERE follower_userid = {$followerID} AND followed_userid = {$followedID}");
        
        $result = $query->fetch();

        if ( $result["follow_id"] )
        {
            if ( $result["removed_by_user"] == 0 )
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * Checks if a user is following another user.
     *
     * @param int $followerID The ID of the follower.
     * @param int $followedID The ID of the user we're checking against.
     *
     * @return bool
     */
    function userFollowsUser($followerID, $followedID)
    {
        $followerIDQ = $this->_db->quoteInto("?", $followerID);
        $followedIDQ = $this->_db->quoteInto("?", $followedID);

        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_follow
                    WHERE follower_userid = {$followerIDQ} AND followed_userid = {$followedIDQ}");
        
        $result = $query->fetch();
        
        if ( !$result["follow_id"] )
        {
            return false;
        }

        return true;
    }

    /**
     * Gets the number of people following a user.
     *
     * @param int $userID The ID of the user.
     *
     * @return int The number of followers
     */
    function getFollowerCount($userID)
    {
        $query = $this->_db->select()
        ->from($this->_name, "COUNT(*)")
        ->where("followed_userid = ?", $userID);

        return $this->_db->fetchOne($query);
    }

    /**
     * Gets the number of a user is following.
     *
     * @param int $userID The ID of the user.
     *
     * @return int The number of followers
     */
    function getFollowingCount($userID)
    {
        $query = $this->_db->select()
        ->from($this->_name, "COUNT(*)")
        ->where("follower_userid = ?", $userID);

        return $this->_db->fetchOne($query);
    }

    /**
     * Gets a user's list of followers.
     *
     * @param int $userID The ID of the user.
     *
     * @return array of user IDs.
     */
    function getFollowerList($userID)
    {
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_follow
                    WHERE followed_userid = {$userID} AND follower_userid <> {$userID}");
        
        $result = $query->fetchAll();

        return $result;
    }

    /**
     * Gets a list of the user IDs of people the user follows.
     *
     * @param int $userID The ID of the user.
     *
     * @return array of user IDs.
     */
    function getFollowingList($userID)
    {
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_follow
                    WHERE follower_userid = {$userID} AND followed_userid <> {$userID}");
        
        $result = $query->fetchAll();

        return $result;
    }

    /**
     * Gets a list of people the user follows along with their data.
     *
     * @param int $userID The ID of the user.
     *
     * @return array of users.
     */
    function getFollowingListWithUserData($userID, $currentUserID = -1)
    {
        $query = $this->_db->select()->from("sh_user")
                ->join("sh_scapes_follow", "sh_scapes_follow.followed_userid = sh_user.user_id AND sh_scapes_follow.follower_userid = {$userID} AND sh_scapes_follow.followed_userid <> {$userID}", array())
                ->limit(500, 0);
                
        $list = $this->_db->fetchAll($query);

        $threadTable = new Scapes_Model_DbTable_Shthread();
        $userTable = new Scapes_Model_DbTable_Shuser();
        $blocklistTable = new Scapes_Model_DbTable_Shblocklist();

        if ( $currentUserID != -1 )
        {
            $userID = $currentUserID;
        }

        foreach ( $list as $key => $user )
        {   
            // Get the user's latest status update.
            $latestStatus = $threadTable->getLatestStatusUpdate($user["user_id"]);
            $result = array_merge($user, $latestStatus);

            $result["dp_hash"] = $userTable->getPicture($user["user_id"]);
            
            if ( $currentUserID != -1 )
            {
                if ( $spottedTable->didSpotUser($user["user_id"], $userID) )
                {
                    $result["spotted_user"] = 1;
                }
                else
                {
                    $result["spotted_user"] = 0;
                }
            }
            else
            {
                $result["spotted_user"] = 1;
            }
            
            // First check if the current user has been blocked by this contact.
            $userIsblocked = $blocklistTable->isBlocked($user["user_id"], $userID);
            
            if ( $userIsblocked )
            {
                unset($list[$key]);
            }
            else
            {
                // Check if this person is blocked.
                $followingUserIsblocked = $blocklistTable->isBlocked($userID, $user["user_id"]);
                
                if ( $followingUserIsblocked )
                {
                    $result["blocked"] = 1;
                }
                else
                {
                    $result["blocked"] = 0;
                }
    
                if ( $this->userFollowsUser($user["user_id"], $userID) )
                {
                    $result["follows_user"] = 1;
                }
                else
                {
                    $result["follows_user"] = 0;
                    unset($result["email_address"]);
                }
    
                $list[$key] = $result;
            }
        }

        return $list;
    }

    /**
     * Gets a list of people following the user along with their data.
     *
     * @param int $userID The ID of the user.
     *
     * @return array of users.
     */
    function getFollowersListWithUserData($userID, $currentUserID = -1)
    {
        $query = $this->_db->select()->from("sh_user")
                ->join("sh_scapes_follow", "sh_scapes_follow.followed_userid = {$userID} AND sh_scapes_follow.follower_userid = sh_user.user_id AND sh_scapes_follow.follower_userid <> {$userID}", array())
                ->limit(500, 0);
        
        $list = $this->_db->fetchAll($query);

        $threadTable = new Scapes_Model_DbTable_Shthread();
        $userTable = new Scapes_Model_DbTable_Shuser();
        $blocklistTable = new Scapes_Model_DbTable_Shblocklist();

        if ( $currentUserID != -1 )
        {
            $userID = $currentUserID;
        }
        
        foreach ( $list as $key => $user )
        {
            // Get the user's latest status update.
            $latestStatus = $threadTable->getLatestStatusUpdate($user["user_id"]);
            $result = array_merge($user, $latestStatus);

            $result["dp_hash"] = $userTable->getPicture($user["user_id"]);
            
            if ( $currentUserID != -1 )
            {
                if ( $spottedTable->didSpotUser($user["user_id"], $userID) )
                {
                    $result["spotted_user"] = 1;
                }
                else
                {
                    $result["spotted_user"] = 0;
                }
            }
            else
            {
                $result["spotted_user"] = 1;
            }

            // First check if the current user has been blocked by this contact.
            $userIsblocked = $blocklistTable->isBlocked($user["user_id"], $userID);
            
            if ( $userIsblocked )
            {
                unset($list[$key]);
            }
            else
            {
                // Check if this person is blocked.
                $followingUserIsblocked = $blocklistTable->isBlocked($userID, $user["user_id"]);
                
                if ( $followingUserIsblocked )
                {
                    $result["blocked"] = 1;
                }
                else
                {
                    $result["blocked"] = 0;
                }
    
                if ( $this->userFollowsUser($user["user_id"], $userID) )
                {
                    $result["follows_user"] = 1;
                }
                else
                {
                    $result["follows_user"] = 0;
                    unset($result["email_address"]);
                }
    
                $list[$key] = $result;
            }
        }

        return $list;
    }
}