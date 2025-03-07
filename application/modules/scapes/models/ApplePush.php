<?php

/**
 * Apple push notification processor.
 *
 * @copyright  2014 Scapehouse
 */

class Scapes_Model_ApplePush
{
    private $_message;
    private $_receiverID;
    private $_appleTokenData;
    private $_supdata;

    function __construct($message, $receiverID, $supdata)
    {
        $this->_message = $message;
        $this->_receiverID = $receiverID;
        $this->_supdata = $supdata;

        // Connect to the Apple token table.
        $appletokenTable = new Scapes_Model_DbTable_Shappletoken();
        $this->_appleTokenData = $appletokenTable->getTokensByUserID($this->_receiverID);
    }

    public function dispatchNotif()
    {
        $appletokenTable = new Scapes_Model_DbTable_Shappletoken();
        $threadTable = new Scapes_Model_DbTable_Shthread();
        $badgeCount = $threadTable->getTotalUnreadThreadCount($this->_receiverID);
        
        foreach ( $this->_appleTokenData as $device )
        {
            $payload = NULL;
            $apnsMessage = NULL;
            $deviceToken = $device["token"];

            // JSON payload.
            $payload["aps"] = array("alert" => $this->_message, "badge" => $badgeCount);
            $payload["type"] = $this->_supdata["type"];
            
            if ( $this->_supdata["type"] === "join" ) 
            {
                $payload["user_id"] = $this->_supdata["user_id"];
            }
            if ( $this->_supdata["type"] === "new_follower" ) 
            {
                $payload["user_id"] = $this->_supdata["user_id"];
            }
            else if ( $this->_supdata["type"] === "board_request" )
            {
                $payload["board_id"] = $this->_supdata["board_id"];
            }
            else if ( $this->_supdata["type"] === "new_board_post" )
            {
                $payload["board_id"] = $this->_supdata["board_id"];
            }
            else if ( $this->_supdata["type"] === "new_hashtag" )
            {
                $payload["board_id"] = $this->_supdata["board_id"];
            }

            $payload = json_encode($payload);

            $apnsHost = "gateway.push.apple.com";
            //$apnsHost = 'gateway.sandbox.push.apple.com'; // Dev vals
            $apnsPort = 2195;
            $apnsCert = "/opt/nginx/html/trunk/applePush/ck_production_nightboard.pem";
            //$apnsCert = '../applePush/ck.pem'; // Dev vals
            $passphrase = "CharlEYbravOsOleio8086_APNS";

            $streamContext = stream_context_create();
            $error;
            $errorString;

            stream_context_set_option($streamContext, "ssl", "local_cert", $apnsCert);
            stream_context_set_option($streamContext, "ssl", "passphrase", $passphrase);

            $apns = stream_socket_client("ssl://" . $apnsHost . ":" . $apnsPort, $error, $errorString, 20, STREAM_CLIENT_CONNECT, $streamContext);
            
            if ( $apns )
            {
                $apnsMessage = chr(0) . chr(0) . chr(32) . pack("H*", $deviceToken) . chr(0) . chr(strlen($payload)) . $payload;
                fwrite($apns, $apnsMessage);
                sleep(2);
                fclose($apns);
                //echo "{$payload} sent to {$deviceToken}.";
            }
            else // Sending falilure!
            {
                //echo "failed.";
            }
        }
        
        // Update the badge count.
        $appletokenTable->updateBadgeCount($this->_receiverID, $badgeCount);
    }
}

?>