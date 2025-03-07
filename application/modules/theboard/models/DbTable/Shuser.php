<?php

/**
 * Scapehouse user table management class.
 *
 * @copyright  2015 Scapehouse
 */

class Theboard_Model_DbTable_Shuser extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_user"; // The table name.
    protected $_schema = "theboard"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Creates a new user, & saves the access token.
     *
     * @param string $name The user's name.
     * @param string $email The user's email.
     * @param string $timezone The user's timezone.
     * @param string $accessToken The Scapehouse access token.
     * @param string $osName The name of the OS running on the device of the user.
     * @param string $osVersion The OS version running on the device of the user.
     * @param string $deviceName The name given to the device by the user.
     * @param string $deviceType The device the user used to register.
     * 
     * @return The newly-created user's ID.
     */
    function createUser($name, $email, $locale, $timezone, $accessToken, $osName, $osVersion, $deviceName, $deviceType)
    {
        // Main table entry.
        $this->_db->insert($this->_name, array(
            "name" => $name,
            "email_address" => $email,
            "locale" => $locale,
            "timezone" => $timezone,
            "join_date" => gmdate("Y-m-d H:i:s", time()))
        );

        $freshUserID = $this->_db->lastInsertId($this->_name);

        if ($freshUserID)
        {         
            // Store access token.
            $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
            $accessTokenTable->logToken($freshUserID, $accessToken, $osName, $osVersion, $deviceName, $deviceType);

            return $freshUserID;
        }
    }

    /**
     * Gets a user by phone number.
     *
     * @param string $phone The phone number.
     * @param string $ignore ID to ignore.
     * @return The user array.
     */
    function getUserByPhoneNumber($countryCallingCode, $prefix, $phoneNumber, $ignore = 0)
    {
        $phoneNumberTable = new Theboard_Model_DbTable_Shphonenumber();
        $userID = $phoneNumberTable->getUserIDForNumber($countryCallingCode, $prefix, $phoneNumber);
        
        if ( $userID )
        {
            $query = $this->_db->query("
                    SELECT *
                    FROM sh_user
                    WHERE user_id = {$userID}");
        
            $user = $query->fetch();

            // Attach the DP hash.
            $DPHash = $this->getPicture($user["user_id"]);

            // Get the user's latest status update.
            $threadTable = new Theboard_Model_DbTable_Shthread();
            $latestStatus = $threadTable->getLatestStatusUpdate($userID);

            $result = array_merge($user, $latestStatus);
            $result["dp_hash"] = $DPHash;

            if ( $result["user_id"] )
            {
                return $result;
            }
        }
    }

    /**
     * Gets a user by an ID.
     *
     * @param int $userID The ID.
     * @param string $ignore ID to ignore.
     * @return The user array.
     */
    function getUserByUserID($userID, $ignore = 0)
    {
        $query = $this->_db->select()->from($this->_name)->where("user_id = ?", $userID)->where("user_id != ?", $ignore);
        
        $user = $this->_db->fetchRow($query);

        if ( $user["user_id"] )
        {
            // Attach the DP hash.
            $DPHash = $this->getPicture($user["user_id"]);
            
            // Get the user's latest status update.
            $threadTable = new Theboard_Model_DbTable_Shthread();
            $latestStatus = $threadTable->getLatestStatusUpdate($userID);
            
            $result = array_merge($user, $latestStatus);
            $result["dp_hash"] = $DPHash;

            return $result;
        }
    }

    /**
     * Gets a user by username.
     *
     * @param string $username The username.
     * @param string $ignore ID to ignore.
     * @return The user array.
     */
    function getUserByUsername($username, $ignore = 0)
    {
        $query = $this->_db->select()->from($this->_name)->where("user_handle = ?", $username)->where("user_id != ?", $ignore);

        $user = $this->_db->fetchRow($query);
        
        if ( $user["user_id"] )
        {
            $userID = $user["user_id"];
            
            // Attach the DP hash.
            $DPHash = $this->getPicture($user["user_id"]);
            
            // Get the user's latest status update.
            $threadTable = new Theboard_Model_DbTable_Shthread();
            $latestStatus = $threadTable->getLatestStatusUpdate($userID);
            
            $result = array_merge($user, $latestStatus);
            $result["dp_hash"] = $DPHash;

            return $result;
        }
    }

    /**
     * Gets a user by email.
     *
     * @param string $email The email of the user.
     * @param string $ignore ID to ignore.
     * @return The user array.
     */
    function getUserByEmail($email, $ignore = 0)
    {
        $query = $this->_db->select()->from($this->_name)->where("email_address = ?", $email)->where("user_id != ?", $ignore);

        $user = $this->_db->fetchRow($query);

        if ( $user["user_id"] )
        {
            $userID = $user["user_id"];
            
            // Attach the DP hash.
            $DPHash = $this->getPicture($userID);
            
            // Get the user's latest status update.
            $threadTable = new Theboard_Model_DbTable_Shthread();
            $latestStatus = $threadTable->getLatestStatusUpdate($userID);
            
            $result = array_merge($user, $latestStatus);
            $result["dp_hash"] = $DPHash;
            
            return $result;
        }
    }

    /**
     * Returns the total number of registered users.
     * @return The user count.
     */
    function getUserCount()
    {
        $query = $this->_db->select()->from($this->_name, "COUNT({$this->_name}.user_id)");

        return $this->_db->fetchOne($query);
    }

    /**
     * Updates a user's locale.
     *
     * @param int $userID ID of the user.
     * @return void
     */
    function updateLocale($userID, $locale)
    {
        $userID = $this->_db->quoteInto("?", $userID);
        $this->_db->update($this->_name, array("locale" => $locale), "user_id = {$userID}");
    }

    /**
     * Updates a user's timezone.
     *
     * @param int $userID ID of the user.
     * @return void
     */
    function updateTimezone($userID, $timezone)
    {
        $userID = $this->_db->quoteInto("?", $userID);
        $this->_db->update($this->_name, array("timezone" => $timezone), "user_id = {$userID}");
    }

    /**
     * Updates a user's profile info.
     *
     * @param int $userID ID of the user.
     * @return void
     */
    function updateProfile($userID, $name, $username, $gender, $birthday, $location_country, $location_state, $location_city, $bio, $website)
    {
        $userIDQ = $this->_db->quoteInto("?", $userID);

        $this->_db->update($this->_name, array(
            "name" => $name,
            "user_handle" => $username,
            "gender" => $gender,
            "birthday" => $birthday,
            "location_country" => $location_country,
            "location_state" => $location_state,
            "location_city" => $location_city,
            "website" => $website,
            "bio" => $bio), "user_id = {$userIDQ}");
    }

    /**
     * Saves a picture's hash for a user.
     *
     * @param int $userID The ID of the user.
     * @param string $hash Hash of the picture.
     * @return void
     */
    function savePicture($userID, $hash)
    {
        // Remove any previous entry.
        $this->deletePicture($userID);

        $this->_db->insert("sh_user_dp", array(
                    "user_id" => $userID,
                    "hash" => $hash,
                    "timestamp" => gmdate("Y-m-d H:i:s", time()))
        );
    }

    /**
     * Returns the hash for the user's current DP.
     *
     * @param int $userID The ID of the user.
     *
     * @return void
     */
    function getCurrentDPHash($userID)
    {
        return $this->_db->fetchOne("
                SELECT hash
                FROM sh_user_dp
                WHERE user_id = {$userID}");
    }

    /**
     * Deletes a picture's hash for a user.
     *
     * @param int $userID ID of the user.
     * @return void
     */
    function deletePicture($userID)
    {
        $userIDQ = $this->_db->quoteInto("user_id = ?", $userID);
        $this->_db->delete("sh_user_dp", $userIDQ);
    }

    /**
     * Gets a picture's hash for a user.
     *
     * @param int $userID ID of the user.
     * @return The picture hash.
     */
    function getPicture($userID)
    {
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_user_dp
                    WHERE user_id = {$userID}");
        
        $result = $query->fetch();
        
        if ( $result["hash"] )
        {
            return $result["hash"];
        }
    }

    /**
     * Checks the cheat table for a potential user's email. The email has to be
     * manually entered beforehand.
     *
     * @param string $email The email of the potential user.
     * 
     * @return array The cheat entry.
     */
    function checkCheatTableForEmail($email)
    {
        $emailQ = $this->_db->quoteInto("?", $email);

        $query = $this->_db->query("
            SELECT *
            FROM sh_cheat
            WHERE email_address = {$emailQ}");
        
        $result = $query->fetch();

        return $result;
    }

    /**
     * ADMIN FUNCTIONS
     * Returns data about all registered users.
     * 
     * @return array Registered users' data.
     */
    function admin_getUsers()
    {
        $query = $this->_db->query("
            SELECT sh_user.user_id, sh_user.name_first, sh_user.name_last, sh_user.join_date, sh_user_dp.hash, sh_country.name, sh_scapes_device_type.device_name
            FROM sh_user
            LEFT JOIN sh_user_dp ON sh_user_dp.user_id = sh_user.user_id
            INNER JOIN sh_scapes_access_token ON sh_scapes_access_token.user_id = sh_user.user_id
            LEFT JOIN sh_scapes_device_type ON sh_scapes_device_type.device_type_id = sh_scapes_access_token.device_type_id
            INNER JOIN sh_scapes_phone_number ON sh_scapes_phone_number.user_id = sh_user.user_id
            INNER JOIN sh_country ON sh_country.country_id = sh_scapes_phone_number.country_calling_code_id
            GROUP BY sh_user.user_id
            ORDER BY join_date ASC");
        
        $results = $query->fetchAll();

        return $results;
    }

    function admin_getDeviceRankings()
    {
        $query = $this->_db->query("
            SELECT sh_scapes_device_type.device_name, COUNT(*) as count
            FROM sh_user
            INNER JOIN sh_scapes_access_token ON sh_scapes_access_token.user_id = sh_user.user_id
            INNER JOIN sh_scapes_device_type ON sh_scapes_device_type.device_type_id = sh_scapes_access_token.device_type_id
            GROUP BY sh_scapes_device_type.device_type_id
            ORDER BY count DESC");
        
        $results = $query->fetchAll();

        return $results;
    }

    function admin_getCountryRankings()
    {
        $query = $this->_db->query("
            SELECT sh_country.name, COUNT(*) as count
            FROM sh_user
            INNER JOIN sh_scapes_phone_number ON sh_scapes_phone_number.user_id = sh_user.user_id
            INNER JOIN sh_country ON sh_country.country_id = sh_scapes_phone_number.country_calling_code_id
            GROUP BY sh_country.country_id
            ORDER BY count DESC");
        
        $results = $query->fetchAll();

        return $results;
    }

    function admin_getOnlineUsers()
    {
        $query = $this->_db->query("
            SELECT sh_user.user_id, sh_user.name_first, sh_user.name_last, sh_user_dp.hash, sh_country.name, sh_user_online_status.status, sh_user_online_status.timestamp
            FROM sh_user
            LEFT JOIN sh_user_dp ON sh_user_dp.user_id = sh_user.user_id
            INNER JOIN sh_scapes_phone_number ON sh_scapes_phone_number.user_id = sh_user.user_id
            INNER JOIN sh_country ON sh_country.country_id = sh_scapes_phone_number.country_calling_code_id
            INNER JOIN sh_user_online_status ON sh_user_online_status.user_id = sh_user.user_id AND sh_user_online_status.status NOT IN (1, 14)");
        
        $results = $query->fetchAll();

        return $results;
    }
}