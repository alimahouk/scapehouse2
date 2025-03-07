<?php

/**
 * Tipbox twitter table management class
 *
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_DbTable_Tbtwitter extends Zend_Db_Table_Abstract {

    protected $_name = "tb_twitter";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs a twitter token
     *
     * @param int $userid id of user
     * @param string $twtUsername Twitter username
     * @param string $twtToken Twitter token string
     * @param int $twtid Twitter userid
     * @param string $twtTokenSec Token secret
     * @return id of latest created token entry
     */
    public function logToken($userid, $twtUsername, $twtToken, $twtid, $twtTokenSec) {

        $this->_db->insert($this->_name, array(
            "token" => $twtToken,
            "twtid" => $twtid,
            "userid" => $userid,
            "connected" => 1,
            "tokensec" => $twtTokenSec,
            "twtusername" => $twtUsername,
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
     * Gets a token log's row by looking up the Twitter userid of the user
     *
     * @param string $twtid Twitter userid of the user
     * @return token row data
     */
    public function getTokenByTwtid($twtid) {

        $query = $this->_db->select()->from($this->_name)->where("twtid=?", $twtid);

        $result = $this->_db->fetchRow($query);

        return $result;
    }
//
//    /**
//     * Updates a token log
//     *
//     * @param string $tokenOld Old Facebook token
//     * @param string $tokenNew Newly issued Facebook token
//     * @param string $fbTokenExp Issued token expiry date
//     * @param bool $connected State of Facebook auth
//
//     * @return void
//     */
//    public function updateToken($tokenOld, $tokenNew, $fbTokenExp, $connected) {
//
//        $tokenOld = $this->_db->quoteInto("?", $tokenOld);
//        $this->_db->update($this->_name, array(
//            "token" => $tokenNew,
//            "expdate" => gmdate("Y-m-d H:i:s", $fbTokenExp),
//            "connected" => $connected), "token={$tokenOld}");
//    }
//
//    /**
//     * Updates a token log and sets the Facebook auth connection state to false
//     *
//     * @param int $fbid Facebook userid of the user
//
//     * @return void
//     */
//    public function disconnectEntry($fbid) {
//
//        $fbid = $this->_db->quoteInto("?", $fbid);
//        $this->_db->update($this->_name, array("connected" => 0), "fbid={$fbid}");
//    }

}

