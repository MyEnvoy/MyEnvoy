<?php

/**
 * Only for JPEG pictures!
 */
class Picture {

    const MAX_FILE_SIZE = 3145728;
    const PROFILEPIC_PATH = APPLICATION_PATH . '../work/pic/';
    const PROFILEPIC_FOREIGN_CACHE = APPLICATION_PATH . '../work/pic/cache/';

    /**
     * @param string $name HTML element name
     * @return Picture or NULL on error
     * @throws Exception if user is realy evil
     */
    public static function getFromUpload($name) {
        if (!isset($_FILES[$name]['tmp_name'])) {
            return NULL;
        }
        $path = $_FILES[$name]['tmp_name'];

        if (is_uploaded_file($path) !== TRUE) {
            throw new Exception('Disallowed file operation!', Errorcode::PICTURE_DISALLOWED_OPERATION);
        }

        $pic = new Picture();
        try {
            $pic->loadPictureFromPath($path);
        } catch (Exception $e) {
            // catch because maybe user was not evil but dump
            if ($e->getCode() === Errorcode::PICTURE_FILE_IS_NO_PIC ||
                    $e->getCode() === Errorcode::PICTURE_FILE_TOO_BIG ||
                    $e->getCode() === Errorcode::PICTURE_NO_JPEG) {
                return NULL;
            } else {
                throw $e;
            }
        }
        return $pic;
    }

    public static function getUserPicName($userid, $size, $groupId = 'default') {
        return str_pad($userid, 11, '0', STR_PAD_LEFT) . '-' . $size . '-' . $groupId . '.jpg';
    }

    public static function getConnectionPicture($connType) {
        $pic = 'src="/img/link-3.png" style="transform: rotate(-45deg);"';
        if ($connType === Currentuser::NO_CONNECTION) {
            $pic = 'src="/img/link-0.png" style="transform: rotate(-45deg);"';
        } elseif ($connType === Currentuser::FOLLOWS_ME) {
            $pic = 'src="/img/link-2.png" style="transform: rotate(-45deg);"';
        } elseif ($connType === Currentuser::I_AM_FOLLOWING) {
            $pic = 'src="/img/link-2.png" style="transform: rotate(135deg);"';
        }
        return $pic;
    }

    private $_path = NULL;
    private $_width = NULL;
    private $_height = NULL;

    public function loadPictureFromPath($path) {
        if (file_exists($path)) {
            $file = getimagesize($path);

            if ($file === FALSE) {
                throw new Exception('File is no picture.', Errorcode::PICTURE_FILE_IS_NO_PIC);
            }

            $width = $file[0];
            $height = $file[1];
            $size = filesize($path);

            if ($size > self::MAX_FILE_SIZE) {
                throw new Exception('File is too big.', Errorcode::PICTURE_FILE_TOO_BIG);
            }

            if ($file[2] !== IMAGETYPE_JPEG) {
                throw new Exception('The picture is not in JPEG format', Errorcode::PICTURE_NO_JPEG);
            }

            $this->_path = $path;
            $this->_width = $width;
            $this->_height = $height;
        } else {
            throw new Exception('Can\'t load picture from given path.', Errorcode::PICTURE_INVALID_PATH);
        }
    }

    public function makeProfilePics($userid, $groupId = 'default') {
        $im = imagecreatefromjpeg($this->_path);
        $finalSizes = array(256, 32);

        $originSize = $this->_width;
        if ($this->_width > $this->_height) {
            $originSize = $this->_height;
        }

        $imSquare = imagecreatetruecolor($originSize, $originSize);
        ImageCopy($imSquare, $im, 0, 0, 0, 0, $originSize, $originSize);

        foreach ($finalSizes as $newsize) {
            $imFinish = imagecreatetruecolor($newsize, $newsize);
            imagecopyresampled($imFinish, $imSquare, 0, 0, 0, 0, $newsize, $newsize, $originSize, $originSize);
            imagejpeg($imFinish, self::PROFILEPIC_PATH . self::getUserPicName($userid, $newsize, $groupId), 100);
        }
    }

    public function remove() {
        unlink($this->_path);
        $this->_path = NULL;
        $this->_width = NULL;
        $this->_height = NULL;
    }

}
