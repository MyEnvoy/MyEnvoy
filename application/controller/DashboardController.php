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
        $this->_view->addCSS(HTTP_ROOT . APPLICATION_LANG . '/style/custom');
    }

    public function indexAction() {
        $this->_view->addJS(HTTP_ROOT . 'js/comment.js');

        $weather = new Openweathermap($this->_view->user);
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
        $otheruser = NULL;

        // username pattern
        if (preg_match('/^[a-z0-9.]{3,40}$/i', $query) !== 1) {
            // maybe it's a full qualified username
            if (preg_match('/^[a-z0-9.]{3,40}@[^\s]{3,100}$/i', $query)) {
                // get name and host
                $parts = explode('@', $query);
                $name = strtolower($parts[0]);
                $domain = Security::getRealEnvoyDomain($parts[1]);
                $otheruser = Otheruser::getByGid(User::generateGid($name, $domain), $this->_view->user->getId());
                if ($otheruser === NULL) {
                    // no userdata yet, try to import it
                    $otheruser = Foreignotheruser::getForeignByName($name, $domain, $this->_view->user->getId(), FALSE);
                }
            }

            if ($otheruser !== NULL) {
                // got a user
                echo json_encode(array(array('name' => Security::wbrusername($otheruser->getName(), TRUE),
                        'icon' => $otheruser->getPictureUrl(Currentuser::PIC_SMALL),
                        'server' => ($otheruser->getHost() === NULL ? Server::getMyHost() : $otheruser->getHost()->getDomain()),
                        'url' => '/' . APPLICATION_LANG . '/user/' . $otheruser->getName())));
            } else {
                // no result
                echo json_encode(array());
            }
            exit();
        }

        $query = '%' . strtolower($query) . '%';

        $stm = Famework_Registry::getDb()->prepare('SELECT u.name, u.id FROM user u '
                . 'JOIN user_data d ON d.user_id = u.id '
                . 'WHERE u.host_gid IS NULL AND d.activated = 1 AND u.name LIKE ?');
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
