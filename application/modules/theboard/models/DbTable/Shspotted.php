<?php

/**
 * Nightboard peer spotting table management class.
 *
 * @copyright  2015 Scapehouse
 */

class Theboard_Model_DbTable_Shspotted extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_spotted"; // The table name.
    protected $_schema = "theboard"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Records that 2 users spotted each other.
     *
     * @param int $spotter The ID of the 1st user.
     * @param int $spottee The ID of the 2nd user.
     *
     * @return void
     */
    function spotted($spotter, $spottee)
    {
        // Use sorting to make the query order-insensitive.
        $user1 = min($spotter, $spottee);
        $user2 = max($spotter, $spottee);

        $user1Q = $this->_db->quoteInto("?", $user1);
        $user2Q = $this->_db->quoteInto("?", $user2);

        $query = $this->_db->query("
                    SELECT *
                    FROM sh_spotted
                    WHERE user_1 = {$user1Q} AND user_2 = {$user2Q}");
        
        $result = $query->fetch();
        
        if ( !$result["spot_id"] )
        {
            $this->_db->insert($this->_name, array(
                "user_1" => $user1,
                "user_2" => $user2,
                "frequency" => 1,
                "timestamp" => gmdate("Y-m-d", time()))
            );
        }
        else
        {
            $timestamp = $result["timestamp"];
            $timestampDateTimeObj = new DateTime($timestamp);
            $todayDateTimeObj = new DateTime(gmdate("Y-m-d", time()));
            $frequency = $result["frequency"];

            $firstDate = $todayDateTimeObj->format('Y-m-d');
            $secondDate = $timestampDateTimeObj->format('Y-m-d');
            
            if ( $firstDate != $secondDate )
            {
                $frequency++;
            }

            $this->_db->update($this->_name, array("frequency" => $frequency, "timestamp" => $date), "user_1 = {$user1Q} AND user_2 = {$user2Q}");
        }
    }

    /**
     * Deletes the spot.
     *
     * @param int $spotter The ID of the 1st user.
     * @param int $spottee The ID of the 2nd user.
     *
     * @return void
     */
    function removeSpot($spotter, $spottee)
    {
        // Use sorting to make the query order-insensitive.
        $user1 = min($spotter, $spottee);
        $user2 = max($spotter, $spottee);

        $this->_db->delete($this->_name, array(
            "user_1 = ?" => $user1,
            "user_2 = ?" => $user2
        ));
    }

    /**
     * Checks if 2 users spotted each other before.
     *
     * @param int $spotter The ID of the 1st user.
     * @param int $spottee The ID of the 2nd user.
     *
     * @return bool
     */
    function didSpotUser($spotter, $spottee)
    {
        // Use sorting to make the query order-insensitive.
        $user1 = min($spotter, $spottee);
        $user2 = max($spotter, $spottee);

        $query = $this->_db->query("
                    SELECT *
                    FROM sh_spotted
                    WHERE user_1 = {$user1} AND user_2 = {$user2}");
        
        $result = $query->fetch();

        if ( $result["spot_id"] )
        {
            return true;
        }

        return false;
    }

    /**
     * Gets a list of people the user spotted along with their data.
     *
     * @param int $userID The ID of the user.
     *
     * @return array of users.
     */
    function getSpottedListWithUserData($userID)
    {
        $query = $this->_db->select()->from("sh_user")
                ->join("sh_spotted", "((sh_spotted.user_1 = sh_user.user_id AND sh_spotted.user_1 <> {$userID}) OR (sh_spotted.user_2 = sh_user.user_id AND sh_spotted.user_2 <> {$userID})) AND (sh_spotted.user_1 = {$userID} OR sh_spotted.user_2 = {$userID})", array())
                ->order(new Zend_Db_Expr('RAND()'))
                ->limit(50, 0);

        $list = $this->_db->fetchAll($query);
        $userTable = new Theboard_Model_DbTable_Shuser();
        $threadTable = new Theboard_Model_DbTable_Shthread();
        $followTable = new Theboard_Model_DbTable_Shfollow();

        foreach ( $list as $key => $user )
        {
            // Get the user's latest status update.
            $latestStatus = $threadTable->getLatestStatusUpdate($user["user_id"]);
            $result = array_merge($user, $latestStatus);
            
            if ( $followTable->userFollowsUser($user["user_id"], $userID) )
            {
                $result["follows_user"] = 1;
            }
            else
            {
                $result["follows_user"] = 0;
                unset($result["email_address"]);
            }

            $result["spotted_user"] = 1;
            $result["dp_hash"] = $userTable->getPicture($user["user_id"]);
            
            $list[$key] = $result;
        }

        return $list;
    }
}