<?php

/**
 * Scapes API calls
 *
 * @copyright  2013 Scapehouse
 */
class Scapes_ApiController extends Zend_Controller_Action
{
    public function init() {

        /* Initialize action controller here */

        // No layout
        $this->_helper->layout->disableLayout();
        // No view ---
        $this->_helper->viewRenderer->setNoRender(true);

        $GLOBALS["batchSize"] = 15;

        //Check access token for all requests except those specified uploadpicture

        if (
                $this->_request->getActionName() != "login" &&
                $this->_request->getActionName() != "logout" &&
                $this->_request->getActionName() != "signup" &&
                $this->_request->getActionName() != "tipticker" &&
                $this->_request->getActionName() != "forgotpassword" &&
                $this->_request->getActionName() != "userexists"
            )
        {

            $token = $_POST["token"];
            $tbaccesstokenTable = new Scapes_Model_DbTable_Tbaccesstoken();
            $tokenExists = $tbaccesstokenTable->getTokenByToken($token);

//            if (!$tokenExists) {
//                echo json_encode(array("responce" => "false", "errormsg" => "authFail", "error" => 1));
//                die;
//            }
        }
    }

    public function pokeserverAction() {

        include "../fb/src/facebook.php";
        $token = $_POST["token"];
        $appVer = $_POST["appVer"];
        $deviceToken = $_POST["deviceToken"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        //Device token update...

        if (trim($deviceToken) == "" || strlen($deviceToken) == 64) {

            if ($tokenData["id"] && strlen(trim($deviceToken)) == 64) {
                // Connect to the Apple token table and log token
                $tbappletokenTable = new Tipbox_Model_DbTable_Tbappletoken();
                $tbappletokenTable->logToken($tokenData["id"], $tokenData["userid"], $deviceToken);
            }
        }


        $tb_facebook = new Tipbox_Model_DbTable_Tbfacebook();
        $fbOutput = $tb_facebook->getTokenByUserid($tokenData["userid"]);

        if ($fbOutput["connected"] == 1 && (86400 <= (strtotime($fbOutput["exptime"]) - time()))) {

            $config = array();
            $config['appId'] = '278585982224213';
            $config['secret'] = 'ee20e733a6f53658ae3a7822240f8fbc';

            $facebook = new Facebook($config);

            $newTokenResponce = $facebook->getExtendedAccessToken($fbOutput["token"]);

            //Log FB Token
            $tb_facebook->updateToken($fbOutput["token"], $newTokenResponce["access_token"], $newTokenResponce["expires"] + time(), 1);
        }

        $critMsg = "false";

        if ($appVer != "1.1.0" && $appVer != "1.0.1") {// BETA Temp msg
            $critMsg = "You're using an outdated version of Tipbox! Some things might not work properly anymore. Go to the App Store to get the latest version.";
        }

        echo json_encode(array("responce" => array("appid" => 551452025, "critUp" => "false", "critMsg" => $critMsg), "errormsg" => "", "error" => 0));
    }

    // FACEBOOK ACTIONS
    public function logfbtokenAction() {

        $token = $_POST["token"];
        $fbid = $_POST["fbid"];
        $fbToken = $_POST["fbToken"];
        $fbTokenExp = $_POST["fbTokenExp"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $tb_facebook = new Tipbox_Model_DbTable_Tbfacebook();
        $fbOutput = $tb_facebook->getTokenByFbid($fbid);

        if ($fbOutput) {// If there is an entry
            if ($fbOutput["connected"] === 0) { // and that entry is disconnected
                $tb_facebook->updateToken($fbOutput["token"], $fbToken, $fbTokenExp, 1);
            }
        } else {// If there is no FB token entry in the 1st place
            $tb_facebook->logToken($tokenData["userid"], $fbToken, $fbid, $fbTokenExp);
        }

        echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
    }

    public function logtwttokenAction() {

        $token = $_POST["token"];
        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $twtToken = $_POST["twtToken"];
        $twtid = $_POST["twtid"];
        $twtTokenSec = $_POST["twtTokenSec"];
        $twtUsername = $_POST["twtUsername"];

        $tb_twitter = new Tipbox_Model_DbTable_Tbtwitter();
        $twtOutput = $tb_twitter->getTokenByTwtid($twtid);

        if (!$twtOutput) {
            $tb_twitter = new Tipbox_Model_DbTable_Tbtwitter();
            $tb_twitter->logToken($tokenData["userid"], $twtUsername, $twtToken, $twtid, $twtTokenSec);
        }

        echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0)); // Success       
    }

    public function importtwtpicAction() {


        $token = $_POST["token"];
        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);
        $image["picture"] = str_replace("normal", "bigger", $_POST["twtPic"]);

// Resizer ------

        if (strstr(basename($image["picture"]), "png")) {
            if (!is_dir("userphotos/{$tokenData["userid"]}/profile/")) {
                mkdir("userphotos/{$tokenData["userid"]}/profile/", 0777, true);
            }

            $getImage = new Model_Lib_GetImage();
            $getImage->quality = "100";
            $getImage->save_to = "userphotos/{$tokenData["userid"]}/profile/";
            $getImage->source = $image["picture"];

            $getImage->download();

            $fullImageName = basename($image["picture"]);
            $hashedName = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));

            //Store pichash
            $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
            $tbuserTable->savePicture($tokenData["userid"], $hashedName);

            if (rename("userphotos/{$tokenData["userid"]}/profile/{$fullImageName}", "userphotos/{$tokenData["userid"]}/profile/f_{$hashedName}.jpg")) {

                include "../wideimage/WideImage.php";

                $photoFull = WideImage::load("userphotos/{$tokenData["userid"]}/profile/f_{$hashedName}.jpg");

                $photoFull = $photoFull->resize(180, 180, 'outside', 'down');
                $photoFull = $photoFull->crop('center', 'center', 180, 180);

                $photoFull->saveToFile("userphotos/{$tokenData["userid"]}/profile/m_{$hashedName}.jpg", "100");
            }
        }

        echo json_encode(array("responce" => $hashedName, "errormsg" => "", "error" => 0)); // Success
        // Grab Twitter picture END...
    }

    public function importfbpicAction() {

        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $tb_facebook = new Tipbox_Model_DbTable_Tbfacebook();
        $fbOutput = $tb_facebook->getTokenByUserid($tokenData["userid"]);

        if ($fbOutput["connected"] == 1) {

            // Grab picture from Facebook
            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, "http://graph.facebook.com/{$fbOutput["fbid"]}/?fields=picture&type=large");
            curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            $buffer = curl_exec($curl_handle);
            curl_close($curl_handle);

            if (empty($buffer)) {

                $curl_handle = curl_init();
                curl_setopt($curl_handle, CURLOPT_URL, "http://graph.facebook.com/{$fbOutput["fbid"]}/?fields=picture&type=large");
                curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                $buffer = curl_exec($curl_handle);
                curl_close($curl_handle);

                if (!empty($buffer)) {
                    goto fetch;
                } else {
                    echo json_encode(array("responce" => "false", "errormsg" => "Unable to get picture", "error" => 1)); // Failure
                }
            } else {

                fetch: // Go to marker.

                $image = (array) json_decode($buffer);

// Resizer ------

                if (strstr(basename($image["picture"]), "jpg")) {
                    if (!is_dir("userphotos/{$tokenData["userid"]}/profile/")) {
                        mkdir("userphotos/{$tokenData["userid"]}/profile/", 0777, true);
                    }

                    $getImage = new Model_Lib_GetImage();
                    $getImage->quality = "100";
                    $getImage->save_to = "userphotos/{$tokenData["userid"]}/profile/";
                    $getImage->source = $image["picture"];

                    $getImage->download();

                    $fullImageName = basename($image["picture"]);
                    $hashedName = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));

                    //Store pichash
                    $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
                    $tbuserTable->savePicture($tokenData["userid"], $hashedName);

                    if (rename("userphotos/{$tokenData["userid"]}/profile/{$fullImageName}", "userphotos/{$tokenData["userid"]}/profile/f_{$hashedName}.jpg")) {

                        include "../wideimage/WideImage.php";

                        $photoFull = WideImage::load("userphotos/{$tokenData["userid"]}/profile/f_{$hashedName}.jpg");

                        $photoFull = $photoFull->resize(180, 180, 'outside', 'down');
                        $photoFull = $photoFull->crop('center', 'center', 180, 180);

                        $photoFull->saveToFile("userphotos/{$tokenData["userid"]}/profile/m_{$hashedName}.jpg", "100");
                    }
                }

                echo json_encode(array("responce" => $hashedName, "errormsg" => "", "error" => 0)); // Success
            }
        }

        // Grab Facebook picture END...
    }

    // FACEBOOK ACTIONS END
    // TIP ACTIONS
    public function createtipAction() {

        //https://graph.facebook.com/machosx/tipboxapp:leave?tip=http://scapehouse.com/tipbox/tip/23342&tip_text=hello&access_token=278585982224213%7C03U23qbfMWtN-oTGEF4_ncvJ2fs&method=post
        if ($_POST) {

            $content = Model_Lib_Func::stripExtraSpace($_POST["content"]);
            $topicContent = Model_Lib_Func::stripExtraSpace(trim($_POST["topicContent"]));
            $topicid = $_POST["topicid"];
            $catid = trim($_POST["catid"]);
            $token = $_POST["token"];
            $location_lat = ($_POST["location_lat"] == 9999) ? NULL : $_POST["location_lat"];
            $location_long = ($_POST["location_long"] == 9999) ? NULL : $_POST["location_long"];
            $fbPost = $_POST["fbPost"];
            $twtPost = $_POST["twtPost"];

            $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
            $tokenData = $tbaccesstokenTable->getTokenByToken($token);

            //Error checking topic, tip and cat

            if ($catid == "") {// Blank category check
                echo json_encode(array("responce" => "false", "errormsg" => "Category is blank", "error" => 1));
                die;
            }

            if ($content == "") {// Blank tip check
                echo json_encode(array("responce" => "false", "errormsg" => "Tip is blank", "error" => 1));
                die;
            }

            if ($topicContent == "") {// Blank topic check
                echo json_encode(array("responce" => "false", "errormsg" => "Topic is blank", "error" => 1));
                die;
            }

            if ($topicid == 0) {

                if (mb_strlen($topicContent, "UTF8") > 32) { // Topic length check
                    echo json_encode(array("responce" => "false", "errormsg" => "Topic over 32 characters", "error" => 1));
                    die;
                }

                $tbTopicTable = new Tipbox_Model_DbTable_Tbtopic();
                $topicid = $tbTopicTable->createTopic($tokenData["userid"], $topicContent);
            }

            // Create tip in DB

            include "../parseUrl/parseUrlBitly.php";

            $bitlyedTip = make_bitly($content);
            $tbTipTable = new Tipbox_Model_DbTable_Tbtip();
            $newTipid = $tbTipTable->createTip($tokenData["userid"], $bitlyedTip, $topicid, $catid, $location_lat, $location_long);

            if ($fbPost == 1) {// Post to Facebook
                $tb_facebook = new Tipbox_Model_DbTable_Tbfacebook();
                $fbOutput = $tb_facebook->getTokenByUserid($tokenData["userid"]);

                if ($fbOutput["connected"] == 1) {

                    $tip = $tbTipTable->getTipbyId($newTipid);
                    $tip[0]["content"] = $bitlyedTip;

                    $tbTipcardProcessor = new Tipbox_Model_Tipcard();
                    $hashedName = $tbTipcardProcessor->createTipCard($tip[0]);

                    $image = '@' . realpath("../tmp/tipcard_{$hashedName}.png");

                    $params = array('access_token' => $fbOutput["token"], 'message' => "A tip on {$tip[0]["topicContent"]}: \n\n" . $bitlyedTip . " - (http://scapehouse.com/tipbox/tip/{$newTipid})", 'source' => $image);

                    $url = "https://graph.facebook.com/{$fbOutput["fbid"]}/photos";

                    $ch = curl_init();

                    curl_setopt_array($ch, array(
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_URL => $url,
                        CURLOPT_POSTFIELDS => $params,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_VERBOSE => true
                    ));

                    $result = curl_exec($ch);

                    unlink("../tmp/tipcard_{$hashedName}.png");

// GRAPH POSTING PENDING APPROVAL
//                    $curl_handle = curl_init();
//                    curl_setopt($curl_handle, CURLOPT_URL, "https://graph.facebook.com/{$fbOutput["fbid"]}/tipboxapp:leave?tip=http://scapehouse.com/tipbox/tip/{$newTipid}&message={$encodedTip}&access_token=278585982224213%7C03U23qbfMWtN-oTGEF4_ncvJ2fs&method=post");
//                    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
//                    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
//                    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
//                    echo $buffer = curl_exec($curl_handle);
//                    curl_close($curl_handle);
//                    $result = curl_exec($ch);
                }
            }

            if ($twtPost == 1) {// Post to Twitter
                $tb_twitter = new Tipbox_Model_DbTable_Tbtwitter();
                $twtOutput = $tb_twitter->getTokenByUserid($tokenData["userid"]);

                $consumerKey = 'JbrsDqt4q6uS6Av58WkIw';
                $consumerSecret = 'Fap79e0DaCNEqXfnUyJDm3rNfBEa5PSDhrUXygvW5s';
                $OAuthToken = $twtOutput["token"];
                $OAuthSecret = $twtOutput["tokensec"];

                // Full path to twitterOAuth.php
                require_once('../twitter_oauth/twitteroauth.php');
                // create new instance
                $tweet = new TwitterOAuth($consumerKey, $consumerSecret, $OAuthToken, $OAuthSecret);
                // Your Message

                if (strlen($bitlyedTip) > 115) {
                    $shortTip = substr($bitlyedTip, 0, 115) . "…";
                } else {
                    $shortTip = $bitlyedTip;
                }

                $message = $shortTip . " http://scapehouse.com/tipbox/tip/{$newTipid}";
                // Send tweet 
                $tweet->post('statuses/update', array('status' => "$message"));
            }

            // Return JSON confirmation

            echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
        }
    }

    public function tipsearchAction() {

        $term = $_POST["term"];
        $token = $_POST["token"];
        $batch = $_POST["batch"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $tbTipTable = new Tipbox_Model_DbTable_Tbtip();
        $tips = $tbTipTable->search($term, $tokenData["userid"], $batch);

        $tips = $this->processTips($tips);

        if (!$tips) {
            $tips = NULL;
        }

        echo json_encode(array("responce" => $tips, "errormsg" => "", "error" => 0));
    }

    public function gethottipsAction() {

        $batch = $_POST["batch"];
        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        if (!$tokenData) {
            $tokenData["userid"] = 0;
        }

        $tbTipTable = new Tipbox_Model_DbTable_Tbtip();
        $tips = $tbTipTable->getHotTips($tokenData["userid"], $batch);

        $tips = $this->processTips($tips);

        echo json_encode(array("responce" => $tips, "errormsg" => "", "error" => 0));
    }

    public function getrecenttipsAction() {

        $batch = $_POST["batch"];
        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        if (!$tokenData) {
            $tokenData["userid"] = 0;
        }

        $tbTipTable = new Tipbox_Model_DbTable_Tbtip();
        $tips = $tbTipTable->getRecentTips($tokenData["userid"], $batch);

        $tips = $this->processTips($tips);

        echo json_encode(array("responce" => $tips, "errormsg" => "", "error" => 0));
    }

    public function deletetipAction() {

        $tipid = $_POST["tipid"];
        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $tbTipTable = new Tipbox_Model_DbTable_Tbtip();
        $tbTipTable->delete($tokenData["userid"], $tipid);

        echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
    }

    public function tiptickerAction() {

        $tbTipTable = new Tipbox_Model_DbTable_Tbtip();

        $tips = $tbTipTable->tipTicker(array("262", "270", "274", "178", "222"));
        $tips = $this->processTips($tips);

        echo json_encode(array("responce" => $tips, "errormsg" => "", "error" => 0));
    }

    public function gettipsbyuserAction() {

        $userid = $_POST["userid"];
        $token = $_POST["token"];
        $batch = $_POST["batch"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        if (!$tokenData) {
            $tokenData["userid"] = 0;
        }

        $tbTipTable = new Tipbox_Model_DbTable_Tbtip();
        $tips = $tbTipTable->getTipsByUser($userid, $tokenData["userid"], $batch);

        $tips = $this->processTips($tips);

        echo json_encode(array("responce" => $tips, "errormsg" => "", "error" => 0));
    }

    public function gettipsbytopicidAction() {

        $topicid = $_POST["topicid"];
        $token = $_POST["token"];
        $batch = $_POST["batch"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        if (!$tokenData) {
            $tokenData["userid"] = 0;
        }
        
        $tbTipTable = new Tipbox_Model_DbTable_Tbtip();
        $tips = $tbTipTable->getTipsByTopicid($topicid, $tokenData["userid"], $batch);

        $tips = $this->processTips($tips);

        echo json_encode(array("responce" => $tips, "errormsg" => "", "error" => 0));
    }

    public function gettipbyidAction() {

        $tipid = $_POST["tipid"];
        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $tbTipTable = new Tipbox_Model_DbTable_Tbtip();
        $tipArray = $tbTipTable->getTipbyId($tipid, $tokenData["userid"]);

        $processedTips = $this->processTips($tipArray, 9);
        $processedTip = $processedTips[0];

        echo json_encode(array("responce" => $processedTip, "errormsg" => "", "error" => 0));
    }

    public function gettipslikedbyuserAction() {

        $userid = $_POST["userid"];
        $token = $_POST["token"];
        $batch = $_POST["batch"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        if (!$tokenData) { // If no session
            $tokenData["userid"] = 0;
        }

        $tbTipTable = new Tipbox_Model_DbTable_Tbtip();
        $tips = $tbTipTable->getTipsLikedByUser($userid, $tokenData["userid"], $batch);

        $tips = $this->processTips($tips);

        echo json_encode(array("responce" => $tips, "errormsg" => "", "error" => 0));
    }

    public function getuserfeedAction() {

        $token = $_POST["token"];
        $batch = $_POST["batch"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $tbTipTable = new Tipbox_Model_DbTable_Tbtip();
        $tips = $tbTipTable->getUserFeed($tokenData["userid"], $batch);

        $tips = $this->processTips($tips);

        echo json_encode(array("responce" => $tips, "errormsg" => "", "error" => 0));
    }

    public function getlatesttipsAction() {

        $tbTipTable = new Tipbox_Model_DbTable_Tbtip();
        $latestTips = $tbTipTable->getLatestTips();

        echo json_encode($latestTips);
    }

    public function liketipAction() {

        $tipid = $_POST["tipid"];
        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);


        //Log a like on a tip, acts as a switch.
        $tblikeTable = new Tipbox_Model_DbTable_Tblike();
        $likeid = $tblikeTable->like($tokenData["userid"], $tipid);

        if ($likeid) {

            // If the like was created sucessfully, then push a notification to the like receivers device.

            $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
            $tokenUser = $tbuserTable->getUserById($tokenData["userid"]);

            $tbtipTable = new Tipbox_Model_DbTable_Tbtip();
            $tipData = $tbtipTable->getTipbyId($tipid);

            $applePushProcessor = new Tipbox_Model_ApplePush(
                            "{$tokenUser["fullname"]} found your tip on \"{$tipData[0]["topicContent"]}\" useful.",
                            $tipData[0]["userid"],
                            array("type" => "tip", "id" => $tipid));

            $applePushProcessor->dispatchNotif();
        }

        echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
    }

    // TIP ACTIONS END
    // TOPIC ACTIONS    

    public function gettopicsbygeniusAction() {

        $userid = $_POST["userid"];
        $token = $_POST["token"];
        $batch = $_POST["batch"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        if (!$tokenData) { // If no session
            $tokenData["userid"] = 0;
        }

        $tbTopicTable = new Tipbox_Model_DbTable_Tbtopic();
        $topics = $tbTopicTable->getTopicsByGenius($userid, $tokenData["userid"], $batch);

        echo json_encode(array("responce" => $topics, "errormsg" => "", "error" => 0));
    }

    public function getfollowedtopicsAction() {

        $userid = $_POST["userid"];
        $token = $_POST["token"];
        $batch = $_POST["batch"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        if (!$tokenData) { // If no session
            $tokenData["userid"] = 0;
        }

        $tbTopicTable = new Tipbox_Model_DbTable_Tbtopic();
        $topics = $tbTopicTable->getTopicsByUserFollow($userid, $tokenData["userid"], $batch);

        echo json_encode(array("responce" => $topics, "errormsg" => "", "error" => 0));
    }

    public function gettopicinfoAction() {

        $topicid = $_POST["topicid"];
        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        if (!$tokenData) {
            $tokenData["userid"] = 0;
        }

        $tbTopicTable = new Tipbox_Model_DbTable_Tbtopic();
        $tbFollowTable = new Tipbox_Model_DbTable_Tbfollow();

        $topicInfo = $tbTopicTable->getTopicInfo($topicid, $tokenData["userid"]);

        $topicInfo["relativeTime"] = Model_Lib_Func::relativeTime(strtotime($topicInfo["time"]));
        $topicInfo["followers"] = $tbFollowTable->getFollowers($topicid);

        if (!$topicInfo["id"]) {
            $topicInfo = NULL;
        }

        echo json_encode(array("responce" => $topicInfo, "errormsg" => "", "error" => 0));
    }

    public function followtopicAction() {

        $topicid = $_POST["topicid"];
        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        //Log a follow on a topic, acts as a switch.
        $tbfollowTable = new Tipbox_Model_DbTable_Tbfollow();
        $tbfollowTable->follow($tokenData["userid"], $topicid);

        echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
    }

    public function followalltopicsAction() {

        $userid = $_POST["userid"];
        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $transferToId = $tokenData["userid"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();

        $tbfollowTable = new Tipbox_Model_DbTable_Tbfollow();
        $tbfollowTable->followAll($userid, $transferToId);

        echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
    }

    public function getfollowersAction() {

        $topicid = $_POST["topicid"];
        $token = $_POST["token"];
        $batch = $_POST["batch"];

        $tbfollowTable = new Tipbox_Model_DbTable_Tbfollow();
        $followers = $tbfollowTable->getFollowers($topicid, 15, $batch);

        echo json_encode(array("responce" => $followers, "errormsg" => "", "error" => 0));
    }

    public function topicsearchAction() {

        $term = trim($_POST["term"]);
        $token = $_POST["token"];
        $batch = $_POST["batch"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $tbTopicTable = new Tipbox_Model_DbTable_Tbtopic();
        $topics = $tbTopicTable->search($term, $tokenData["userid"], $batch);

        if (!$topics[0]) {
            $topics = NULL;
        }

        echo json_encode(array("responce" => $topics, "errormsg" => "", "error" => 0));
    }

    // TOPIC ACTIONS END 
    // CATEGORY ACTIONS

    public function getcatsAction() {

        $type = $_POST["type"];

        $tbcatTable = new Tipbox_Model_DbTable_Tbcat();
        $cats = $tbcatTable->getCats($type);

        foreach ($cats as $key => $cat) {

            if ($cat["id"] == "44" || $cat["id"] == "45") {

                array_push($cats, $cats[$key]);
                unset($cats[$key]);
            }
        }

        $cats = array_values($cats);

        echo json_encode(array("responce" => $cats, "errormsg" => "", "error" => 0));
    }

    // CATEGORY ACTIONS END
    // PICTURE ACTIONS

    public function uploadpictureAction() {

        $imageFile = $_FILES["imageFile"];
        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $photoProcessor = new Tipbox_Model_Photo();
        $processorResponce = $photoProcessor->savePicture($tokenData["userid"], $imageFile);

        if (!preg_match("/[a-f0-9]{40,40}/", $processorResponce)) {
            echo json_encode(array("responce" => "false", "errormsg" => $processorResponce, "error" => 1));
            die;
        }

        $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
        $tbuserTable->savePicture($tokenData["userid"], $processorResponce);

        echo json_encode(array("responce" => $processorResponce, "errormsg" => "", "error" => 0));
    }

    public function deletepictureAction() {

        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
        $tbuserTable->deletePicture($tokenData["userid"], $processorResponce);

        echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
    }

    // PICTURE ACTIONS END
    // PROFILE ACIONS
    public function editprofileAction() {

        $token = $_POST["token"];

        $profileProcessor = new Tipbox_Model_Profile();
        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $profileProcessor->fullname = trim($_POST["fullname"]);
        $profileProcessor->username = trim($_POST["username"]);
        $profileProcessor->email = trim($_POST["email"]);
        $profileProcessor->location = (trim($_POST["location"]) != "") ? trim($_POST["location"]) : NULL;
        $profileProcessor->website = (trim($_POST["website"]) != "") ? trim($_POST["website"]) : NULL;
        $profileProcessor->bio = (trim($_POST["bio"]) != "") ? trim($_POST["bio"]) : NULL;
        $profileProcessor->userid = $tokenData["userid"];

        if ($profileProcessor->editProfile()) {
            echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
        } else {
            echo json_encode(array("responce" => "false", "errormsg" => $profileProcessor->profileErrors, "error" => 1));
        }
    }

    // PROFILE ACTIONS END
    // USER ACTIONS
    public function getprofileAction() {

        $username = $_POST["username"];

        $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
        $user = $tbuserTable->getProfile($username);

        if ($user) {

            $tb_facebook = new Tipbox_Model_DbTable_Tbfacebook();
            $fbOutput = $tb_facebook->getTokenByUserid($user["id"]);

            $user["fbConnected"] = $fbOutput["connected"];

            if ($fbOutput) {
                $user["fbProfile"] = "{$fbOutput["fbid"]}";
            } else {
                $user["fbConnected"] = 0;
            }

            $tb_twitter = new Tipbox_Model_DbTable_Tbtwitter();
            $twtOutput = $tb_twitter->getTokenByUserid($user["id"]);

            if ($twtOutput) {
                $user["twtProfile"] = "{$twtOutput["twtusername"]}";
            } else {
                $user["twtConnected"] = 0;
            }

            echo json_encode(array("responce" => $user, "errormsg" => "", "error" => 0));
        }
    }

    public function userexistsAction() {

        $username = $_POST["username"];

        $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
        $user = $tbuserTable->getUserByUsername($username);

        if ($user) {
            echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
        } else {
            echo json_encode(array("responce" => "false", "errormsg" => "", "error" => 0));
        }
    }

    public function usersearchAction() {

        $term = $_POST["term"];
        $batch = $_POST["batch"];

        $validateEmail = new Zend_Validate_EmailAddress();
        $tbuserTable = new Tipbox_Model_DbTable_Tbuser();

        $term = trim($term);

        if ($validateEmail->isValid($term)) {

            // Email fetch process
            $user = array($tbuserTable->getUserByEmail($term));
        } elseif ($term[0] == "@") {

            // Username fetch process
            $term = str_replace("@", "", $term);
            $user = array($tbuserTable->getUserByUsername($term));
        } else {

            // Normal name search
            $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
            $user = $tbuserTable->search($term, $batch);
        }

        if (!$user[0]) {// Crash prevention
            $user = NULL;
        }

        echo json_encode(array("responce" => $user, "errormsg" => "", "error" => 0));
    }

    // USER ACTIONS END
    // AUTHENTICATION ACTIONS

    public function forgotpasswordAction() {
        $email = trim($_POST["email"]);
        $tbuserTable = new Tipbox_Model_DbTable_Tbuser();

        if ($user = $tbuserTable->getUserByEmail($email)) {
            // Check if exits in out DB -> YES | NO
            // if YES send verifier email

            $msg = nl2br($msg);
            $token = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));
            $hostname = "http://scapehouse.com";

            $bodyHTML = <<<MAIL

Hey {$user["fullname"]} (@{$user["username"]})!<br /><br />

You recently requested to have your password changed.<br />Just click this link and follow the instructions: <a href="{$hostname}/tipbox/forgotpass?vc={$token}" target="_blank" style="color:#053497;">{$hostname}/tipbox/forgotpass?vc={$token}</a>.<br /><br />

If you did not request this change, simply ignore this email.<br /><br />

Kind regards,<br /><br />

-The Scapehouse Team

MAIL;

            if (Model_Lib_Func::shMailer($email, $user["fullname"], "You have requested to reset your password.", $bodyHTML)) {

                $tbforgotPassTable = new Tipbox_Model_DbTable_Tbforgotpass();
                $tbforgotPassTable->logToken($user["id"], $token);

                echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
            } else {
                echo json_encode(array("responce" => "false", "errormsg" => "", "error" => 1));
            }
        }
    }

    public function changepasswordAction() {// TODO: Modify to support email token verifier
        $oldPassword = $_POST["oldPass"];
        $newPass = $_POST["newPass"];
        $newPassConfirm = $_POST["newPassConfirm"];
        $username = $_POST["username"];
        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        // Check old pass

        $sha1OldPassword = Model_Lib_Func::saltedSha1($oldPassword);

        $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
        $user = $tbuserTable->getUserByUsername($username);

        if ($user["password"] == $sha1OldPassword) { // Old pass check
            if ($newPass == $newPassConfirm) {

                $lengthCheck = new Zend_Validate_StringLength();
                $lengthCheck->setMin(6);

                if (!$lengthCheck->isValid($newPass)) {
                    // Password Length Error
                    echo json_encode(array("responce" => "false", "errormsg" => array("PwdLenErr" => true), "error" => 1));
                } else {
                    // Sucessful password update
                    $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
                    $user = $tbuserTable->updatePassword($tokenData["userid"], Model_Lib_Func::saltedSha1($newPass));
                    echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
                }
            } else {
                // Password match error
                echo json_encode(array("responce" => "false", "errormsg" => array("PwdMatchErr" => true), "error" => 1));
            }
        } else {
            //Old pass fail
            echo json_encode(array("responce" => "false", "errormsg" => array("PwdOldErr" => true), "error" => 1));
        }
    }

    public function signupAction() {

        $fullname = $_POST["fullname"];
        $email = $_POST["email"];
        $username = $_POST["username"];
        $password = $_POST["password"];
        $passwordConfirm = $_POST["passwordConfirm"];
        $fbid = $_POST["fbid"];
        $location = $_POST["location"];
        $websiteUrl = $_POST["url"];
        $timezone = $_POST["timezone"];
        $fbToken = $_POST["fbToken"];
        $fbTokenExp = $_POST["fbTokenExp"];

        $twtToken = $_POST["twtToken"];
        $twtid = $_POST["twtid"];
        $twtSecret = $_POST["twtTokenSec"];
        $twtPic = $_POST["twtPic"];
        $twtBio = $_POST["twtBio"];
        $twtUsername = $_POST["twtUsername"];

        $signupType = $_POST["signupType"];
        $deviceToken = $_POST["deviceToken"];

        $signupProcessor = new Tipbox_Model_Signup();

        if ($signupType == "twt") {// Twitter signup params
            $signupProcessor->twtToken = $twtToken;
            $signupProcessor->twtid = $twtid;
            $signupProcessor->twtSecret = $twtSecret;
            $signupProcessor->twtBio = $twtBio;
            $signupProcessor->website = $websiteUrl;
            $signupProcessor->timezone = $timezone / 3600;
            $signupProcessor->twtUsername = $twtUsername;
        } elseif ($signupType == "fb") {// Facebook signup params
            $websiteArray = explode("\n", $websiteUrl); // Email splitter     

            $signupProcessor->fbToken = $fbToken;
            $signupProcessor->fbid = $fbid;
            $signupProcessor->fbTokenExp = $fbTokenExp;
            $signupProcessor->website = $websiteArray[0];
            $signupProcessor->timezone = $timezone;
        }

        $signupProcessor->fullname = trim($fullname);
        $signupProcessor->username = trim($username);
        $signupProcessor->email = trim($email);
        $signupProcessor->password = $password;
        $signupProcessor->passwordConfirm = $passwordConfirm;
        $signupProcessor->location = $location;
        $signupProcessor->accessToken = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));
        $signupProcessor->signupType = $signupType;

        if ($newUserid = $signupProcessor->signup()) {

            if ($newUserid) {//If an account was sucessfully created
                $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
                $tokenData = $tbaccesstokenTable->getTokenByToken($signupProcessor->accessToken);

                // Connect to the Apple token table and log token
                $tbappletokenTable = new Tipbox_Model_DbTable_Tbappletoken();
                $tbappletokenTable->logToken($tokenData["id"], $newUserid, $deviceToken);
            }

            if ($signupType == "twt") { // Twitter picture grabber.
                if ($twtPic) {
                    $fetchURL = str_replace("normal", "bigger", $twtPic);
                    goto fetch; // Make a light speed jump ;)
                }
            } elseif ($signupType == "fb") {
                $fetchURL = "http://graph.facebook.com/{$fbid}/?fields=picture&type=large";
            }

            // Grab picture
            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, $fetchURL);
            curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            $buffer = curl_exec($curl_handle);
            curl_close($curl_handle);

            if (empty($buffer)) {

                $curl_handle = curl_init();
                curl_setopt($curl_handle, CURLOPT_URL, $fetchURL);
                curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                $buffer = curl_exec($curl_handle);
                curl_close($curl_handle);

                if (!empty($buffer)) {
                    goto fetch;
                }
            } else {

                fetch: // Go to marker.

                if ($signupType == "twt") {
                    $image["picture"] = $fetchURL;
                } else {
                    $image = (array) json_decode($buffer);
                }

// Resizer ------
                if (strstr(basename($image["picture"]), "jpg")) {
                    if (!is_dir("userphotos/{$newUserid}/profile/")) {
                        mkdir("userphotos/{$newUserid}/profile/", 0777, true);
                    }

                    $getImage = new Model_Lib_GetImage();
                    $getImage->quality = "100";
                    $getImage->save_to = "userphotos/{$newUserid}/profile/";
                    $getImage->source = $image["picture"];

                    $getImage->download();

                    $fullImageName = basename($image["picture"]);
                    $hashedName = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));

                    //Store pichash
                    $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
                    $tbuserTable->savePicture($newUserid, $hashedName);

                    if (rename("userphotos/{$newUserid}/profile/{$fullImageName}", "userphotos/{$newUserid}/profile/f_{$hashedName}.jpg")) {

                        include "../wideimage/WideImage.php";

                        $photoFull = WideImage::load("userphotos/{$newUserid}/profile/f_{$hashedName}.jpg");

                        $photoFull = $photoFull->resize(180, 180, 'outside', 'down');
                        $photoFull = $photoFull->crop('center', 'center', 180, 180);

                        $photoFull->saveToFile("userphotos/{$newUserid}/profile/m_{$hashedName}.jpg", "100");
                    }
                }

                // Grab picture END...
            }

            echo json_encode(array("responce" => $signupProcessor->accessToken, "errormsg" => "", "error" => 0));
        } else {

            echo json_encode(array("responce" => "false", "errormsg" => $signupProcessor->signupErrors, "error" => 1));
        }
    }

    public function loginAction() {

        if ($_POST) {

            $username = $_POST["username"];
            $password = $_POST["password"];
            $deviceToken = $_POST["deviceToken"];

            $sha1Password = Model_Lib_Func::saltedSha1($password);

            $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
            $user = $tbuserTable->getUserByUsername($username);

            if ($user) {

                if ($user["password"] == $sha1Password) {

                    // Create Access Token                    
                    $accessToken = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));

                    //Store access token
                    $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
                    $tokenid = $tbaccesstokenTable->logToken($user["id"], $accessToken);

                    // Return access token
                    $tb_facebook = new Tipbox_Model_DbTable_Tbfacebook();
                    $fbOutput = $tb_facebook->getTokenByUserid($user["id"]);

                    $tbuserTable = new Tipbox_Model_DbTable_Tbuser();
                    $user = $tbuserTable->getProfile($username);

                    //Check device token validity

                    if (trim($deviceToken) == "" || strlen($deviceToken) == 64) {

                        if ($tokenid && strlen(trim($deviceToken)) == 64) {//If a tipbox token was sucessfully created
                            // Connect to the Apple token table and log token
                            $tbappletokenTable = new Tipbox_Model_DbTable_Tbappletoken();
                            $tbappletokenTable->logToken($tokenid, $user["id"], $deviceToken);
                        }

                        echo json_encode(array("responce" => array("shToken" => $accessToken, "fbToken" => $fbOutput["token"], "userData" => $user), "errormsg" => "", "error" => 0));
                    } else {

                        // Login Fails
                        echo json_encode(array("responce" => "false", "errormsg" => "deviceTokenErr", "error" => 1));
                    }
                } else {

                    // Login Fails
                    echo json_encode(array("responce" => "false", "errormsg" => "loginFail", "error" => 1));
                }
            } else {

                // Login Fails
                echo json_encode(array("responce" => "false", "errormsg" => "loginFail", "error" => 1));
            }
        }
    }

    public function contactAction() {

        if ($_POST) {

            $email = trim($_POST["email"]);
            $fullname = trim($_POST["fullname"]);
            $msg = trim($_POST["msg"]);

            $validateEmail = new Zend_Validate_EmailAddress();

            if (!$validateEmail->isValid($email) || !Model_Lib_Func::verifyEmailDomain($email)) {
                echo "emailErr";
                die;
            }

            if (empty($fullname)) {
                echo "fullnameErr";
                die;
            }

            if (empty($msg)) {
                echo "msgErr";
                die;
            }


            $contactTable = new Model_DbTable_Contact();
            if ($contactTable->log($email, $fullname, $msg, "app")) {

                $msg = nl2br($msg);

                $bodyHTML = <<<MAIL

{$fullname} ({$email}) sent us a message:<br><br>{$msg}<br><br>- Scapehouse One, out.

MAIL;

                Model_Lib_Func::shMailer("scapehouse.com@gmail.com", "Scapehouse Support", "Scapehouse Feedback", $bodyHTML, $fullname);
                Model_Lib_Func::shMailer("amrazzouk@gmail.com", "Scapehouse Support", "Scapehouse Feedback", $bodyHTML, $fullname);

                echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
                die;
            }
        }
    }

    public function logoutAction() {

        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tbaccesstokenTable->deleteTokenByToken($token);

        echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
    }

    // AUTHENTICATION ACTIONS END
    // REPORTING ACTIONS

    public function reporttipAction() {

        $reason = $_POST["reason"];
        $tipid = $_POST["tipid"];
        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $tb_tipreport = new Tipbox_Model_DbTable_Tbtipreport();
        $tb_tipreport->log($tokenData["userid"], $reason, $tipid);

        echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
    }

    public function reportuserAction() {

        $reason = $_POST["reason"];
        $userid = $_POST["userid"];
        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $tb_userreport = new Tipbox_Model_DbTable_Tbuserreport();
        $tb_userreport->log($tokenData["userid"], $userid, $reason);

        echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
    }

    // REPORTING ACTIONS END
    // MISC PROCEDURES

    function quickguideAction() {

        $token = $_POST["token"];
        $ids = $_POST["ids"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $ids = explode(",", $ids);

        //Increment
        foreach ($ids as $key => $id) {
            $ids[$key] = $id + 1;
        }

        $tbFollowTable = new Tipbox_Model_DbTable_Tbfollow();
        $tbFollowTable->qsgFollow($ids, $tokenData["userid"]);

        echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
    }

    function resetbadgecountAction() {

        $token = $_POST["token"];

        $tbaccesstokenTable = new Tipbox_Model_DbTable_Tbaccesstoken();
        $tokenData = $tbaccesstokenTable->getTokenByToken($token);

        $applePushProcessor = new Tipbox_Model_ApplePush("", $tokenData["userid"], "resetBadge");
        $applePushProcessor->dispatchNotif();

        echo json_encode(array("responce" => "true", "errormsg" => "", "error" => 0));
    }

    /**
     * Processes tips, adds essential data, etc
     *
     * @param array $tips Array of tips
     * @return Processed tips array
     */
    public function processTips($tips, $likerLimit = 9) {

        if ($tips) {
            foreach ($tips as $key => $tip) {

                $tips[$key]["relativeTime"] = Model_Lib_Func::relativeTime(strtotime($tip["time"]));
                $tips[$key]["relativeTimeShort"] = Model_Lib_Func::relativeTimeShort(strtotime($tip["time"]));

                //* REMOVE 
//                if($tip["id"] == 221){
//                    
//                    $tips[$key]["content"] = "Tipbox is like a community. It's all about useful and quality content. If you find anyone posting spam or offensive content as tips, report it immediately.";
//                    $tips[$key]["topicContent"] = "Tipbox Tips";
//                    $tips[$key]["relativeTimeShort"] = "25m";
//                }
//                
//                if($tip["id"] == 269){
//                    
//                    $tips[$key]["content"] = 'If Google keeps redirecting you to other sub domains like ".ae", ".co.uk" against your will just add "/ncr" after entering your desired Google domain e.g ".com", ".be" like so (http://google.ae/ncr)';
//                    $tips[$key]["fullname"] = "Kilian Vande Velde";
//                    $tips[$key]["username"] = "kilian.vv";
//                    $tips[$key]["userid"] = "38";
//                    $tips[$key]["pichash"] = "ac6ce9de17e41bda05b5eef38d3215230ebb193f";
//                    $tips[$key]["topicContent"] = "Google";
//                    $tips[$key]["relativeTimeShort"] = "5h";
//                    $tips[$key]["relativeTime"] = "5 hours ago";
//                }
                //* REMOVE END
                //Ask for participant pic hashes
                $tblikeTable = new Tipbox_Model_DbTable_Tblike();
                $likers = $tblikeTable->getLikers($tip["id"], $likerLimit);
                $likerCount = $tblikeTable->getLikerCount($tip["id"]);

                $tips[$key]["likerCount"] = $likerCount;
                $tips[$key]["likers"] = $likers;
            }

            return $tips;
        } else {
            return NULL;
        }
    }

// MISC PROCEDURES END
}

?>
