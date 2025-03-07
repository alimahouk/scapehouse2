<?php

/**
 * Tipbox mailing list table management class
 *
 * @copyright  2012 Scapehouse
 */

class Model_DbTable_Tbmailinglist extends Zend_Db_Table_Abstract {

    protected $_name = "tb_mailinglist";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs an email address into the mailing list
     *
     * @param string $email The email address
     * @return id of latest created entry
     */
    function insertEmail($email) {

        try {

            $this->_db->insert($this->_name, array(
                "email" => $email,
                "time" => gmdate("Y-m-d H:i:s", time()))
            );

            $logid = $this->_db->lastInsertId($this->_name);

            return $logid;
        } catch (Exception $e) {
            
        }
    }

}

