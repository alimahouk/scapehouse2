<?php

/**
 * Manages creation of tip cards.
 */
class Tipbox_Model_Tipcard {

    public function createTipCard($tip,$printImage = false) {

        $tipCard = imagecreatefrompng("../tipcard/BlankTemplateTip.png");

        //Repalacement profile picture
        $insert = imagecreatefromjpeg("userphotos/{$tip["userid"]}/profile/m_{$tip["pichash"]}.jpg");
        $insert_x = imagesx($insert);
        $insert_y = imagesy($insert);

        imagecopyresampled($insert, $insert, 0, 0, 0, 0, 69, 69, $insert_x, $insert_y);
        imagecopymerge($tipCard, $insert, 44, 134, 0, 0, 69, 69, 100);

        //Category placement

        switch ($tip["parentcat"]) {
            case "thing":
                $category = imagecreatefrompng("../tipcard/category_thing.png");
                break;
            case "place":
                $category = imagecreatefrompng("../tipcard/category_place.png");
                break;
            case "idea":
                $category = imagecreatefrompng("../tipcard/category_idea.png");
                break;
        }

        imagesavealpha($category, true);

        imagecopy($tipCard, $category, 526, 436, 0, 0, 40, 40);


        // Username Text

        $shadowColor = imagecolorallocate($tipCard, 181, 181, 181);
        $shadowLightColor = imagecolorallocate($tipCard, 220, 220, 220);
        $cor = imagecolorallocate($tipCard, 157, 157, 157);

        $font = '../tipcard/HelveticaNeue.ttf';
        $text = "@{$tip["username"]}";


        imagettftext($tipCard, 22, 0, 136, 201, $shadowLightColor, $font, $text);
        imagettftext($tipCard, 22, 0, 135, 201, $cor, $font, $text);

        //Name Text 
        $cor = imagecolorallocate($tipCard, 51, 51, 51);

        $font = '../tipcard/HelveticaNeueBold.ttf';
        $text = "{$tip["fullname"]}";

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
        $text = $tip["content"];
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
        $text = $tip["topicContent"];

        imagettftext($tipCard, 21, 0, 109, 465, $shadowColor, $font, $text);
        imagettftext($tipCard, 21, 0, 108, 465, $cor, $font, $text);

        if ($printImage) {
            //Header output   
            header('Content-type: image/png');
            imagepng($tipCard);
            die;         
        }

        $hashedName = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));
        imagepng($tipCard, "../tmp/tipcard_{$hashedName}.png");

        return $hashedName;
    }

}

?>