<?php

/**
 * Scapehouse ad hoc conversation management class.
 *
 * @copyright  2014 Scapehouse
 */

class Scapes_Model_DbTable_Shadhoc extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_scapes_ad_hoc_conversation"; // The table name.
    protected $_schema = "scapes"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Spawns a conversation between a user & 2 original participants.
     *
     * @param int $userID_1 The first user's ID.
     * @param int $userID_2 The second user's ID.
     * @param int $newParticipant The new participant's ID.
     * 
     * @return void
     */
    function createConversationWithInitialParticipants($userID_1, $userID_2, $newParticipant)
    {
        // Use sorting to make the query order-insensitive.
        $originalUser_1 = min($userID_1, $userID_2);
        $originalUser_2 = max($userID_1, $userID_2);

        $this->_db->insert($this->_name, array(
            "original_user_1" => $originalUser_1,
            "original_user_2" => $originalUser_2,
            "user_id" => $newParticipant,
            "timestamp" => gmdate("Y-m-d H:i:s", time()))
        );
    }

    /**
     * Deletes a conversation.
     *
     * @param int $userID_1 The first original user's ID.
     * @param int $userID_2 The second original user's ID.
     * 
     * @return void
     */
    function destroyConversation($userID_1, $userID_2)
    {
        $originalUser_1 = min($userID_1, $userID_2);
        $originalUser_2 = max($userID_1, $userID_2);

        $originalUserIDQ_1 = $this->_db->quoteInto("original_user_1 = ?", $originalUser_1);
        $originalUserIDQ_2 = $this->_db->quoteInto("original_user_2 = ?", $originalUser_2);

        $where = $originalUserIDQ_1 . " AND " . $originalUserIDQ_2;
        
        $this->_db->delete($this->_name, $where);
    }

    /**
     * Gets a list of conversations that a user is participating in.
     *
     * @param int $userID The user's ID.
     * 
     * @return array An array of conversations.
     */
    function conversationsForUser($userID)
    {
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_ad_hoc_conversation
                    WHERE user_id = {$userID}");
        
        $results = $query->fetchAll();

        return $results;
    }

    /**
     * Gets the list of users listening to a conversation, i.e. messages should be pushed out to them.
     *
     * @param int $userID_1 The first original user's ID.
     * @param int $userID_2 The second original user's ID.
     * 
     * @return array An array of users.
     */
    function listenersForConversation($userID_1, $userID_2)
    {
        $originalUser_1 = min($userID_1, $userID_2);
        $originalUser_2 = max($userID_1, $userID_2);

        $query = $this->_db->query("
                    SELECT follower_userid
                    FROM sh_scapes_follow
                    WHERE followed_userid = {$originalUser_1} AND follower_userid <> {$originalUser_1} AND follower_userid IN
                        (SELECT follower_userid
                         FROM sh_scapes_follow
                         WHERE followed_userid = {$originalUser_2} AND follower_userid <> {$originalUser_2}
                        )");
        
        $results = $query->fetchAll(PDO::FETCH_COLUMN, 0);

        return $results;
    }

    /**
     * Gets the list of users participating in a conversation.
     *
     * @param int $userID_1 The first original user's ID.
     * @param int $userID_2 The second original user's ID.
     * 
     * @return array An array of users.
     */
    function participantsForConversation($userID_1, $userID_2)
    {
        $originalUser_1 = min($userID_1, $userID_2);
        $originalUser_2 = max($userID_1, $userID_2);

        $query = $this->_db->query("
                    SELECT user_id
                    FROM sh_scapes_ad_hoc_conversation
                    WHERE original_user_1 = {$originalUser_1} AND original_user_2 = {$originalUser_2}");
        
        $results = $query->fetchAll(PDO::FETCH_COLUMN, 0);

        return $results;
    }

    /**
     * Removes a given user from a conversation.
     *
     * @param int $userID_1 The first original user's ID.
     * @param int $userID_2 The second original user's ID.
     * @param int $userID The user ID.
     * 
     * @return void
     */
    function removeUserFromConversation($userID_1, $userID_2, $userID)
    {
        $originalUser_1 = min($userID_1, $userID_2);
        $originalUser_2 = max($userID_1, $userID_2);

        $originalUserIDQ_1 = $this->_db->quoteInto("original_user_1 = ?", $originalUser_1);
        $originalUserIDQ_2 = $this->_db->quoteInto("original_user_2 = ?", $originalUser_2);
        $userIDQ = $this->_db->quoteInto("user_id = ?", $userID);

        $where = $originalUserIDQ_1 . " AND " . $originalUserIDQ_2 . " AND " . $userIDQ;
        
        $this->_db->delete($this->_name, $where);
    }

    /**
     * Checks if a given user is participating in a conversation.
     *
     * @param int $userID_1 The first original user's ID.
     * @param int $userID_2 The second original user's ID.
     * @param int $userID The user ID.
     * 
     * @return boolean True if the user is participating in the conversation.
     */
    function userIsInConversation($userID_1, $userID_2, $userID)
    {
        $originalUser_1 = min($userID_1, $userID_2);
        $originalUser_2 = max($userID_1, $userID_2);

        $query = $this->_db->query("
                    SELECT COUNT(*)
                    FROM sh_scapes_ad_hoc_conversation
                    WHERE original_user_1 = {$originalUser_1} AND original_user_2 = {$originalUser_2} AND user_id = {$userID}");
        
        $query->setFetchMode(Zend_Db::FETCH_NUM);
        $result = $query->fetch();

        if ( $result[0] == 0 ) // User not in the conversation.
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}