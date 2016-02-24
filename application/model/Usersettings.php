<?php

use Famework\Registry\Famework_Registry;

class Usersettings {

    use Hasmeta;

    const DB_TABLE = 'user_settings';
    const APC_TTL = 600;
    const APC_KEY_PRE = 'me_user_settings_';

    public static function getByUserID($user_id) {
        $cache = apc_fetch(self::APC_KEY_PRE . $user_id);
        if ($cache === FALSE) {
            $cache = NULL;
        }

        $settings = new Usersettings($user_id, $cache);
        if ($settings->isCorrectlyInitialized() === FALSE) {
            return NULL;
        }
        return $settings;
    }

    /**
     * @var PDO
     */
    private $_db;
    private $_id;
    private $_settingsJson;
    private $_settingsObject;

    private function __construct($user_id, $settingsJson = NULL) {
        $this->_db = Famework_Registry::getDb();
        $this->_id = (int) $user_id;

        if ($settingsJson === NULL) {
            try {
                $this->_settingsJson = $this->getWhatever('settings');
            } catch (Exception $e) {
                if ($e->getCode() === Errorcode::HASMETA_NODATA) {
                    // init settings entry for user
                    $stm = $this->_db->prepare('INSERT INTO user_settings (user_id, settings) VALUES (?, ?)');
                    $emptySettings = json_encode(new stdClass());
                    $stm->execute(array($this->_id, $emptySettings));
                    // reload meta
                    $this->loadMeta();
                    $this->_settingsJson = $this->getWhatever('settings');
                } else {
                    throw $e;
                }
            }
        } else {
            $this->_settingsJson = $settingsJson;
        }

        $this->_settingsObject = json_decode($this->_settingsJson);
    }

    public function isCorrectlyInitialized() {
        return ($this->_settingsObject === NULL ? FALSE : TRUE);
    }

    public function __destruct() {
        $this->_settingsJson = json_encode($this->_settingsObject);

        // save data in db
        $stm = $this->_db->prepare('UPDATE user_settings SET settings = ? WHERE user_id = ?');
        $stm->execute(array($this->_settingsJson, $this->_id));

        // save data in cache
        apc_store(self::APC_KEY_PRE . $this->_id, $this->_settingsJson, self::APC_TTL);
    }

    public function getWeatherCity() {
        if (!isset($this->_settingsObject->weathercity)) {
            return NULL;
        }
        return $this->_settingsObject->weathercity;
    }

    public function setWeatherCity($city) {
        $city = Security::trim($city);

        if (!empty($city) && preg_match('/^[\w\s]{0,100}$/u', $city) !== 1) {
            return FALSE;
        }

        $this->_settingsObject->weathercity = $city;
        return TRUE;
    }

}
