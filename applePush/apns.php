<?php

/**
 * Apple push notification processor.
 *
 * @copyright  2014 Scapehouse
 */

$payload = NULL;
$apnsMessage = NULL;

// APNS.
$message = $argv[1];
$badgeCount = $argv[2];
$supData = json_decode($argv[3]);
$deviceToken = $argv[4];
$supportsReplying = $argv[5];

// JSON payload.
if ( $supportsReplying != 0 )
{
    $payload["aps"] = array("alert" => array("action-loc-key" => "reply", "body" => $message), "badge" => $badgeCount, "sound" => "beep_1.aif", "supdata" => $supData);
}
else
{
    $payload["aps"] = array("alert" => $message, "badge" => $badgeCount, "supdata" => $supData);
}

$payload = json_encode($payload);

$apnsHost = "gateway.push.apple.com";
//$apnsHost = 'gateway.sandbox.push.apple.com'; // Dev vals
$apnsPort = 2195;
$apnsCert = "/opt/nginx/html/trunk/applePush/ck_production.pem";
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
else
{
    // Sending falilure!
}

?>