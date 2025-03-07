<?php

require_once (__DIR__ . '/../../../../public_html/autoload.php'); // Crypto.
require_once (__DIR__ . '/../../../../twilio-php/Services/Twilio.php'); // Twilio SMS.

/**
 * Twilio API calls.
 *
 * @copyright  2014 Scapehouse
 */

class Scapes_SmsController extends Zend_Controller_Action
{
    protected $twilioSID = "REPLACE-THIS-WITH-TWILIO-SID";
    protected $twilioToken = "REPLACE-THIS-WITH-TWILIO-TOKEN";

    public function init()
    {
        /* Initialize action controller here. */
        
        $this->_helper->layout->disableLayout();         // No layout.
        $this->_helper->viewRenderer->setNoRender(true); // No view.

        $GLOBALS["init_token"] = "REPLACE-THIS-WITH-INIT-TOKEN"; // For fresh logins & signups.
    }

	public function sendAction()
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
        $number = $payload['phone_number'];
        $code = $payload['code'];
        $assembledPhoneNumber = "+{$countryCallingCode}{$prefix}{$number}";

        $twilioPhoneNumber = "+48799449498";
        $twilioMessage = "Scapes code: {$code}";

        $rand = rand(0, 1);

        if ( $rand == 1 )
        {
            $twilioPhoneNumber = "+16084780800";
        }

        $client = new Services_Twilio($this->twilioSID, $this->twilioToken);
        $message = $client->account->messages->sendMessage(
            $twilioPhoneNumber, // From a valid Twilio number.
            $assembledPhoneNumber, // Text this number.
            $twilioMessage
        );

        $output = json_encode(array("response" => "done.", "errorMessage" => "", "errorCode" => 0));
        $base64Encrypted = $encryptor->encrypt($output, $encryptionKey);
        
        echo "while(1);" . $base64Encrypted;
    }
}

?>