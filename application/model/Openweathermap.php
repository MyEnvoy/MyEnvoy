<?php

use Famework\Registry\Famework_Registry;

class Openweathermap {

    const TIMEOUT = 4;
    const CACHE_TTL = 600; // ten mins

    private $_apikey;

    /**
     * @var Currentuser
     */
    private $_user;

    public function __construct(Currentuser $user) {
        $config = Famework_Registry::get('\famework_config');
        $this->_apikey = $config->getValue('api', 'openweathermap_apikey');
        $this->_user = $user;
    }

    public function getCurrentWeather() {
        // there may be no api key set on some envoys
        if (empty($this->_apikey)) {
            return NULL;
        }
        
        $city = $this->_user->getSettings()->getWeatherCity();
        if($city === NULL) {
            return NULL;
        }
        
        $apc_key = 'me_weather_' . md5($city);
        $result = apc_fetch($apc_key);

        if ($result === FALSE) {
            $data = $this->fetchCurrentData($city);

            $result = array();

            if (!empty($data) && isset($data->name)) {
                $result['city'] = $data->name;
                $result['icon'] = $this->getIconUrl($data->weather[0]->icon);
                // from Kelvin to Celsius
                $result['temp'] = Security::round($data->main->temp - 273.15, 2);
                $result['desc'] = $data->weather[0]->description;
            }

            if (!empty($result)) {
                apc_store($apc_key, $result, self::CACHE_TTL);
            }
        }

        return $result;
    }

    private function fetchCurrentData($city) {
        $url = sprintf('http://api.openweathermap.org/data/2.5/weather?lang=%s&q=%s&appid=%s', APPLICATION_LANG, urlencode($city), $this->_apikey);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($status !== 200) {
            return NULL;
        }
        curl_close($ch);

        return json_decode($result);
    }

    private function getIconUrl($id) {
        if (strlen($id) !== 3) {
            // fallback for errors
            return '/img/weather/01d.png';
        }
        // "n" stands for "night" but we only have 01n (plain moon), so convert to day
        if (strpos($id, 'n') !== FALSE && $id !== '01n') {
            $id = str_replace('n', 'd', $id);
        }

        return '/img/weather/' . $id . '.png';
    }

}
