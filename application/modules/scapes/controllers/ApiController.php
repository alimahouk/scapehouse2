<?php

require_once (__DIR__ . '/../../../../public_html/autoload.php'); // Crypto.

/**
 * Scapehouse Scapes API calls.
 *
 * @copyright  2014 Scapehouse
 */

class Scapes_ApiController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here. */
        
        $this->_helper->layout->disableLayout();         // No layout.
        $this->_helper->viewRenderer->setNoRender(true); // No view.

        $GLOBALS["init_token"] = "54f01568a9e6d50e9190a1e21b1800445585d427"; // For fresh logins & signups.
        $GLOBALS["batch_size"] = 15;
    }

	public function testrunAction()
    {
        /*$applePushProcessor = new Scapes_Model_ApplePush(
                        "I just started using Scapes.",
                        1,
                        array("type" => "join", "user_id" => 20));

        $applePushProcessor->dispatchNotif();*/
    }

    /*
     * Use this function for admin functions.
     */
    public function fatladyAction()
    {
        $password = urldecode($_GET["password"]);

        if ( $password && $password == "caput draconis" )
        {
            $userTable = new Scapes_Model_DbTable_Shuser();
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
            // Online users.

            echo "<div style='clear:both; margin-left:-5px; padding:5px;'>";
            echo "<h2>online users.</h2>";
            echo "<table style='border:1px solid #ccc; border-collapse:collapse; width:1280px;'>";
            echo "<tr>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>ID</strong></td>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>NAME</strong></td>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>COUNTRY</strong></td>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>PRESENCE</strong></td>";
            echo "<td style='border-bottom:1px solid #ccc; padding:10px;'><strong>TIMESTAMP</strong></td>";
            echo "</tr>";

            $i = 0;

            foreach ( $onlineUsers as $key => $user )
            {   
                $DPPath = "https://fbcdn-profile-a.akamaihd.net/hprofile-ak-xfp1/v/t1.0-1/c29.0.100.100/p100x100/954801_10150002137498316_604636659114323291_n.jpg?oh=684988b49134612ef144aad49bb3fdaf&oe=5454651D&__gda__=1414772237_09c259ff5137fb9927a0e851de69d4e9";

                if ( $user["hash"] )
                {
                    $DPPath = "http://scapehouse.com/userphotos/" . $user["user_id"] . "/profile/f_" . $user["hash"] . ".jpg";
                }

                $presence = $user["status"];

                switch ( $presence )
                {
                    case 1:
                        $presence = "offline.";
                        break;
                    
                    case 2:
                        $presence = "<span style='color:#21ff1a;'>online.</span>";
                        break;

                    case 3:
                        $presence = "<span style='color:#21ff1a;'>online (masked).</span>";
                        break;
                        
                    case 4:
                        $presence = "<span style='color:#ffa538;'>away.</span>";
                        break;

                    case 5:
                        $presence = "<span style='color:#919191;'>typing.</span>";
                        break;

                    case 6:
                        $presence = "<span style='color:#21ff1a;'>stopped activity.</span>";
                        break;

                    case 13:
                        $presence = "<span style='color:#919191;'>checking a link.</span>";
                        break;
                        
                    case 14:
                        $presence = "offline (masked).";
                        break;

                    default:
                        $presence = "<span style='color:#919191;'>sending media.</span>";
                        break;
                }

                echo "<tr>";
                echo "<td style='border-right:1px solid #ccc; color:#ccc; padding:10px; text-align:center;'><em>" . $user["user_id"] . "</em></td>";
                echo "<td style='padding:10px;'><img src='" . $DPPath . "' width='50' height='50' />" . $user["name_first"] . " " . $user["name_last"] . "</td>";
                echo "<td style='padding:10px;'>" . $user["name"] . "</td>";
                echo "<td style='padding:10px;'>" . $presence . "</td>";
                echo "<td style='padding:10px;'>" . Model_Lib_Func::relativeTime(strtotime($user["timestamp"])) . "</td>";
                echo "</tr>";

                $i++;
            }

            echo "<tr>";
            echo "<td style='border-top:1px solid #ccc; padding:10px;'>TOTAL:</td>";
            echo "<td style='border-top:1px solid #ccc; padding:10px;' colspan='4'><strong>" . number_format($i) . "</strong></td>";
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
            $input = json_decode($_POST["request"], true);
            $payload = $input['payload'];
            
            $encryptionKey = $GLOBALS["init_token"];
            
            $encryptor = new \RNCryptor\Encryptor();
            $decryptor = new \RNCryptor\Decryptor();
            
            // Decryption.
            $plaintext = $decryptor->decrypt($payload, $encryptionKey); // The decrypted payload.
            $payload = json_decode($plaintext, true);

            $countryCallingCode = $payload['country_calling_code'];
            $prefix = $payload['prefix'];
            $phoneNumber = $payload['phone_number'];
            $locale = $payload['locale'];
            $timezone = $payload['timezone'];
            $osName = $payload['os_name'];
            $osVersion = $payload['os_version'];
            $deviceName = $payload['device_name'];
            $deviceType = $payload['device_type'];
            $deviceToken = $payload['device_token'];
            $cheating = $payload['cheat'];

            $userTable = new Scapes_Model_DbTable_Shuser();

            /*
             * SPECIAL: this login param checks a cheat table in the DB (for bypassing the SMS verification code).
             */
            if ( $cheating == 1 )
            {
                $existsInCheatTable = $userTable->checkCheatTableForPhoneNumber($countryCallingCode, $prefix, $phoneNumber);

                if ( !$existsInCheatTable )
                {
                    // Login failed.
                    $output = json_encode(array("response" => "false", "errorMessage" => "error!", "errorCode" => 500));
                    $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);
                    
                    echo "while(1);" . $base64Encrypted;
    
                    return;
                }
            }

            $user = $userTable->getUserByPhoneNumber($countryCallingCode, $prefix, $phoneNumber);
            
            if ( $user )
            {
                $blacklistTable = new Scapes_Model_DbTable_Shblacklist();

                if ( $blacklistTable->isBadUser($user["user_id"]) ) // User is blacklisted. Don't let them in.
                {
                    $output =  json_encode(array("response" => "false", "errorMessage" => "nope. you've been bad lately.", "errorCode" => 86));
                    $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);
                    
                    echo "while(1);" . $base64Encrypted;
                }

                // Create an access token.                 
                $accessToken = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));

                // Store the access token.
                $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
                $tokenID = $accessTokenTable->logToken($user["user_id"], $accessToken, $osName, $osVersion, $deviceName, $deviceType);

                $userTable = new Scapes_Model_DbTable_Shuser();
                $userTable->updateLocale($user["user_id"], $locale);
                $userTable->updateTimezone($user["user_id"], $timezone);
                
                // Check device token validity.
                if ( trim($deviceToken) == "" || strlen($deviceToken) == 64 )
                {
                    if ( $tokenID && strlen(trim($deviceToken)) == 64 ) // If a Scapehouse token was sucessfully created...
                    {
                        // Connect to the Apple token table and log the token.
                        $appletokenTable = new Scapes_Model_DbTable_Shappletoken();
                        $appletokenTable->logToken($tokenID, $user["user_id"], $deviceToken);
                    }

                    $phoneNumberTable = new Scapes_Model_DbTable_Shphonenumber();
                    $userPhoneNumbers = $phoneNumberTable->getNumbersForUserID($user["user_id"]);
                    $phoneNumberPackages = array();
        
                    foreach ( $userPhoneNumbers as $phoneNumberKey => $userPhoneNumber )
                    {
                        $numberCountryCallingCode = $phoneNumberTable->getCountryCallingCode($userPhoneNumber["country_calling_code_id"]);
                        $numberPrefix = $userPhoneNumber["prefix"];
                        $number = $userPhoneNumber["number"];
                        $timestamp = $userPhoneNumber["timestamp"];

                        $phoneNumberPackages[] = array("country_calling_code" => $numberCountryCallingCode, "prefix" => $numberPrefix, "phone_number" => $number, "timestamp" => $timestamp);
                    }
                    
                    $user["phone_numbers"] = $phoneNumberPackages;
                    $user["talking_mask"] = $userTable->getTalkingMask($user["user_id"]);
                    $user["presence_mask"] = $userTable->getPresenceMask($user["user_id"]);

                    $licenseTable = new Scapes_Model_DbTable_Shlicense();
                    $user["license"] = $licenseTable->getLicenseForUserID($user["user_id"]);

                    $output = json_encode(array("response" => array("SHToken" => $accessToken, "SHToken_id" => $tokenID, "user_data" => $user), "errorMessage" => "", "errorCode" => 0));
                    $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);

                    echo "while(1);" . $base64Encrypted;
                }
                else
                {
                    // Login failed.
                    $output = json_encode(array("response" => "false", "errorMessage" => "device token error!", "errorCode" => 500));
                    $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);
                    
                    echo "while(1);" . $base64Encrypted;
                }
            }
            else
            {
                // User not found!
                $output =  json_encode(array("response" => "false", "errorMessage" => "user not found!", "errorCode" => 404));
                $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);
                    
                echo "while(1);" . $base64Encrypted;
            }
        }
    }

    /*
     * Discards any dead tokens left over on the device.
     */
    public function purgestaletokenAction()
    {
        $staleToken = $_POST["stale_token"];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $passcodeTable = new Scapes_Model_DbTable_Shpasscode();

        $tokenData = $accessTokenTable->getTokenByToken($staleToken);
        $passcodesLeft = $passcodeTable->deletePasscodeByTokenID($tokenData["token_id"], $tokenData["user_id"]);
        $accessTokenTable->deleteTokenByToken($staleToken);

        if ( $passcodesLeft == 0 )
        {
            $userTable = new Scapes_Model_DbTable_Shuser();
            $userTable->unlockAccount($tokenData["user_id"]);
        }

        echo "while(1);" . json_encode(array("response" => "", "errorMessage" => "", "errorCode" => 0));
    }

    /*
     * Creates an account for a new user.
     */
    public function signupAction()
    {
        $input = json_decode($_POST["request"], true);
        $payload = $input['payload'];
        
        $encryptionKey = $GLOBALS["init_token"];
        
        $encryptor = new \RNCryptor\Encryptor();
        $decryptor = new \RNCryptor\Decryptor();
        
        // Decryption.
        $plaintext = $decryptor->decrypt($payload, $encryptionKey); // The decrypted payload.
        $payload = json_decode($plaintext, true);

        /*
        // Uncomment this block to temporarily halt signups when needed.
        $output = json_encode(array("response" => "false", "errorMessage" => "signups are not allowed at the moment.", "errorCode" => 500));
        $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);

        echo "while(1);" . $base64Encrypted;
        return;
        */

        $firstName = $payload['name_first'];
        $lastName = $payload['name_last'];
        $countryID = $payload['country_id'];
        $countryCallingCode = $payload['country_calling_code'];
        $prefix = $payload['prefix'];
        $phoneNumber = $payload['phone_number'];
        $locale = $payload['locale'];
        $timezone = $payload['timezone'];
        $osName = $payload['os_name'];
        $osVersion = $payload['os_version'];
        $deviceName = $payload['device_name'];
        $deviceType = $payload['device_type'];
        $deviceToken = $payload['device_token'];
        $imageFile = $_FILES["image_file"];

        $signupProcessor = new Scapes_Model_Signup();

        $signupProcessor->firstName = trim($firstName);
        $signupProcessor->lastName = trim($lastName);
        $signupProcessor->countryID = $countryID;
        $signupProcessor->countryCallingCode = $countryCallingCode;
        $signupProcessor->prefix = $prefix;
        $signupProcessor->phoneNumber = trim($phoneNumber);
        $signupProcessor->locale = $locale;
        $signupProcessor->timezone = $timezone;
        $signupProcessor->accessToken = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));
        $signupProcessor->osName = $osName;
        $signupProcessor->osVersion = $osVersion;
        $signupProcessor->deviceName = $deviceName;
        $signupProcessor->deviceType = $deviceType;
        
        $picHash;
        $newUserID;

        $userTable = new Scapes_Model_DbTable_Shuser();
        $possiblyExistingUser = $userTable->getUserByPhoneNumber($countryCallingCode, $prefix, $phoneNumber);
        
        if ( $possiblyExistingUser )
        {
            $blacklistTable = new Scapes_Model_DbTable_Shblacklist();

            if ( $blacklistTable->isBadUser($possiblyExistingUser["user_id"]) ) // User is blacklisted. Don't let them in.
            {
                $output =  json_encode(array("response" => "false", "errorMessage" => "nope. you've been bad lately.", "errorCode" => 86));
                $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);
                
                echo "while(1);" . $base64Encrypted;
            }

            // Create an access token.                 
            $accessToken = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));

            // Store the access token.
            $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
            $tokenID = $accessTokenTable->logToken($possiblyExistingUser["user_id"], $accessToken, $osName, $osVersion, $deviceName, $deviceType);
            
            $userTable = new Scapes_Model_DbTable_Shuser();
            $userTable->updateLocale($possiblyExistingUser["user_id"], $locale);
            $userTable->updateTimezone($possiblyExistingUser["user_id"], $timezone);
            
            // Check device token validity.
            if ( trim($deviceToken) == "" || strlen($deviceToken) == 64 )
            {
                if ( $tokenID && strlen(trim($deviceToken)) == 64 ) // If a Scapehouse token was sucessfully created...
                {
                    // Connect to the Apple token table and log the token.
                    $appletokenTable = new Scapes_Model_DbTable_Shappletoken();
                    $appletokenTable->logToken($tokenID, $possiblyExistingUser["user_id"], $deviceToken);
                }

                $output = json_encode(array("response" => array("SHToken" => $accessToken, "SHToken_id" => $tokenID, "DP_hash" => $possiblyExistingUser["dp_hash"], "last_status_id" => $possiblyExistingUser["last_status_id"], "join_date" => $possiblyExistingUser["join_date"]), "errorMessage" => "", "errorCode" => 0));
                $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);

                echo "while(1);" . $base64Encrypted;
            }
            else
            {
                // Login failed.
                $output = json_encode(array("response" => "false", "errorMessage" => "device token error!", "errorCode" => 500));
                $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);
                
                echo "while(1);" . $base64Encrypted;
            }
        }
        elseif ( $newUserID = $signupProcessor->signup() ) // If an account was sucessfully created...
        {
            //Model_Lib_Func::notifyCommanderInChief($firstName, $lastName);
            
            $presenceTable = new Scapes_Model_DbTable_Shuseronlinestatus();
            $threadTable = new Scapes_Model_DbTable_Shthread();
            $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
            $tokenData = $accessTokenTable->getTokenByToken($signupProcessor->accessToken);

            // Comment/Uncomment this block during promotion periods.
            $annualLicense = 2;
            $lifetimeLicense = 3;
            $licenseTable = new Scapes_Model_DbTable_Shlicense();
            $licenseTable->addLicense($newUserID, $lifetimeLicense);

            // Upon signup, a series of statuses are automatically generated by the new user.
            $presenceTable->createFreshPresence($newUserID);
            $threadTable->publishStatus($newUserID, "just started using Scapes.", 7);
            
            // Check device token validity.
            if ( trim($deviceToken) == "" || strlen($deviceToken) == 64 )
            {
                // Connect to the Apple token table and log the token.
                $appletokenTable = new Scapes_Model_DbTable_Shappletoken();
                $appletokenTable->logToken($tokenData["token_id"], $newUserID, $deviceToken);
            }

            if ( $imageFile )
            {
                $photoProcessor = new Scapes_Model_Photo();
                $picHash = $photoProcessor->savePicture($newUserID, $imageFile);

                if ( preg_match("/[a-f0-9]{40,40}/", $picHash) )
                {
                    $userTable->savePicture($newUserID, $picHash);
                    $threadTable->publishStatus($newUserID, "🌅 has a new picture.", 5);
                }
            }

            $lastStatusID = $threadTable->publishStatus($newUserID, "🔵 available.", 2);

            $adders = $userTable->erasePotentialUser($countryCallingCode, $prefix, $phoneNumber);

            $followTable = new Scapes_Model_DbTable_Shfollow();
            $followTable->follow($newUserID, $newUserID); // Every user also follows themselves.

            foreach ( $adders as $adder )
            {
                $followTable->follow($adder["adder_user_id"], $newUserID); // Subscribe all adders to the contact's updates.

                // Notify any people who added this person.
                $applePushProcessor = new Scapes_Model_ApplePush(
                                "{$firstName} {$lastName} just started using Scapes.",
                                $adder["adder_user_id"],
                                array("type" => "join", "user_id" => $newUserID));

                $applePushProcessor->dispatchNotif();
            }

            $output = json_encode(array("response" => array("SHToken" => $signupProcessor->accessToken, "SHToken_id" => $tokenData["token_id"], "userID" => $newUserID, "DP_hash" => $picHash, "last_status_id" => $lastStatusID, "join_date" => gmdate("Y-m-d H:i:s", time())), "errorMessage" => "", "errorCode" => 0));
            $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);

            echo "while(1);" . $base64Encrypted;
        }
        else
        {
            $output = json_encode(array("response" => "false", "errorMessage" => $signupProcessor->signupErrors, "errorCode" => 1));
            $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);

            echo "while(1);" . $base64Encrypted;
        }
    }

    /*
     * Updates the client with its current user's latest info.
     */
    public function pokeserverAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);

        if ( !$tokenData ) // No valid token found, return what the client sent.
        {
            echo "while(1);" . $_POST["request"];
            
            return;
        }

        $encryptor = new \RNCryptor\Encryptor();
        $decryptor = new \RNCryptor\Decryptor();

        $password = $tokenData["token"];
        
        // Decryption.
        $plaintext = $decryptor->decrypt($payload, $password); // The decrypted payload.
        $payload = json_decode($plaintext, true);

        $appVersion = $payload['app_version'];
        $locale = $payload['locale'];
        $timezone = $payload['timezone'];
        $osName = $payload['os_name'];
        $osVersion = $payload['os_version'];
        $deviceName = $payload['device_name'];
        $deviceToken = $payload['device_token'];

        $accessTokenTable->updateDeviceName($tokenID, $deviceName);
        $accessTokenTable->updateOsInfo($tokenID, $osName, $osVersion);

        if ( strlen($deviceToken) > 0 )
        {
            $appletokenTable = new Scapes_Model_DbTable_Shappletoken();
            $appletokenTable->updateToken($tokenID, $tokenData["user_id"], $deviceToken);
        }

        $userTable = new Scapes_Model_DbTable_Shuser();
        $userData = $userTable->getUserByUserID($tokenData["user_id"]);
        $userData["talking_mask"] = $userTable->getTalkingMask($tokenData["user_id"]);
        $userData["presence_mask"] = $userTable->getPresenceMask($tokenData["user_id"]);

        $phoneNumberTable = new Scapes_Model_DbTable_Shphonenumber();
        $userPhoneNumbers = $phoneNumberTable->getNumbersForUserID($tokenData["user_id"]);
        $phoneNumberPackages = array();

        foreach ( $userPhoneNumbers as $phoneNumberKey => $userPhoneNumber )
        {
            $numberCountryCallingCode = $phoneNumberTable->getCountryCallingCode($userPhoneNumber["country_calling_code_id"]);
            $numberPrefix = $userPhoneNumber["prefix"];
            $number = $userPhoneNumber["number"];
            $timestamp = $userPhoneNumber["timestamp"];

            $phoneNumberPackages[] = array("country_calling_code" => $numberCountryCallingCode, "prefix" => $numberPrefix, "phone_number" => $number, "timestamp" => $timestamp);
        }

        $userData["phone_numbers"] = $phoneNumberPackages;
        
        $licenseTable = new Scapes_Model_DbTable_Shlicense();
        $userData["license"] = $licenseTable->getLicenseForUserID($tokenData["user_id"]);

        $criticalMessage = "nada";

        if ( $appVersion == "0.0" )
        {
            $criticalMessage = "update";
        }

        $output = json_encode(array("response" => array("user_data" => $userData, "critical_message" => $criticalMessage), "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Posts a status update.
     */
    public function updatestatusAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $status = $payload['status'];
        $statusType = $payload['status_type'];

        $threadTable = new Scapes_Model_DbTable_Shthread();

        // Check if the new status is exactly like the previous one & it's been less than 24 hours. Prevent spam.
        $lastStatusData = $threadTable->getLatestGenericStatusUpdate($tokenData["user_id"]);

        if ( $lastStatusData["message"] == $status && time() <= strtotime($lastStatusData["timestamp_sent"]) + 86400 )
        {
            echo "while(1);" . json_encode(array("response" => "false", "errorMessage" => "New status is duplicate of the last one.", "errorCode" => 500));

            return;
        }

        $statusID = $threadTable->publishStatus($tokenData["user_id"], trim($status), $statusType);
        
        if ( $statusID )
        {
            $freshStatusData = $threadTable->getLatestGenericStatusUpdate($tokenData["user_id"]); // Return the whole data chunk.
            
            echo "while(1);" . json_encode(array("response" => $freshStatusData, "errorMessage" => "", "errorCode" => 0));
        }
        else
        {
            echo "while(1);" . json_encode(array("response" => "false", "errorMessage" => "Error posting status!", "errorCode" => 500));
        }
    }

    /*
     * Deletes a status update.
     */
    public function deletestatusAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $statusID = $payload['status_id'];
        $statusType = $payload['status_type'];

        $threadTable = new Scapes_Model_DbTable_Shthread();

        $lastStatusData = $threadTable->getLatestStatusUpdate($tokenData["user_id"]);

        if ( $lastStatusData["thread_type"] != 7 && $statusType != 7 )
        {
            $threadTable->deleteStatus($statusID);
    
            // The client needs to update itself with the last status before the one that just got deleted.
            $lastStatusData = $threadTable->getLatestGenericStatusUpdate($tokenData["user_id"]);

            echo "while(1);" . json_encode(array("response" => $lastStatusData, "errorMessage" => "", "errorCode" => 0));
        }
        else
        {
            echo "while(1);" . json_encode(array("response" => "false", "errorMessage" => "Error deleting status!", "errorCode" => 500));
        }
    }

    /*
     * Sets a user's presence/talking mask.
     */
    public function setusermaskAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $maskType = $payload['mask_type'];
        $mask = $payload['mask_value'];

        $userTable = new Scapes_Model_DbTable_Shuser();

        if ( $maskType == 1 ) // Presence
        {
            $userTable->setPresenceMask($tokenData["user_id"], $mask);
        }
        else if ( $maskType == 2 ) // Talking
        {
            $userTable->setTalkingMask($tokenData["user_id"], $mask);
        }

        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Gets a user's current online presence.
     */
    public function getuserpresenceAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $userID = $payload['user_id'];

        $presenceTable = new Scapes_Model_DbTable_Shuseronlinestatus();
        $blocklistTable = new Scapes_Model_DbTable_Shblocklist();

        // First check if the current user has been blocked by this contact.
        $userIsblocked = $blocklistTable->isBlocked($userID, $tokenData["user_id"]);

        if ( $userIsblocked )
        {
            $output = json_encode(array("response" => "", "errorMessage" => "", "errorCode" => 66));
        }
        else
        {
            $output = json_encode(array("response" => $presenceTable->getStatus($userID), "errorMessage" => "", "errorCode" => 0));
        }
        
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Gets the online presence of all followed users.
     */
    public function getallpresenceAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);

        if ( !$tokenData ) // No valid token found, return what the client sent.
        {
            echo "while(1);" . $tokenID;
            
            return;
        }

        $password = $tokenData["token"];
        
        $encryptor = new \RNCryptor\Encryptor();

        $presenceTable = new Scapes_Model_DbTable_Shuseronlinestatus();
        $statuses = $presenceTable->getStatusAll($tokenData["user_id"]);

        $output = json_encode(array("response" => $statuses, "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Returns all unread messages for a given user.
     */
    public function getlatestmessagesAction()
    {
        $tokenID = $_POST["scope"];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);

        if ( !$tokenData ) // No valid token found, return what the client sent.
        {
            echo "while(1);" . $tokenID;

            return;
        }

        $password = $tokenData["token"];
        
        $encryptor = new \RNCryptor\Encryptor();

        $userID = $tokenData["user_id"];

        $threadTable = new Scapes_Model_DbTable_Shthread();
        $unreadThreadCount = $threadTable->getTotalUnreadThreadCount($userID);
        
        if ( $unreadThreadCount == 0 ) // Client is up to date. Return a blank response.
        {
            $output = json_encode(array("response" => "false", "errorMessage" => "Up to date.", "errorCode" => 404));
            $base64Encrypted = $encryptor->encrypt($output, $password);
            
            echo "while(1);" . $base64Encrypted;
        }
        else // Return all messages that have not been read yet.
        {
            $unreadMessages = $threadTable->getAllUnreadMessagesForUserID($userID);

            foreach ( $unreadMessages as $key => $thread )
            {
                $thread["status_sent"] = 1;
                
                if ( !$thread["group_id"] )
                {
                    $thread["group_id"] = -1;
                }

                // Obfuscate location data of the other user's threads.
                if ( $thread["owner_id"] != $userID )
                {
                    if ( $thread["thread_type"] != 8 )
                    {
                        $thread["location_latitude"] = "";
                        $thread["location_longitude"] = "";
                    }
                }

                $unreadMessages[$key] = $thread;
            }

            $output = json_encode(array("response" => array("unread_thread_count" => $unreadThreadCount, "threads" => $unreadMessages), "errorMessage" => "", "errorCode" => 0));
            $base64Encrypted = $encryptor->encrypt($output, $password);
                        
            echo "while(1);" . $base64Encrypted;
        }
    }

    /*
     * Returns the last 20 messages of a conversation between 2 people.
     */
    public function getlastmessagesbetweenusersAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $recipientID = $payload['recipient_id'];
        $userID = $tokenData["user_id"];

        $threadTable = new Scapes_Model_DbTable_Shthread();
        $privacy = $threadTable->getPrivacyBetweenUsers($userID, $recipientID);
        $latestMessageBatch = $threadTable->getLastMessagesBetweenUsers($userID, $recipientID);

        foreach ( $latestMessageBatch as $key => $thread )
        {
            $thread["status_sent"] = 1;

            if ( !$thread["group_id"] )
            {
                $thread["group_id"] = -1;
            }

            // Obfuscate location data of the other user's threads.
            if ( $thread["owner_id"] != $userID )
            {
                if ( $thread["thread_type"] != 8 )
                {
                    $thread["location_latitude"] = "";
                    $thread["location_longitude"] = "";
                }
            }

            $latestMessageBatch[$key] = $thread;
        }

        $output = json_encode(array("response" => array("messages" => $latestMessageBatch, "privacy" => $privacy), "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
                    
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Deletes a thread along with all its children.
     */
    public function deletethreadAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $threadID = $payload['thread_id'];
        $userID = $tokenData["user_id"];

        $threadTable = new Scapes_Model_DbTable_Shthread();
        $threadTable->deleteThread($userID, $threadID);

        echo "while(1);" . json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
    }

    /*
     * Fetches Mini Feed entries.
     */
    public function downloadminifeedAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $threadTable = new Scapes_Model_DbTable_Shthread();
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

            echo "while(1);" . json_encode(array("response" => $updates, "errorMessage" => "", "errorCode" => 0));
        }
        else
        {
            echo "while(1);" . json_encode(array("response" => "false", "errorMessage" => "No statuses found!", "errorCode" => 404));
        }
    }

    /*
     * Scans a user's contacts and returns all the people they're following.
     */
    public function scanaddressbookAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $countryCallingCodes = json_decode($payload['country_calling_codes'], true);
        $prefixes = json_decode($payload['prefixes'], true);
        $phoneNumbers = json_decode($payload['phone_numbers'], true);

        if ( isset($phoneNumbers) && is_array($phoneNumbers) )
        {
            $userTable = new Scapes_Model_DbTable_Shuser();
            $phoneNumberTable = new Scapes_Model_DbTable_Shphonenumber();
            $followTable = new Scapes_Model_DbTable_Shfollow();
            $blocklistTable = new Scapes_Model_DbTable_Shblocklist();

            $followingUsers = $followTable->getFollowingList($tokenData["user_id"]);
            
            foreach ( $followingUsers as $userKey => $targetUser ) // Go through each user & attach their details.
            {
                $followingUser = $userTable->getUserByUserID($targetUser["followed_userid"]);

                // Set this flag.
                $followingUser["removed_by_user"] = $targetUser["removed_by_user"];

                // First check if the current user has been blocked by this contact.
                $userIsblocked = $blocklistTable->isBlocked($targetUser["followed_userid"], $tokenData["user_id"]);
                
                if ( $userIsblocked )
                {
                    $followingUser["blocked_by"] = 1;
                }
                else
                {
                    $followingUser["blocked_by"] = 0;
                }
    
                $userPhoneNumbers = $phoneNumberTable->getNumbersForUserID($followingUser["user_id"]);
                $phoneNumberPackages = array();
    
                foreach ( $userPhoneNumbers as $phoneNumberKey => $userPhoneNumber )
                {
                    $numberCountryCallingCode = $phoneNumberTable->getCountryCallingCode($userPhoneNumber["country_calling_code_id"]);
                    $numberPrefix = $userPhoneNumber["prefix"];
                    $number = $userPhoneNumber["number"];
    
                    $phoneNumberPackages[] = array("country_calling_code" => $numberCountryCallingCode, "prefix" => $numberPrefix, "phone_number" => $number);
                }
    
                $followingUser["phone_numbers"] = $phoneNumberPackages;
                unset($followingUser["passcodes"]); // Finally, weed out the passcodes. No need to return this.
    
                // Check if this person is blocked.
                $followingUserIsblocked = $blocklistTable->isBlocked($tokenData["user_id"], $targetUser["followed_userid"]);
                
                if ( $followingUserIsblocked )
                {
                    $followingUser["blocked"] = 1;
                }
                else
                {
                    $followingUser["blocked"] = 0;
                }
    
                $followingUsers[$userKey] = $followingUser;
            }

            foreach ( $phoneNumbers as $mainKey => $phoneNumber )
            {
                $user = $userTable->getUserByPhoneNumber($countryCallingCodes[$mainKey], $prefixes[$mainKey], $phoneNumber);

                if ( $user )
                {
                    $userID = $user["user_id"];

                    // First check if the current user has been blocked by this contact.
                    $userIsblocked = $blocklistTable->isBlocked($userID, $tokenData["user_id"]);
                    
                    if ( $userIsblocked )
                    {
                        $user["blocked_by"] = 1;
                    }
                    else
                    {
                        $user["blocked_by"] = 0;
                    }

                    $shouldAddToList = true;

                    foreach ( $followingUsers as $userKey => $targetUser )
                    {
                        if ( $targetUser["user_id"] == $userID || $userID == $tokenData["user_id"] ) // Already in the following list, or it's the user who requested this list.
                        {
                            $shouldAddToList = false;

                            break;
                        }
                    }

                    if ( $followTable->isRemovedByFollower($tokenData["user_id"], $userID) ) // Previously hidden by this user.
                    {
                        $shouldAddToList = false;
                    }
                    
                    if ( $shouldAddToList ) // This is a fresh follow.
                    {
                        $followTable->follow($tokenData["user_id"], $userID); // Subscribe to the contact's updates.

                        // Attach the phone numbers to send back.
                        $userPhoneNumbers = $phoneNumberTable->getNumbersForUserID($user["user_id"]);
                        $phoneNumberPackages = array();
        
                        foreach ( $userPhoneNumbers as $phoneNumberKey => $userPhoneNumber )
                        {
                            $numberCountryCallingCode = $phoneNumberTable->getCountryCallingCode($userPhoneNumber["country_calling_code_id"]);
                            $numberPrefix = $userPhoneNumber["prefix"];
                            $number = $userPhoneNumber["number"];
                            
                            $phoneNumberPackages[] = array("country_calling_code" => $numberCountryCallingCode, "prefix" => $numberPrefix, "phone_number" => $number);
                        }
        
                        $user["phone_numbers"] = $phoneNumberPackages;
                        unset($user["passcodes"]); // Finally, weed out the passcodes. No need to return this.
                        $user["blocked"] = 0; // Set the default value for the block flag.

                        $followingUsers[] = $user; // Add the contact's data to the array to return.
                    }
                }
                else
                {
                    // Contact is not a user. Add them to the master phone number table.
                    $userTable->logPotentialUser($countryCallingCodes[$mainKey], $prefixes[$mainKey], $phoneNumber, $tokenData["user_id"]);
                }
            }
        }

        $boardTable = new Scapes_Model_DbTable_Shboard();
        $boards = $boardTable->boardsForUser($tokenData["user_id"]);
        
        if ( $followingUsers || $boards )
        {
            echo "while(1);" . json_encode(array("response" => array("users" => $followingUsers, "boards" => $boards), "errorMessage" => "", "errorCode" => 0));
        }
        else
        {
            echo "while(1);" . json_encode(array("response" => "false", "errorMessage" => "No users found!", "errorCode" => 404));
        }
    }

    /*
     * Gets a list of people & boards the user might be interested in.
     */
    public function getrecommendationsAction()
    {
        $tokenID = $_POST["scope"];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);

        if ( !$tokenData ) // No valid token found.
        {
            return;
        }

        $boardTable = new Scapes_Model_DbTable_Shboard();
        $spottedTable = new Scapes_Model_DbTable_Shspotted();
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
        
        echo "while(1);" . json_encode(array("response" => array("users" => $users, "boards" => $boards), "errorMessage" => "", "errorCode" => 0));
    }

    /*
     * Gets a list of people following the user.
     */
    public function getfollowersAction()
    {
        $tokenID = $_POST["scope"];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);

        if ( !$tokenData ) // No valid token found.
        {
            return;
        }

        $followTable = new Scapes_Model_DbTable_Shfollow();
        $followers = $followTable->getFollowersListWithUserData($tokenData["user_id"]);

        echo "while(1);" . json_encode(array("response" => $followers, "errorMessage" => "", "errorCode" => 0));
    }

    /*
     * Gets a list of people following a specific user.
     */
    public function getfollowersforuserAction()
    {
        $tokenID = $_POST["scope"];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);

        if ( !$tokenData ) // No valid token found.
        {
            return;
        }

        $userID = $_POST["user_id"];

        $followTable = new Scapes_Model_DbTable_Shfollow();
        $followers = $followTable->getFollowersListWithUserData($userID);

        echo "while(1);" . json_encode(array("response" => $followers, "errorMessage" => "", "errorCode" => 0));
    }

    /*
     * Gets a list of people a specific user is following.
     */
    public function getfollowingforuserAction()
    {
        $tokenID = $_POST["scope"];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);

        if ( !$tokenData ) // No valid token found.
        {
            return;
        }

        $userID = $_POST["user_id"];

        $followTable = new Scapes_Model_DbTable_Shfollow();
        $following = $followTable->getFollowingListWithUserData($userID);

        echo "while(1);" . json_encode(array("response" => $following, "errorMessage" => "", "errorCode" => 0));
    }

    /*
     * Updates a user's profile info.
     */
    public function updateprofileAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);

        if ( !$tokenData ) // No valid token found, return what the client sent.
        {
            echo "while(1);" . $_POST["request"];
            
            return;
        }

        $decryptor = new \RNCryptor\Decryptor();

        $password = $tokenData["token"];
        
        // Decryption.
        $plaintext = $decryptor->decrypt($payload, $password); // The decrypted payload.
        $payload = json_decode($plaintext, true);

        $firstName = $payload['name_first'];
        $lastName = $payload['name_last'];
        $username = $payload['user_handle'];
        $gender = $payload['gender'];
        $email = $payload['email_address'];
        $birthday = $payload['birthday'];
        $location_country = $payload['location_country'];
        $location_state = $payload['location_state'];
        $location_city = $payload['location_city'];
        $bio = $payload['bio'];
        $website = $payload['website'];
        $facebookID = $payload['facebook_id'];
        $twitterID = $payload['twitter_id'];
        $instagramID = $payload['instagram_id'];

        $profileProcessor = new Scapes_Model_Profile();
        $profileProcessor->firstName = trim($firstName);
        $profileProcessor->lastName = trim($lastName);
        $profileProcessor->username = trim($username);
        $profileProcessor->gender = (trim($gender) != "") ? trim($gender) : NULL;
        $profileProcessor->email = (trim($email) != "") ? trim($email) : NULL;
        $profileProcessor->birthday = (trim($birthday) != "") ? trim($birthday) : NULL;
        $profileProcessor->location_country = (trim($location_country) != "") ? trim($location_country) : NULL;
        $profileProcessor->location_state = (trim($location_state) != "") ? trim($location_state) : NULL;
        $profileProcessor->location_city = (trim($location_city) != "") ? trim($location_city) : NULL;
        $profileProcessor->bio = (trim($bio) != "") ? trim($bio) : NULL;
        $profileProcessor->website = (trim($website) != "") ? trim($website) : NULL;
        $profileProcessor->facebookID = (trim($facebookID) != "") ? trim($facebookID) : NULL;
        $profileProcessor->twitterID = (trim($twitterID) != "") ? trim($twitterID) : NULL;
        $profileProcessor->instagramID = (trim($instagramID) != "") ? trim($instagramID) : NULL;
        $profileProcessor->userID = $tokenData["user_id"];

        if ( $profileProcessor->editProfile() )
        {
            $output =  json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        
            echo "while(1);" . $output;
        }
        else
        {
            $output =  json_encode(array("response" => "error!", "errorMessage" => $profileProcessor->profileErrors, "errorCode" => 1));
        
            echo "while(1);" . $output;
        }
    }

    /*
     * Gets the info of a single user.
     */
    public function getuserinfoAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);

        if ( !$tokenData ) // No valid token found, return what the client sent.
        {
            echo "while(1);" . $_POST["request"];
            
            return;
        }

        $decryptor = new \RNCryptor\Decryptor();

        $password = $tokenData["token"];
        
        // Decryption.
        $plaintext = $decryptor->decrypt($payload, $password); // The decrypted payload.
        $payload = json_decode($plaintext, true);

        $userID = $payload['user_id'];
        $fullProfile = $payload['full'];

        if ( $fullProfile )
        {
            $userTable = new Scapes_Model_DbTable_Shuser();
            $userData = $userTable->getUserByUserID($userID);

            $userPhoneNumbers = $phoneNumberTable->getNumbersForUserID($user);
            $phoneNumberPackages = array();

            foreach ( $userPhoneNumbers as $phoneNumberKey => $userPhoneNumber )
            {
                $numberCountryCallingCode = $phoneNumberTable->getCountryCallingCode($userPhoneNumber["country_calling_code_id"]);
                $numberPrefix = $userPhoneNumber["prefix"];
                $number = $userPhoneNumber["number"];

                $phoneNumberPackages[] = array("country_calling_code" => $numberCountryCallingCode, "prefix" => $numberPrefix, "phone_number" => $number);
            }

            $userData["phone_numbers"] = $phoneNumberPackages;
            unset($userData["passcodes"]); // Finally, weed out the passcodes. No need to return this.

            // Check if this person is blocked.
            $blocklistTable = new Scapes_Model_DbTable_Shblocklist();
            $userIsblocked = $blocklistTable->isBlocked($userID, $tokenData["user_id"]);
            
            if ( $userIsblocked )
            {
                $userData["blocked"] = 1;
            }
            else
            {
                $userData["blocked"] = 0;
            }
            
            $followTable = new Scapes_Model_DbTable_Shfollow();

            if ( $followTable->userFollowsUser($userID, $tokenData["user_id"]) )
            {
                $userData["follows_user"] = 1;
            }
            else
            {
                $userData["follows_user"] = 0;
                unset($userData["email_address"]);
            }

            $output = json_encode(array("response" => $userData, "errorMessage" => "", "errorCode" => 0));
            $base64Encrypted = $encryptor->encrypt($output, $password);
            
            echo "while(1);" . $base64Encrypted;
        }
        else
        {
            $followTable = new Scapes_Model_DbTable_Shfollow();
            $followerCount = $followTable->getFollowerCount($userID);
            $followingCount = $followTable->getFollowingCount($userID);

            $output = json_encode(array("response" => array("follower_count" => $followerCount, "following_count" => $followingCount), "errorMessage" => "", "errorCode" => 0));
            $base64Encrypted = $encryptor->encrypt($output, $password);
            
            echo "while(1);" . $base64Encrypted;
        }
    }

    /*
     * Gets the info of a list of users.
     */
    public function getusersinfoAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $userIDs = json_decode($payload['users'], true);

        $userTable = new Scapes_Model_DbTable_Shuser();
        $userData = array();

        foreach ( $userIDs as $key => $user )
        {
            $data = $userTable->getUserByUserID($user);

            $userPhoneNumbers = $phoneNumberTable->getNumbersForUserID($user);
            $phoneNumberPackages = array();

            foreach ( $userPhoneNumbers as $phoneNumberKey => $userPhoneNumber )
            {
                $numberCountryCallingCode = $phoneNumberTable->getCountryCallingCode($userPhoneNumber["country_calling_code_id"]);
                $numberPrefix = $userPhoneNumber["prefix"];
                $number = $userPhoneNumber["number"];

                $phoneNumberPackages[] = array("country_calling_code" => $numberCountryCallingCode, "prefix" => $numberPrefix, "phone_number" => $number);
            }

            $data["phone_numbers"] = $phoneNumberPackages;
            unset($data["passcodes"]); // Finally, weed out the passcodes. No need to return this.
            
            $userData[] = $data;
        }

        $output = json_encode(array("response" => $userData, "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Deletes (unfollows) a user.
     */
    public function deleteuserAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $userID = $payload['user_id'];

        $followTable = new Scapes_Model_DbTable_Shfollow();
        $followTable->unfollow($tokenData["user_id"], $userID);
        
        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Removes a user from recommendations.
     */
    public function removerecommendeduserAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $userID = $payload['user_id'];

        $spottedTable = new Scapes_Model_DbTable_Shspotted();
        $spottedTable->removeSpot($tokenData["user_id"], $userID);
        
        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Blocks a user.
     */
    public function blockuserAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $userID = $payload['user_id'];

        $blocklistTable = new Scapes_Model_DbTable_Shblocklist();
        $blocklistTable->block($tokenData["user_id"], $userID);
        
        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Unblocks a user.
     */
    public function unblockuserAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $userID = $payload['user_id'];

        $blocklistTable = new Scapes_Model_DbTable_Shblocklist();
        $blocklistTable->unblock($tokenData["user_id"], $userID);
        
        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Re-adds (or follows) a user.
     */
    public function adduserAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $userID = $payload['user_id'];
        $userTable = new Scapes_Model_DbTable_Shuser();
        $userData = $userTable->getUserByUserID($userID);

        if ( $userData )
        {
            $adderName = $userData["name_first"] . " " . $userData["name_last"];

            $phoneNumberTable = new Scapes_Model_DbTable_Shphonenumber();
            $userPhoneNumbers = $phoneNumberTable->getNumbersForUserID($userID);
            $phoneNumberPackages = array();

            foreach ( $userPhoneNumbers as $phoneNumberKey => $userPhoneNumber )
            {
                $numberCountryCallingCode = $phoneNumberTable->getCountryCallingCode($userPhoneNumber["country_calling_code_id"]);
                $numberPrefix = $userPhoneNumber["prefix"];
                $number = $userPhoneNumber["number"];

                $phoneNumberPackages[] = array("country_calling_code" => $numberCountryCallingCode, "prefix" => $numberPrefix, "phone_number" => $number);
            }

            $userData["phone_numbers"] = $phoneNumberPackages;
            unset($userData["passcodes"]); // Finally, weed out the passcodes. No need to return this.

            // Check if the newly-added person has blocked this user.
            $blocklistTable = new Scapes_Model_DbTable_Shblocklist();
            $userIsblocked = $blocklistTable->isBlocked($userID, $tokenData["user_id"]);
            $userData["blocked"] = 0;

            if ( $userIsblocked )
            {
                $userData["presence"] = 14;
                $userData["presence_target"] = -1;
                $userData["presence_audience"] = 1;
                $userData["presence_timestamp"] = gmdate("Y-m-d H:i:s", time());
            }
            else
            {
                // Notify this person.
                $applePushProcessor = new Scapes_Model_ApplePush(
                                            "{$adderName} added you as a contact",
                                            $tokenData["user_id"],
                                            array("type" => "new_follower", "user_id" => $userID));
    
                $applePushProcessor->dispatchNotif();
            }

            $followTable = new Scapes_Model_DbTable_Shfollow();
            $followTable->follow($tokenData["user_id"], $userID);
            
            // Check if this person is already following them back.
            $userFollowsAdder = $followTable->userFollowsUser($userID, $adderID);
            
            if ( $userFollowsAdder ) // If so, delete the peer entry from the DB.
            {
                $spottedTable = new Scapes_Model_DbTable_Shspotted();
                $spottedTable->removeSpot($adderID, $userID);

                $userData["follows_user"] = 1;
            }
            else
            {
                $userData["follows_user"] = 0;
            }

            $output = json_encode(array("response" => $userData, "errorMessage" => "", "errorCode" => 0));
            $base64Encrypted = $encryptor->encrypt($output, $password);
            
            echo "while(1);" . $base64Encrypted;
        }
        else
        {
            $output = json_encode(array("response" => "false", "errorMessage" => "user not found!", "errorCode" => 404));
            $base64Encrypted = $encryptor->encrypt($output, $password);
            
            echo "while(1);" . $base64Encrypted;
        }
    }

    /*
     * Adds a user by their user handle.
     */
    public function addbyusernameAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);

        if ( !$tokenData ) // No valid token found, return what the client sent.
        {
            echo "while(1);" . $_POST["request"];
            
            return;
        }

        $encryptor = new \RNCryptor\Encryptor();
        $decryptor = new \RNCryptor\Decryptor();

        $password = $tokenData["token"];
        
        // Decryption.
        $plaintext = $decryptor->decrypt($payload, $password); // The decrypted payload.
        $payload = json_decode($plaintext, true);

        $username = $payload['username'];
        $userTable = new Scapes_Model_DbTable_Shuser();
        $userData = $userTable->getUserByUsername($username);
        
        if ( $userData )
        {
            $userID = $userData["user_id"];
            $adderName = $userData["name_first"] . " " . $userData["name_last"];

            $phoneNumberTable = new Scapes_Model_DbTable_Shphonenumber();
            $userPhoneNumbers = $phoneNumberTable->getNumbersForUserID($userID);
            $phoneNumberPackages = array();

            foreach ( $userPhoneNumbers as $phoneNumberKey => $userPhoneNumber )
            {
                $numberCountryCallingCode = $phoneNumberTable->getCountryCallingCode($userPhoneNumber["country_calling_code_id"]);
                $numberPrefix = $userPhoneNumber["prefix"];
                $number = $userPhoneNumber["number"];

                $phoneNumberPackages[] = array("country_calling_code" => $numberCountryCallingCode, "prefix" => $numberPrefix, "phone_number" => $number);
            }

            $userData["phone_numbers"] = $phoneNumberPackages;
            unset($userData["passcodes"]);

            // Check if the newly-added person has blocked this user.
            $blocklistTable = new Scapes_Model_DbTable_Shblocklist();
            $userIsblocked = $blocklistTable->isBlocked($userID, $tokenData["user_id"]);
            $userData["blocked"] = 0;
            
            if ( $userIsblocked )
            {
                $userData["presence"] = 14;
                $userData["presence_target"] = -1;
                $userData["presence_audience"] = 1;
                $userData["presence_timestamp"] = gmdate("Y-m-d H:i:s", time());
            }
            else
            {
                // Notify this person.
                $applePushProcessor = new Scapes_Model_ApplePush(
                                            "{$adderName} added you as a contact",
                                            $tokenData["user_id"],
                                            array("type" => "new_follower", "user_id" => $userID));
    
                $applePushProcessor->dispatchNotif();
            }

            $followTable = new Scapes_Model_DbTable_Shfollow();
            $followTable->follow($tokenData["user_id"], $userID);
            
            // Check if this person is already following them back.
            $userFollowsAdder = $followTable->userFollowsUser($userID, $adderID);
            
            if ( $userFollowsAdder ) // If so, delete the peer entry from the DB.
            {
                $spottedTable = new Scapes_Model_DbTable_Shspotted();
                $spottedTable->removeSpot($adderID, $userID);

                $userData["follows_user"] = 1;
            }
            else
            {
                $userData["follows_user"] = 0;
            }

            $output =  json_encode(array("response" => $userData, "errorMessage" => "", "errorCode" => 0));
            $base64Encrypted = $encryptor->encrypt($output, $password);
            
            echo "while(1);" . $base64Encrypted;
        }
        else
        {
            // User not found!
            $output =  json_encode(array("response" => "false", "errorMessage" => "user not found!", "errorCode" => 404));
            $base64Encrypted = $encryptor->encrypt($output, $password);
                
            echo "while(1);" . $base64Encrypted;
        }
    }

    /*
     * Uploads a new user DP.
     */
    public function dpuploadAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        $imageFile = $_FILES["imageFile"];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $keyword = $payload['dummy'];

        if ( $keyword == "mum's the word" )
        {
            $photoProcessor = new Scapes_Model_Photo();
            $processorResponse = $photoProcessor->savePicture($tokenData["user_id"], $imageFile);
            
            if ( !preg_match("/[a-f0-9]{40,40}/", $processorResponse) )
            {
                $output = json_encode(array("response" => "", "errorMessage" => "Error uploading pic!", "errorCode" => 504));
                $base64Encrypted = $encryptor->encrypt($output, $password);
                
                echo "while(1);" . $base64Encrypted;
                die;
            }
            
            $userTable = new Scapes_Model_DbTable_Shuser();
            $threadTable = new Scapes_Model_DbTable_Shthread();

            $oldHash = $userTable->getCurrentDPHash($tokenData["user_id"]);

            if ( $oldHash )
            {
                $didDeleteOldDP = $photoProcessor->deletePicture($tokenData["user_id"], $oldHash);
            }

            $userTable->savePicture($tokenData["user_id"], $processorResponse);
            $threadTable->publishStatus($tokenData["user_id"], "🌅 has a new picture.", 5);
            
            $output = json_encode(array("response" => $processorResponse, "errorMessage" => "", "errorCode" => 0));
            $base64Encrypted = $encryptor->encrypt($output, $password);
            
            echo "while(1);" . $base64Encrypted;
        }
    }

    /*
     * Removes a user's DP.
     */
    public function dpremoveAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $keyword = $payload['dummy'];

        if ( $keyword == "mum's the word" )
        {
            $photoProcessor = new Scapes_Model_Photo();
            $userTable = new Scapes_Model_DbTable_Shuser();
            $threadTable = new Scapes_Model_DbTable_Shthread();

            $oldHash = $userTable->getCurrentDPHash($tokenData["user_id"]);
            $didDeleteOldDP = $photoProcessor->deletePicture($tokenData["user_id"], $oldHash);

            $userTable->deletePicture($tokenData["user_id"]);
            $threadTable->deleteStatusesOfType($tokenData["user_id"], 5);

            $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
            $base64Encrypted = $encryptor->encrypt($output, $password);
            
            echo "while(1);" . $base64Encrypted;
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

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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
            $photoProcessor = new Scapes_Model_Photo();
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

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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
        
        $photoProcessor = new Scapes_Model_Photo();
        $photoProcessor->deleteMediaPhoto($tokenData["user_id"], $mediaHash);
        
        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Uploads a new board cover photo.
     */
    public function boardcoveruploadAction()
    {
        $imageFile = $_FILES["image_file"];

        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $boardID = $payload['board_id'];
        $photoProcessor = new Scapes_Model_Photo();
        $processorResponse = $photoProcessor->saveBoardCover($boardID, $imageFile);
        
        if ( !preg_match("/[a-f0-9]{40,40}/", $processorResponse) )
        {
            echo json_encode(array("response" => "", "errorMessage" => "Error uploading pic!", "errorCode" => 504));
            die;
        }
        
        $boardTable = new Scapes_Model_DbTable_Shboard();
        $oldHash = $boardTable->getCurrentCoverHash($boardID);

        if ( $oldHash )
        {
            $didDeleteOldDP = $photoProcessor->deleteBoardCover($boardID, $oldHash);
        }

        $boardTable->saveCoverHash($boardID, $processorResponse);
        
        $output = json_encode(array("response" => $processorResponse, "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Removes a board's cover photo.
     */
    public function boardcoverremoveAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $boardID = $payload['board_id'];
        $photoProcessor = new Scapes_Model_Photo();
        $boardTable = new Scapes_Model_DbTable_Shboard();

        $oldHash = $boardTable->getCurrentCoverHash($boardID);
        $didDeleteOldDP = $photoProcessor->deleteBoardCover($boardID, $oldHash);

        $boardTable->deleteCurrentCoverHash($boardID);

        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Creates a new board.
     */
    public function createboardAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $name = $payload['name'];
        $privacy = $payload['privacy'];

        $boardTable = new Scapes_Model_DbTable_Shboard();
        $boardID = $boardTable->createBoard($name, $privacy);
        
        // Add the creator as the first member.
        $boardTable->join($tokenData["user_id"], $boardID);

        $output = json_encode(array("response" => $boardID, "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Gets a board's data.
     */
    public function getboardinfoAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $boardID = $payload['board_id'];

        $boardTable = new Scapes_Model_DbTable_Shboard();
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

        $output = json_encode(array("response" => $boardData, "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Updates a board's info.
     */
    public function updateboardinfoAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $boardID = $payload['board_id'];
        $name = $payload['name'];
        $description = $payload['description'];
        $privacy = $payload['privacy'];

        $boardTable = new Scapes_Model_DbTable_Shboard();
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
                $applePushProcessor = new Scapes_Model_ApplePush(
                                            "Your request to join {$boardName} was accepted",
                                            $request["user_id"],
                                            array("type" => "board_request", "board_id" => $boardID));
                
                $applePushProcessor->dispatchNotif();
            }
        }

        $boardTable->updateBoard($boardID, $data);

        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Gets all pending join requests for a board.
     */
    public function getboardrequestsAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $boardID = $payload['board_id'];

        $boardTable = new Scapes_Model_DbTable_Shboard();
        $requests = $boardTable->getRequests($tokenData["user_id"], $boardID);

        $output = json_encode(array("response" => $requests, "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Gets all board members.
     */
    public function getboardmembersAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $boardID = $payload['board_id'];

        $boardTable = new Scapes_Model_DbTable_Shboard();
        $members = $boardTable->getMemberListWithUserData($tokenData["user_id"], $boardID);

        echo "while(1);" . json_encode(array("response" => $members, "errorMessage" => "", "errorCode" => 0));
    }

    /*
     * Adds the user as a member of a board.
     */
    public function joinboardAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $boardID = $payload['board_id'];

        $boardTable = new Scapes_Model_DbTable_Shboard();
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

            $userTable = new Scapes_Model_DbTable_Shuser();
            $userInfo = $userTable->getUserByUserID($tokenData["user_id"]);
            $userName = $userInfo["name"];
            
            foreach ( $boardMembers as $key => $member )
            {
                // Notify this person.
                $applePushProcessor = new Scapes_Model_ApplePush(
                                            "{$userName} would like to join {$boardName}",
                                            $member["user_id"],
                                            array("type" => "board_request", "board_id" => $boardID));
                
                $applePushProcessor->dispatchNotif();
            }
        }

        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Confirms a user's board request.
     */
    public function acceptboardrequestAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $boardID = $payload['board_id'];
        $newMemberID = $payload['user_id'];

        $boardTable = new Scapes_Model_DbTable_Shboard();
        $userIsMember = $boardTable->userIsMemberOfBoard($tokenData["user_id"], $boardID);

        if ( $userIsMember )
        {
            $boardTable->join($newMemberID, $boardID);

            $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
            $base64Encrypted = $encryptor->encrypt($output, $password);
            
            echo "while(1);" . $base64Encrypted;
        }
        else
        {
            $output = json_encode(array("response" => "you're not a member to allow access.", "errorMessage" => "private board!", "errorCode" => 1));
            $base64Encrypted = $encryptor->encrypt($output, $password);
            
            echo "while(1);" . $base64Encrypted;
        }
    }

    /*
     * Declines a user's board request.
     */
    public function declineboardrequestAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $boardID = $payload['board_id'];
        $newMemberID = $payload['user_id'];

        $boardTable = new Scapes_Model_DbTable_Shboard();
        $userIsMember = $boardTable->userIsMemberOfBoard($tokenData["user_id"], $boardID);

        if ( $userIsMember )
        {
            $boardTable->cancelRequest($newMemberID, $boardID);

            $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
            $base64Encrypted = $encryptor->encrypt($output, $password);
            
            echo "while(1);" . $base64Encrypted;
        }
        else
        {
            $output = json_encode(array("response" => "you're not a member to deny access.", "errorMessage" => "private board!", "errorCode" => 1));
            $base64Encrypted = $encryptor->encrypt($output, $password);
            
            echo "while(1);" . $base64Encrypted;
        }
    }

    /*
     * Removes the user as a member of a board.
     */
    public function leaveboardAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $boardID = $payload['board_id'];

        $boardTable = new Scapes_Model_DbTable_Shboard();
        $boardData = $boardTable->getInfo($tokenData["user_id"], $boardID);

        // Don't leave dead cover photos lying around. Clean up.
        if ( $boardData["cover_hash"] )
        {
            $photoProcessor = new Scapes_Model_Photo();
            $photoProcessor->deleteBoardCover($boardID, $boardData["cover_hash"]);
        }

        $boardTable->leave($tokenData["user_id"], $boardID);
        $memberCount = $boardTable->getMemberCount($boardID);

        // Once a board has no members, it gets removed.
        if ( $memberCount == 0 )
        {
            $boardTable->deleteBoard($boardID);
        }

        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Creates a new board post.
     */
    public function createboardpostAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $boardID = $payload['board_id'];
        $text = $payload['text'];
        $color = $payload['color'];
        $mediaHash = $payload['media_hash'];

        $data = array("owner_id" => $tokenData["user_id"],
                      "text" => $text,
                      "color" => $color,
                      "media_hash" => $mediaHash);

        $boardTable = new Scapes_Model_DbTable_Shboard();
        $postID = $boardTable->postToBoard($boardID, $data);

        $output = json_encode(array("response" => $postID, "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Edits an existing board post.
     */
    public function editboardpostAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $postID = $payload['post_id'];
        $text = $payload['text'];

        $boardTable = new Scapes_Model_DbTable_Shboard();
        $postID = $boardTable->editPost($postID, $text);

        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Deletes a board post.
     */
    public function deleteboardpostAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $threadID = $payload['post_id'];
            
        $boardTable = new Scapes_Model_DbTable_Shboard();
        $boardTable->deletePost($threadID);

        $output = json_encode(array("response" => $postID, "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Registers a view on a post.
     */
    public function recordboardpostviewAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $threadID = $payload['post_id'];
    
        $boardTable = new Scapes_Model_DbTable_Shboard();
        $boardTable->registerView($tokenData["user_id"], $threadID);
        
        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    /*
     * Returns all posts containing the given hashtag.
     */
    public function searchboardpostsAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $boardID = $payload['board_id'];
        $hashtag = $payload['hashtag'];

        $boardTable = new Scapes_Model_DbTable_Shboard();
        $results = $boardTable->searchForHashtag("#" . $hashtag, $boardID);
        
        echo json_encode(array("response" => $results, "errorMessage" => "", "errorCode" => 0));
    }

    /*
     * Saves a user's hashtag list so they can be notified about them.
     */
    public function savehashtaglistAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $list = json_decode($payload['hashtag_list'], true);
    
        $hashtagTable = new Scapes_Model_DbTable_Shhashtag();
        $hashtagTable->removeAllHashtagsForUser($tokenData["user_id"]);
        
        foreach ( $list as $key => $hashtag )
        {
            $hashtagTable->addHashtag($tokenData["user_id"], $hashtag);
        }
    
        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $password);
        
        echo "while(1);" . $base64Encrypted;
    }

    public function getcountrylistAction()
    {
        $phoneNumberTable = new Scapes_Model_DbTable_Shphonenumber();
        $countryList = $phoneNumberTable->getCountryList();
        $detailedCountrylist = array();
        
        if ( $countryList )
        {
            echo "while(1);" . json_encode(array("response" => $countryList, "errorMessage" => "", "errorCode" => 0));
        }
        else
        {
            echo "while(1);" . json_encode(array("response" => "false", "errorMessage" => "Error fetching country list!", "errorCode" => 1));
        }
    }

    /*
     * Checks if an account is password-locked.
     */
    public function checkforpasswordAction()
    {
        $tokenID = $_POST["scope"];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $userTable = new Scapes_Model_DbTable_Shuser();

        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);;

        if ( !$tokenData ) // No valid token found, return what the client sent.
        {
            echo "while(1);" . $tokenID;
            
            return;
        }

        $userHasPassword = $userTable->userHasPassword($tokenData["user_id"]);

        if ( $userHasPassword )
        {
            echo "while(1);" . json_encode(array("response" => "", "errorMessage" => "", "errorCode" => 0));
        }
        else
        {
            echo "while(1);" . json_encode(array("response" => "false", "errorMessage" => "No password found!", "errorCode" => 404));
        }
    }

    /*
     * Locks down an account with a password.
     */
    public function setpasswordAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $newPassword = $payload['password'];

        $lengthCheck = new Zend_Validate_StringLength();
        $lengthCheck->setMin(8);

        if ( !$lengthCheck->isValid($newPassword) )
        {
            // Password length error.
            echo "while(1);" . json_encode(array("response" => "", "errorMessage" => "Password is too short!", "errorCode" => 1));
        }
        else
        {
            // Sucessful password update.
            $userTable = new Scapes_Model_DbTable_Shuser();
            $userTable->updatePassword($tokenData["user_id"], Model_Lib_Func::saltedSha1($newPassword));
            echo "while(1);" . json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
    }

    public function addpasscodeAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $passcode = $payload['passcode'];

        $userTable = new Scapes_Model_DbTable_Shuser();
        $passcodeTable = new Scapes_Model_DbTable_Shpasscode();

        $passcodeTable->addPasscode($tokenData["user_id"], $tokenData["token_id"], $passcode);
        $userTable->lockAccount($tokenData["user_id"]);

        echo "while(1);" . json_encode(array("response" => "", "errorMessage" => "", "errorCode" => 0));
    }

    public function changepasscodeAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $newPasscode = $payload['passcode'];

        $passcodeTable = new Scapes_Model_DbTable_Shpasscode();
        $passcodeTable->changePasscodeForTokenID($tokenData["token_id"], $newPasscode);

        echo "while(1);" . json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
    }

    public function removepasscodeAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $passcode = $payload['passcode'];

        $passcodeTable = new Scapes_Model_DbTable_Shpasscode();
        $passcodesLeft = $passcodeTable->deletePasscodeByPasscode($passcode, $tokenData["user_id"]);

        if ( $passcodesLeft == 0 )
        {
            $userTable = new Scapes_Model_DbTable_Shuser();
            $userTable->unlockAccount($tokenData["user_id"]);
        }

        echo "while(1);" . json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
    }

    /*
     * This function is used when a user activates a passcode lock.
     */
    public function lockaccountAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $passcode = $payload['passcode'];

        $userTable = new Scapes_Model_DbTable_Shuser();
        $userTable->lockAccount($tokenData["user_id"]);

        echo "while(1);" . json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
    }

    /*
     * This function is used when a user activates a passcode lock.
     */
    public function purchaselisenceannaualAction()
    {
        $input = json_decode($_POST["request"], true);
        $tokenID = $_POST["scope"];
        $payload = $input['payload'];
        
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
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

        $licenseType = $payload['license_type'];

        if ( strlen($licenseType) > 0 )
        {
            $licenseTable = new Scapes_Model_DbTable_Shlicense();
            $licenseTable->addLicense($tokenData["user_id"], $licenseType);

            echo "while(1);" . json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        }
        else
        {
            echo "while(1);" . json_encode(array("response" => "", "errorMessage" => "invalid request!", "errorCode" => 1));
        }
    }
}

?>