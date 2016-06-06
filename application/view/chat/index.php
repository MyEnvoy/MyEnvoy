<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo t('chatcontroller_title') . ' | ' . $this->user->getName() . '@MyEnvoy'; ?></title>
    </head>
    <body>
        <div id="dashboard_content">

            <?php require APPLICATION_PATH . 'view/dashboard/helper/menubar.php'; ?>

        </div>
        <div class="chat">
            <div id="chat_area">
                <ul class="chat_messages"></ul>
            </div>
            <div id="chat_input_container">
                <input class="large_input input_message" placeholder="Etwas schreiben...">
                <input class="input_username" type="hidden" value="<?php echo $this->user->getDisplayName(); ?>">
            </div>
        </div>
        <div id="online"></div>
    </body>
</html>