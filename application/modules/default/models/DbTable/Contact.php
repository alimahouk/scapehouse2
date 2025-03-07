<?php
/**
 * "contact" table communication class
 *
 * This class contains methods for managing the "contact" table.
 *
 * @copyright  2012 Scapehouse
 */

class Model_DbTable_Contact extends Zend_Db_Table_Abstract {

    protected $_name = "contact";

    
    /**
     * Logs an entry into the "contact" table.
     *
     * @param  string   $email  E-mail address
     * @param  string   $fullname Fullname
     * @param  string   $content  Content of message
     * @return integer  Id of the new entry
     */
    
    public function log($email, $fullname, $content, $source) {

        $this->_db->insert($this->_name, array(
            "content" => $content,
            "fullname" => $fullname,
            "email" => $email,
            "source" => $source,
            "time" => gmdate("Y-m-d H:i:s", time())));

        $logid = $this->_db->lastInsertId($this->_name);
        
        return $logid;
    }

     /**
     * Fetches all entries in the "contact" table
     *
     * @return array Array of all entries.
     */
    
    public function getAll(){
        
        $query = $this->_db->select()->from($this->_name);
        return $this->_db->fetchAll($query);
    }

}

?>
