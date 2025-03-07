<?php

/**
 * Manages image saving & display.
 * 
 * @copyright  2014 Scapehouse
 */

class Scapes_Model_Photo
{
    private $mimetype;

    public function savePicture($userID, $photo)
    {
        $file = getImageSize($photo['tmp_name']);
        $this->mimetype = $file['mime'];

        if ( $this->mimetype == "image/jpeg" || $this->mimetype == "image/png" || $this->mimetype == "image/gif" )
        {
            $valid = 1;
        }
        else
        {
            return "Invalid file type!";
        }

        if ( $photo['size'] > 5242880 )
        {
            return "File size exceded!";
        }

        if ( $valid )
        {
            $hashedName = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));

            include "../wideimage/WideImage.php";

            // Resizer.
            $photoFull = WideImage::load(file_get_contents($photo["tmp_name"]));

            if ( !is_dir("userphotos/{$userID}/profile/") )
            {
                mkdir("userphotos/{$userID}/profile/", 0777, true);
            }
            
            $photoFull->resizeDown(800, 600)->saveToFile("userphotos/{$userID}/profile/f_{$hashedName}.jpg", "100");
            $sizes = array("m" => 180);

            foreach ( $sizes as $sizeName => $size )
            {
                $photoResize = WideImage::load(file_get_contents($photo["tmp_name"]));

                switch ( true )
                {
                    case ($photoResize->getHeight() > $photoResize->getWidth()):

                        if ( $size < 190 )
                        {
                            $photoResize = $photoResize->resize($size, $size, 'outside', 'down');
                            $photoResize = $photoResize->crop('center', 'center', $size, $size);
                        }
                        else
                        {
                            $photoResize = $photoResize->resize($size, $size, 'outside', 'down');
                        }

                        break;

                    case ($photoResize->getHeight() < $photoResize->getWidth()):

                        if ( $size < 190 )
                        {
                            $photoResize = $photoResize->resize($size, $size, 'outside', 'down');
                            $photoResize = $photoResize->crop('center', 'center', $size, $size);
                        }
                        else
                        {
                            $photoResize = $photoResize->resize($size, $size, 'inside', 'down');
                        }

                        break;

                    case ($photoResize->getHeight() == $photoResize->getWidth()):

                        if ( $size < 190 )
                        {
                            $photoResize = $photoResize->resize($size, $size, 'inside', 'down');
                            $photoResize = $photoResize->crop('center', 'center', $size, $size);
                        }
                        else
                        {
                            $photoResize = $photoResize->resize($size, $size, 'inside', 'down');
                        }

                        break;
                }

                $photoResize->saveToFile("userphotos/{$userID}/profile/{$sizeName}_{$hashedName}.jpg", "100");
            }

            return $hashedName;
        }
    }

    public function deletePicture($userID, $hashedName)
    {
        unlink("userphotos/{$userID}/profile/f_{$hashedName}.jpg");
        unlink("userphotos/{$userID}/profile/m_{$hashedName}.jpg");

        return true;
    }

    public function saveMediaPhoto($userID, $photo)
    {
        $file = getImageSize($photo['tmp_name']);
        $this->mimetype = $file['mime'];

        if ( $this->mimetype == "image/jpeg" || $this->mimetype == "image/png" || $this->mimetype == "image/gif" )
        {
            $valid = 1;
        }
        else
        {
            return "Invalid file type!";
        }

        if ( $photo['size'] > 5242880 )
        {
            return "File size exceded!";
        }

        if ( $valid )
        {
            $hashedName = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));

            include "../wideimage/WideImage.php";

            // Resizer.
            $photoFull = WideImage::load(file_get_contents($photo["tmp_name"]));

            if ( !is_dir("userphotos/{$userID}/photos/") )
            {
                mkdir("userphotos/{$userID}/photos/", 0777, true);
            }
            
            $photoFull->resizeDown(2048, 2048)->saveToFile("userphotos/{$userID}/photos/f_{$hashedName}.jpg", "100");

            return $hashedName;
        }
    }

    public function deleteMediaPhoto($userID, $hashedName)
    {
        unlink("userphotos/{$userID}/photos/f_{$hashedName}.jpg");
        
        return true;
    }

    public function saveBoardCover($boardID, $photo)
    {
        $file = getImageSize($photo['tmp_name']);
        $this->mimetype = $file['mime'];

        if ( $this->mimetype == "image/jpeg" || $this->mimetype == "image/png" || $this->mimetype == "image/gif" )
        {
            $valid = 1;
        }
        else
        {
            return "Invalid file type!";
        }

        if ( $photo['size'] > 5242880 )
        {
            return "File size exceded!";
        }

        if ( $valid )
        {
            $hashedName = sha1(microtime(true) . str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz!@#$%^&*"));

            include "../wideimage/WideImage.php";

            // Resizer.
            $photoFull = WideImage::load(file_get_contents($photo["tmp_name"]));

            if ( !is_dir("userphotos/nightboard/boards/{$boardID}/photos/") )
            {
                mkdir("userphotos/nightboard/boards/{$boardID}/photos/", 0777, true);
            }
            
            $photoFull->resizeDown(2048, 2048)->saveToFile("userphotos/nightboard/boards/{$boardID}/photos/f_{$hashedName}.jpg", "100");

            return $hashedName;
        }
    }

    public function deleteBoardCover($boardID, $hashedName)
    {
        unlink("userphotos/nightboard/boards/{$boardID}/photos/f_{$hashedName}.jpg");
        
        return true;
    }
}

?>