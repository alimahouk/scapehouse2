<?php

/**
 * Tipbox tip like management class
 *
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_DbTable_Tblike extends Zend_Db_Table_Abstract {

    protected $_name = "tb_like";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs like by a user on a tip
     *
     * @param int $userid id of user
     * @param int $tipid id of the tip
     * @return id of latest created like
     */
    function like($userid, $tipid) {

        $query = $this->_db->select()->from($this->_name)->where("userid=?", $userid)->where("tipid=?", $tipid);
        $likeExists = $this->_db->fetchOne($query);

        if ($likeExists) {// If a like by the user already exists, delete it.
            $this->delete($userid, $tipid);

            // dencrement like counter in tip table
            $tipidQ = $this->_db->quoteInto("?", $tipid);
            $this->_db->update("tb_tip", array("like_count" => new Zend_Db_Expr("like_count - 1")), "id={$tipidQ}");

            return false;
        }

        // Get the tip data
        $query = $this->_db->select()->from("tb_tip", array("userid", "topicid"))->where("id=?", $tipid);
        $tipData = $this->_db->fetchRow($query);

        if ($userid != $tipData["userid"]) {

            $this->_db->insert($this->_name, array(
                "userid" => $userid,
                "tipownerid" => $tipData["userid"],
                "tipid" => $tipid,
                "time" => gmdate("Y-m-d H:i:s", time()))
            );

            $likeid = $this->_db->lastInsertId($this->_name);

            // inncrement like counter in tip table
            $tipidQ = $this->_db->quoteInto("?", $tipid);
            $this->_db->update("tb_tip", array("like_count" => new Zend_Db_Expr("like_count + 1")), "id={$tipidQ}");

            // Genius Calculation

            $tbTopicTable = new Tipbox_Model_DbTable_Tbtopic();
            $tbTopicTable->calcGenius($tipData["topicid"], $tipData["userid"]);

            return $likeid;
        }
    }

    function getLikerCount($tipid) {

        $query = $this->_db->select()->from($this->_name, "COUNT(id)")->where("tb_like.tipid=?", $tipid);

        return $this->_db->fetchOne($query);
    }

    /**
     * Gets likers of a particular tip
     *
     * @param int $tipid id of tip
     * @param int $limit maxiumum number of likers to be returned
     * @return user array of likers.
     */
    function getLikers($tipid, $limit = 7) {

        $query = $this->_db->select()->from($this->_name)
                ->join("tb_user", "tb_user.id = tb_like.userid", array("fullname", "username", "pichash", "id"))
                ->where("tb_like.tipid=?", $tipid)
                ->limit($limit)
                ->order("tb_like.time DESC");

        return $this->_db->fetchAll($query);
    }

    /**
     * Deletes a like
     *
     * @param int $userid id of user
     * @param int $tipid id of the tip
     * @return void
     */
    function delete($userid, $tipid) {

        $userid = $this->_db->quoteInto("?", $userid);
        $tipid = $this->_db->quoteInto("?", $tipid);

        $this->_db->delete($this->_name, "userid={$userid} AND tipid={$tipid}");
    }

}

