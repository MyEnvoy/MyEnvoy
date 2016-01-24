<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;
use Famework\Registry\Famework_Registry;

class DashboardController extends Controller {

    public function init() {
        parent::init();
        $this->_view->user = Currentuser::auth();
        $this->_paramHandler = new Paramhandler();
        $this->_view->title($this->_view->user->getName() . '@MyEnvoy');
        $this->_view->addJS(HTTP_ROOT . 'js/jquery-2.1.4.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/popover.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/dropdown.js');
    }

    public function indexAction() {
        $this->_view->addJS(HTTP_ROOT . 'js/comment.js');

        $weather = new Openweathermap();
        $data = $weather->getCurrentWeather();
        if (!empty($data)) {
            $this->_view->weather_city = Security::htmloutput($data['city']);
            $this->_view->weather_icon = Security::htmloutput($data['icon']);
            $this->_view->weather_temp = Security::htmloutput($data['temp']);
            $this->_view->weather_desc = Security::htmloutput($data['desc']);
        }
    }

    public function searchAction() {
        $this->_view->addJS(HTTP_ROOT . 'js/search.js');
        $this->_view->disable_searchbar = TRUE;
    }

    public function searchDoAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        $query = $this->_paramHandler->getValue('s');

        // username pattern
        if (preg_match('/^[a-z0-9.]{3,40}$/i', $query) !== 1) {
            // maybe it's a full qualified username
            if (preg_match('/^[a-z0-9.]{3,40}@[^\s]{3,100}$/i', $query)) {
                // get name and host
                $parts = explode('@', $query);
                $name = strtolower($parts[0]);
                $domain = $parts[1];
                if (Security::getRealEnvoyDomain($domain) === Server::getMyHost()) {
                    // maybe the user is from this host
                    $query = $name;
                } else {
                    // it's a foreign user
                    $envoy = Envoy::getByDomain($domain);
                    
                }
            } else {
                // no result
                echo json_encode(array());
                exit();
            }
        }

        $query = '%' . strtolower($query) . '%';

        $stm = Famework_Registry::getDb()->prepare('SELECT name, id FROM user WHERE host_gid IS NULL AND name LIKE ?');
        $stm->execute(array($query));

        $result = array();

        foreach ($stm->fetchAll() as $row) {
            $otheruser = new Otheruser($row['id'], $this->_view->user->getId());
            $result[] = array('name' => Security::wbrusername($otheruser->getName(), TRUE),
                'icon' => $otheruser->getPictureUrl(Currentuser::PIC_SMALL),
                'server' => Server::getMyHost(),
                'url' => '/' . APPLICATION_LANG . '/user/' . $otheruser->getName());
        }

        echo json_encode($result);

        exit();
    }

    public function logoutAction() {
        $this->_view->user->logout();
        Famework_Request::redirect('/' . APPLICATION_LANG . '/');
    }

}
