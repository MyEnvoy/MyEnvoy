<div id="dashboard_content">

    <?php require APPLICATION_PATH . 'view/dashboard/helper/menubar.php'; ?>

    <div id="dashboard_central_container">
        <div class="dashboard_realsize_container">

            <?php require APPLICATION_PATH . 'view/settings/helper/tabs.php'; ?>

            <div class="row margin2" style="margin-bottom: 40px;">
                <div class="col five center" style="float: none;">
                    <?php foreach (Userinfo::getCompleteLog($this->user->getId()) as $row) : ?>
                        <div class="row margin">
                            <div class="col one center_txt">
                                <?php
                                switch ($row['action']) {
                                    case Userinfo::MESSAGE_LOGIN_BLOCKED:
                                        echo '<span class="genericon genericon-spam" style="color: red;"></span>';
                                        break;
                                    case Userinfo::MESSAGE_LOGIN_FAIL:
                                        echo '<span class="genericon genericon-warning" style="color: orange;"></span>';
                                        break;
                                    case Userinfo::MESSAGE_PWD_CHANGED:
                                        echo '<span class="genericon genericon-refresh"></span>';
                                        break;
                                    case Userinfo::MESSAGE_LOGIN_SUCCESS:
                                        echo '<span class="genericon genericon-checkmark" style="color: green;"></span>';
                                        break;
                                    case Userinfo::MESSAGE_REGISTER:
                                        echo '<span class="genericon genericon-reply"></span>';
                                        break;
                                    case Userinfo::MESSAGE_ACTIVATE_ACCOUNT:
                                        echo '<span class="genericon genericon-subscribed"></span>';
                                        break;
                                    case Userinfo::MESSAGE_CREATE_PROSODY_ACCOUNT:
                                        echo '<span class="genericon genericon-chat"></span>';
                                        break;
                                }
                                ?>
                            </div>
                            <div class="col three">
                                <?php echo $row['action']; ?>
                            </div>
                            <div class="col three right_txt">
                                <?php echo $row['ip'] ?>
                            </div>
                            <div class="col three right_txt">
                                <?php echo Dateutils::formatDateTime(new DateTime($row['datetime'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>