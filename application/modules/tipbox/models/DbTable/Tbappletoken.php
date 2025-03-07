<?php

/**
 * Tipbox Apple token table management class
 *
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_DbTable_Tbappletoken extends Zend_Db_Table_Abstract {

    protected $_name = "tb_apple_token";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs an access token
     *
     * @param int $sessionid id of the current user session
     * @param string $userid user id of token holder
     * @param string $deviceToken  Apple token
     * @return id of latest created token
     */
    function logToken($sessionid, $userid, $deviceToken) {

        //Prevent duplicate same device token logs
        $deviceTokenQ = $this->_db->quoteInto("?", $deviceToken);
        $this->_db->delete($this->_name, "token={$deviceTokenQ}");

        //Prevent duplicate same session token logs
        $sessionidQ = $this->_db->quoteInto("?", $sessionid);
        $this->_db->delete($this->_name, "sessionid={$sessionidQ}");

        $this->_db->insert($this->_name, array(
            "sessionid" => $sessionid,
            "userid" => $userid,
            "token" => $deviceToken,
            "badge_count" => 0,
            "time" => gmdate("Y-m-d H:i:s", time()))
        );

        $tokenid = $this->_db->lastInsertId($this->_name);

        return $tokenid;
    }

    /**
     * Gets tokens log's row by looking up the userid
     *
     * @param string $userid user id of token holder
     * @return token row data
     */
    function getTokensByUserid($userid) {

        if (!$userid) {//Just a failsafe to against NULL values
            $userid = "";
        }

        $query = $this->_db->select()->from($this->_name)->where("userid=?", $userid);
        return $this->_db->fetchAll($query);
    }

    /**
     * Updates the badge_count
     *
     * @param string $deviceToken  Apple token
     * @param mixed $badgeCount Input "+1" to increment by 1 or input "0" to reset counter
     * @return void
     */
    function updateBadgeCount($userid, $badgeCount) {

        $useridQ = $this->_db->quoteInto("?", $userid);

        if ($badgeCount == "+1") {
            $this->_db->update($this->_name, array("badge_count" => new Zend_Db_Expr("badge_count + 1")), "userid={$useridQ}");
        } elseif ($badgeCount == 0) {
            $this->_db->update($this->_name, array("badge_count" => 0), "userid={$useridQ}");
        }
    }

}

