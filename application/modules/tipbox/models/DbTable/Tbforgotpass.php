<?php

/**
 * Tipbox forgotten password hash verificaiton table management class
 *
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_DbTable_Tbforgotpass extends Zend_Db_Table_Abstract {

    protected $_name = "tb_forgotpass";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs a verifier hash
     *
     * @param int $userid id of user
     * @param string $hash Token string
     * @return id of latest created hash
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
     * @param int $token token to be deleted
     * @return void
     */
    function deleteTokenByToken($token) {

        $token = $this->_db->quoteInto("?", $token);
        $this->_db->delete($this->_name, "token={$token}");
    }

}

