<?php

/**
 * Tipbox topic follow table management class
 *
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_DbTable_Tbfollow extends Zend_Db_Table_Abstract {

    protected $_name = "tb_follow";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Logs follow by a user on a topic
     *
     * @param int $userid id of user
     * @param int $topicid id of the topic
     * @return id of latest created like
     */
    function follow($userid, $topicid) {


        $query = $this->_db->select()->from($this->_name)->where("userid=?", $userid)->where("topicid=?", $topicid);
        $followExists = $this->_db->fetchOne($query);

        if ($followExists) {// If a follow by the user already exists, delete it.
            $this->delete($userid, $topicid);
            return false;
        }

        $this->_db->insert($this->_name, array(
            "userid" => $userid,
            "topicid" => $topicid,
            "time" => gmdate("Y-m-d H:i:s", time()))
        );

        $followid = $this->_db->lastInsertId($this->_name);

        return $followid;
    }

    /**
     * Transfers topic follows from userid A to userid B
     *
     * @param int $userid id of user
     * @param int $transferToId id of user that needs the transfer
     * @return void
     */
    function followAll($userid, $transferToId) {

        $query = $this->_db->select()->from($this->_name)->where("userid=?", $userid);
        $userFollows = $this->_db->fetchAll($query);

        foreach ($userFollows as $follow) {

            $query = $this->_db->select()->from($this->_name)->where("userid=?", $transferToId)->where("topicid=?", $follow["topicid"]);
            $followExistsForTransferId = $this->_db->fetchOne($query);

            if (!$followExistsForTransferId) {
                $this->_db->insert($this->_name, array(
                    "userid" => $transferToId,
                    "topicid" => $follow["topicid"],
                    "time" => gmdate("Y-m-d H:i:s", time()))
                );
            }
        }
    }

    /**
     * Transfers topic follows to a user who has used the QSG
     *
     * @param int $qsgList the list of qsgids
     * @param int $transferToId id of user that needs the transfer
     * @return void
     */
    function qsgFollow($qsgList, $transferToId) {

        $qsgList = implode(",", $qsgList);
        $qsgTopicsQuery = $this->_db->select()->from("tb_topic")->where("qsgid IN ({$qsgList})");
        $qsgTopics = $this->_db->fetchAll($qsgTopicsQuery);

        foreach ($qsgTopics as $topic) {

            $query = $this->_db->select()->from($this->_name)->where("userid=?", $transferToId)->where("topicid=?", $topic["id"]);
            $followExistsForTransferId = $this->_db->fetchOne($query);

            if (!$followExistsForTransferId) {
                $this->_db->insert($this->_name, array(
                    "userid" => $transferToId,
                    "topicid" => $topic["id"],
                    "time" => gmdate("Y-m-d H:i:s", time()))
                );
            }
        }
    }

    /**
     * Gets the followers of a particular topic
     *
     * @param int $topicid id of topic
     * @param int $limit maximum number of followers returned
     * @return user array (followers)
     */
    function getFollowers($topicid, $limit = 0, $batch = 0) {

        $offset = $batch * $GLOBALS["batchSize"];
        $query = $this->_db->select()->from($this->_name, array("tb_follow.*", "(SELECT count(DISTINCT tb_like.userid) FROM tb_like WHERE tb_like.tipownerid = tb_user.id) AS helpCount"))
                ->join("tb_user", "tb_user.id = tb_follow.userid", array("fullname", "username", "pichash", "id", "bio"))
                ->where("tb_follow.topicid=?", $topicid)
                ->limit(15,$offset)
                ->order("tb_follow.time DESC");

        return $this->_db->fetchAll($query);
    }

    /**
     * Deletes a follow
     *
     * @param int $userid id of user
     * @param int $topicid id of the topic
     * @return void
     */
    function delete($userid, $topicid) {

        $userid = $this->_db->quoteInto("?", $userid);
        $topicid = $this->_db->quoteInto("?", $topicid);

        $this->_db->delete($this->_name, "userid={$userid} AND topicid={$topicid}");
    }

}

