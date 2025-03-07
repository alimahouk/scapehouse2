<?php

/**
 * Tipbox facebook table management class
 *
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_DbTable_Tbfacebook extends Zend_Db_Table_Abstract {

    protected $_name = "tb_facebook";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs a Facebook token
     *
     * @param int $userid id of user
     * @param string $fbToken Facebook token string
     * @param int $fbid Facebook userid
     * @param string $fbTokenExp Issued token expiry date
     * @return id of latest created token entry
     */
    public function logToken($userid, $fbToken, $fbid, $fbTokenExp) {

        $this->_db->insert($this->_name, array(
            "token" => $fbToken,
            "fbid" => $fbid,
            "userid" => $userid,
            "connected" => 1,
            "expdate" => gmdate("Y-m-d H:i:s", $fbTokenExp),
            "time" => gmdate("Y-m-d H:i:s", time()))
        );

        return $this->_db->lastInsertId($this->_name);
    }

    /**
     * Gets a token log's row by looking up the userid
     *
     * @param string $userid Userid of the user
     * @return token row data
     */
    public function getTokenByUserid($userid) {

        $query = $this->_db->select()->from($this->_name)->where("userid=?", $userid);

        $result = $this->_db->fetchRow($query);

        return $result;
    }

    /**
     * Gets a token log's row by looking up the Facebook userid of the user
     *
     * @param string $fbid Facebook userid of the user
     * @return token row data
     */
    public function getTokenByFbid($fbid) {

        $query = $this->_db->select()->from($this->_name)->where("fbid=?", $fbid);

        $result = $this->_db->fetchRow($query);

        return $result;
    }

    /**
     * Updates a token log
     *
     * @param string $tokenOld Old Facebook token
     * @param string $tokenNew Newly issued Facebook token
     * @param string $fbTokenExp Issued token expiry date
     * @param bool $connected State of Facebook auth

     * @return void
     */
    public function updateToken($tokenOld, $tokenNew, $fbTokenExp, $connected) {

        $tokenOld = $this->_db->quoteInto("?", $tokenOld);
        $this->_db->update($this->_name, array(
            "token" => $tokenNew,
            "expdate" => gmdate("Y-m-d H:i:s", $fbTokenExp),
            "connected" => $connected), "token={$tokenOld}");
    }

    /**
     * Updates a token log and sets the Facebook auth connection state to false
     *
     * @param int $fbid Facebook userid of the user

     * @return void
     */
    public function disconnectEntry($fbid) {

        $fbid = $this->_db->quoteInto("?", $fbid);
        $this->_db->update($this->_name, array("connected" => 0), "fbid={$fbid}");
    }

}

