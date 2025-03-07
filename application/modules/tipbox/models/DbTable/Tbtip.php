<?php

/**
 * Tipbox tip table management class
 *
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_DbTable_Tbtip extends Zend_Db_Table_Abstract {

    protected $_name = "tb_tip";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Creates a tip
     *
     * @param int    $userid id of the user creating the tip
     * @param string $content Content of the tip
     * @param int    $topicid id of the topid of the tip
     * @param int    $catid id of the category of the tip
     * @param double $location_lat location coordinates if any
     * @param double $location_long location coordinates if any
     * @return id of latest created tip
     */
    function createTip($userid, $content, $topicid, $catid, $location_lat, $location_long) {

        $this->_db->insert($this->_name, array(
            "userid" => $userid,
            "content" => $content,
            "topicid" => $topicid,
            "catid" => $catid,
            "like_count" => 0,
            "location_lat" => $location_lat,
            "location_long" => $location_long,
            "time" => gmdate("Y-m-d H:i:s", time())
        ));

        $tipid = $this->_db->lastInsertId($this->_name);

        $tbTopicTable = new Tipbox_Model_DbTable_Tbtopic();
        $topic = $tbTopicTable->getTopicById($topicid);

        $this->_db->insert("tb_tip_search", array(
            "tipid" => $tipid,
            "content" => $content,
            "topic" => $topic
        ));

        return $tipid;
    }

    /**
     * Searches and returns tips based on search terms
     *
     * @param string $term Query string for search
     * @param int $viewerid Id of the viewer
     * @return Tips array
     */
    function search($term, $viewerid, $batch = 0) {

        if ($batch == -1) { // Batch bypasser        
            $batch = 0;
            $GLOBALS["batchSize"] = 1000;
        }

        $query = $this->_db->select()->from("tb_tip_search", array("content", "topic as topicContent"))
                ->join("tb_tip", "tb_tip.id = tb_tip_search.tipid", array("id", "userid", "topicid", "catid", "like_count", "location_lat", "location_long", "time"))
                ->joinLeft("tb_like", "tb_like.tipid = tb_tip.id AND tb_like.userid = {$viewerid}", array("IF(tb_like.id,1,0) AS marked"))
                ->joinLeft("tb_follow", "tb_follow.topicid = tb_tip.topicid AND tb_follow.userid = {$viewerid}", array("IF(tb_follow.id,1,0) AS followsTopic"))
                ->join("tb_user", "tb_user.id = tb_tip.userid", array("fullname", "username", "pichash"))
                ->join("tb_topic", "tb_topic.id = tb_tip.topicid", array("genius"))
                ->join("tb_cat", "tb_cat.id = tb_tip.catid", array("parentcat", "subcat"))
                ->where("MATCH(`tb_tip_search`.`content`,`topic`) AGAINST (? IN NATURAL LANGUAGE MODE)", $term)
                ->limit($GLOBALS["batchSize"], $batch * $GLOBALS["batchSize"]);

        $result = $this->_db->fetchAll($query);

        if ($result) {
            return $result;
        }

        return false;
    }

    /**
     * Gets a tip by id
     *
     * @param int   $id Id of the tip
     * @return Tip array
     */
    function getTipbyId($id, $viewerid = 0) {

        $query = $this->_db->select()->from($this->_name)
                ->joinLeft("tb_like", "tb_like.tipid = tb_tip.id AND tb_like.userid = {$viewerid}", array("IF(tb_like.id,1,0) AS marked"))
                ->joinLeft("tb_follow", "tb_follow.topicid = tb_tip.topicid AND tb_follow.userid = {$viewerid}", array("IF(tb_follow.id,1,0) AS followsTopic"))
                ->join("tb_user", "tb_user.id = tb_tip.userid", array("fullname", "username", "pichash"))
                ->join("tb_topic", "tb_topic.id = tb_tip.topicid", array("content as topicContent", "genius"))
                ->join("tb_cat", "tb_cat.id = tb_tip.catid", array("parentcat", "subcat"))
                ->where("tb_tip.id=?", $id);

        return $this->_db->fetchAll($query);
    }

    function editTip($tipid, $content) {

        $tipid = $this->_db->quoteInto("?", $tipid);

        $this->_db->update($this->_name, array("content" => $content), "id={$tipid}");

        // Update search table
        $this->_db->update("{$this->_name}_search", array("content" => $content), "tipid={$tipid}");
    }

    function getLatestTips() {

        $query = $this->_db->select()->from($this->_name)->order("BY time")->limit($GLOBALS["batchSize"]);
        return $this->_db->fetchAll($query);
    }

    /**
     * Gets hot tips according to a particular calculation
     *
     * @param int   $viewerid Id of the viewer
     * @param int   $batch the batch number for the set of tips
     * @return Tips array
     */
    function getHotTips($viewerid, $batch = 0) {

        $query = $this->_db->select()->from($this->_name)
                ->joinLeft("tb_like", "tb_like.tipid = tb_tip.id AND tb_like.userid = {$viewerid}", array("IF(tb_like.id,1,0) AS marked"))
                ->joinLeft("tb_follow", "tb_follow.topicid = tb_tip.topicid AND tb_follow.userid = {$viewerid}", array("IF(tb_follow.id,1,0) AS followsTopic"))
                ->join("tb_user", "tb_user.id = tb_tip.userid", array("fullname", "username", "pichash"))
                ->join("tb_topic", "tb_topic.id = tb_tip.topicid", array("content as topicContent", "genius"))
                ->join("tb_cat", "tb_cat.id = tb_tip.catid", array("parentcat", "subcat"))
                ->where("tb_tip.like_count >= 1")
                ->limit($GLOBALS["batchSize"], $batch * $GLOBALS["batchSize"])
                ->order("tb_tip.like_count DESC");

        return $this->_db->fetchAll($query);
    }

    /**
     * Gets recent tips according to a particular calculation
     *
     * @param int   $viewerid Id of the viewer
     * @param int   $batch the batch number for the set of tips
     * @return Tips array
     */
    function getRecentTips($viewerid, $batch = 0) {

        $query = $this->_db->select()->from($this->_name)
                ->joinLeft("tb_like", "tb_like.tipid = tb_tip.id AND tb_like.userid = {$viewerid}", array("IF(tb_like.id,1,0) AS marked"))
                ->joinLeft("tb_follow", "tb_follow.topicid = tb_tip.topicid AND tb_follow.userid = {$viewerid}", array("IF(tb_follow.id,1,0) AS followsTopic"))
                ->join("tb_user", "tb_user.id = tb_tip.userid", array("fullname", "username", "pichash"))
                ->join("tb_topic", "tb_topic.id = tb_tip.topicid", array("content as topicContent", "genius"))
                ->join("tb_cat", "tb_cat.id = tb_tip.catid", array("parentcat", "subcat"))
                ->limit($GLOBALS["batchSize"], $batch * $GLOBALS["batchSize"])
                ->order("tb_tip.time DESC");

        return $this->_db->fetchAll($query);
    }

    /**
     * Gets tips on a particular topic
     *
     * @param int   $topicid Id of the topic
     * @param int   $offset offset for loading more tips
     * @return Tips array
     */
    function getTipsByTopicid($topicid, $viewerid, $batch = 0) {

        $query = $this->_db->select()->from($this->_name)
                ->joinLeft("tb_like", "tb_like.tipid = tb_tip.id AND tb_like.userid = {$viewerid}", array("IF(tb_like.id,1,0) AS marked"))
                ->joinLeft("tb_follow", "tb_follow.topicid = tb_tip.topicid AND tb_follow.userid = {$viewerid}", array("IF(tb_follow.id,1,0) AS followsTopic"))
                ->join("tb_user", "tb_user.id = tb_tip.userid", array("fullname", "username", "pichash"))
                ->join("tb_topic", "tb_topic.id = tb_tip.topicid", array("content as topicContent", "genius"))
                ->join("tb_cat", "tb_cat.id = tb_tip.catid", array("parentcat", "subcat"))
                ->where("tb_tip.topicid=?", $topicid)
                ->limit($GLOBALS["batchSize"], $batch * $GLOBALS["batchSize"])
                ->order("tb_tip.time DESC");

        return $this->_db->fetchAll($query);
    }

    /**
     * Gets tips liked by a user
     *
     * @param int   $userid Id of the user
     * @param int   $offset offset for loading more tips
     * @return Tips array
     */
    function getTipsLikedByUser($userid, $viewerid, $batch = 0) {

        $query = $this->_db->select()->from($this->_name)
                ->joinLeft("tb_like AS l1", "l1.userid = {$userid}", NULL)
                ->joinLeft("tb_like AS l2", "l2.tipid = tb_tip.id AND l2.userid = {$viewerid}", array("IF(l2.id,1,0) AS marked"))
                ->joinLeft("tb_follow", "tb_follow.topicid = tb_tip.topicid AND tb_follow.userid = {$viewerid}", array("IF(tb_follow.id,1,0) AS followsTopic"))
                ->join("tb_user", "tb_user.id = tb_tip.userid", array("fullname", "username", "pichash"))
                ->join("tb_topic", "tb_topic.id = tb_tip.topicid", array("content as topicContent", "genius"))
                ->join("tb_cat", "tb_cat.id = tb_tip.catid", array("parentcat", "subcat"))
                ->where("tb_tip.id = l1.tipid")
                ->limit($GLOBALS["batchSize"], $batch * $GLOBALS["batchSize"])
                ->order("l1.time DESC");

        return $this->_db->fetchAll($query);
    }

    /**
     * Gets tips made by the user
     *
     * @param int   $userid Id of the user
     * @param int   $offset offset for loading more tips
     * @return Tips array
     */
    function getTipsByUser($userid, $viewerid, $batch = 0) {

        $query = $this->_db->select()->from($this->_name)
                ->joinLeft("tb_like", "tb_like.tipid = tb_tip.id AND tb_like.userid = {$viewerid}", array("IF(tb_like.id,1,0) AS marked"))
                ->joinLeft("tb_follow", "tb_follow.topicid = tb_tip.topicid AND tb_follow.userid = {$viewerid}", array("IF(tb_follow.id,1,0) AS followsTopic"))
                ->join("tb_user", "tb_user.id = tb_tip.userid", array("fullname", "username", "pichash"))
                ->join("tb_topic", "tb_topic.id = tb_tip.topicid", array("content as topicContent", "genius"))
                ->join("tb_cat", "tb_cat.id = tb_tip.catid", array("parentcat", "subcat"))
                ->where("tb_tip.userid=?", $userid)
                ->limit($GLOBALS["batchSize"], $batch * $GLOBALS["batchSize"])
                ->order("tb_tip.time DESC");

        return $this->_db->fetchAll($query);
    }

    /**
     * Gets a user's tip feed according to the topics
     * the user is following.
     *
     * @param int   $userid id of the feed owner
     * @param int   $offset offset for loading more tips
     * @return Tips array
     */
    function getUserFeed($userid, $batch = 0) {

        $query = $this->_db->select()->from("tb_tip")
                ->joinLeft("tb_like", "tb_like.tipid = tb_tip.id AND tb_like.userid = {$userid}", array("IF(tb_like.id,1,0) AS marked"))
                ->join("tb_user", "tb_user.id = tb_tip.userid", array("fullname", "username", "pichash"))
                ->join("tb_follow", "tb_follow.userid = {$userid}", array("IF(tb_follow.id,1,0) AS followsTopic"))
                ->join("tb_topic", "tb_topic.id = tb_follow.topicid", array("content as topicContent", "genius"))
                ->join("tb_cat", "tb_cat.id = tb_tip.catid", array("parentcat", "subcat"))
                ->where("tb_tip.topicid = tb_follow.topicid")
                ->limit($GLOBALS["batchSize"], $batch * $GLOBALS["batchSize"])
                ->order("time DESC");

        return $this->_db->fetchAll($query);
    }

    /**
     * Gets tips for the ticker
     *
     * @param int   $ids hand picked ids to fetch
     * @return Tips array
     */
    function tipTicker($ids) {

        // print_r($ids);
        $implodedIds = implode(",", $ids);

        // echo $implodedIds;

        $query = $this->_db->select()->from($this->_name)
                ->join("tb_user", "tb_user.id = tb_tip.userid", array("fullname", "username", "pichash"))
                ->join("tb_topic", "tb_topic.id = tb_tip.topicid", array("content as topicContent", "genius"))
                ->join("tb_cat", "tb_cat.id = tb_tip.catid", array("parentcat", "subcat"))
                ->where("tb_tip.id IN ({$implodedIds})")
                ->order("FIND_IN_SET(tb_tip.id, '{$implodedIds}')");

        return $this->_db->fetchAll($query);
    }

    function getAll() {

        $query = $this->_db->select()->from($this->_name)
                ->join("tb_user", "tb_user.id = tb_tip.userid", array("fullname", "username", "pichash"))
                ->join("tb_topic", "tb_topic.id = tb_tip.topicid", array("content as topicContent", "genius"))
                ->join("tb_cat", "tb_cat.id = tb_tip.catid", array("parentcat", "subcat"))
                ->order("tb_tip.time DESC");

        return $this->_db->fetchAll($query);
    }

    function getTipCount() {

        $query = $this->_db->select()->from($this->_name, "COUNT({$this->_name}.id)");

        return $this->_db->fetchOne($query);
    }

    function getReportedTips() {

        $query = $this->_db->select()->from($this->_name,array("tb_tip.*","(SELECT COUNT(tb_tipreport.id) FROM tb_tipreport WHERE tb_tip.id = tb_tipreport.tipid) AS reportCount"))
                ->join("tb_user AS ut1", "ut1.id = tb_tip.userid", array("ut1.username AS tipOwnerUsername"))
                ->join("tb_tipreport", "tb_tipreport.tipid = tb_tip.id", array("time AS reporttime"))
                ->join("tb_tipreporttype", "tb_tipreporttype.id = tb_tipreport.reason", array("content AS reason"))
                ->join("tb_topic", "tb_topic.id = tb_tip.topicid", array("content as topicContent", "genius"))
                ->group("tb_tip.id")
                ->order("reportCount DESC");
        
        $tips = $this->_db->fetchAll($query);
        
        foreach($tips as $key => $tip){
           $reasonListQuery = $this->_db->select()->from("tb_tipreport","reason")->where("tipid=?",$tip["id"]);
           $tips[$key]["reasonList"] = $this->_db->fetchCol($reasonListQuery);
        }

        return $tips;
    }

    /**
     * Deletes a tip
     *
     * @param int   $userid id of tip owner
     * @param int   $tipid id of the tip
     * @return void
     */
    function delete($userid, $tipid) {

        $returnedTip = $this->getTipbyId($tipid);

        $userid = $this->_db->quoteInto("?", $userid);
        $tipid = $this->_db->quoteInto("?", $tipid);

        if ($this->_db->delete($this->_name, "userid={$userid} AND id={$tipid}")) {

            $this->_db->delete("tb_tip_search", "tipid={$tipid}");

            $tipsOnTopicOfTipBeingDeleted = $this->getTipsByTopicid($returnedTip[0]["topicid"], $userid);

            if (count($tipsOnTopicOfTipBeingDeleted) == 0) { // If there are no more tips left in a topic, delete that topic.
                $tbTopicTable = new Tipbox_Model_DbTable_Tbtopic();
                $tbTopicTable->deleteTopic($returnedTip[0]["topicid"]);
            }
        }
    }

}

