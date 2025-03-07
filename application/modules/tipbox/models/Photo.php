<?php

/**
 * Manages photo save and display.
 * 
 * @copyright  2012 Scapehouse
 */
class Tipbox_Model_Photo {

    private $mimetype;

    public function savePicture($userid, $photo) {

        $file = getImageSize($photo['tmp_name']);
        $this->mimetype = $file['mime'];

        if ($this->mimetype == "image/jpeg" || $this->mimetype == "image/png" || $this->mimetype == "image/gif") {
            $valid = 1;
        } else {
            return "Invalid file type";
        }

        if ($photo['size'] > 5242880) {
            return "File size exceded";
        }

        if ($valid) {

            $hashedName = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));

            include "../wideimage/WideImage.php";

// Resizer ------

            $photoFull = WideImage::load(file_get_contents($photo["tmp_name"]));
            if (!is_dir("userphotos/{$userid}/profile/")) {
                mkdir("userphotos/{$userid}/profile/", 0777, true);
            }

            $photoFull->resizeDown(800, 600)->saveToFile("userphotos/{$userid}/profile/f_{$hashedName}.jpg", "100");

            $sizes = array("m" => 180);

            foreach ($sizes as $sizeName => $size) {

                $photoResize = WideImage::load(file_get_contents($photo["tmp_name"]));

                switch (true) {
                    case($photoResize->getHeight() > $photoResize->getWidth()):

                        if ($size < 190) {
                            $photoResize = $photoResize->resize($size, $size, 'outside', 'down');
                            $photoResize = $photoResize->crop('center', 'center', $size, $size);
                        } else {
                            $photoResize = $photoResize->resize($size, $size, 'outside', 'down');
                        }
                        break;

                    case($photoResize->getHeight() < $photoResize->getWidth()):

                        if ($size < 190) {
                            $photoResize = $photoResize->resize($size, $size, 'outside', 'down');
                            $photoResize = $photoResize->crop('center', 'center', $size, $size);
                        } else {
                            $photoResize = $photoResize->resize($size, $size, 'inside', 'down');
                        }
                        break;

                    case($photoResize->getHeight() == $photoResize->getWidth()):

                        if ($size < 190) {
                            $photoResize = $photoResize->resize($size, $size, 'inside', 'down');
                            $photoResize = $photoResize->crop('center', 'center', $size, $size);
                        } else {
                            $photoResize = $photoResize->resize($size, $size, 'inside', 'down');
                        }
                        break;
                }
                $photoResize->saveToFile("userphotos/{$userid}/profile/{$sizeName}_{$hashedName}.jpg", "100");
            }
            return $hashedName;
        }
    }

    public function display($id, $idType, $size = "full") {

        switch ($size) {
            case "full":
                $size = "f";
                break;
            case "med":
                $size = "m";
                break;
            case "small":
                $size = "s";
                break;
        }
        if ($id && $idType == "user") {

            $userid = $id;
            $photoTable = new Logged_Model_DbTable_Photo();
            $photoInfo = $photoTable->getCurrent($userid);
            if ($photoInfo) {
                return "/userphotos/{$photoInfo["username"]}/profile/{$size}_{$photoInfo["hash"]}.jpg";
            } else {
                return "/userphotos/user_silhouette.gif";
            }
        } elseif ($id && $idType == "photo") {
            $photoTable = new Logged_Model_DbTable_Photo();
            $photoInfo = $photoTable->getPhotos($id, "photo");
            return "/userphotos/{$photoInfo[0]["username"]}/profile/{$size}_{$photoInfo[0]["hash"]}.jpg";
        }
    }

}

?>