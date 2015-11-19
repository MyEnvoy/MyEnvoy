<?php

use Famework\Controller\Famework_Controller;
use Famework\View\Famework_View_Frame_Controller;

class FrameController extends Famework_Controller implements Famework_View_Frame_Controller {

    public function init() {
        $this->_view->addCSS(HTTP_ROOT . 'css/myenvoy.css');
    }

    public function renderTop() {
        ?>
        <div id="page_title_myenvoy">
            <h1>MyEnvoy</h1>
        </div>
        <?php
    }

    public function renderBottom() {
        ?>
        <footer></footer>
        <?php
    }

}
