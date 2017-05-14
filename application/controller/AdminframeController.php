<?php

use Famework\View\Famework_View_Frame_Controller;

class AdminframeController implements Famework_View_Frame_Controller {

    public function __construct($view) {
        $view->addCSS('https://fonts.googleapis.com/icon?family=Material+Icons');
        $view->addCSS('https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.2/css/materialize.min.css');
        $view->addMeta('viewport', 'width=device-width, initial-scale=1.0');
        $view->addCSS(HTTP_ROOT . 'css/admin.css');
    }

    public function renderTop() {
        ?>
        <nav>
            <div class="nav-wrapper grey darken-3">
                <a href="/admin/index" class="brand-logo"><img id="logo" src="/img/logo/logo.svg"></a>
                <ul class="right hide-on-med-and-down">
                    <li><a href="/admin/index" class="waves-effect waves-light btn">Overview <i class="material-icons right">dashboard</i></a></li>
                    <li><a href="/admin/user" class="waves-effect waves-light btn">Users <i class="material-icons right">account_circle</i></a></li>
                    <li><a href="/admin/prosody" class="waves-effect waves-light btn">Prosody <i class="material-icons right">chat_bubble_outline</i></a></li>
                    <li><a href="/admin/logout.do" class="waves-effect btn red darken-2">Logout <i class="material-icons right">lock</i></a></li>
                </ul>
            </div>
        </nav>
        <div class="container">
            <?php
        }

        public function renderBottom() {
            ?>
        </div>
        <?php
    }

}
