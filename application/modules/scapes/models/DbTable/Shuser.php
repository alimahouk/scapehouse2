<?php

/**
 * Scapehouse user table management class.
 *
 * @copyright  2015 Scapehouse
 */

class Scapes_Model_DbTable_Shuser extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_user"; // The table name.
    protected $_schema = "scapes"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Creates a new user, & saves the access token.
     *
     * @param string $firstName The user's fist name.
     * @param string $lastName The user's last name.
     * @param string $timezone The user's timezone.
     * @param string $accessToken The Scapehouse access token.
     * @param string $osName The name of the OS running on the device of the user.
     * @param string $osVersion The OS version running on the device of the user.
     * @param string $deviceName The name given to the device by the user.
     * @param string $deviceType The device the user used to register.
     * 
     * @return The newly-created user's ID.
     */
    function createUser($firstName, $lastName, $locale, $timezone, $accessToken, $osName, $osVersion, $deviceName, $deviceType)
    {
        // Main table entry.
        $this->_db->insert($this->_name, array(
            "name_first" => $firstName,
            "name_last" => $lastName,
            "locale" => $locale,
            "timezone" => $timezone,
            "join_date" => gmdate("Y-m-d H:i:s", time()))
        );

        $freshUserID = $this->_db->lastInsertId($this->_name);

        if ($freshUserID)
        {         
            // Store access token.
            $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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
        $phoneNumberTable = new Scapes_Model_DbTable_Shphonenumber();
        $userID = $phoneNumberTable->getUserIDForNumber($countryCallingCode, $prefix, $phoneNumber);
        
        if ( $userID )
        {
            $query = $this->_db->query("
                    SELECT *
                    FROM sh_user
                    WHERE user_id = {$userID}");
        
            $user = $query->fetch();

            if ( $user["passcode_protect"] ) // Account is passcode-protected. Attach the passcodes for matching on the client side.
            {
                $passcodeTable = new Scapes_Model_DbTable_Shpasscode();
                $passcodes = $passcodeTable->getPasscodesByUserID($userID);

                $user["passcodes"] = $passcodes;
            }

            // Attach the DP hash.
            $DPHash = $this->getPicture($user["user_id"]);
            
            // Get the user's presence.
            $presenceTable = new Scapes_Model_DbTable_Shuseronlinestatus();
            $presence = $presenceTable->getStatus($userID);

            // Get the user's latest status update.
            $threadTable = new Scapes_Model_DbTable_Shthread();
            $latestStatus = $threadTable->getLatestGenericStatusUpdate($userID);

            $result = array_merge($user, $latestStatus);
            $result["dp_hash"] = $DPHash;
            $result["presence"] = $presence["status"];
            $result["presence_target"] = $presence["target_id"];
            $result["presence_audience"] = $presence["audience"];
            $result["presence_timestamp"] = $presence["timestamp"];

            unset($result["password"]); // Weed out the password. No need to return this.

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
            if ( $user["passcode_protect"] ) // Account is passcode-protected. Attach the passcodes for matching on the client side.
            {
                $passcodeTable = new Scapes_Model_DbTable_Shpasscode();
                $passcodes = $passcodeTable->getPasscodesByUserID($userID);
    
                $user["passcodes"] = $passcodes;
            }
            
            // Attach the DP hash.
            $DPHash = $this->getPicture($user["user_id"]);
            
            // Get the user's presence.
            $presenceTable = new Scapes_Model_DbTable_Shuseronlinestatus();
            $presence = $presenceTable->getStatus($userID);
            
            // Get the user's latest status update.
            $threadTable = new Scapes_Model_DbTable_Shthread();
            $latestStatus = $threadTable->getLatestGenericStatusUpdate($userID);
            
            $result = array_merge($user, $latestStatus);
            $result["dp_hash"] = $DPHash;
            $result["presence"] = $presence["status"];
            $result["presence_target"] = $presence["target_id"];
            $result["presence_audience"] = $presence["audience"];
            $result["presence_timestamp"] = $presence["timestamp"];
            
            unset($result["password"]); // Weed out the password. No need to return this.

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

            if ( $user["passcode_protect"] ) // Account is passcode-protected. Attach the passcodes for matching on the client side.
            {
                $passcodeTable = new Scapes_Model_DbTable_Shpasscode();
                $passcodes = $passcodeTable->getPasscodesByUserID($userID);
    
                $user["passcodes"] = $passcodes;
            }
            
            // Attach the DP hash.
            $DPHash = $this->getPicture($user["user_id"]);
            
            // Get the user's presence.
            $presenceTable = new Scapes_Model_DbTable_Shuseronlinestatus();
            $presence = $presenceTable->getStatus($userID);
            
            // Get the user's latest status update.
            $threadTable = new Scapes_Model_DbTable_Shthread();
            $latestStatus = $threadTable->getLatestGenericStatusUpdate($userID);
            
            $result = array_merge($user, $latestStatus);
            $result["dp_hash"] = $DPHash;
            $result["presence"] = $presence["status"];
            $result["presence_target"] = $presence["target_id"];
            $result["presence_audience"] = $presence["audience"];
            $result["presence_timestamp"] = $presence["timestamp"];
            
            unset($result["password"]); // Weed out the password. No need to return this.

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
            
            if ( $user["passcode_protect"] ) // Account is passcode-protected. Attach the passcodes for matching on the client side.
            {
                $passcodeTable = new Scapes_Model_DbTable_Shpasscode();
                $passcodes = $passcodeTable->getPasscodesByUserID($userID);
    
                $user["passcodes"] = $passcodes;
            }
            
            // Attach the DP hash.
            $DPHash = $this->getPicture($user["user_id"]);
            
            // Get the user's presence.
            $presenceTable = new Scapes_Model_DbTable_Shuseronlinestatus();
            $presence = $presenceTable->getStatus($userID);
            
            // Get the user's latest status update.
            $threadTable = new Scapes_Model_DbTable_Shthread();
            $latestStatus = $threadTable->getLatestGenericStatusUpdate($userID);
            
            $result = array_merge($user, $latestStatus);
            $result["dp_hash"] = $DPHash;
            $result["presence"] = $presence["status"];
            $result["presence_target"] = $presence["target_id"];
            $result["presence_audience"] = $presence["audience"];
            $result["presence_timestamp"] = $presence["timestamp"];
            
            unset($result["password"]); // Weed out the password. No need to return this.
            
            return $result;
        }
    }

    /**
     * Returns true if a user has a password, false otherwise.
     *
     * @param string $userID The ID of the user.
     * @param string $ignore ID to ignore.
     * @return The user array.
     */
    function userHasPassword($userID, $ignore = 0)
    {
        $query = $this->_db->select()->from($this->_name)->where("user_id = ?", $userID)->where("user_id != ?", $ignore);

        $result = $this->_db->fetchRow($query);

        if ( $result["password"] )
        {
            return true;
        }
        else
        {
            return false;
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
     * Updates the count of the total messages received by this user.
     *
     * @param int $userID The ID of the user.
     * 
     * @return void
     */
    function updateMessagesReceivedCountForUser($userID)
    {
        $userIDQ = $this->_db->quoteInto("?", $userID);

        $this->_db->update($this->_name, array("total_messages_received" => new Zend_Db_Expr("total_messages_received + 1")), "user_id = {$userIDQ}");
    }

    /**
     * Updates the count of the total messages sent by this user.
     *
     * @param int $userID The ID of the user.
     * 
     * @return void
     */
    function updateMessagesSentCountForUser($userID)
    {
        $userIDQ = $this->_db->quoteInto("?", $userID);

        $this->_db->update($this->_name, array("total_messages_sent" => new Zend_Db_Expr("total_messages_sent + 1")), "user_id = {$userIDQ}");
    }

    /**
     * Updates a user's profile info.
     *
     * @param int $userID ID of the user.
     * @return void
     */
    function updateProfile($userID, $firstName, $lastName, $username, $gender, $email, $birthday, $location_country, $location_state, $location_city, $bio, $website, $facebookID, $twitterID, $instagramID)
    {
        $userID = $this->_db->quoteInto("?", $userID);

        $this->_db->update($this->_name, array(
            "name_first" => $firstName,
            "name_last" => $lastName,
            "user_handle" => $username,
            "email_address" => $email,
            "gender" => $gender,
            "birthday" => $birthday,
            "location_country" => $location_country,
            "location_state" => $location_state,
            "location_city" => $location_city,
            "website" => $website,
            "bio" => $bio,
            "facebook_id" => $facebookID,
            "twitter_id" => $twitterID,
            "instagram_id" => $instagramID), "user_id = {$userID}");
    }

    /**
     * Updates a user's password.
     *
     * @param int $userID ID of the user.
     * @param string $password A new, salted SHA-1 password hash.
     * @return void
     */
    function updatePassword($userID, $password)
    {
        $userIDQ = $this->_db->quoteInto("user_id = ?", $userID);
        $this->_db->update($this->_name, array("password" => $password), $userIDQ);
    }

    /**
     * Saves a picture's hash for a user.
     *
     * @param int $userID ID of the user.
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
     * @param int $userID ID of the user.
     * @param string $hash Hash of the picture.
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
     * Locks a user's account so it would require a passcode to log in.
     *
     * @param int $userID ID of the user.
     * @return void
     */
    function lockAccount($userID)
    {
        $userIDQ = $this->_db->quoteInto("user_id = ?", $userID);
        $this->_db->update($this->_name, array("passcode_protect" => 1), $userIDQ);
    }

    /**
     * Unlocks a user's account so it no longer requires a passcode to log in.
     *
     * @param int $userID ID of the user.
     * @return void
     */
    function unlockAccount($userID)
    {
        $userIDQ = $this->_db->quoteInto("user_id = ?", $userID);
        $this->_db->update($this->_name, array("passcode_protect" => 0), $userIDQ);
    }

    /**
     * Updates a user's talking mask value.
     *
     * @param int $userID ID of the user.
     * @param int $mask The mask value.
     * @return void
     */
    function setTalkingMask($userID, $mask)
    {
        $userIDQ = $this->_db->quoteInto("user_id = ?", $userID);
        $this->_db->update($this->_name, array("mask_talking" => $mask), $userIDQ);
    }

    /**
     * Fetches a user's talking mask value.
     *
     * @param int $userID ID of the user.
     * @return void
     */
    function getTalkingMask($userID)
    {
        return $this->_db->fetchOne("
                SELECT mask_talking
                FROM sh_user
                WHERE user_id = {$userID}");
    }

    /**
     * Updates a user's presence mask value.
     *
     * @param int $userID ID of the user.
     * @param int $mask The mask value.
     * @return void
     */
    function setPresenceMask($userID, $mask)
    {
        $userIDQ = $this->_db->quoteInto("user_id = ?", $userID);
        $this->_db->update($this->_name, array("mask_presence" => $mask), $userIDQ);
        $this->_db->update("sh_user_online_status", array("masked" => $mask), $userIDQ);
    }

    /**
     * Fetches a user's talking mask value.
     *
     * @param int $userID ID of the user.
     * @return void
     */
    function getPresenceMask($userID)
    {
        return $this->_db->fetchOne("
                SELECT mask_presence
                FROM sh_user
                WHERE user_id = {$userID}");
    }

    /**
     * Saves a reference to the user's contact for notifying them in case they ever join the service.
     *
     * @param string $countryCallingCode The international country code of the phone number.
     * @param string $prefix The phone number's prefix.
     * @param string $phoneNumber The phone number.
     * @param int $adderID The ID of the user adding the person.
     * 
     * @return void
     */
    function logPotentialUser($countryCallingCode, $prefix, $phoneNumber, $adderID)
    {
        $query = $this->_db->query("
                    SELECT *
                    FROM sh_scapes_potential_user
                    WHERE country_code = {$countryCallingCode} AND prefix = {$prefix} AND phone_number = {$phoneNumber} AND adder_user_id = {$adderID}");
        
        $result = $query->fetch();
        
        if ( !$result["user_id"] )
        {
            $this->_db->insert("sh_scapes_potential_user", array(
                "country_code" => $countryCallingCode,
                "prefix" => $prefix,
                "phone_number" => $phoneNumber,
                "adder_user_id" => $adderID,
                "timestamp" => gmdate("Y-m-d H:i:s", time()))
            );
        }
    }

    /**
     * Erases a potential user.
     *
     * @param string $countryCallingCode The international country code of the phone number.
     * @param string $prefix The phone number's prefix.
     * @param string $phoneNumber The phone number.
     * 
     * @return The adder's details.
     */
    function erasePotentialUser($countryCallingCode, $prefix, $phoneNumber)
    {
        $query = $this->_db->query("
            SELECT *
            FROM sh_scapes_potential_user
            WHERE country_code = {$countryCallingCode} AND prefix = {$prefix} AND phone_number = {$phoneNumber}");
        
        $results = $query->fetchAll();

        $this->_db->delete("sh_scapes_potential_user", array(
            "country_code = ?" => $countryCallingCode,
            "prefix = ?" => $prefix,
            "phone_number = ?" => $phoneNumber
        ));

        return $results;
    }

    /**
     * Checks the cheat table for a potential user's phone number. The phone number has to be
     * manually entered beforehand.
     *
     * @param string $countryCallingCode The international country code of the phone number.
     * @param string $prefix The phone number's prefix.
     * @param string $phoneNumber The phone number.
     * 
     * @return void.
     */
    function checkCheatTableForPhoneNumber($countryCallingCode, $prefix, $phoneNumber)
    {
        $phoneNumber = "" . $countryCallingCode . $prefix . $phoneNumber;

        $query = $this->_db->query("
            SELECT *
            FROM sh_scapes_phone_cheat
            WHERE phone_number = {$phoneNumber}");
        
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