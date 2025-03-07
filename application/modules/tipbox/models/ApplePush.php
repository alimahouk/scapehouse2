<?php

/**
 * Apple push notification processor
 *
 * @copyright  2012 Scapehouse
 */

class Tipbox_Model_ApplePush {

    private $_message;
    private $_receiverid;
    private $_appleTokenData;
    private $_supdata;

    function __construct($message, $receiverid, $supdata) {

        $this->_message = $message;
        $this->_receiverid = $receiverid;
        $this->_supdata = $supdata;

        // Connect to the Apple token table
        $tbappletokenTable = new Tipbox_Model_DbTable_Tbappletoken();
        $this->_appleTokenData = $tbappletokenTable->getTokensByUserid($this->_receiverid);
    }

    public function dispatchNotif() {

        foreach ($this->_appleTokenData as $device) {

            $payload = NULL;
            $apnsMessage = NULL;

            // APNs Push...
            $deviceToken = $device["token"]; // Device token
            $badgeCount = 1 + $device["badge_count"];

            if ($this->_supdata == "resetBadge") { // Badge reset
                $badgeCount = 0;
            }

            // JSON Payload
            $payload['aps'] = array('alert' => $this->_message, 'badge' => $badgeCount, 'supdata' => $this->_supdata);
            $payload = json_encode($payload);

            $apnsHost = 'gateway.push.apple.com';
//            $apnsHost = 'gateway.sandbox.push.apple.com'; // Dev vals
            $apnsPort = 2195;
            $apnsCert = '../applePush/ck_production.pem';
//            $apnsCert = '../applePush/ck.pem'; // Dev vals
            $passphrase = 'akrdOsIp8700nm45admin_APNS';

            $streamContext = stream_context_create();

            stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
            stream_context_set_option($streamContext, 'ssl', 'passphrase', $passphrase);

            $apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 20, STREAM_CLIENT_CONNECT, $streamContext);

            if ($apns) {

                $apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $deviceToken)) . chr(0) . chr(strlen($payload)) . $payload;
                fwrite($apns, $apnsMessage);
                sleep(2);
                fclose($apns);

            } else { // Sending falilure
            }
        }

        $tbappletokenTable = new Tipbox_Model_DbTable_Tbappletoken();

        if ($this->_supdata == "resetBadge") { // Badge reset
            $tbappletokenTable->updateBadgeCount($this->_receiverid, 0);
        } else {
            // Add one to the badge count
            $tbappletokenTable->updateBadgeCount($this->_receiverid, "+1");
        }
    }

}

?>
