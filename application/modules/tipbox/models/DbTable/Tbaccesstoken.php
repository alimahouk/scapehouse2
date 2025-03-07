<?php

/**
 * Tipbox Access token table management class
 *
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_DbTable_Tbaccesstoken extends Zend_Db_Table_Abstract {

    protected $_name = "tb_accesstoken";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs an access token
     *
     * @param int $userid id of user
     * @param string $token Token string
     * @return id of latest created token
     */
    function logToken($userid, $token) {

        $this->_db->insert($this->_name, array(
            "userid" => $userid,
            "token" => $token,
            "time" => gmdate("Y-m-d H:i:s", time()))
        );

        $tokenid = $this->_db->lastInsertId($this->_name);

        return $tokenid;
    }

    /**
     * Gets a token log's row by looking up the token
     *
     * @param string $token Token string
     * @return token row data
     */
    function getTokenByToken($token) {

        if (!$token) {//Just a failsafe to against NULL values
            $token = "";
        }

        $query = $this->_db->select()->from($this->_name)->where("token=?", $token);
        return $this->_db->fetchRow($query);
    }

    /**
     * Gets a token log's row by looking up the userid
     *
     * @param int $userid id of the user holding the token
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
     * Deletes a token entry
     *
     * @param int $userid id of the user holding the token
     * @return void
     */
    function deleteTokenByUserid($userid) {

        $userid = $this->_db->quoteInto("?", $userid);
        $this->_db->delete($this->_name, "userid={$userid}");
    }

    /**
     * Deletes a token entry
     *
     * @param int $token Token to be deleted
     * @return void
     */
    function deleteTokenByToken($token) {

        $token = $this->_db->quoteInto("?", $token);
        return $this->_db->delete($this->_name, "token={$token}");
    }

}

