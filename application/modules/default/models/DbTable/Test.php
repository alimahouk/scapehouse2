<?php

class Model_DbTable_Test extends Zend_Db_Table_Abstract {

    function insertEmail($email) {
        
        $create = $this->_db->insert("tb_mailinglist", array(
                    "email" => $email,
                    "time" => gmdate("Y-m-d H:i:s", time()))
        );

        return $userid;
        
    }

    function getUsers() {
        $query = $this->_db->select()->from("user", "email");
        return $this->_db->fetchCol($query);
    }

    function getEmailsFromInviteList() {
        $query = $this->_db->select()->from("invite", "email");
        return $this->_db->fetchCol($query);
    }

}

