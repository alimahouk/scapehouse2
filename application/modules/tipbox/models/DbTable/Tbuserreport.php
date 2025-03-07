<?php

/**
 * Tipbox user report management class
 *
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_DbTable_Tbuserreport extends Zend_Db_Table_Abstract {

    protected $_name = "tb_userreport";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs a report by a user on an other user
     *
     * @param int $reporterid id of user sending the report
     * @param int $userid id of user
     * @param int $reason exit;reason of the report
     * 
     * @return newly created report id
     */
    function log($reporterid, $userid, $reason) {

        $query = $this->_db->select()->from($this->_name)->where("userid=?", $userid)->where("reporterid=?", $reporterid);
        $reportExists = $this->_db->fetchOne($query);

        if ($reportExists) {// If a report by the user already exists, delete it.
            $this->delete($userid, $reporterid);
        }

        $this->_db->insert($this->_name, array(
            "reporterid" => $reporterid,
            "userid" => $userid,
            "reason" => $reason,
            "time" => gmdate("Y-m-d H:i:s", time()))
        );

        $reportid = $this->_db->lastInsertId($this->_name);

        return $reportid;
    }

    function getReportCount() {

        $query = $this->_db->select()->from($this->_name, "COUNT({$this->_name}.id)");

        return $this->_db->fetchOne($query);
    }

    /**
     * Deletes a report
     *
     * @param int $userid id of user
     * @param int $reporterid id of user sending the report
     * @return void
     */
    function delete($userid, $reporterid) {

        $userid = $this->_db->quoteInto("?", $userid);
        $reporterid = $this->_db->quoteInto("?", $reporterid);

        $this->_db->delete($this->_name, "userid={$userid} AND reporterid={$reporterid}");
    }

}

