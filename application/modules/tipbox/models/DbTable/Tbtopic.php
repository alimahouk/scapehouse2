<?php

/**
 * Tipbox topic table management class
 *
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_DbTable_Tbtopic extends Zend_Db_Table_Abstract {

    protected $_name = "tb_topic";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Creates a topic
     *
     * @param int   $userid id of the user creating the topic
     * @param string    $content Content of the topic
     * @return id of latest created topic or identical topic
     */
    function createTopic($userid, $content) {

        // A failsafe to prevent duplicate topics
        $query = $this->_db->select()->from($this->_name, "id")->where("content=?", $content);
        $existingTopicid = $this->_db->fetchOne($query);

        if (!$existingTopicid) {

            $this->_db->insert($this->_name, array(
                "userid" => $userid,
                "content" => $content,
                "time" => gmdate("Y-m-d H:i:s", time())
            ));

            $topicid = $this->_db->lastInsertId($this->_name);

            $this->_db->insert("tb_topic_search", array(
                "topicid" => $topicid,
                "content" => $content
            ));

            // Make the user follow the topic he created
            $tb_follow = new Tipbox_Model_DbTable_Tbfollow();
            $tb_follow->follow($userid, $topicid);
        } else {
            $topicid = $existingTopicid;
        }

        return $topicid;
    }
    
    /**
     * Calculates who should be the genius of a topic.
     *
     * @param int     $topicid Id of the topic being calculated
     * @param int     $userid Id of the user being calculated
     * @param bool    $notifs To notify or not to notify, that is the question.
     * @return void
     */
    function calcGenius($topicid, $userid, $notifs = true) {

        $queryLikeCount = $this->_db->select()->from("tb_tip", "SUM(like_count) as totalLikeCount")
                ->where("userid=?", $userid)
                ->where("topicid=?", $topicid);

        $totalLikeCount = $this->_db->fetchOne($queryLikeCount);

        // Fetch topic needed for calculation
        $query = $this->_db->select()->from($this->_name)->where("id=?", $topicid);
        $topic = $this->_db->fetchRow($query);

        $topicidQ = $this->_db->quoteInto("?", $topicid);

        if (!$topic["genius"] && $totalLikeCount >= 10) { // Set the genius and his rank
            $this->_db->update($this->_name, array("genius" => $userid, "genius_rank" => $totalLikeCount), "id={$topicidQ}");

            if ($notifs) {
                // Notify user about becoming the genius
                $applePushProcessor = new Tipbox_Model_ApplePush(
                                "Congrats! You just became the genius of \"{$topic["content"]}\"!",
                                $userid,
                                array("type" => "topic", "id" => $topicid));

                $applePushProcessor->dispatchNotif();
            }

            // Notify user about becoming the genius END
        } else if ($topic["genius"] == $userid) {// modifiy the genius rank
            $this->_db->update($this->_name, array("genius_rank" => $totalLikeCount), "id={$topicidQ}");
        } else if ($topic["genius"] &&
                $topic["genius"] != $userid &&
                ($topic["genius_rank"] + ($topic["genius_rank"] * 0.1) < $totalLikeCount)) {

            // Oust the genius
            $this->_db->update($this->_name, array("genius" => $userid, "genius_rank" => $totalLikeCount), "id={$topicidQ}");

            if ($notifs) {
                // Notify user about becoming the genius
                $applePushProcessor = new Tipbox_Model_ApplePush(
                                "Congrats! You just became the genius of \"{$topic["content"]}\"!",
                                $userid,
                                array("type" => "topic", "id" => $topicid));

                $applePushProcessor->dispatchNotif();
            }

            // Notify user about becoming the genius END
            // Notify other user about being ousted as the genius
            $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
            $userData = $tbuserTable->getUserById($userid);

            if ($notifs) {

                $applePushProcessor = new Tipbox_Model_ApplePush(
                                "Whoops! Looks like {$userData["fullname"]} has ousted you as the genius of \"{$topic["content"]}\"!",
                                $topic["genius"],
                                array("type" => "topic", "id" => $topicid));

                $applePushProcessor->dispatchNotif();
            }
            // Notify other user about being ousted as the genius END
        }

        //Genius rank retractor
        if ($topic["genius"] == $userid && $totalLikeCount < 10) { //If the user is a genius but the rank does not qualify
            $this->_db->update($this->_name, array("genius" => NULL, "genius_rank" => NULL), "id={$topicidQ}");
        }
    }

    /**
     * Searches and returns topics based on search terms
     *
     * @param string $term Query string for search
     * @return Topics array
     */
    function search($term, $viewerid, $batch = 0) {

        if ($batch == -1) { // Batch bypasser        
            $batch = 0;
            $GLOBALS["batchSize"] = 1000;
        }

        $offset = $batch * $GLOBALS["batchSize"];

        $query = $this->_db->query("
             SELECT tb_topic.*, tb_topic.id AS topicid, tb_user.username,
            (SELECT count(id) FROM tb_tip WHERE tb_tip.topicid = tb_topic_search.topicid) AS tipCount,
            (SELECT count(id) FROM tb_follow WHERE tb_follow.topicid = tb_topic_search.topicid) AS followCount,
            IF(f1.id,1,0) AS `followsTopic`
            FROM tb_topic_search
            LEFT JOIN `tb_topic` ON tb_topic_search.topicid = tb_topic.id
            LEFT JOIN `tb_user` ON tb_topic.userid = tb_user.id
            LEFT JOIN `tb_follow` AS `f1` ON f1.topicid = tb_topic_search.topicid AND f1.userid = {$viewerid}
            WHERE MATCH(`tb_topic_search`.`content`) AGAINST (? IN BOOLEAN MODE)
            LIMIT {$offset}, {$GLOBALS["batchSize"]}", $term . "*");

        return $query->fetchAll();
    }

    /**
     * Gets a particular topics details and information
     *
     * @param int     $topicid Id of the topic
     * @param int     $viewerid Id of current session holder
     * @return array of topic information
     */
    function getTopicInfo($topicid, $viewerid) {

        $query = $this->_db->query("
 SELECT
`tb_topic`.*,
 IF(f1.id,1,0) AS `followsTopic`,
 u2.username AS `topicCreatorUsername`,
 (SELECT COUNT(tb_follow.id) FROM tb_follow WHERE tb_follow.topicid = {$topicid}) AS `followCount`,
 (SELECT COUNT(tb_tip.id) FROM tb_tip WHERE tb_tip.topicid = {$topicid}) AS `tipCount`,
    `u1`.`fullname`, `u1`.`username`,
     `u1`.`pichash` FROM `tb_topic`
     LEFT JOIN `tb_follow` AS `f1` ON f1.topicid = {$topicid} AND f1.userid = {$viewerid}
     LEFT JOIN `tb_user` AS u1 ON u1.id = tb_topic.genius
     LEFT JOIN `tb_user` AS u2 ON u2.id = tb_topic.userid WHERE (tb_topic.id={$topicid})");

        return $query->fetch();
    }

    function getQsgTopics() {

        $query = $this->_db->select()->from($this->_name)->where("qsgid != ?", 0);
        return $this->_db->fetchAll($query);
    }

    function clearQsgTopics() {

        $this->_db->update("tb_topic", array("qsgid" => 0));
    }

    function saveQsgTopic($topicName, $qsgid) {
        
        $topicNameQ = $this->_db->quoteInto("?", $topicName);
        
        $this->_db->update("tb_topic", array("qsgid" => $qsgid), "content={$topicNameQ}");
    }

    /**
     * Gets a topics of which a particual user is a genius of
     *
     * @param int     $userid userid of the user
     * @param int     $viewerid Id of current session holder
     * @param int     $batch current batch for loading
     * @return array of topics
     */
    function getTopicsByGenius($userid, $viewerid, $batch = 0) {

        $offset = $batch * $GLOBALS["batchSize"];

        $query = $this->_db->query("
            SELECT tb_topic.*, tb_topic.id AS topicid,
            (SELECT count(id) FROM tb_tip WHERE tb_tip.topicid = tb_topic.id) AS tipCount,
            (SELECT count(id) FROM tb_follow WHERE tb_follow.topicid = tb_topic.id) AS followCount,
            IF(f1.id,1,0) AS `followsTopic`
            FROM tb_topic
            LEFT JOIN `tb_follow` AS `f1` ON f1.topicid = tb_topic.id AND f1.userid = {$viewerid}
            WHERE tb_topic.genius = {$userid}
            LIMIT {$offset}, {$GLOBALS["batchSize"]}");

        return $query->fetchAll();
    }

    /**
     * Gets a topics of which a particual user follows
     *
     * @param int     $userid userid of the user
     * @param int     $viewerid Id of current session holder
     * @param int     $batch current batch for loading
     * @return array of topics
     */
    function getTopicsByUserFollow($userid, $viewerid, $batch = 0) {

        $offset = $batch * $GLOBALS["batchSize"];

        $query = $this->_db->query("
            SELECT tb_topic.*, tb_topic.id AS topicid,
            (SELECT count(id) FROM tb_tip WHERE tb_tip.topicid = tb_topic.id) AS tipCount,
            (SELECT count(id) FROM tb_follow WHERE tb_follow.topicid = tb_topic.id) AS followCount,
            IF(f1.id,1,0) AS `followsTopic`
            FROM tb_topic
            LEFT JOIN `tb_follow` AS `f1` ON f1.topicid = tb_topic.id AND f1.userid = {$viewerid}
            LEFT JOIN `tb_follow` AS `f2` ON f2.userid = {$userid}
            WHERE tb_topic.id = f2.topicid
            ORDER BY `f2`.time DESC
            LIMIT {$offset}, {$GLOBALS["batchSize"]}");

        return $query->fetchAll();
    }

    function getAll() {

        $query = $this->_db->query("
            SELECT tb_topic.*, tb_topic.id AS topicid,
            (SELECT username FROM tb_user WHERE tb_user.id = tb_topic.userid) AS username,
            (SELECT count(id) FROM tb_tip WHERE tb_tip.topicid = tb_topic.id) AS tipCount,
            (SELECT count(id) FROM tb_follow WHERE tb_follow.topicid = tb_topic.id) AS followCount
            FROM tb_topic ORDER BY tipCount DESC");

        return $query->fetchAll();
    }

    function getTopicCount() {

        $query = $this->_db->select()->from($this->_name, "COUNT({$this->_name}.id)");

        return $this->_db->fetchOne($query);
    }

    function getTopicById($id) {

        $query = $this->_db->select()->from($this->_name, "content")->where("id=?", $id);
        return $this->_db->fetchOne($query);
    }

    function shiftTipToTopic($topicName, $tipid) {

        // Get the topic by the specified name
        $query = $this->_db->select()->from("tb_topic")->where("content=?", $topicName);
        $topicA = $this->_db->fetchRow($query);

        // Continue only if the topic A exists else exit quitely.
        if ($topicA) {

            $tip = $this->_db->select()->from("tb_tip")
                    ->where("id=?", $tipid);

            $tip = $this->_db->fetchRow($tip);

            // Set topic B id
            $topicBid = $tip["topicid"];

            //Move tip to topic A
            $tipidQ = $this->_db->quoteInto("?", $tipid);
            $this->_db->update("tb_tip", array("topicid" => $topicA["id"]), "id={$tipidQ}");
            $this->_db->update("tb_tip_search", array("topic" => $topicA["content"]), "tipid={$tipidQ}");

            //Get number of tips under topic B
            $tipCountTopicBQuery = $this->_db->select()->from("tb_tip", "COUNT(id)")->where("topicid=?", $topicBid);
            $tipCountTopicB = $this->_db->fetchOne($tipCountTopicBQuery);

            // If there are no more tips left in topic B, delete topic B
            if ($tipCountTopicB == 0) {

                $this->deleteTopic($topicBid);
            } else { // Get more info about topic B
                $topicBQuery = $this->_db->select()->from("tb_topic")->where("id=?", $topicBid);
                $topicB = $this->_db->fetchRow($topicBQuery);

                // If there is a genius for Topic B, recalculate the genius of the topic
                if ($topicB["genius"]) {

                    $topicBTippersIdsQuery = $this->_db->select()->from("tb_tip", "userid")->where("topicid=?", $topicBid)->group("userid");
                    $topicBTippersIds = $this->_db->fetchCol($topicBTippersIdsQuery);

                    // Loop though all the tippers in Topic B to see if anyone else deserves to be the genius
                    foreach ($topicBTippersIds as $useridToBeCalcualted) {
                        $this->calcGenius($topicBid, $useridToBeCalcualted, false);
                    }
                }

                //Recalculate the genius for Topic A after the tip has been shifted to it
                $this->calcGenius($topicA["id"], $tip["userid"]);
            }
        } else {
            return false;
        }

        return true;
    }

    function updateTopic($topicid, $topicName) {

        // Get the topic by the specified name
        $query = $this->_db->select()->from("tb_topic")->where("content=?", $topicName);
        $existingTopicByName = $this->_db->fetchRow($query);

        if ($existingTopicByName["id"] == $topicid || !$existingTopicByName) {

            //Move tip to topic A
            $topicidQ = $this->_db->quoteInto("?", $topicid);
            $this->_db->update("tb_topic", array("content" => $topicName), "id={$topicidQ}");
            $this->_db->update("tb_topic_search", array("content" => $topicName), "topicid={$topicidQ}");

            $tipsUnderTopicQuery = $this->_db->select()->from("tb_tip", "id")->where("topicid=?", $topicid);
            $tipids = $this->_db->fetchCol($tipsUnderTopicQuery);

            foreach ($tipids as $tipid) {
                $tipidQ = $this->_db->quoteInto("?", $tipid);
                $this->_db->update("tb_tip_search", array("topic" => $topicName), "tipid={$tipidQ}");
            }
        }
    }

    /**
     * Deletes a topic
     *
     * @param int $topicid if of the topic
     * @return void
     */
    function deleteTopic($topicid) {

        $topicid = $this->_db->quoteInto("?", $topicid);

        if ($this->_db->delete($this->_name, "id={$topicid}")) {

            $this->_db->delete("tb_topic_search", "topicid={$topicid}");
        }
    }

}

