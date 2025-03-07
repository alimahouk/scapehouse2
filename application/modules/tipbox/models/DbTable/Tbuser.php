<?php

/**
 * Tipbox User table management class
 *
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_DbTable_Tbuser extends Zend_Db_Table_Abstract {

    protected $_name = "tb_user";
    protected $_schema = "tipbox"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Creates are new user, updates user search index, saves FB token
     *
     * @param string $username username
     * @param string $fullname fullname of user
     * @param string $email Email of user
     * @param string $password Password of user
     * 
     * @return The user array
     */
    function createUser($username, $fullname, $email, $password, $location, $website, $timezone, $fbtoken, $fbid, $fbTokenExp, $accessToken, $signupType, $twtToken, $twtid, $twtSecret, $bio, $twtUsername) {

        // Main table entry
        $this->_db->insert($this->_name, array(
            "fullname" => $fullname,
            "email" => $email,
            "username" => $username,
            "password" => $password,
            "location" => $location,
            "website" => $website,
            "bio" => $bio,
            "timezone" => $timezone,
            "time" => gmdate("Y-m-d H:i:s", time()))
        );


        $userid = $this->_db->lastInsertId($this->_name);

        if ($userid) {

            // Search table entry
            $this->_db->insert("tb_user_search", array(
                "fullname" => $fullname,
                "username" => $username,
                "userid" => $userid)
            );

                     
            if ($signupType == "fb") {

                //Log FB Token
                $tb_facebook = new Tipbox_Model_DbTable_Tbfacebook();
                $tb_facebook->logToken($userid, $fbtoken, $fbid, $fbTokenExp);
                
            } elseif ($signupType == "twt") {

                //Log twitter Token
                $tb_twitter = new Tipbox_Model_DbTable_Tbtwitter();
                $tb_twitter->logToken($userid, $twtUsername, $twtToken, $twtid, $twtSecret);
            }

            //Login Access token
            //Store access token

            $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
            $tbaccesstokenTable->logToken($userid, $accessToken);
        }

        return $userid;
    }

    /**
     * Gets a user by username
     *
     * @param string $username username
     * @param string $ignore id to ignore
     * @return The user array
     */
    function getUserByUsername($username, $ignore = 0) {

        $query = $this->_db->select()->from($this->_name)->where("username=?", $username)->where("tb_user.id != ?", $ignore)
                ->joinLeft("tb_like", "tb_like.tipownerid = tb_user.id", array("COUNT(DISTINCT tb_like.userid) AS helpCount"));

        $result = $this->_db->fetchRow($query);

        if ($result["id"]) {
            return $result;
        }
    }

    function getUserCount() {

        $query = $this->_db->select()->from($this->_name, "COUNT({$this->_name}.id)");
        return $this->_db->fetchOne($query);
    }

    /**
     * Gets a users profile and stats
     *
     * @param int $userrname of the user
     * @return The data array
     */
    function getProfile($username) {

        $username = $this->_db->quoteInto("?", $username);
        $query = $this->_db->query("
            SELECT tb_user.*,
            (SELECT count(id) FROM tb_tip WHERE tb_tip.userid = tb_user.id) AS tipCount,
            (SELECT count(id) FROM tb_follow WHERE tb_follow.userid = tb_user.id) AS followCount,
            (SELECT count(id) FROM tb_topic WHERE tb_topic.genius = tb_user.id) AS geniusCount,   
            (SELECT count(id) FROM tb_like WHERE tb_like.userid = tb_user.id) AS likeCount,
            (SELECT count(DISTINCT tb_like.userid) FROM tb_like WHERE tb_like.tipownerid = tb_user.id) AS helpCount,
            (SELECT count(id) FROM tb_facebook WHERE tb_facebook.userid = tb_user.id AND tb_facebook.connected = 1) AS fbConnected,
            (SELECT count(id) FROM tb_twitter WHERE tb_twitter.userid = tb_user.id AND tb_twitter.connected = 1) AS twtConnected
            FROM tb_user WHERE tb_user.username = {$username}");

        return $query->fetch();
    }

    function getAll() {

        $query = $this->_db->query("
            SELECT tb_user.*,
            (SELECT count(id) FROM tb_tip WHERE tb_tip.userid = tb_user.id) AS tipCount,
            (SELECT count(id) FROM tb_follow WHERE tb_follow.userid = tb_user.id) AS followCount,
            (SELECT count(id) FROM tb_topic WHERE tb_topic.genius = tb_user.id) AS geniusCount,   
            (SELECT count(id) FROM tb_like WHERE tb_like.userid = tb_user.id) AS likeCount,
            (SELECT count(DISTINCT tb_like.userid) FROM tb_like WHERE tb_like.tipownerid = tb_user.id) AS helpCount,
            (SELECT count(id) FROM tb_facebook WHERE tb_facebook.userid = tb_user.id AND tb_facebook.connected = 1) AS fbConnected
            FROM tb_user ORDER BY tb_user.time ASC");

        return $query->fetchAll();
    }

    /**
     * Updates a users profile info.
     *
     * @param int $userid Id of the user
     * @return void
     */
    function updateProfile($userid, $bio, $location, $username, $website, $fullname, $email) {

        $userid = $this->_db->quoteInto("?", $userid);
        $this->_db->update($this->_name, array(
            "bio" => $bio,
            "location" => $location,
            "username" => $username,
            "website" => $website,
            "fullname" => $fullname,
            "email" => $email), "id={$userid}");

        // Update search table
        $this->_db->update("tb_user_search", array("fullname" => $fullname, "username" => $username), "userid={$userid}");
    }

    /**
     * Updates a users password.
     *
     * @param int $userid Id of the user
     * @param string $password new salted SHA1 password hash
     * @return void
     */
    function updatePassword($userid, $password) {

        $userid = $this->_db->quoteInto("?", $userid);
        $this->_db->update($this->_name, array("password" => $password), "id={$userid}");
    }

    /**
     * Gets a user by email
     *
     * @param string $email email
     * @param string $ignore id to ignore
     * @return The user array
     */
    function getUserByEmail($email, $ignore = 0) {

        $query = $this->_db->select()->from($this->_name)->where("email=?", $email)->where("tb_user.id != ?", $ignore)
                ->joinLeft("tb_like", "tb_like.tipownerid = tb_user.id", array("COUNT(DISTINCT tb_like.userid) AS helpCount"));

        $result = $this->_db->fetchRow($query);


        if ($result["id"]) {
            return $result;
        }
    }

    /**
     * Gets a user by id
     *
     * @param string $id id of the user
     * @return The user array
     */
    function getUserById($id) {

        $query = $this->_db->select()->from($this->_name)->where("tb_user.id = ?", $id);

        $result = $this->_db->fetchRow($query);

        return $result;
    }

    /**
     * Saves a pictures hash for a user
     *
     * @param int $userid Id of the user
     * @param string $hash Hash of the picture
     * @return void
     */
    function savePicture($userid, $hash) {

        $userid = $this->_db->quoteInto("?", $userid);
        $this->_db->update($this->_name, array("pichash" => $hash), "id={$userid}");
    }

    /**
     * Searches and returns topics based on search terms
     *
     * @param string $term Query string for search
     * @return Topics array
     */
    function search($term, $batch = 0) {

        $offset = $batch * $GLOBALS["batchSize"];

        $query = $this->_db->query("
            SELECT 
            tb_user.id,
            tb_user.fullname,
            tb_user.username,
            tb_user.bio,
            tb_user.pichash,
            (SELECT count(DISTINCT tb_like.userid) FROM tb_like WHERE tb_like.tipownerid = tb_user.id) AS helpCount
            FROM tb_user_search, tb_user
            WHERE MATCH(`tb_user_search`.`fullname`,`tb_user_search`.`username`) AGAINST (? IN BOOLEAN MODE) AND tb_user_search.userid = tb_user.id
            LIMIT {$offset}, {$GLOBALS["batchSize"]}", $term . "*");

        return $query->fetchAll();
    }

    /**
     * Deletes a pictures hash for a user
     *
     * @param int $userid Id of the user
     * @return void
     */
    function deletePicture($userid) {

        $userid = $this->_db->quoteInto("?", $userid);
        $this->_db->update($this->_name, array("pichash" => NULL), "id={$userid}");
    }

}

