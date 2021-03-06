<?php

use Famework\Famework;
use Famework\Controller\Famework_Controller;
use Famework\View\Famework_View_Frame_Controller;
use Famework\Registry\Famework_Registry;

class FrameController extends Famework_Controller implements Famework_View_Frame_Controller {

    public function init() {
        $this->_view->addCSS(HTTP_ROOT . 'css/genericons.css');
        $this->_view->addCSS(HTTP_ROOT . 'css/myenvoy.css');
        $this->_view->addHeadElement('<link rel="shortcut icon" href="/img/logo/favicon.ico">');
    }

    public function renderTop() {
        ?>
        <div class="overlay"></div>
        <?php
        if (!isset($this->_view->user)) :
            ?>
            <div id="page_title_myenvoy">
                <h1><a href="/<?php echo APPLICATION_LANG ?>" class="noa">MyEnvoy</a></h1>
            </div>
            <?php
        else:
            ProsodyChat::prebind($this->_view->user);
            ProsodyChat::render();
        endif;

        if (Famework_Registry::getEnv() === Famework::ENV_DEV) {
            echo '<style>div#dashboard_header_container {background-color: #f2dede !important;}</style>';
        }
    }

    public function renderBottom() {
        ?>
        <footer></footer>
        <?php
    }

}
