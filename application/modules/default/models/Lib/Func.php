<?php

/**
 * This is a class for all general purpose static functions.
 *
 * @copyright  2015 Scapehouse
 */
class Model_Lib_Func
{
    /**
     * Adds salt (randomization) to a string before converting to SHA1
     * 
     * WARNING: Any modification to this code can lead to mass auth failure
     * for the whole application.
     * 
     * @param string $string The string to be processed
     * @return string The converted string
     */
    static function saltedSha1($string)
    {
        return sha1("+scapehouse-Salt+" . strrev($string) . "+scapehouse-Salt+" . $string . strrev($string) . "+scapehouse-Salt+");
    }

    /**
     * Recursively removes all folders in a directory
     *
     * @param string $dir Directory path
     * @return void
     */
    static function rrmdir($dir)
    {
        if ( is_dir($dir) )
        {
            $objects = scandir($dir);

            foreach ( $objects as $object )
            {
                if ( $object != "." && $object != ".." )
                {
                    if ( filetype($dir . "/" . $object) == "dir" )
                    {
                        rmdir($dir . "/" . $object);
                    }
                    else
                    {
                        unlink($dir . "/" . $object);
                    }   
                }
            }

            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * Make asyc php calls to other scripts.
     *
     * @param string $url URL of the script to be executed
     * @param array $params URL params
     * @return void
     */
    static function curlPostAsync($url, $params)
    {
        foreach ( $params as $key => &$val )
        {
            if ( is_array($val) )
                $val = implode(',', $val);
            $post_params[] = $key . '=' . urlencode($val);
        }

        $post_string = implode('&', $post_params);

        $parts = parse_url($url);

        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);

        $out = "POST " . $parts['path'] . " HTTP/1.1\r\n";
        $out.= "Host: " . $parts['host'] . "\r\n";
        $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out.= "Content-Length: " . strlen($post_string) . "\r\n";
        $out.= "Connection: Close\r\n\r\n";

        if ( isset($post_string) )
        {
            $out.= $post_string;
        } 

        fwrite($fp, $out);
        fclose($fp);
    }

    /**
     * Sends out an email (HTML) using SendGrid.
     *
     * @param string $toEmail  E-mail address of recipent
     * @param string $toName   Name of recipent
     * @param string $subject  E-mail subject
     * @param string $bodyHTML E-mail content (HTML supported)
     * @param string $fromName Name of the sender. Default: "Scapehouse"
     * @param string $from     E-mail address of the sender. Default: "postmaster@scapehouse.com"
     * @return bool  TRUE on sucess, FALSE on failure
     */
    static function shMailer($toEmail, $toName, $subject, $bodyHTML, $fromName = "Scapehouse", $from = "postmaster@scapehouse.com")
    {
        require_once '../phpmailer/class.phpmailer.php';
        require_once '../phpmailer/class.smtp.php';

        $mail = new PHPMailer();

        $mail->IsSMTP(); // telling the class to use SMTP
		
        $mail->SMTPAuth = true; // enable SMTP authentication
        $mail->Host = "smtp.sendgrid.net"; // sets SendGrid as the SMTP server
        $mail->Port = 25; // alternate is "26" - set the SMTP port

        $mail->Username = "postmaster@scapehouse.com";
        $mail->Password = "CharlEYbravOsOleio8086_SENDGRID";

        $mail->FromName = $fromName;
        $mail->From = $from;
        $mail->IsHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $bodyHTML;

        $mail->AddAddress($toEmail);

        if ( $mail->Send() )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Checks if the an e-mail addresses domain exists
     *
     * @param string $email  E-mail address to be tested
     * @return bool  TRUE on sucess, FALSE on failure
     */
    static function verifyEmailDomain($email)
    {
        list($user, $domain) = explode("@", $email);
        $result = checkdnsrr($domain, 'MX');

        return($result);
    }

    /**
     * Takes in text, trims with respect to complete words.
     *
     * @param  string $text    Text to be processed
     * @param  int    $maxchar Charater limit
     * @param  string $end     The string to be concatnated at the end of the text
     * @return string Processed text
     */
    static function subStrWords($text, $maxchar, $end = '')
    {
        if ( strlen($text) > $maxchar )
        {
            $words = explode(" ", $text);
            $output = '';
            $i = 0;

            while ( 1 )
            {
                $length = (strlen($output) + strlen($words[$i]));
                
                if ( $length > $maxchar )
                {
                    break;
                }
                else
                {
                    $output = $output . " " . $words[$i];
                    ++$i;
                }
            }
        }
        else
        {
            $output = $text;
        }

        return $output . $end;
    }

    /**
     * Removes white space from between words.
     *
     * @param  string $s    Text to be processed
     * @return string Processed text
     */
    static function stripExtraSpace($s)
    {
        $newstr = "";

        for ( $i = 0; $i < strlen($s); $i++ )
        {
            $newstr = $newstr . substr($s, $i, 1);

            if ( substr($s, $i, 1) == ' ' )
                while ( substr($s, $i + 1, 1) == ' ' )
                    $i++;
        }

        return $newstr;
    }

    /**
     * Returns JSON formatted for human review in an HTML browser
     *
     * @param  string $json JSON to be processed
     * @return string Processed text
     */
    static function indentJSONHTML($json)
    {
        $result = '';
        $pos = 0;
        $strLen = strlen($json);
        $indentStr = '  ';
        $newLine = "</br>";
        $prevChar = '';
        $outOfQuotes = true;

        for ( $i = 0; $i <= $strLen; $i++ )
        {
            // Grab the next character in the string.
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\')
            {
                $outOfQuotes = !$outOfQuotes;

                // If this character is the end of an element, 
                // output a new line and indent the next line.
            }
            else if ( ($char == '}' || $char == ']') && $outOfQuotes )
            {
                $result .= $newLine;
                $pos--;

                for ( $j = 0; $j < $pos; $j++ )
                {
                    $result .= $indentStr;
                }
            }

            // Add the character to the result string.
            $result .= $char;

            // If the last character was the beginning of an element, 
            // output a new line and indent the next line.
            if ( ($char == ',' || $char == '{' || $char == '[') && $outOfQuotes )
            {
                $result .= $newLine;

                if ( $char == '{' || $char == '[' )
                {
                    $pos++;
                }

                for ( $j = 0; $j < $pos; $j++ )
                {
                    $result .= $indentStr;
                }
            }

            $prevChar = $char;
        }

        return $result;
    }

    /**
     * Searches a multidimentional array for a value. 
     * (don't exactly know what args it takes)
     *
     * @param  array $array Array to be searched
     * @param  int|string $key
     * @return string Processed text
     */
    static function search($array, $key, $value)
    {
        $results = array();

        if ( is_array($array) )
        {
            if ($array[$key] == $value)
            {
                $results[] = $array;
            }
                

            foreach ( $array as $subarray )
            {
                $results = array_merge($results, self::search($subarray, $key, $value));
            }  
        }

        return $results;
    }

    /**
     * Takes in text, trims with respect to complete words (UTF8 Support)
     *
     * @param  string $text    Text to be processed
     * @param  int    $maxchar Charater limit
     * @param  string $end     The string to be concatnated at the end of the text
     * @return string Processed text
     */
    static function subStrWordsUTF8($text, $maxchar, $end = '')
    {
        if ( mb_strlen($text, 'UTF8') > $maxchar )
        {
            $words = explode(" ", $text);
            $output = '';
            $i = 0;

            while ( 1 )
            {
                $length = (mb_strlen($output, 'UTF8') + mb_strlen($words[$i], 'UTF8'));
                
                if ( $length > $maxchar )
                {
                    break;
                }
                else
                {
                    $output = $output . " " . $words[$i];
                    ++$i;
                }
            }
        }
        else
        {
            $output = $text;
        }

        return $output . $end;
    }

    /** Relative time ---- user friendly time
     * this function will calculate a friendly date difference string
     * based upon $time and how it compares to the current time
     * for example it will return "1 minute ago" if the difference
     * in seconds is between 60 and 120 seconds
     * $time is a GM-based Unix timestamp, this makes for a timezone
     * neutral comparison
     * 
     * @param $time GM-based UNIX timestamp
     * @param string Relative time
     */
    static function relativeTime($time)
    {
        define(MINUTE, 60);
        define(HOUR, 60 * 60);
        define(DAY, 60 * 60 * 24);
        define(MONTH, 60 * 60 * 24 * 30);

        $delta = strtotime(gmdate("Y-m-d H:i:s", time())) - $time;

        if ( $delta < 1 * MINUTE )
        {
            return $delta <= 1 ? "a moment ago" : $delta . " seconds ago";
        }

        if ( $delta < 2 * MINUTE )
        {
            return "1 minute ago";
        }

        if ( $delta < 45 * MINUTE )
        {
            return floor($delta / MINUTE) . " minutes ago";
        }

        if ( $delta < 90 * MINUTE )
        {
            return "an hour ago";
        }

        if ( $delta < 24 * HOUR )
        {
            return ceil($delta / HOUR) . " hours ago";
        }

        if ( $delta < 48 * HOUR )
        {
            return "yesterday";
        }

        if ( $delta < 30 * DAY )
        {
            return floor($delta / DAY) . " days ago";
        }

        if ( $delta < 12 * MONTH )
        {
            $months = floor($delta / DAY / 30);

            return $months <= 1 ? "one month ago" : $months . " months ago";
        }
        else
        {
            $years = floor($delta / DAY / 365);

            return $years <= 1 ? "one year ago" : $years . " years ago";
        }
    }

    /** Relative time short ---- user friendly time using 1 charater only
     * 
     * this function will calculate a friendly date difference string
     * based upon $time and how it compares to the current time
     * for example it will return "1 minute ago" if the difference
     * in seconds is between 60 and 120 seconds
     * $time is a GM-based Unix timestamp, this makes for a timezone
     * neutral comparison
     * 
     * @param $time GM-based UNIX timestamp
     * @param string Relative time
     */
    static function relativeTimeShort($time)
    {
        define(MINUTE, 60);
        define(HOUR, 60 * 60);
        define(DAY, 60 * 60 * 24);
        define(MONTH, 60 * 60 * 24 * 30);

        $delta = strtotime(gmdate("Y-m-d H:i:s", time())) - $time;

        if ( $delta < 1 * MINUTE )
        {
            return $delta <= 1 ? "0s" : $delta . "s";
        }

        if ( $delta < 2 * MINUTE )
        {
            return "1m";
        }

        if ( $delta < 45 * MINUTE )
        {
            return floor($delta / MINUTE) . "m";
        }

        if ( $delta < 90 * MINUTE )
        {
            return "1h";
        }

        if ( $delta < 24 * HOUR )
        {
            return ceil($delta / HOUR) . "h";
        }

        if ( $delta < 48 * HOUR )
        {
            return "1d";
        }

        if ( $delta < 30 * DAY )
        {
            return floor($delta / DAY) . "d";
        }

        if ( $delta < 12 * MONTH )
        {
            $months = floor($delta / DAY / 30);

            return $months <= 1 ? "1mo" : $months . "mo";
        }
        else
        {
            $years = floor($delta / DAY / 365);

            return $years <= 1 ? "1y" : $years . "y";
        }
    }

    static function logToFile($data)
    {
        $my_file = 'logFile.txt';
        $handle = fopen($my_file, 'w') or die('Cannot open file:  ' . $my_file);
        fwrite($handle, $data);
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Vincenty formula.
     *
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     *
     * @return float Distance between points in [m] (same as earthRadius)
     */
    public static function vincentyGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
      // convert from degrees to radians
      $latFrom = deg2rad($latitudeFrom);
      $lonFrom = deg2rad($longitudeFrom);
      $latTo = deg2rad($latitudeTo);
      $lonTo = deg2rad($longitudeTo);
        
      $lonDelta = $lonTo - $lonFrom;
      $a = pow(cos($latTo) * sin($lonDelta), 2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
      $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
        
      $angle = atan2(sqrt($a), $b);
      return $angle * $earthRadius;
    }
}

?>