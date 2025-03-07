<?php

/**
 * Tipbox category table management class
 *
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_DbTable_Tbcat extends Zend_Db_Table_Abstract {

    protected $_name = "tb_cat";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Gets the list of categories
     *
     * @param string   $type thing|place
     * @return Array of categories
     */
    function getCats($type) {

        $query = $this->_db->select()->from($this->_name)->order("subcat")->where("parentcat=?",$type);
        return $this->_db->fetchAll($query);
        
    }

}

