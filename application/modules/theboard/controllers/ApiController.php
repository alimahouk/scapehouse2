<?php

/**
 * Scapehouse Nightboard API calls.
 *
 * @copyright  2014 Scapehouse
 */

class Theboard_ApiController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here. */
        
        $this->_helper->layout->disableLayout();         // No layout.
        $this->_helper->viewRenderer->setNoRender(true); // No view.

        $GLOBALS["init_token"] = "YOUR_INIT_TOKEN"; // For fresh logins & signups.
        $GLOBALS["batch_size"] = 15;
    }

	public function testrunAction()
    {
        /*$applePushProcessor = new Theboard_Model_ApplePush(
                        "Yo mate!",
                        8,
                        array("type" => "join", "user_id" => 20));

        $applePushProcessor->dispatchNotif();*/
    }

    /*
     * Use this function for admin functions.
     */
    public function fatladyAction()
    {
        $password = urldecode($_GET["password"]);

        if ( $password && $password == "YOUR_TEST_PASSWORD" )
        {
            $userTable = new Theboard_Model_DbTable_Shuser();
            $users = $userTable->admin_getUsers();
            $onlineUsers = $userTable->admin_getOnlineUsers();
            $devices = $userTable->admin_getDeviceRankings();
            $countries = $userTable->admin_getCountryRankings();

            echo "<!DOCTYPE html>";
            echo "<html lang='en-US'>";
            echo "<head>";
            echo "<title>Scapehouse User Report</title>";
            echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
            echo "<style>";
            echo "* {font-family:\"Myriad Set Pro\", \"Helvetica Neue\", Helvetica, Arial, Verdana, sans-serif;}";
            echo "h1,h2,h3,h4,h5,h6 {font-weight:200; letter-spacing:-1px; line-height:1.105;}";
            echo "tr:nth-child(even) {background-color:#f0f0f0;}";
            echo "td img {border:2px solid #fff; float:left; margin-right:10px; vertical-align:middle; -webkit-border-radius:30px; -moz-border-radius:30px; border-radius:30px;}";
            echo "</style>";
            echo "</head>";
            echo "<body style='color:#333; line-height: 1.7857;'>";
            echo "<div style='margin:20px auto;width:1280px;'>";

            // ============
            // All registered users.

            echo "<div style='float:left; margin-right:20px;'>";
            echo "<h2>all registered users.</h2>";
            echo "<table style='border:1px solid #ccc; border-collapse:collapse; width:1280px;'>";
            echo "<tr>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>ID</strong></td>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>NAME</strong></td>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>JOINED</strong></td>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>COUNTRY</strong></td>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>DEVICE</strong></td>";
            echo "</tr>";

            $totalUsers = 0;
            $usersWithDP = 0;

            foreach ( $users as $key => $user )
            {
                $DPPath = "https://fbcdn-profile-a.akamaihd.net/hprofile-ak-xfp1/v/t1.0-1/c29.0.100.100/p100x100/954801_10150002137498316_604636659114323291_n.jpg?oh=684988b49134612ef144aad49bb3fdaf&oe=5454651D&__gda__=1414772237_09c259ff5137fb9927a0e851de69d4e9";

                if ( $user["hash"] )
                {
                    $usersWithDP++;
                    $DPPath = "http://scapehouse.com/userphotos/" . $user["user_id"] . "/profile/f_" . $user["hash"] . ".jpg";
                }

                echo "<tr>";
                echo "<td style='border-right:1px solid #ccc; color:#777; padding:10px; text-align:center;'><em>" . $user["user_id"] . "</em></td>";
                echo "<td style='padding:10px;'><img src='" . $DPPath . "' width='50' height='50' />" . $user["name_first"] . " " . $user["name_last"] . "</td>";
                echo "<td style='padding:10px;'>" . Model_Lib_Func::relativeTime(strtotime($user["join_date"])) . "</td>";
                echo "<td style='padding:10px;'>" . $user["name"] . "</td>";
                echo "<td style='padding:10px;'>" . $user["device_name"] . "</td>";
                echo "</tr>";

                $totalUsers++;
            }

            $percentage = ($usersWithDP * 100) / $totalUsers;

            echo "<tr>";
            echo "<td style='border-top:1px solid #ccc; padding:10px;'>TOTAL:</td>";
            echo "<td style='border-top:1px solid #ccc; padding:10px;' colspan='4'><strong>" . number_format($totalUsers) . "</strong> users.</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='border-top:1px solid #ccc; padding:10px;' colspan='5'><strong>" . round($percentage, 2) . "%</strong> of them have profile pics.</td>";
            echo "</tr>";
            echo "</table>";
            echo "</div>";

            // ============
            // Device rankings.

            echo "<div style='float:left;'>";
            echo "<h2>users by device.</h2>";
            echo "<table style='border:1px solid #ccc; border-collapse:collapse; width:630px;'>";
            echo "<tr>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>RANK</strong></td>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>DEVICE</strong></td>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>COUNT</strong></td>";
            echo "</tr>";

            $i = 1;

            foreach ( $devices as $key => $device )
            {
                $percentage = ($device["count"] * 100) / $totalUsers;

                echo "<tr>";
                echo "<td style='border-right:1px solid #ccc; color:#777; padding:10px; text-align:center;'><em>" . $i . "</em></td>";
                echo "<td style='padding:10px;'>" . $device["device_name"] . "</td>";
                echo "<td style='padding:10px;'>" . number_format($device["count"]) . " (<strong>" . round($percentage, 2) . "%</strong>)" . "</td>";
                echo "</tr>";

                $i++;
            }

            echo "</table>";
            echo "</div>";

            // ============
            // Country rankings.

            echo "<div style='float:right;'>";
            echo "<h2>users by country.</h2>";
            echo "<table style='border:1px solid #ccc; border-collapse:collapse; width:630px;'>";
            echo "<tr>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>RANK</strong></td>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>COUNTRY</strong></td>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>COUNT</strong></td>";
            echo "</tr>";

            $i = 1;

            foreach ( $countries as $key => $country )
            {
                $percentage = ($country["count"] * 100) / $totalUsers;

                echo "<tr>";
                echo "<td style='border-right:1px solid #ccc; color:#777; padding:10px; text-align:center;'><em>" . $i . "</em></td>";
                echo "<td style='padding:10px;'>" . $country["name"] . "</td>";
                echo "<td style='padding:10px;'>" . number_format($country["count"]) . " (<strong>" . round($percentage, 2) . "%</strong>)" . "</td>";
                echo "</tr>";

                $i++;
            }

            echo "</table>";
            echo "</div>";

            // ============

            echo "</div>";
            echo "<p style='clear:both; color:#ccc; padding:20px 0; text-align:center;'>&copy;2014 Scapehouse. be original.</p>";
            echo "</body>";
            echo "<script type='text/javascript'>window.scrollTo(0, document.body.scrollHeight);</script>";
            echo "</html>";
        }
        else
        {
            $this->_helper->layout->setLayout('layout'); // Enable layout.

            // Uh uh uh! Didn't say the magic word! (^-^)
            $this->_response->clearBody();
            $this->_response->clearHeaders();
            $this->_response->setHttpResponseCode(404);
        }
    }

    /*
     * Creates a session for a user & returns an access token along with their info.
     */
    public function loginAction()
    {
        if ( $_POST )
        {
            $email = $_POST["email_address"];
            $locale = $_POST["locale"];
            $timezone = $_POST["timezone"];
            $osName = $_POST["os_name"];
            $osVersion = $_POST["os_version"];
            $deviceName = $_POST["device_name"];
            $deviceType = $_POST["device_type"];
            $deviceToken = $_POST["device_token"];
            $cheating = $_POST["cheat"];

            $userTable = new Theboard_Model_DbTable_Shuser();

            /*
             * SPECIAL: this login param checks a cheat table in the DB (for bypassing the email verification code).
             */
            if ( $cheating == 1 )
            {
                $existsInCheatTable = $userTable->checkCheatTableForEmail($email);

                if ( !$existsInCheatTable )
                {
                    // Login failed.
                    $output = json_encode(array("response" => "false", "errorMessage" => "error!", "errorCode" => 500));
                    $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);
                    
                    echo "while(1);" . $base64Encrypted;
    
                    return;
                }
            }

            $user = $userTable->getUserByEmail($email);
            
            if ( $user )
            {
                $blacklistTable = new Theboard_Model_DbTable_Shblacklist();

                if ( $blacklistTable->isBadUser($user["user_id"]) ) // User is blacklisted. Don't let them in.
                {
                    $output =  json_encode(array("response" => "false", "errorMessage" => "nope. you've been bad lately.", "errorCode" => 86));
                    
                    echo $output;
                }

                // Create an access token.                 
                $accessToken = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));

                // Store the access token.
                $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
                $tokenID = $accessTokenTable->logToken($user["user_id"], $accessToken, $osName, $osVersion, $deviceName, $deviceType);

                $userTable = new Theboard_Model_DbTable_Shuser();
                $userTable->updateLocale($user["user_id"], $locale);
                $userTable->updateTimezone($user["user_id"], $timezone);
                
                // Check device token validity.
                if ( trim($deviceToken) == "" || strlen($deviceToken) == 64 )
                {
                    if ( $tokenID && strlen(trim($deviceToken)) == 64 ) // If a Scapehouse token was sucessfully created...
                    {
                        // Connect to the Apple token table and log the token.
                        $appletokenTable = new Theboard_Model_DbTable_Shappletoken();
                        $appletokenTable->logToken($tokenID, $user["user_id"], $deviceToken);
                    }

                    $followTable = new Theboard_Model_DbTable_Shfollow();
                    $following = $followTable->getFollowingListWithUserData($user["user_id"]);

                    $boardTable = new Theboard_Model_DbTable_Shboard();
                    $boards = $boardTable->boardsForUser($user["user_id"]);

                    echo json_encode(array("response" => array("SHToken" => $accessToken, "SHToken_id" => $tokenID, "user_data" => $user, "following" => $following, "boards" => $boards), "errorMessage" => "", "errorCode" => 0));
                }
                else
                {
                    // Login failed.
                    echo json_encode(array("response" => "false", "errorMessage" => "device token error!", "errorCode" => 500));
                }
            }
            else
            {
                // User not found!
                echo json_encode(array("response" => "false", "errorMessage" => "user not found!", "errorCode" => 404));
            }
        }
    }

    /*
     * Discards any dead tokens left over on the device.
     */
    public function purgestaletokenAction()
    {
        $staleToken = $_POST["stale_token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();

        $tokenData = $accessTokenTable->getTokenByToken($staleToken);
        $accessTokenTable->deleteTokenByToken($staleToken);
        
        echo json_encode(array("response" => "Purged.", "errorMessage" => "", "errorCode" => 0));
    }

    /*
     * Creates an account for a new user.
     */
    public function signupAction()
    {
        /*
        // Uncomment this block to temporarily halt signups when needed.
        $output = json_encode(array("response" => "false", "errorMessage" => "signups are not allowed at the moment.", "errorCode" => 500));
        $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);

        echo "while(1);" . $base64Encrypted;
        return;
        */

        $name = $_POST['name'];
        $email = $_POST['email_address'];
        $locale = $_POST['locale'];
        $timezone = $_POST['timezone'];
        $osName = $_POST['os_name'];
        $osVersion = $_POST['os_version'];
        $deviceName = $_POST['device_name'];
        $deviceType = $_POST['device_type'];
        $deviceToken = $_POST['device_token'];
        $imageFile = $_FILES["image_file"];

        $signupProcessor = new Theboard_Model_Signup();

        $signupProcessor->name = trim($name);
        $signupProcessor->email = trim($email);
        $signupProcessor->locale = $locale;
        $signupProcessor->timezone = $timezone;
        $signupProcessor->accessToken = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));
        $signupProcessor->osName = $osName;
        $signupProcessor->osVersion = $osVersion;
        $signupProcessor->deviceName = $deviceName;
        $signupProcessor->deviceType = $deviceType;
        
        $picHash;
        $newUserID;

        $userTable = new Theboard_Model_DbTable_Shuser();
        $possiblyExistingUser = $userTable->getUserByEmail($email);
        
        if ( $possiblyExistingUser )
        {
            $blacklistTable = new Theboard_Model_DbTable_Shblacklist();

            if ( $blacklistTable->isBadUser($possiblyExistingUser["user_id"]) ) // User is blacklisted. Don't let them in.
            {
                $output =  json_encode(array("response" => "false", "errorMessage" => "nope. you've been bad lately.", "errorCode" => 86));
                
                echo $output;
            }

            // Create an access token.                 
            $accessToken = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));

            // Store the access token.
            $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
            $tokenID = $accessTokenTable->logToken($possiblyExistingUser["user_id"], $accessToken, $osName, $osVersion, $deviceName, $deviceType);
            
            $userTable = new Theboard_Model_DbTable_Shuser();
            $userTable->updateLocale($possiblyExistingUser["user_id"], $locale);
            $userTable->updateTimezone($possiblyExistingUser["user_id"], $timezone);
            
            // Check device token validity.
            if ( trim($deviceToken) == "" || strlen($deviceToken) == 64 )
            {
                if ( $tokenID && strlen(trim($deviceToken)) == 64 ) // If a Scapehouse token was sucessfully created...
                {
                    // Connect to the Apple token table and log the token.
                    $appletokenTable = new Theboard_Model_DbTable_Shappletoken();
                    $appletokenTable->logToken($tokenID, $possiblyExistingUser["user_id"], $deviceToken);
                }

                echo json_encode(array("response" => array("SHToken" => $accessToken, "SHToken_id" => $tokenID, "DP_hash" => $possiblyExistingUser["dp_hash"], "last_status_id" => $possiblyExistingUser["last_status_id"], "join_date" => $possiblyExistingUser["join_date"]), "errorMessage" => "", "errorCode" => 0));
            }
            else
            {
                // Login failed.
                echo json_encode(array("response" => "false", "errorMessage" => "device token error!", "errorCode" => 500));
            }
        }
        elseif ( $newUserID = $signupProcessor->signup() ) // If an account was sucessfully created...
        {
            $threadTable = new Theboard_Model_DbTable_Shthread();
            $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
            $tokenData = $accessTokenTable->getTokenByToken($signupProcessor->accessToken);

            // Create the user's personal board.
            $boardTable = new Theboard_Model_DbTable_Shboard();
            $boardTable->createBoardForUser($newUserID);

            // Upon signup, a series of statuses are automatically generated by the new user.
            $lastStatusID = $threadTable->publishStatus($newUserID, "just started using Nightboard.", 7);
            
            // Check device token validity.
            if ( trim($deviceToken) == "" || strlen($deviceToken) == 64 )
            {
                // Connect to the Apple token table and log the token.
                $appletokenTable = new Theboard_Model_DbTable_Shappletoken();
                $appletokenTable->logToken($tokenData["token_id"], $newUserID, $deviceToken);
            }

            if ( $imageFile )
            {
                $photoProcessor = new Theboard_Model_Photo();
                $picHash = $photoProcessor->savePicture($newUserID, $imageFile);

                if ( preg_match("/[a-f0-9]{40,40}/", $picHash) )
                {
                    $userTable->savePicture($newUserID, $picHash);
                    $lastStatusID = $threadTable->publishStatus($newUserID, "🌅 has a new picture.", 5);
                }
            }

            $userData = $userTable->getUserByUserID($newUserID);

            echo json_encode(array("response" => array("SHToken" => $signupProcessor->accessToken, "SHToken_id" => $tokenData["token_id"], "user_data" => $userData), "errorMessage" => "", "errorCode" => 0));
        }
        else
        {
            echo json_encode(array("response" => "false", "errorMessage" => $signupProcessor->signupErrors, "errorCode" => 1));
        }
    }

    /*
     * Updates the client with its current user's latest info.
     */
    public function pokeserverAction()
    {
        $token = $_POST["token"];
        $appVersion = $_POST["app_version"];
        $locale = $_POST["locale"];
        $timezone = $_POST["timezone"];
        $osName = $_POST["os_name"];
        $osVersion = $_POST["os_version"];
        $deviceName = $_POST["device_name"];
        $deviceToken = $_POST["device_token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $accessTokenTable->updateDeviceName($tokenData["token_id"], $deviceName);
            $accessTokenTable->updateOsInfo($tokenData["token_id"], $osName, $osVersion);
    
            if ( strlen($deviceToken) > 0 )
            {
                $appletokenTable = new Theboard_Model_DbTable_Shappletoken();
                $appletokenTable->updateToken($tokenData["token_id"], $tokenData["user_id"], $deviceToken);
            }
    
            $userTable = new Theboard_Model_DbTable_Shuser();
            $userData = $userTable->getUserByUserID($tokenData["user_id"]);
    
            $userData["phone_numbers"] = $phoneNumberPackages;
    
            $criticalMessage = "nada";
    
            if ( $appVersion == "0.0" )
            {
                $criticalMessage = "update";
            }
    
            $followTable = new Theboard_Model_DbTable_Shfollow();
            $following = $followTable->getFollowingListWithUserData($tokenData["user_id"]);

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $boards = $boardTable->boardsForUser($tokenData["user_id"]);

            echo json_encode(array("response" => array("user_data" => $userData, "following" => $following, "boards" => $boards, "critical_message" => $criticalMessage), "errorMessage" => "", "errorCode" => 0));
        }
        else
        {
            echo json_encode(array("response" => "", "errorMessage" => "Invalid token!", "errorCode" => 404));
        }
    }

    /*
     * Posts a status update.
     */
    public function updatestatusAction()
    {
        $token = $_POST["token"];
        
        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData ) // No valid token found, return what the client sent.
        {
            $status = $_POST["status"];
            $statusType = $_POST["status_type"];

            $threadTable = new Theboard_Model_DbTable_Shthread();

            // Check if the new status is exactly like the previous one & it's been less than 24 hours. Prevent spam.
            $lastStatusData = $threadTable->getLatestGenericStatusUpdate($tokenData["user_id"]);

            if ( $lastStatusData["message"] == $status && time() <= strtotime($lastStatusData["timestamp_sent"]) + 86400 )
            {
                echo json_encode(array("response" => "false", "errorMessage" => "New status is duplicate of the last one.", "errorCode" => 500));
    
                return;
            }
    
            $statusID = $threadTable->publishStatus($tokenData["user_id"], trim($status), $statusType);
            
            if ( $statusID )
            {
                $freshStatusData = $threadTable->getLatestGenericStatusUpdate($tokenData["user_id"]); // Return the whole data chunk.
                
                echo json_encode(array("response" => $freshStatusData, "errorMessage" => "", "errorCode" => 0));
            }
            else
            {
                echo json_encode(array("response" => "false", "errorMessage" => "Error posting status!", "errorCode" => 500));
            }
        }
    }

    /*
     * Deletes a status update.
     */
    public function deletestatusAction()
    {
        $token = $_POST["token"];
        
        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData ) // No valid token found, return what the client sent.
        {
            $statusID = $_POST["status_id"];
            $statusType = $_POST["status_type"];
    
            $threadTable = new Theboard_Model_DbTable_Shthread();
            $lastStatusData = $threadTable->getLatestStatusUpdate($tokenData["user_id"]);
    
            if ( $lastStatusData["thread_type"] != 7 && $statusType != 7 )
            {
                $threadTable->deleteStatus($statusID);
        
                // The client needs to update itself with the last status before the one that just got deleted.
                $lastStatusData = $threadTable->getLatestGenericStatusUpdate($tokenData["user_id"]);
    
                echo json_encode(array("response" => $lastStatusData, "errorMessage" => "", "errorCode" => 0));
            }
            else
            {
                echo json_encode(array("response" => "false", "errorMessage" => "Error deleting status!", "errorCode" => 500));
            }
        }
    }

    /*
     * Fetches Mini Feed entries.
     */
    public function downloadminifeedAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);

        if ( !$tokenData ) // No valid token found, return what the client sent.
        {
            echo "while(1);" . $_POST["request"];
            
            return;
        }

        $password = $tokenData["token"];
        
        $decryptor = new \RNCryptor\Decryptor();
        
        // Decryption.
        $plaintext = $decryptor->decrypt($payload, $password); // The decrypted payload.
        $payload = json_decode($plaintext, true);

        $batch = $payload['batch'];

        $threadTable = new Theboard_Model_DbTable_Shthread();
        $updates = $threadTable->getMiniFeed($tokenData["user_id"], $batch);

        if ( $updates )
        {
            // Process the statuses.
            foreach ( $updates as $key => $update )
            {
                $updates[$key]["status_sent"] = 1;
                
                if ( !$updates[$key]["group_id"] )
                {
                    $updates[$key]["group_id"] = -1;
                }

                // Obfuscate location data of the other user's threads.
                if ( $updates[$key]["owner_id"] != $userID )
                {
                    $mediaExtra = json_decode($updates[$key]["media_extra"], true);

                    if ( $mediaExtra['attachment_value'] != "current_location" )
                    {
                        $updates[$key]["location_latitude"] = "";
                        $updates[$key]["location_longitude"] = "";
                    }
                }
            }

            echo json_encode(array("response" => $updates, "errorMessage" => "", "errorCode" => 0));
        }
        else
        {
            echo json_encode(array("response" => "false", "errorMessage" => "No statuses found!", "errorCode" => 404));
        }
    }

    /*
     * Gets a list of people & boards the user might be interested in.
     */
    public function getrecommendationsAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $boardTable = new Theboard_Model_DbTable_Shboard();
            $spottedTable = new Theboard_Model_DbTable_Shspotted();
            $spottedList = json_decode($_POST['users'], true);
            
            foreach ( $spottedList as $key => $spottedUserID )
            {
                if ( $spottedUserID != $tokenData["user_id"] ) // Make sure we don't pick up the same user from a different device.
                {
                    $spottedTable->spotted($tokenData["user_id"], $spottedUserID);

                    // Get the boards this user is a member of.
                    $boards = $boardTable->boardsForUser($spottedUserID);

                    // Add them as suggestions for the current user.
                    foreach ( $boards as $boardKey => $board )
                    {
                        $boardID = $board["board_id"];

                        if ( !$boardTable->userIsMemberOfBoard($tokenData["user_id"], $boardID) )
                        {
                            $boardTable->addBoardSuggestion($tokenData["user_id"], $boardID);
                        }
                    }
                }
            }

            $users = $spottedTable->getSpottedListWithUserData($tokenData["user_id"]);
            $boards = $boardTable->getRecommendedBoardsForUser($tokenData["user_id"]);
            
            echo json_encode(array("response" => array("users" => $users, "boards" => $boards), "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Gets a list of people following the user.
     */
    public function getfollowersAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $followTable = new Theboard_Model_DbTable_Shfollow();
            $followers = $followTable->getFollowersListWithUserData($tokenData["user_id"]);

            echo json_encode(array("response" => $followers, "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Gets a list of people the user is following.
     */
    public function getfollowingAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $followTable = new Theboard_Model_DbTable_Shfollow();
            $following = $followTable->getFollowingListWithUserData($tokenData["user_id"]);

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $boards = $boardTable->boardsForUser($tokenData["user_id"]);

            echo json_encode(array("response" => array("users" => $following, "boards" => $boards), "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Gets a list of people following a specific user.
     */
    public function getfollowersforuserAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $userID = $_POST["user_id"];

            $followTable = new Theboard_Model_DbTable_Shfollow();
            $followers = $followTable->getFollowersListWithUserData($userID);

            echo json_encode(array("response" => $followers, "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Gets a list of people a specific user is following.
     */
    public function getfollowingforuserAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $userID = $_POST["user_id"];

            $followTable = new Theboard_Model_DbTable_Shfollow();
            $following = $followTable->getFollowingListWithUserData($userID);

            echo json_encode(array("response" => $following, "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Updates a user's profile info.
     */
    public function updateprofileAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData ) // No valid token found, return what the client sent.
        {
            $name = $_POST['name'];
            $username = $_POST['user_handle'];
            $gender = $_POST['gender'];
            $birthday = $_POST['birthday'];
            $location_country = $_POST['location_country'];
            $location_state = $_POST['location_state'];
            $location_city = $_POST['location_city'];
            $bio = $_POST['bio'];
            $website = $_POST['website'];
    
            $profileProcessor = new Theboard_Model_Profile();
            $profileProcessor->name = trim($name);
            $profileProcessor->username = trim($username);
            $profileProcessor->gender = (trim($gender) != "") ? trim($gender) : NULL;
            $profileProcessor->birthday = (trim($birthday) != "") ? trim($birthday) : NULL;
            $profileProcessor->location_country = (trim($location_country) != "") ? trim($location_country) : NULL;
            $profileProcessor->location_state = (trim($location_state) != "") ? trim($location_state) : NULL;
            $profileProcessor->location_city = (trim($location_city) != "") ? trim($location_city) : NULL;
            $profileProcessor->bio = (trim($bio) != "") ? trim($bio) : NULL;
            $profileProcessor->website = (trim($website) != "") ? trim($website) : NULL;
            $profileProcessor->userID = $tokenData["user_id"];
    
            if ( $profileProcessor->editProfile() )
            {
                echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
            }
            else
            {
                echo json_encode(array("response" => "error!", "errorMessage" => $profileProcessor->profileErrors, "errorCode" => 1));
            }
        }
    }

    /*
     * Follows a user by their user ID.
     */
    public function adduserAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $adderID = $tokenData["user_id"];
            $userID = $_POST["user_id"];

            $userTable = new Theboard_Model_DbTable_Shuser();
            $userData = $userTable->getUserByUserID($userID);

            // Check if the newly-added person has blocked this user.
            $blocklistTable = new Theboard_Model_DbTable_Shblocklist();
            $userIsblocked = $blocklistTable->isBlocked($userID, $adderID);

            if ( $userIsblocked )
            {
                echo json_encode(array("response" => "false", "errorMessage" => "user not found!", "errorCode" => 404));
            }
            else
            {
                $followTable = new Theboard_Model_DbTable_Shfollow();
                $followTable->follow($adderID, $userID);

                $adderInfo = $userTable->getUserByUserID($adderID);
                $adderName = $adderInfo["name"];

                // Check if this person is already following them back.
                $userFollowsAdder = $followTable->userFollowsUser($userID, $adderID);
                
                if ( $userFollowsAdder ) // If so, delete the peer entry from the DB.
                {
                    $spottedTable = new Theboard_Model_DbTable_Shspotted();
                    $spottedTable->removeSpot($adderID, $userID);

                    $userData["follows_user"] = 1;
                }
                else
                {
                    $userData["follows_user"] = 0;
                    unset($userData["email_address"]);
                }
                
                // Notify this person.
                $applePushProcessor = new Theboard_Model_ApplePush(
                                            "{$adderName} added you as a contact",
                                            $userID,
                                            array("type" => "new_follower", "user_id" => $adderID));
    
                $applePushProcessor->dispatchNotif();
                
                $userData["blocked"] = 0;

                echo json_encode(array("response" => $userData, "errorMessage" => "", "errorCode" => 0));
            }
            
        }
    }

    /*
     * Follows a user by their user handle.
     */
    public function addbyusernameAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData ) // No valid token found, return what the client sent.
        {
            $username = $_POST["username"];
            $userTable = new Theboard_Model_DbTable_Shuser();
            $userData = $userTable->getUserByUsername($username);
            
            if ( $userData )
            {
                $userID = $userData["user_id"];
    
                // Check if the newly-added person has blocked this user.
                $blocklistTable = new Theboard_Model_DbTable_Shblocklist();
                $userIsblocked = $blocklistTable->isBlocked($userID, $tokenData["user_id"]);

                if ( $userIsblocked )
                {
                    echo json_encode(array("response" => "false", "errorMessage" => "user not found!", "errorCode" => 404));
                }
                else
                {
                    $adderID = $tokenData["user_id"];
                    $adderInfo = $userTable->getUserByUserID($adderID);

                    // Notify this person.
                    $applePushProcessor = new Theboard_Model_ApplePush(
                                                "{$adderName} added you as a contact",
                                                $userID,
                                                array("type" => "new_follower", "user_id" => $adderID));
        
                    $applePushProcessor->dispatchNotif();

                    $followTable = new Theboard_Model_DbTable_Shfollow();
                    $followTable->follow($tokenData["user_id"], $userID);
                    
                    if ( $followTable->userFollowsUser($userID, $tokenData["user_id"]) )
                    {
                        $userData["follows_user"] = 1;
                    }
                    else
                    {
                        $userData["follows_user"] = 0;
                        unset($userData["email_address"]);
                    }

                    $userData["blocked"] = 0;
    
                    echo json_encode(array("response" => $userData, "errorMessage" => "", "errorCode" => 0));
                }
            }
            else
            {
                // User not found!
                echo json_encode(array("response" => "false", "errorMessage" => "user not found!", "errorCode" => 404));
            }
        }
    }

    /*
     * Unfollows a user by their user ID.
     */
    public function removeuserAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $userID = $_POST["user_id"];

            $followTable = new Theboard_Model_DbTable_Shfollow();
            $followTable->unfollow($tokenData["user_id"], $userID);

            echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Unfollows a user by their user ID.
     */
    public function removerecommendeduserAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $userID = $_POST["user_id"];
            
            $spottedTable = new Theboard_Model_DbTable_Shspotted();
            $spottedTable->removeSpot($tokenData["user_id"], $userID);
            
            echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Gets the info of a user.
     */
    public function getuserinfoAction()
    {
        $token = $_POST["token"];
        
        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData ) // No valid token found, return what the client sent.
        {
            $userID = $_POST["user_id"];
            $fullProfile = $_POST["full"];

            if ( $fullProfile )
            {
                $userTable = new Theboard_Model_DbTable_Shuser();
                $userData = $userTable->getUserByUserID($userID);

                // Check if this person is blocked.
                $blocklistTable = new Theboard_Model_DbTable_Shblocklist();
                $userIsblocked = $blocklistTable->isBlocked($userID, $tokenData["user_id"]);
                
                if ( $userIsblocked )
                {
                    $userData["blocked"] = 1;
                }
                else
                {
                    $userData["blocked"] = 0;
                }
                
                $followTable = new Theboard_Model_DbTable_Shfollow();

                if ( $followTable->userFollowsUser($userID, $tokenData["user_id"]) )
                {
                    $userData["follows_user"] = 1;
                }
                else
                {
                    $userData["follows_user"] = 0;
                    unset($userData["email_address"]);
                }

                echo json_encode(array("response" => $userData, "errorMessage" => "", "errorCode" => 0));
            }
            else
            {
                $followTable = new Theboard_Model_DbTable_Shfollow();
                $followerCount = $followTable->getFollowerCount($userID);
                $followingCount = $followTable->getFollowingCount($userID);

                echo json_encode(array("response" => array("follower_count" => $followerCount, "following_count" => $followingCount), "errorMessage" => "", "errorCode" => 0));
            }
            
        }
    }

    /*
     * Blocks a user.
     */
    public function blockuserAction()
    {
        $token = $_POST["token"];
        
        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData ) // No valid token found, return what the client sent.
        {
            $userID = $_POST["user_id"];

            $blocklistTable = new Theboard_Model_DbTable_Shblocklist();
            $blocklistTable->block($tokenData["user_id"], $userID);
            
            echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Unblocks a user.
     */
    public function unblockuserAction()
    {
        $token = $_POST["token"];
        
        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $userID = $_POST["user_id"];

            $blocklistTable = new Theboard_Model_DbTable_Shblocklist();
            $blocklistTable->unblock($tokenData["user_id"], $userID);
            
            echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Uploads a new user DP.
     */
    public function dpuploadAction()
    {
        $token = $_POST["token"];
        $imageFile = $_FILES["image_file"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $photoProcessor = new Theboard_Model_Photo();
            $processorResponse = $photoProcessor->savePicture($tokenData["user_id"], $imageFile);
            
            if ( !preg_match("/[a-f0-9]{40,40}/", $processorResponse) )
            {
                echo json_encode(array("response" => "", "errorMessage" => "Error uploading pic!", "errorCode" => 504));
                die;
            }
            
            $userTable = new Theboard_Model_DbTable_Shuser();
            $threadTable = new Theboard_Model_DbTable_Shthread();

            $oldHash = $userTable->getCurrentDPHash($tokenData["user_id"]);

            if ( $oldHash )
            {
                $didDeleteOldDP = $photoProcessor->deletePicture($tokenData["user_id"], $oldHash);
            }

            $userTable->savePicture($tokenData["user_id"], $processorResponse);
            $threadTable->publishStatus($tokenData["user_id"], "🌅 has a new picture.", 5);
            
            echo json_encode(array("response" => $processorResponse, "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Removes a user's DP.
     */
    public function dpremoveAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $photoProcessor = new Theboard_Model_Photo();
            $userTable = new Theboard_Model_DbTable_Shuser();
            $threadTable = new Theboard_Model_DbTable_Shthread();

            $oldHash = $userTable->getCurrentDPHash($tokenData["user_id"]);
            $didDeleteOldDP = $photoProcessor->deletePicture($tokenData["user_id"], $oldHash);

            $userTable->deletePicture($tokenData["user_id"]);
            $threadTable->deleteStatusesOfType($tokenData["user_id"], 5);

            echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Uploads a new board cover photo.
     */
    public function boardcoveruploadAction()
    {
        $token = $_POST["token"];
        $imageFile = $_FILES["image_file"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $boardID = $_POST["board_id"];
            $photoProcessor = new Theboard_Model_Photo();
            $processorResponse = $photoProcessor->saveBoardCover($boardID, $imageFile);
            
            if ( !preg_match("/[a-f0-9]{40,40}/", $processorResponse) )
            {
                echo json_encode(array("response" => "", "errorMessage" => "Error uploading pic!", "errorCode" => 504));
                die;
            }
            
            $boardTable = new Theboard_Model_DbTable_Shboard();
            $oldHash = $boardTable->getCurrentCoverHash($boardID);

            if ( $oldHash )
            {
                $didDeleteOldDP = $photoProcessor->deleteBoardCover($boardID, $oldHash);
            }

            $boardTable->saveCoverHash($boardID, $processorResponse);
            
            echo json_encode(array("response" => $processorResponse, "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Removes a board's cover photo.
     */
    public function boardcoverremoveAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $boardID = $_POST["board_id"];
            $photoProcessor = new Theboard_Model_Photo();
            $boardTable = new Theboard_Model_DbTable_Shboard();

            $oldHash = $boardTable->getCurrentCoverHash($boardID);
            $didDeleteOldDP = $photoProcessor->deleteBoardCover($boardID, $oldHash);

            $boardTable->deleteCurrentCoverHash($boardID);

            echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Creates a new board.
     */
    public function createboardAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $name = $_POST["name"];
            $privacy = $_POST["privacy"];

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $boardID = $boardTable->createBoard($name, $privacy);
            
            // Add the creator as the first member.
            $boardTable->join($tokenData["user_id"], $boardID);

            echo json_encode(array("response" => $boardID, "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Gets a board's data.
     */
    public function getboardinfoAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $boardID = $_POST["board_id"];

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $boardData = $boardTable->getInfo($tokenData["user_id"], $boardID, true); // Include posts.
            $userIsMember = $boardTable->userIsMemberOfBoard($tokenData["user_id"], $boardID);
            $privacy = $boardData["privacy"];

            if ( $userIsMember )
            {
                $boardData["user_is_member"] = 1;

                if ( $privacy == 2 ) // Closed board.
                {
                    $boardData["request_count"] = $boardTable->getRequestCount($boardID);
                }
            }
            else
            {
                $boardData["user_is_member"] = 0;

                if ( $privacy == 2 ) // Closed board
                {
                    if ( $boardTable->userRequestedBoardJoin($tokenData["user_id"], $boardID) )
                    {
                        $boardData["user_requested_join"] = 1;
                    }
                    else
                    {
                        $boardData["user_requested_join"] = 0;
                    }

                    unset($boardData["posts"]); // Remove posts since user is not a member.
                }
            }

            echo json_encode(array("response" => $boardData, "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Updates a board's info.
     */
    public function updateboardinfoAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $boardID = $_POST["board_id"];
            $name = $_POST["name"];
            $description = $_POST["description"];
            $privacy = $_POST["privacy"];

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $boardCurrentData = $boardTable->getInfo($tokenData["user_id"], $boardID);

            $data = array("name" => $name,
                          "description" => $description,
                          "privacy" => $privacy);

            if ( $boardCurrentData["privacy"] == 2 && $privacy == 1 ) // Private board became public.
            {
                $requests = $boardTable->getRequests($tokenData["user_id"], $boardID);
                $boardName = $boardCurrentData["name"];

                foreach ( $requests as $key => $request )
                {
                    // Accept all requests.
                    $boardTable->join($request["user_id"], $boardID);

                    // Notify everyone.
                    $applePushProcessor = new Theboard_Model_ApplePush(
                                                "Your request to join {$boardName} was accepted",
                                                $request["user_id"],
                                                array("type" => "board_request", "board_id" => $boardID));
                    
                    $applePushProcessor->dispatchNotif();
                }
            }

            $boardTable->updateBoard($boardID, $data);

            echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Gets all pending join requests for a board.
     */
    public function getboardrequestsAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $boardID = $_POST["board_id"];

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $requests = $boardTable->getRequests($tokenData["user_id"], $boardID);

            echo json_encode(array("response" => $requests, "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Gets all board members.
     */
    public function getboardmembersAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $boardID = $_POST["board_id"];

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $members = $boardTable->getMemberListWithUserData($tokenData["user_id"], $boardID);

            echo json_encode(array("response" => $members, "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Adds the user as a member of a board.
     */
    public function joinboardAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $boardID = $_POST["board_id"];

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $privacy = $boardTable->getPrivacy($boardID);

            if ( $privacy == 1 )
            {
                $boardTable->join($tokenData["user_id"], $boardID);
            }
            else
            {
                $boardTable->requestJoin($tokenData["user_id"], $boardID);
                $boardMembers = $boardTable->getMemberList($boardID);
                $boardInfo = $boardTable->getInfo($tokenData["user_id"], $boardID);
                $boardName = $boardInfo["name"];

                $userTable = new Theboard_Model_DbTable_Shuser();
                $userInfo = $userTable->getUserByUserID($tokenData["user_id"]);
                $userName = $userInfo["name"];
                
                foreach ( $boardMembers as $key => $member )
                {
                    // Notify this person.
                    $applePushProcessor = new Theboard_Model_ApplePush(
                                                "{$userName} would like to join {$boardName}",
                                                $member["user_id"],
                                                array("type" => "board_request", "board_id" => $boardID));
                    
                    $applePushProcessor->dispatchNotif();
                }
            }

            echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Confirms a user's board request.
     */
    public function acceptboardrequestAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $boardID = $_POST["board_id"];
            $newMemberID = $_POST["user_id"];

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $userIsMember = $boardTable->userIsMemberOfBoard($tokenData["user_id"], $boardID);

            if ( $userIsMember )
            {
                $boardTable->join($newMemberID, $boardID);

                echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
            }
            else
            {
                echo json_encode(array("response" => "you're not a member to allow access.", "errorMessage" => "private board!", "errorCode" => 1));
            }
        }
    }

    /*
     * Declines a user's board request.
     */
    public function declineboardrequestAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $boardID = $_POST["board_id"];
            $newMemberID = $_POST["user_id"];

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $userIsMember = $boardTable->userIsMemberOfBoard($tokenData["user_id"], $boardID);

            if ( $userIsMember )
            {
                $boardTable->cancelRequest($newMemberID, $boardID);

                echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
            }
            else
            {
                echo json_encode(array("response" => "you're not a member to deny access.", "errorMessage" => "private board!", "errorCode" => 1));
            }
        }
    }

    /*
     * Removes the user as a member of a board.
     */
    public function leaveboardAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $boardID = $_POST["board_id"];

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $boardData = $boardTable->getInfo($tokenData["user_id"], $boardID);

            // Don't leave dead cover photos lying around. Clean up.
            if ( $boardData["cover_hash"] )
            {
                $photoProcessor = new Theboard_Model_Photo();
                $photoProcessor->deleteBoardCover($boardID, $boardData["cover_hash"]);
            }

            $boardTable->leave($tokenData["user_id"], $boardID);
            $memberCount = $boardTable->getMemberCount($boardID);

            // Once a board has no members, it gets removed.
            if ( $memberCount == 0 )
            {
                $boardTable->deleteBoard($boardID);
            }

            echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Creates a new board post.
     */
    public function createboardpostAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $boardID = $_POST["board_id"];
            $text = $_POST["text"];
            $color = $_POST["color"];
            $mediaHash = $_POST["media_hash"];

            $data = array("owner_id" => $tokenData["user_id"],
                          "text" => $text,
                          "color" => $color,
                          "media_hash" => $mediaHash);

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $postID = $boardTable->postToBoard($boardID, $data);

            echo json_encode(array("response" => $postID, "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Edits an existing board post.
     */
    public function editboardpostAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $postID = $_POST["post_id"];
            $text = $_POST["text"];

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $postID = $boardTable->editPost($postID, $text);

            echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Deletes a board post.
     */
    public function deleteboardpostAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $threadID = $_POST["post_id"];
            
            $boardTable = new Theboard_Model_DbTable_Shboard();
            $boardTable->deletePost($threadID);

            echo json_encode(array("response" => $postID, "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Registers a view on a post.
     */
    public function recordboardpostviewAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $threadID = $_POST["post_id"];
    
            $boardTable = new Theboard_Model_DbTable_Shboard();
            $boardTable->registerView($tokenData["user_id"], $threadID);
            
            echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Returns all posts containing the given hashtag.
     */
    public function searchAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $boardID = $_POST["board_id"];
            $hashtag = $_POST["hashtag"];

            $boardTable = new Theboard_Model_DbTable_Shboard();
            $results = $boardTable->searchForHashtag("#" . $hashtag, $boardID);
            
            echo json_encode(array("response" => $results, "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Saves a user's hashtag list so they can be notified about them.
     */
    public function savehashtaglistAction()
    {
        $token = $_POST["token"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByToken($token);

        if ( $tokenData )
        {
            $list = json_decode($_POST['hashtag_list'], true);
    
            $hashtagTable = new Theboard_Model_DbTable_Shhashtag();
            $hashtagTable->removeAllHashtagsForUser($tokenData["user_id"]);
            
            foreach ( $list as $key => $hashtag )
            {
                $hashtagTable->addHashtag($tokenData["user_id"], $hashtag);
            }
    
            echo json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
    }

    /*
     * Uploads a new media file & returns its hash.
     */
    public function mediauploadAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        $mediaFile = $_FILES["mediaFile"];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);

        if ( !$tokenData ) // No valid token found, return what the client sent.
        {
            echo "while(1);" . $_POST["request"];
            
            return;
        }

        $password = $tokenData["token"];
        
        $encryptor = new \RNCryptor\Encryptor();
        $decryptor = new \RNCryptor\Decryptor();
        
        // Decryption.
        $plaintext = $decryptor->decrypt($payload, $password); // The decrypted payload.
        $payload = json_decode($plaintext, true);

        $mediaType = $payload['media_type'];
        
        if ( $mediaType == "1" ) // Photo.
        {
            $photoProcessor = new Theboard_Model_Photo();
            $processorResponse = $photoProcessor->saveMediaPhoto($tokenData["user_id"], $mediaFile);
            
            if ( !preg_match("/[a-f0-9]{40,40}/", $processorResponse) )
            {
                $output = json_encode(array("response" => "", "errorMessage" => "Error uploading pic!", "errorCode" => 504));
                $base64Encrypted = $encryptor->encrypt($output, $password);
                
                echo "while(1);" . $base64Encrypted;
                die;
            }
            
            $output = json_encode(array("response" => $processorResponse, "errorMessage" => "", "errorCode" => 0));
            $base64Encrypted = $encryptor->encrypt($output, $password);
            
            echo "while(1);" . $base64Encrypted;
        }
    }

    /*
     * Deletes a pending media upload.
     */
    public function mediadeleteAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Theboard_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);

        if ( !$tokenData ) // No valid token found, return what the client sent.
        {
            echo "while(1);" . $_POST["request"];
            
            return;
        }

        $password = $tokenData["token"];
        
        $encryptor = new \RNCryptor\Encryptor();
        $decryptor = new \RNCryptor\Decryptor();
        
        // Decryption.
        $plaintext = $decryptor->decrypt($payload, $password); // The decrypted payload.
        $payload = json_decode($plaintext, true);

        $mediaHash = $payload['media_hash'];
        
        $photoProcessor = new Theboard_Model_Photo();
        $photoProcessor->deleteMediaPhoto($tokenData["user_id"], $mediaHash);
        
        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    public function getcountrylistAction()
    {
        $phoneNumberTable = new Theboard_Model_DbTable_Shphonenumber();
        $countryList = $phoneNumberTable->getCountryList();
        $detailedCountrylist = array();
        
        if ( $countryList )
        {
            echo json_encode(array("response" => $countryList, "errorMessage" => "", "errorCode" => 0));
        }
        else
        {
            echo json_encode(array("response" => "false", "errorMessage" => "Error fetching country list!", "errorCode" => 1));
        }
    }

    /*
     * Sends a verification code to the entered email address for verification.
     */
    public function dispatchcodeAction()
    {
        $code = $_POST["code"];
        $email = $_POST["email"];

        $bodyHTML = <<<MAIL

Your verification code is: <strong>{$code}</strong><br /><br />Regards,<br />--<br />Scapehouse

MAIL;

        Model_Lib_Func::shMailer($email, $email, "Verification Code", $bodyHTML, "Nightboard");

        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));

        echo $output;
    }
}

?>