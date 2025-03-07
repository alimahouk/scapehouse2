<?php

class TestController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */

        // Session checker
        if ($this->_request->getActionName() != "login") {
            $sessionContent = Zend_Auth::getInstance()->getStorage()->read();

            if ($sessionContent->password == "ADMIN_PASSWORD_HASH") {
        // Good to go
            } else {

                $this->_redirect("/tipbox/tbadmincon/login");
                die;
            }
        }

            // No layout
        $this->_helper->layout->disableLayout();
    }

    public function imgtestAction() {


// No view ---
        $this->_helper->viewRenderer->setNoRender(true);

        $tipCard = imagecreatefrompng("../tipcard/BlankTemplateTip.png");

//Repalacement profile picture
        $insert = imagecreatefromjpeg("../tipcard/replace.jpg");
        $insert_x = imagesx($insert);
        $insert_y = imagesy($insert);

        imagecopyresampled($insert, $insert, 0, 0, 0, 0, 69, 69, $insert_x, $insert_y);
        imagecopymerge($tipCard, $insert, 44, 134, 0, 0, 69, 69, 100);

//Category placement

        $category = imagecreatefrompng("../tipcard/category_thing.png");

        imagesavealpha($category, true);

        imagecopy($tipCard, $category, 526, 436, 0, 0, 40, 40);


// Username Text

        $shadowColor = imagecolorallocate($tipCard, 181, 181, 181);
        $shadowLightColor = imagecolorallocate($tipCard, 220, 220, 220);
        $cor = imagecolorallocate($tipCard, 157, 157, 157);

        $font = '../tipcard/HelveticaNeue.ttf';
        $text = "@akay64";


        imagettftext($tipCard, 22, 0, 136, 201, $shadowLightColor, $font, $text);
        imagettftext($tipCard, 22, 0, 135, 201, $cor, $font, $text);

//Name Text 
        $cor = imagecolorallocate($tipCard, 51, 51, 51);

        $font = '../tipcard/HelveticaNeueBold.ttf';
        $text = "Abdullah Khan";

// Text size control system
        if (strlen($text) > 28 && strlen($text) < 32) {

            imagettftext($tipCard, 20, 0, 138, 157, $shadowColor, $font, $text);
            imagettftext($tipCard, 20, 0, 137, 157, $cor, $font, $text);
        } elseif (strlen($text) >= 32 && strlen($text) < 43) {

            imagettftext($tipCard, 18, 0, 138, 157, $shadowColor, $font, $text);
            imagettftext($tipCard, 18, 0, 137, 157, $cor, $font, $text);
        } elseif (strlen($text) >= 43) {

            imagettftext($tipCard, 14, 0, 138, 157, $shadowColor, $font, $text);
            imagettftext($tipCard, 14, 0, 137, 157, $cor, $font, $text);
        } else {

            imagettftext($tipCard, 24, 0, 138, 157, $shadowColor, $font, $text);
            imagettftext($tipCard, 24, 0, 137, 157, $cor, $font, $text);
        }


//Tip Text

        $font = '../tipcard/HelveticaNeue.ttf';
        $text = 'When planning your trip just make sure you don\'t have to depend on a taxi between 4 PM to 5 PM. It\'s often extremely hard to get a taxi in this time frame due to the taxi drivers changing shifts.';
        $text = Zend_Text_MultiByte::wordWrap($text, 44, "\n", true);

        $textFrags = explode("\n", $text);

        $startHeight = 270;

        foreach ($textFrags as $textFrags) {

            imagettftext($tipCard, 18, 0, 40, $startHeight, $shadowColor, $font, $textFrags);
            imagettftext($tipCard, 18, 0, 39, $startHeight, $cor, $font, $textFrags);
            $startHeight += 28;
        }

//Topic Text

        $font = '../tipcard/georgia.ttf';
        $text = "Tipbox Tip";

        imagettftext($tipCard, 21, 0, 109, 465, $shadowColor, $font, $text);
        imagettftext($tipCard, 21, 0, 108, 465, $cor, $font, $text);


//Header output
        header('Content-type: image/png');
        imagepng($tipCard);
    }

    public function postdeckAction() {

// MAIN EMAIL        
//$bodyHTML = <<<BODY
//   
//Hey Mashable,<br><br>
//
//You know... I have read way too many posts from Mashable dedicated to giving out tips to people so I did a search for "Tips" @ Mashable &amp; got around 3000 results.&nbsp;Posts titled&nbsp;<em>"5 Tips for Delicious Food Photos"</em>,&nbsp;<em>"10 Online Security Tips "</em>,&nbsp;<em>"10 tips for this and that"</em>&nbsp;and so on. The same search returns not hundreds, but thousands of tip apps all over the App Store, each one a different app on a different topic. That's a lot'a tips and the fragmentation is phenomenal!<br><br>
//
//<b>What if there was one central place to discover and share useful tips?&nbsp;Something that's always right there in your pocket wherever you go.</b><br><br>
//
//My friend and I are computer science students based in Dubai and have developed an iPhone app called Tipbox. We made it as a solution to tip discovery and sharing on the iPhone.<br><br>
//
//I'll skip to the&nbsp;interesting&nbsp;part...<br><br>
//
//<b>Here's a short video our app:</b>&nbsp;<a href="https://vimeo.com/47996308" target="_blank">https://vimeo.com/47996308</a><br>
//<b>Some screenshots:</b>&nbsp;<a href="http://on.fb.me/Rs1Zth" target="_blank">http://on.fb.me/Rs1Zth</a><br>
//<b>Our app's website:</b>&nbsp;<a href="http://scapehouse.com/" target="_blank">http://scapehouse.com</a><br><br>
//
//The app we made is an ever-growing collection of short, simple &amp; personal tips, posted by people just like yourself from their everyday experiences on anything you can think of. The usefulness of the tips is decided by the community for the community.<br><br>
//
//It's not out on the store yet. We're planning to sell it for a buck by the 15th of September. If you do like it, just hit me back with an email. I'll be happy to send over promo codes for you guys and your readers to test drive it before it's even out.<br><br>
//
//I look forward to your reply.<br>
//Kindest regards,<br><br>
//
//--<br>
//Abdullah Khan<br>
//Co-founder | Scapehouse |&nbsp;<a href="http://scapehouse.com/" target="_blank">http://scapehouse.com</a><br>
//<b>Twitter:</b>&nbsp;<a href="http://twitter.com/akay_64" target="_blank">@akay_64</a><br>
//<b>Facebook:</b>&nbsp;<a href="http://fb.com/akay64" target="_blank">http://fb.com/akay64</a><br>
//<b>Skype:</b>&nbsp;akay_dmax<br>
//<b>Mob #:</b>&nbsp;00971-50-2679513<br>
//<b>Email:</b>&nbsp;akay64@scapehouse.com
//
//BODY;
//        
//Model_Lib_Func::shMailer("news@mashable.com", "Abdullah Khan", "Mashable Exclusive: Launching Tipbox, a fun and organized way of finding and sharing great tips on your iPhone.", $bodyHTML, "Abdullah Khan","akay64@scapehouse.com");

//FOR CHIRSTY

//$bodyHTML = <<<BODY
//        Hi Christina :)<br><br>You know... I have read way too many posts from Mashable dedicated to giving out tips to people so I did a search for "Tips" @ Mashable &amp; got around 3000 results.&nbsp;Posts titled&nbsp;"5 Tips for Delicious Food Photos",&nbsp;"10 Online Security Tips ",&nbsp;"10 tips for this and that"&nbsp;and so on.&nbsp;That's a lot'a tips.<br><br>A question:<b>&nbsp;What if there was one central place to discover and share useful tips?&nbsp;Something that's always right there in your pocket wherever you go.</b><br><br>My friend and I have developed an iPhone app called Tipbox. We&nbsp;made as a "solution" to tip discovery and sharing on the iPhone after seeing not hundreds, but thousands of tip apps all over the App Store, each one a different app on a different topic. The fragmentation is&nbsp;phenomenal!<br><br>I'll skip to the&nbsp;interesting&nbsp;part...<br><br><b>Here's a short video our app:</b>&nbsp;<a href="https://vimeo.com/47996308" target="_blank">https://vimeo.com/47996308</a><br><b>Some screenshots:</b>&nbsp;<a href="http://on.fb.me/Rs1Zth" target="_blank">http://on.fb.me/Rs1Zth</a><br><b>Our app's website:</b>&nbsp;<a href="http://scapehouse.com/" target="_blank">http://scapehouse.com</a><br><br>The app we made is an ever-growing collection of short, simple &amp; personal tips, posted by people just like yourself from their everyday experiences on anything you can think of. The usefulness of the tips is decided by the community for the community.<br><br>It's not out on the store yet. We're planning to sell it for a buck by the 15th of September. If you do like it, just hit me back with an email. I'll be happy to send over promo codes for you and your readers to test drive it before it's even out.<br><br>I look forward to your reply.<br>Kindest regards,<br><br>--<br>Abdullah Khan<br>Co-founder | Scapehouse |&nbsp;<a href="http://scapehouse.com/" target="_blank">http://scapehouse.com</a><br><b>Twitter:</b>&nbsp;<a href="http://twitter.com/akay_64" target="_blank">@akay_64</a><br><b>Facebook:</b>&nbsp;<a href="http://fb.com/akay64" target="_blank">http://fb.com/akay64</a><br><b>Skype:</b>&nbsp;akay_dmax<br><b>Mob #:</b>&nbsp;00971-50-2679513<br><b>Email:</b>&nbsp;akay64@scapehouse.com
//BODY;
//
//    Model_Lib_Func::shMailer("christina@christina.is", "Abdullah Khan", "[Mashable related] Hey Christina :) I'm launching an iPhone app, Tipbox. It helps people find & share useful tips.‏", $bodyHTML, "Abdullah Khan","akay64@scapehouse.com");
//    
    }

    public function tipperAction() {

        if ($_POST) {

// No layout
            $this->_helper->layout->disableLayout();
// No view ---
            $this->_helper->viewRenderer->setNoRender(true);

            $username = $_POST["username"];
            $content = trim($_POST["content"]);
            $topicContent = trim($_POST["topicContent"]);
            $topicid = 0;
            $catid = $_POST["catid"];
            $location_lat = NULL;
            $location_long = NULL;
            $fbPost = $_POST["fbPost"];

            $tbUserTable = new Tipbox_Model_DbTable_Tbuser();
            $user = $tbUserTable->getUserByUsername($username);

            if (!$user) {
                echo "ERROR: User does not exist";
                die;
            }

//Error checking topic

            if ($topicid == 0) {

                if (mb_strlen($topicContent, "UTF8") > 32) {
                    echo json_encode(array("responce" => "false", "errormsg" => "Topic over 32 characters", "error" => 1));
                    die;
                }

                $tbTopicTable = new Tipbox_Model_DbTable_Tbtopic();
                $topicid = $tbTopicTable->createTopic($user["id"], $topicContent);
            }

            include "../parseUrl/parseUrlBitly.php";

            $bitlyedTip = make_bitly($content);
            $tbTipTable = new Tipbox_Model_DbTable_Tbtip();
            $newTipid = $tbTipTable->createTip($user["id"], $bitlyedTip, $topicid, $catid, $location_lat, $location_long);


            if ($fbPost == 1) {// Post to Facebook
                $tb_facebook = new Tipbox_Model_DbTable_Tbfacebook();
                $fbOutput = $tb_facebook->getTokenByUserid($user["id"]);

                if ($fbOutput["connected"] == 1) {

                    $tip = $tbTipTable->getTipbyId($newTipid);
                    $tip[0]["content"] = $bitlyedTip;

                    /* $params = array('access_token' => $fbOutput["token"], 'message' => $bitlyedTip, "link" => "http://scapehouse.com/tipbox/tip/{$newTipid}");

                      $url = "https://graph.facebook.com/{$fbOutput["fbid"]}/feed"; */

                    $tbTipcardProcessor = new Tipbox_Model_Tipcard();
                    $hashedName = $tbTipcardProcessor->createTipCard($tip[0]);

                    $image = '@' . realpath("../tmp/tipcard_{$hashedName}.png");

                    $params = array('access_token' => $fbOutput["token"], 'message' => $bitlyedTip . " (http://scapehouse.com/tipbox/tip/{$newTipid})", 'source' => $image);

                    $url = "https://graph.facebook.com/me/feed?link=http://scapehouse.com/tipbox/tip/{$newTipid}&message={$encodedTip}&access_token=YOUR_FACEBOOK_APP_TOKEN&method=post");

//                    $params = array('access_token' => $fbOutput["token"]);
//                    $url = "https://graph.facebook.com/1371720131_4453016168144?method=delete";

                    $ch = curl_init();

                    curl_setopt_array($ch, array(
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_URL => $url,
                        CURLOPT_POSTFIELDS => $params,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_VERBOSE => true
                    ));

                    echo $result = curl_exec($ch);

                    unlink("../tmp/tipcard_{$hashedName}.png");

//                    $encodedTip = rawurlencode($bitlyedTip);
//
//                    $curl_handle = curl_init();
//                    curl_setopt($curl_handle, CURLOPT_URL, "https://graph.facebook.com/{$fbOutput["fbid"]}/tipboxapp:leave?tip=http://scapehouse.com/tipbox/tip/{$newTipid}&message={$encodedTip}&access_token=278585982224213%7C03U23qbfMWtN-oTGEF4_ncvJ2fs&method=post");
//                    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
//                    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
//                    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
//                    echo $buffer = curl_exec($curl_handle);
//                    curl_close($curl_handle);
                }
            }
        }
    }

    public function fbtesttipperAction() {

        if ($_POST) {

// No layout
            $this->_helper->layout->disableLayout();
// No view ---
            $this->_helper->viewRenderer->setNoRender(true);

            $username = trim($_POST["username"]);
            $pwd = trim($_POST["pwd"]);
            $content = trim($_POST["tip"]);
            $topicContent = trim($_POST["topic"]);
            $topicid = 0;
            $catid = 40;
            $location_lat = NULL;
            $location_long = NULL;
            $fbPost = $_POST["fbpost"];

            $tbUserTable = new Tipbox_Model_DbTable_Tbuser();
            $user = $tbUserTable->getUserByUsername($username);

            if ($user["password"] != Model_Lib_Func::saltedSha1($pwd)) {
                $user = NULL;
            }

            if (!$user) {
                echo "err";
                die;
            }

//Error checking topic

            if ($topicid == 0) {

                if (mb_strlen($topicContent, "UTF8") > 32) {
                    echo json_encode(array("responce" => "false", "errormsg" => "Topic over 32 characters", "error" => 1));
                    die;
                }

                $tbTopicTable = new Tipbox_Model_DbTable_Tbtopic();
                $topicid = $tbTopicTable->createTopic($user["id"], $topicContent);
            }

            include "../parseUrl/parseUrlBitly.php";

            $bitlyedTip = make_bitly($content);
            $tbTipTable = new Tipbox_Model_DbTable_Tbtip();
            $newTipid = $tbTipTable->createTip($user["id"], $bitlyedTip, $topicid, $catid, $location_lat, $location_long);

            if ($fbPost == 1) {// Post to Facebook
                $tb_facebook = new Tipbox_Model_DbTable_Tbfacebook();
                $fbOutput = $tb_facebook->getTokenByUserid($user["id"]);

                if ($fbOutput["connected"] == 1) {

                    $encodedTip = rawurlencode($bitlyedTip);

                    $curl_handle = curl_init();
                    curl_setopt($curl_handle, CURLOPT_URL, "https://graph.facebook.com/100001394539148/tipboxapp:leave?tip=http://scapehouse.com/tipbox/tip/{$newTipid}&message={$encodedTip}&access_token=278585982224213%7C03U23qbfMWtN-oTGEF4_ncvJ2fs&method=post");
                    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
                    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }

            echo "success";
        }
    }

    public function indexAction() {

        if ($_GET["key"] != "accessfr") {
            die;
        }

        $this->view->pageTitle = "Scapehouse Firing Range";
        $this->view->pageClass = "loggedIn";

        $this->_helper->layout->disableLayout();

        if ($_GET["command"] == "akayRebuildCss") {

            $filename = sha1(str_shuffle("Note345thatI#@$%#don't4534534wantomod435345ifythe534543534arrayanditsvalu3453453eshou45345345ldntact"));

// Get the old filenames to delete
            $oldfilename = file_get_contents("filenameCss.txt");

// Save new file names
            fopen("filenameCss.txt", "w");
            file_put_contents("filenameCss.txt", $filename);


            unlink("stylesheets/css/web/SH_{$oldfilename}.css");

            $time = date("l, F d, Y \a\\t g:i a", time());

            $commentHeader =
                    "/*
*  Time: {$time}
*/";

// Deal with the CSS ------------------

            fopen("stylesheets/css/web/SH_{$filename}.css", "w");


            $webkitCss = array(
                file_get_contents("stylesheets/css/web/layout.css"),
                file_get_contents("stylesheets/css/web/main.css")
            );

            foreach ($webkitCss as $css) {
                $webkitCssComp .= Model_Lib_CssMin::minify($css);
            }

            file_put_contents("stylesheets/css/web/SH_{$filename}.css", $webkitCssComp);

//-------------------------

            echo "<h1>The deed is done...</h1>";
        }

        if ($_GET["command"] == "akayRebuildJavascript") {

            $filename = sha1(str_shuffle("Note345thatI#@$%#don't4534534wantomod435345ifythe534543534arrayanditsvalu3453453eshou45345345ldntact"));

// Get the old filenames to delete
            $oldfilename = file_get_contents("filename.txt");

// Save new file names
            fopen("filename.txt", "w");
            file_put_contents("filename.txt", $filename);

            unlink("scripts/SH_{$oldfilename}.js");
            unlink("scripts/bootstrap/contact_{$oldfilename}.js");
            unlink("scripts/bootstrap/index_{$oldfilename}.js");

            fopen("scripts/SH_{$filename}.js", "w");

            $SHJS = array(
//file_get_contents("scripts/ajax.js"),
                file_get_contents("scripts/core.js")
            );

//echo "<script>";
            foreach ($SHJS as $js) {
                $SH .= Model_Lib_JSMin::minify($js);
            }

// echo "</script>";

            $time = date("l, F d, Y \a\\t g:i a", time());

            $commentHeader = "
/*
*  Time: {$time}
*/";

            echo file_put_contents("scripts/SH_{$filename}.js", $commentHeader . $SH) . "<br>";
            echo file_put_contents("scripts/bootstrap/contact_{$filename}.js", file_get_contents("scripts/bootstrap/contact.js")) . "<br>";
            echo file_put_contents("scripts/bootstrap/index_{$filename}.js", file_get_contents("scripts/bootstrap/index.js")) . "<br>";

            echo "<h1>Javascript Rebuilt<h2>";
        }
    }

}

