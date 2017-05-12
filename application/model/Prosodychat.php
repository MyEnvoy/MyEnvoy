<?php

use Famework\Registry\Famework_Registry;
use Thirdparty\XmppPrebind;

class ProsodyChat {

    public static function render() {
        $collapse = TRUE;
        $cookies = Famework_Registry::get('\cookies');
        if (isset($cookies['prosody_bar_closed'])) {
            $collapse = $cookies['prosody_bar_closed'] === 'true';
        }
        ?>
        <div id="prosody_chatbar_container" class="<?php echo ($collapse ? 'collapsed' : ''); ?>">
            <div id="prosody_chatbar_content">
                <div id="prosody_chatbar_roster">
                    <div id="prosody_chatbar_roster_controller">
                        <span id="prosody_chatbar_roster_controller_btn" class="genericon genericon-comment"></span>
                    </div>
                    <div id="prosody_chatbar_roster_wrapper">
                        <div id="prosody_chatbar_roster_wrapper_scroll">
                            <!-- Space for user heads -->
                        </div>
                    </div>
                </div>
                <div id="prosody_chatbar_search">
                    <div id="prosody_chatbar_search_wrapper">
                        <input type="text" id="prosody_chatbar_search_input" placeholder="<?php echo t('dashboard_index_search'); ?>">
                    </div>
                    <div id="prosody_loading"><img src="/img/loading.gif" alt="Loading..."></div>
                    <div id="prosody_chatbar_search_results">
                        <!-- Space for search results -->
                    </div>
                </div>
            </div>
        </div>

        <div id="prosody_chatwindow_container">
            <!-- Space for chats -->
        </div>
        <?php
    }

    public static function prebind(Currentuser $user) {
        $prebind = new XmppPrebind(Server::getMyHost(), Server::getRootLink() . 'http-bind', 'MyEnvoyWeb');
        $prebind->connect($user->getName(), $user->getXmppPwd());
        $prebind->auth();
        $prebindData = $prebind->getSessionInfo();
        ?>
        <script>
            var prosodyJid = "<?php echo $prebindData['jid']; ?>";
            var prosodySid = "<?php echo $prebindData['sid']; ?>";
            var prosodyRid = "<?php echo $prebindData['rid']; ?>";
        </script>
        <?php
    }

}
