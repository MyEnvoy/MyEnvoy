<div id="center_content" class="center">
    <?php if (!empty($this->hint)): ?>
        <div class="row">
            <div class="col ten">
                <div class="alert alert_danger"><b><?php echo t('register_errorhint_' . (int) $this->hint); ?></b></div>
            </div>
        </div>
    <?php endif; ?>
    <form action="/<?php echo APPLICATION_LANG; ?>/register/register.do" method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col ten center_txt">
                <div id="pic_prev_holder">
                    <img id="register_profile_pic" width="256" src="/img/profile256.png" alt="MyEnvoy Profile">
                </div>
                <div class="btn btn_default btn_file" data-placement="left" data-toggle="popover" data-trigger="hover" data-content="<?php echo t('register_picture_hint'); ?>">
                    <?php echo t('register_upload_pic'); ?> <input name="profilepic" type="file" onchange="picturePreview(this);">
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Picture::MAX_FILE_SIZE; ?>" /> 
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col ten">
                <div class="form_group">
                    <label for="name"><?php echo t('login_user_id'); ?></label>
                    <input id="input_user_id" type="text" id="name" name="name" placeholder="<?php echo t('login_user_id'); ?>" pattern="[a-z0-9.]{3,40}" 
                           required <?php echo ($this->hint !== RegisterController::ERR_EMAIL_USED ? ($this->hint !== NULL ? 'autofocus' : '') : ''); ?> data-placement="left" data-toggle="popover"
                           data-trigger="focus" data-content="<?php echo t('register_userid_hint'); ?>" value="<?php echo $this->name; ?>">
                    <label id="input_info_overlay">@<?php echo Server::getMyHost(); ?></label>
                </div>
                <div class="form_group">
                    <label for="email"><?php echo t('register_user_email'); ?></label>
                    <input type="email" id="email" name="email" placeholder="<?php echo t('register_user_email'); ?>"
                           required  <?php echo ($this->hint !== RegisterController::ERR_NAME_USED ? ($this->hint !== NULL ? 'autofocus' : '') : ''); ?>
                           data-placement="left" data-toggle="popover" data-trigger="focus" data-content="<?php echo t('register_email_hint'); ?>" value="<?php echo $this->email; ?>">
                </div>
                <div class="form_group">
                    <label for="pwd"><?php echo t('login_user_pwd'); ?></label>
                    <input type="password" id="pwd" name="pwd" placeholder="<?php echo t('login_user_pwd'); ?>" pattern=".{8,}" required autocomplete="off"
                           data-placement="left" data-toggle="popover" data-trigger="focus" data-content="<?php echo t('register_pwd_hint'); ?>">
                </div>
                <div class="form_group">
                    <label for="pwdrepeat"><?php echo t('register_user_pwd_repeat'); ?></label>
                    <input type="password" id="pwdrepeat" name="pwdrepeat" placeholder="<?php echo t('register_user_pwd_repeat'); ?>" pattern=".{8,}" required autocomplete="off">
                </div>
                <div class="form_group">
                    <p><?php echo t('register_accept_terms'); ?></p>
                </div>
                <div class="form_group">
                    <input class="btn btn_success right" type="submit" value="<?php echo t('register_btn_register'); ?>">
                </div>
            </div>
        </div>
    </form>
    <div class="row margin">
        <div class="col five">
            <a href="/<?php echo APPLICATION_LANG ?>" class="ash5"><?php echo t('register_link_login') ?></a>
        </div>
        <div class="col five">
            <a href="/<?php echo APPLICATION_LANG ?>/index/resetpw" class="ash5 right"><?php echo t('login_link_pwdrecover') ?></a>
        </div>
    </div>
</div>
