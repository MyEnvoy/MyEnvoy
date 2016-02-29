<div id="dashboard_content">

    <?php require APPLICATION_PATH . 'view/dashboard/helper/menubar.php'; ?>

    <div id="dashboard_central_container">
        <div class="dashboard_realsize_container">

            <?php require APPLICATION_PATH . 'view/settings/helper/tabs.php'; ?>

            <div class="row margin2">
                <div class="col five">
                    <form action="/<?php echo APPLICATION_LANG; ?>/settings/index.do" method="post">
                        <div class="row form_group horizontal_form">
                            <label for="display_name" class="col three right_txt"><?php echo t('settings_general_displayname'); ?></label>
                            <input class="col six" id="display_name" name="display_name" 
                                   placeholder="<?php echo t('settings_general_displayname'); ?>" value="<?php echo Security::htmloutput($this->user->getDisplayName()); ?>"
                                   data-placement="top" data-toggle="popover" data-trigger="hover" data-content="<?php echo t('settings_general_displayname_hint'); ?>" pattern=".{3,40}">
                        </div>
                        <div class="row form_group horizontal_form">
                            <label for="city" class="col three right_txt"><?php echo t('settings_general_city'); ?></label>
                            <a data-toggle="tooltip" data-placement="top" title="<?php echo t('settings_tab_general_localise_tooltip'); ?>"
                               onclick="getLocation()" id="settings_general_localise" class="noa"><span class="genericon genericon-location"></span></a>
                            <input class="col six" id="city" name="city" placeholder="<?php echo t('settings_general_city'); ?>" pattern=".{0,100}"
                                   value="<?php echo Security::htmloutput($this->user->getSettings()->getWeatherCity()); ?>"
                                   data-placement="top" data-toggle="popover" data-trigger="hover" data-content="<?php echo t('settings_general_city_hint'); ?>">
                        </div>
                        <div class="row form_group horizontal_form">
                            <div class="col nine">
                                <input type="submit" class="btn btn_success right" value="<?php echo t('settings_save_btn'); ?>">
                            </div>
                        </div>
                    </form>

                    <form method="post" action="/<?php echo APPLICATION_LANG; ?>/settings/status.do">
                        <div class="row margin2 form_group horizontal_form">
                            <label for="status" class="col three right_txt"><?php echo t('settings_general_status'); ?></label>
                            <textarea class="col six" id="status" name="status" placeholder="<?php echo t('settings_general_status'); ?>" pattern=".{0,140}" maxlength="140"><?php echo $this->user->getStatus(); ?></textarea>
                        </div>
                        <div class="row form_group horizontal_form">
                            <div class="col nine">
                                <input type="submit" class="btn btn_success right" value="<?php echo t('settings_save_btn'); ?>">
                            </div>
                        </div>
                    </form>

                    <h4 class="margin2"><?php echo t('settings_general_resetpwd_h'); ?></h4>
                    <form method="post" action="/<?php echo APPLICATION_LANG; ?>/settings/pwd.do">
                        <div class="row margin2 form_group horizontal_form">
                            <label for="old_pwd" class="col three right_txt"><?php echo t('settings_general_oldpwd'); ?></label>
                            <input type="password" class="col six" id="old_pwd" name="old_pwd" placeholder="<?php echo t('settings_general_oldpwd'); ?>" pattern=".{8,}" required>
                        </div>
                        <div class="row form_group horizontal_form">
                            <label for="new_pwd" class="col three right_txt"><?php echo t('settings_general_newpwd'); ?></label>
                            <input type="password" class="col six" id="new_pwd" name="new_pwd" placeholder="<?php echo t('settings_general_newpwd'); ?>" pattern=".{8,}" required
                                   data-placement="top" data-toggle="popover" data-trigger="hover" data-content="<?php echo t('register_pwd_hint'); ?>">
                        </div>
                        <div class="row form_group horizontal_form">
                            <label for="new_pwd_rpt" class="col three right_txt" style="padding-top: 1px;"><?php echo t('settings_general_newpwd_repeat'); ?></label>
                            <input type="password" class="col six" id="new_pwd_rpt" name="new_pwd_rpt" placeholder="<?php echo t('settings_general_newpwd_repeat'); ?>" pattern=".{8,}" required>
                        </div>
                        <div class="row form_group horizontal_form">
                            <div class="col nine">
                                <input type="submit" class="btn btn_danger right" value="<?php echo t('settings_save_btn'); ?>">
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col five">
                    <form action="/<?php echo APPLICATION_LANG; ?>/settings/picture.do" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col ten center_txt">
                                <div id="pic_prev_holder">
                                    <img id="register_profile_pic" width="<?php echo Currentuser::PIC_LARGE; ?>" alt="Big Profile Picture" src="<?php echo $this->user->getPictureUrl(Currentuser::PIC_LARGE); ?>">
                                </div>
                                <div class="btn btn_default btn_file" data-placement="left" data-toggle="popover" data-trigger="hover" data-content="<?php echo t('register_picture_hint'); ?>">
                                    <?php echo t('register_upload_pic'); ?> <input name="profilepic" type="file" onchange="picturePreview(this);">
                                    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Picture::MAX_FILE_SIZE; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row form_group horizontal_form">
                            <div class="col ten center_txt">
                                <input type="submit" class="btn btn_success" value="<?php echo t('settings_save_btn'); ?>">
                            </div>
                        </div>
                    </form>
                    <div class="row form_group horizontal_form">
                        <div class="col ten center_txt">
                            <a onclick="jsconfirm('/<?php echo APPLICATION_LANG; ?>/settings/picture.remove', '<?php echo t('settings_jsconfirm'); ?>');" class="noa btn btn_danger"><?php echo t('settings_general_pic_delete'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>