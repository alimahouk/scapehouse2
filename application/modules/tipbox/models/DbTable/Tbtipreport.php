<?php

/**
 * Tipbox report management class
 *
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_DbTable_Tbtipreport extends Zend_Db_Table_Abstract {

    protected $_name = "tb_tipreport";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs a report by a user on a tip
     *
     * @param int $userid id of user
     * @param int $reason reason of the report
     * @param int $tipid id of the reported tip
     * 
     * @return newly created report id
     */
    function log($userid, $reason, $tipid) {

        $query = $this->_db->select()->from($this->_name)->where("reporterid=?", $userid)->where("tipid=?", $tipid);
        $reportExists = $this->_db->fetchOne($query);

        if ($reportExists) {// If a report by the user already exists, delete it.
            $this->delete($userid, $tipid);
        }

        $this->_db->insert($this->_name, array(
            "reporterid" => $userid,
            "reason" => $reason,
            "tipid" => $tipid,
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
     * @param int $tipid id of the tip
     * @return void
     */
    function delete($userid, $tipid) {

        $userid = $this->_db->quoteInto("?", $userid);
        $tipid = $this->_db->quoteInto("?", $tipid);

        $this->_db->delete($this->_name, "reporterid={$userid} AND tipid={$tipid}");
    }

    /**
     * Deletes all reports on a tip
     *
     * @param int $tipid id of the tip
     * @return void
     */
    function deleteReportsOnTip($tipid) {

        $tipid = $this->_db->quoteInto("?", $tipid);

        $this->_db->delete($this->_name, "tipid={$tipid}");
    }

}

