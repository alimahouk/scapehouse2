<?php

/**
 * Nightboard board management class.
 *
 * @copyright  2015 Scapehouse
 */

class Scapes_Model_DbTable_Shboard extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_scapes_board"; // The table name.
    protected $_schema = "scapes"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Creates a board.
     *
     * PRIVACY:
     * 1 = open
     * 2 = closed
     *
     * @param int $name The name of the board.
     * @param int $privacy Whether the board is open or closed.
     *
     * @return int The newly-created board's ID.
     */
    function createBoard($name, $privacy)
    {
        $this->_db->insert($this->_name, array(
                "name" => $name,
                "privacy" => $privacy,
                "date_created" => gmdate("Y-m-d H:i:s", time()))
        );

        $boardID = $this->_db->lastInsertId($this->_name);
        
        return $boardID;
    }

    /**
     * Creates a personal board for a user.
     *
     * @param int $userID The ID of the user.
     *
     * @return int The newly-created board's ID.
     */
    function createBoardForUser($userID)
    {

        $userIDQ = $this->_db->quoteInto("?", $userID);

        // Prevent duplicates.
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_board
                    WHERE owner_id = {$userIDQ}");
        
        $result = $query->fetch();
        
        if ( !$result["board_id"] )
        {
            $this->_db->insert($this->_name, array(
                "owner_id" => $userID,
                "privacy" => 1,
                "date_created" => gmdate("Y-m-d H:i:s", time()))
            );

            $boardID = $this->_db->lastInsertId($this->_name);
        
            return $boardID;
        }
    }

    /**
     * Updates a board's info.
     *
     * @param int $boardID The ID of the board.
     * @param array $data The data as an associative array.
     *
     * @return void
     */
    function updateBoard($boardID, $data)
    {
        $boardIDQ = $this->_db->quoteInto("?", $boardID);

        $this->_db->update($this->_name, array(
            "name" => $data["name"],
            "description" => $data["description"],
            "privacy" => $data["privacy"]), "board_id = {$boardIDQ}");
    }

    function deleteBoard($boardID)
    {
        $this->_db->delete($this->_name, array(
            "board_id = ?" => $boardID
        ));

        $this->_db->delete("sh_scapes_board_post", array(
            "board_id = ?" => $boardID
        ));
    }

    /**
     * Adds a request for joining a closed board.
     *
     * @param int $userID The ID of the user.
     * @param int $boardID The ID of the board.
     *
     * @return void
     */
    function requestJoin($userID, $boardID)
    {
        $this->_db->insert("sh_scapes_board_request", array(
                "user_id" => $userID,
                "board_id" => $boardID)
        );
    }

    /**
     * Removes a request for joining a closed board.
     *
     * @param int $userID The ID of the user.
     * @param int $boardID The ID of the board.
     *
     * @return void
     */
    function cancelRequest($userID, $boardID)
    {
        $this->_db->delete("sh_scapes_board_request", array(
            "user_id = ?" => $userID,
            "board_id = ?" => $boardID
        ));
    }

    /**
     * Adds a user as a member of a board.
     *
     * @param int $userID The ID of the user.
     * @param int $boardID The ID of the board.
     *
     * @return void
     */
    function join($userID, $boardID)
    {
        // Delete their request (if one exists).
        $this->_db->delete("sh_scapes_board_request", array(
            "user_id = ?" => $userID,
            "board_id = ?" => $boardID
        ));

        // Delete from suggestions.
        $this->_db->delete("sh_scapes_board_suggestion", array(
            "user_id = ?" => $userID,
            "board_id = ?" => $boardID
        ));

        $this->_db->insert("sh_scapes_board_membership", array(
                "member_id" => $userID,
                "board_id" => $boardID,
                "timestamp" => gmdate("Y-m-d H:i:s", time()))
        );
    }

    /**
     * Removes a user as a member of a board.
     *
     * @param int $userID The ID of the user.
     * @param int $boardID The ID of the board.
     *
     * @return void
     */
    function leave($userID, $boardID)
    {
        $this->_db->delete("sh_scapes_board_membership", array(
            "member_id = ?" => $userID,
            "board_id = ?" => $boardID
        ));
    }

    /**
     * Gets the data of a board.
     *
     * @param int $boardID The ID of the board.
     * @param bool $includePosts Whether to include posts or not.
     *
     * @return dict The board data
     */
    function getInfo($userID, $boardID, $includePosts = false)
    {
        $boardIDQ = $this->_db->quoteInto("?", $boardID);

        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_board
                    WHERE board_id = {$boardIDQ}");
        
        $result = $query->fetch();

        $result["members"] = $this->getMemberPreview($userID, $boardID);
        $result["member_count"] = $this->getMemberCount($boardID);

        if ( $includePosts )
        {
            $result["posts"] = $this->getPosts($boardID);
        }

        return $result;
    }

    /**
     * Gets the privacy setting of a board.
     *
     * @param int $boardID The ID of the board.
     *
     * @return int The privacy setting of the board.
     */
    function getPrivacy($boardID)
    {
        $boardIDQ = $this->_db->quoteInto("?", $boardID);

        return $this->_db->fetchOne("
                SELECT privacy
                FROM sh_scapes_board
                WHERE board_id = {$boardIDQ}");
    }

    /**
     * Gets the number of join requests for a board.
     *
     * @param int $boardID The ID of the board.
     *
     * @return int The number of requests.
     */
    function getRequestCount($boardID)
    {
        $query = $this->_db->select()
        ->from("sh_scapes_board_request", "COUNT(*)")
        ->where("board_id = ?", $boardID);

        return $this->_db->fetchOne($query);
    }

    /**
     * Gets the number of members of a board.
     *
     * @param int $boardID The ID of the board.
     *
     * @return int The number of members.
     */
    function getMemberCount($boardID)
    {
        $query = $this->_db->select()
        ->from("sh_scapes_board_membership", "COUNT(*)")
        ->where("board_id = ?", $boardID);

        return $this->_db->fetchOne($query);
    }

    /**
     * Gets a small batch of members of a board.
     *
     * @param int $boardID The ID of the board.
     *
     * @return int The number of members
     */
    function getMemberPreview($userID, $boardID)
    {
        $boardIDQ = $this->_db->quoteInto("?", $boardID);

        $query = $this->_db->select()->from("sh_user")
                ->join("sh_scapes_board_membership", "sh_scapes_board_membership.member_id = sh_user.user_id AND sh_scapes_board_membership.board_id = {$boardIDQ}", array())
                ->order(new Zend_Db_Expr('RAND()'))
                ->limit(20, 0);

        $list = $this->_db->fetchAll($query);

        $userTable = new Theboard_Model_DbTable_Shuser();
        $threadTable = new Theboard_Model_DbTable_Shthread();
        $followTable = new Theboard_Model_DbTable_Shfollow();

        foreach ( $list as $key => $user )
        {
            // Get the user's latest status update.
            $latestStatus = $threadTable->getLatestStatusUpdate($user["user_id"]);
            $result = array_merge($user, $latestStatus);

            if ( $userID != $user["user_id"] )
            {
                if ( $followTable->userFollowsUser($user["user_id"], $userID) )
                {
                    $result["follows_user"] = 1;
                }
                else
                {
                    $result["follows_user"] = 0;
                    unset($result["email_address"]);
                }
            }

            $result["dp_hash"] = $userTable->getPicture($user["user_id"]);
            
            $list[$key] = $result;
        }

        return $list;
    }

    /**
     * Gets a list of board members along with their data.
     *
     * @param int $boardID The ID of the board.
     *
     * @return array of users.
     */
    function getMemberList($boardID, $batch = 0)
    {
        $query = $this->_db->select()->from("sh_user")
                ->join("sh_scapes_board_membership", "sh_scapes_board_membership.board_id = {$boardID} AND sh_scapes_board_membership.member_id = sh_user.user_id", array())
                ->limit(200, 0);
        
        $list = $this->_db->fetchAll($query);

        return $list;
    }

    /**
     * Gets a list of board members along with their data.
     *
     * @param int $boardID The ID of the board.
     *
     * @return array of users.
     */
    function getMemberListWithUserData($userID, $boardID, $batch = 0)
    {
        $query = $this->_db->select()->from("sh_user")
                ->join("sh_scapes_board_membership", "sh_scapes_board_membership.board_id = {$boardID} AND sh_scapes_board_membership.member_id = sh_user.user_id", array())
                ->limit(500, 0);
        
        $list = $this->_db->fetchAll($query);

        $threadTable = new Theboard_Model_DbTable_Shthread();
        $userTable = new Theboard_Model_DbTable_Shuser();
        $followTable = new Theboard_Model_DbTable_Shfollow();
        $spottedTable = new Theboard_Model_DbTable_Shspotted();
        $blocklistTable = new Theboard_Model_DbTable_Shblocklist();

        foreach ( $list as $key => $user )
        {
            // Get the user's latest status update.
            $latestStatus = $threadTable->getLatestStatusUpdate($user["user_id"]);
            $result = array_merge($user, $latestStatus);

            $result["dp_hash"] = $userTable->getPicture($user["user_id"]);
            
            // First check if the current user has been blocked by this contact.
            $userIsblocked = $blocklistTable->isBlocked($user["user_id"], $userID);
            
            if ( $userIsblocked )
            {
                unset($list[$key]);
            }
            else
            {
                if ( $userID != $user["user_id"] )
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
        
                    if ( $followTable->userFollowsUser($user["user_id"], $userID) )
                    {
                        $result["spotted_user"] = 1;
                        $result["follows_user"] = 1;
                    }
                    else
                    {
                        if ( $spottedTable->didSpotUser($user["user_id"], $userID) )
                        {
                            $result["spotted_user"] = 1;
                        }
                        else
                        {
                            $result["spotted_user"] = 0;
                        }

                        $result["follows_user"] = 0;
                        unset($result["email_address"]);
                    }
                }
                
                $list[$key] = $result;
            }
        }

        return $list;
    }

    /**
     * Gets the number of join requests for a board.
     *
     * @param int $boardID The ID of the board.
     *
     * @return int The number of requests.
     */
    function getRequests($userID, $boardID)
    {
        $query = $this->_db->select()->from("sh_user")
                ->join("sh_scapes_board_request", "sh_scapes_board_request.board_id = {$boardID} AND sh_scapes_board_request.user_id = sh_user.user_id", array())
                ->limit(5000, 0);
        
        $list = $this->_db->fetchAll($query);

        $threadTable = new Theboard_Model_DbTable_Shthread();
        $userTable = new Theboard_Model_DbTable_Shuser();
        $followTable = new Theboard_Model_DbTable_Shfollow();
        $blocklistTable = new Theboard_Model_DbTable_Shblocklist();

        foreach ( $list as $key => $user )
        {
            // Get the user's latest status update.
            $latestStatus = $threadTable->getLatestStatusUpdate($user["user_id"]);
            $result = array_merge($user, $latestStatus);

            $result["dp_hash"] = $userTable->getPicture($user["user_id"]);
            
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
    
                if ( $followTable->userFollowsUser($user["user_id"], $userID) )
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
     * Gets a list of board posts.
     *
     * @param int $boardID The ID of the board.
     *
     * @return array of posts.
     */
    function getPosts($boardID, $batch = 0)
    {
        $boardIDQ = $this->_db->quoteInto("?", $boardID);

        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_board_post
                    WHERE board_id = {$boardIDQ}
                    ORDER BY timestamp DESC
                    LIMIT 50");
        
        $results = $query->fetchAll();

        foreach ( $results as $key => $post )
        {
            $threadID = $post["post_id"];

            $post["view_count"] = $this->getViewCount($threadID);
            $results[$key] = $post;
        }

        return $results;
    }

    /**
     * Gets the number of views on a thread.
     *
     * @param int $threadID The thread's ID.
     * 
     * @return int View count.
     */
    function getViewCount($threadID)
    {
        $query = $this->_db->query("
                    SELECT COUNT(*)
                    FROM sh_scapes_board_view
                    WHERE post_id = {$threadID}");
        
        $query->setFetchMode(Zend_Db::FETCH_NUM);
        $result = $query->fetch();

        return $result[0];
    }

    /**
     * Get the ID of a user's personal board.
     *
     * @param int $userID The ID of the user.
     *
     * @return int The ID of their board.
     */
    function boardIDForUser($userID)
    {
        $userIDQ = $this->_db->quoteInto("?", $userID);

        return $this->_db->fetchOne("
                SELECT board_id
                FROM sh_scapes_board
                WHERE owner_id = {$userIDQ}");
    }

    /**
     * Returns the hash for the board's current cover photo.
     *
     * @param int $boardID The ID of the board.
     *
     * @return void
     */
    function getCurrentCoverHash($boardID)
    {
        $boardIDQ = $this->_db->quoteInto("?", $boardID);

        return $this->_db->fetchOne("
                SELECT cover_hash
                FROM sh_scapes_board
                WHERE board_id = {$boardIDQ}");
    }

    /**
     * Removes the hash for the board's current cover photo.
     *
     * @param int $boardID The ID of the board.
     *
     * @return void
     */
    function saveCoverHash($boardID, $hash)
    {
        $boardIDQ = $this->_db->quoteInto("?", $boardID);

        $this->_db->update($this->_name, array(
            "cover_hash" => $hash), "board_id = {$boardIDQ}");
    }

    /**
     * Removes the hash for the board's current cover photo.
     *
     * @param int $boardID The ID of the board.
     *
     * @return void
     */
    function deleteCurrentCoverHash($boardID)
    {
        $boardIDQ = $this->_db->quoteInto("?", $boardID);

        $this->_db->update($this->_name, array(
            "cover_hash" => new Zend_Db_Expr('NULL')), "board_id = {$boardIDQ}");
    }

    /**
     * Get the ID of a user's personal board.
     *
     * @param int $userID The ID of the user.
     *
     * @return int The ID of their board.
     */
    function boardsForUser($userID)
    {
        $userIDQ = $this->_db->quoteInto("?", $userID);

        $query = $this->_db->select()->from("sh_scapes_board")
                ->join("sh_scapes_board_membership", "sh_scapes_board_membership.board_id = sh_scapes_board.board_id AND sh_scapes_board_membership.member_id = {$userIDQ}", array())
                ->limit(500, 0);
        
        $results = $this->_db->fetchAll($query);

        return $results;
    }

    /**
     * Checks if a user is a member of a board.
     *
     * @param int $userID The ID of the user.
     * @param int $boardID The ID of the board.
     *
     * @return bool
     */
    function userIsMemberOfBoard($userID, $boardID)
    {
        $userIDQ = $this->_db->quoteInto("?", $userID);
        $boardIDQ = $this->_db->quoteInto("?", $boardID);

        // Prevent duplicates.
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_board_membership
                    WHERE member_id = {$userIDQ} AND board_id = {$boardIDQ}");
        
        $result = $query->fetch();
        
        if ( $result["membership_id"] )
        {
            return true;
        }

        return false;
    }

    /**
     * Checks if a user is a member of a board.
     *
     * @param int $userID The ID of the user.
     * @param int $boardID The ID of the board.
     *
     * @return bool
     */
    function userRequestedBoardJoin($userID, $boardID)
    {
        $userIDQ = $this->_db->quoteInto("?", $userID);
        $boardIDQ = $this->_db->quoteInto("?", $boardID);

        // Prevent duplicates.
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_board_request
                    WHERE user_id = {$userIDQ} AND board_id = {$boardIDQ}");
        
        $result = $query->fetch();
        
        if ( $result["user_id"] )
        {
            return true;
        }

        return false;
    }

    /**
     * Get the ID of a user's personal board.
     *
     * @param int $userID The ID of the user.
     *
     * @return int The ID of their board.
     */
    function addBoardSuggestion($userID, $boardID)
    {
        $userIDQ = $this->_db->quoteInto("?", $userID);
        $boardIDQ = $this->_db->quoteInto("?", $boardID);

        // Prevent duplicates.
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_board_suggestion
                    WHERE user_id = {$userIDQ} AND board_id = {$boardIDQ}");
        
        $result = $query->fetch();
        
        if ( !$result["suggestion_id"] )
        {
            $this->_db->insert("sh_scapes_board_suggestion", array(
                "user_id" => $userID,
                "board_id" => $boardID)
            );
        }
    }

    /**
     * Gets board suggestions for a user.
     *
     * @param int $userID The ID of the user.
     *
     * @return array The list of boards.
     */
    function getRecommendedBoardsForUser($userID)
    {
        $userIDQ = $this->_db->quoteInto("?", $userID);
        
        $query = $this->_db->select()->from("sh_scapes_board")
                ->join("sh_scapes_board_suggestion", "sh_scapes_board_suggestion.board_id = sh_scapes_board.board_id AND sh_scapes_board_suggestion.user_id = {$userIDQ}", array())
                ->order(new Zend_Db_Expr('RAND()'))
                ->limit(20, 0);

        $results = $this->_db->fetchAll($query);

        return $results;
    }

    /**
     * Adds a view to a post. Views are unique to each user.
     *
     * @param int $userID The kicker's ID.
     * @param int $threadID The thread's ID.
     * 
     * @return void
     */
    function registerView($userID, $threadID)
    {
        $userIDQ = $this->_db->quoteInto("?", $userID);
        $threadIDQ = $this->_db->quoteInto("?", $threadID);

        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_board_view
                    WHERE viewer_id = {$userIDQ} AND post_id = {$threadIDQ}");
        
        $result = $query->fetch();
        
        if ( !$result )
        {
            $this->_db->insert("sh_scapes_board_view", array(
                "viewer_id" => $userID,
                "post_id" => $threadID,
                "timestamp" => gmdate("Y-m-d H:i:s", time())
            ));
        }
    }

    /**
     * Posts to a board.
     *
     * @param int $boardID The ID of the board.
     * @param dict $postData The post data.
     *
     * @return int The ID of the newly-created post.
     */
    function postToBoard($boardID, $postData)
    {
        $this->_db->insert("sh_scapes_board_post", array(
            "owner_id" => $postData['owner_id'],
            "board_id" => $boardID,
            "text" => $postData['text'],
            "color" => $postData['color'],
            "media_hash" => $postData['media_hash'],
            "timestamp" => gmdate("Y-m-d H:i:s", time()),
        ));
        
        $postID = $this->_db->lastInsertId("sh_scapes_board_post");
        
        return $postID;
    }

    /**
     * Posts to a user's personal board.
     *
     * @param int $userID The ID of the user.
     * @param dict $postData The post data.
     *
     * @return int The ID of the newly-created post.
     */
    function postToUser($userID, $postData)
    {
        $boardID = $this->boardIDForUser($userID);

        $this->_db->insert("sh_scapes_board_post", array(
            "owner_id" => $postData['owner_id'],
            "board_id" => $boardID,
            "text" => $postData['text'],
            "color" => $postData['color'],
            "media_hash" => $postData['media_hash'],
            "timestamp" => gmdate("Y-m-d H:i:s", time()),
        ));
        
        $postID = $this->_db->lastInsertId("sh_scapes_board_post");
        
        return $postID;
    }

    /**
     * Edits an existing board post.
     *
     * @param int $postID The ID of the post to edit.
     * @param int $text The new post text.
     *
     * @return void
     */
    function editPost($postID, $text)
    {
        $postIDQ = $this->_db->quoteInto("?", $postID);

        $this->_db->update("sh_scapes_board_post", array(
            "text" => $text), "post_id = {$postIDQ}");
    }

    /**
     * Deletes a board post.
     *
     * @param int $userID The ID of the post to delete.
     *
     * @return void
     */
    function deletePost($postID)
    {
        $this->_db->delete("sh_scapes_scapes_board_post", array(
            "post_id = ?" => $postID
        ));

        $this->_db->delete("sh_scapes_board_view", array(
            "post_id = ?" => $postID
        ));
    }

    /**
     * Returns posts that have this hashtag in them.
     *
     * @param string $hashtag The hashtag.
     * 
     * @return array The search results.
     */
    function searchForHashtag($hashtag, $boardID)
    {
        $hashtagQ = $this->_db->quoteInto("?", $hashtag);
        $boardIDQ = $this->_db->quoteInto("?", $boardID);

        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_board_post
                    WHERE board_id = {$boardIDQ} AND (MATCH(text) AGAINST({$hashtagQ} IN BOOLEAN MODE))");
        
        $results = $query->fetchAll();

        foreach ( $results as $key => $post )
        {
            $threadID = $post["post_id"];

            $post["view_count"] = $this->getViewCount($threadID);
            $results[$key] = $post;
        }

        return $results;
    }
}