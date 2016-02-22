<div id="social_icons">
    <div class="row">
        <div class="col ten">
            <a href="https://github.com/LaCodon/MyEnvoy" target="_blank" class="noa">
                <img src="/img/github-square-social-media.png" width="48" height="48" alt="MyEnvoy on GitHub">
            </a>
        </div>
    </div>
</div>
<img id="index_logo_background" src="/img/logo/logo.svg" alt="MyEnvoy Logo">
<div id="center_content" class="center">
    <?php if (!empty($this->hint)): ?>
        <div class="row">
            <div class="col ten">
                <div class="alert <?php echo ($this->hint > 11 ? 'alert_danger' : 'alert_success') ?>">
                    <b><?php echo t('login_hint_' . (int) $this->hint); ?></b>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col ten center_txt">
            <img width="256" height="256" src="/img/profile256.png" alt="MyEnvoy Profile">
        </div>
    </div>
    <div class="row">
        <form action="/<?php echo APPLICATION_LANG . '/index/login.do' ?>" method="post">
            <div class="col ten">
                <div class="form_group">
                    <label for="input_user_id"><?php echo t('login_user_id'); ?></label>
                    <input id="input_user_id" type="text" name="name" placeholder="<?php echo t('login_user_id'); ?>" autofocus required pattern="[a-z0-9.]{3,40}">
                    <label id="input_info_overlay">@<?php echo Server::getMyHost(); ?></label>
                </div>
                <div class="form_group">
                    <label for="pwd"><?php echo t('login_user_pwd'); ?></label>
                    <input type="password" id="pwd" name="pwd" placeholder="<?php echo t('login_user_pwd'); ?>" required pattern=".{8,}">
                </div>
                <div class="form_group">
                    <input class="btn btn_success right" type="submit" value="<?php echo t('login_btn_login'); ?>">
                </div>
            </div>
        </form>
    </div>
    <div class="row margin">
        <div class="col five">
            <a href="/<?php echo APPLICATION_LANG ?>/register" class="ash5"><?php echo t('login_link_register') ?></a>
        </div>
        <div class="col five">
            <a href="/<?php echo APPLICATION_LANG ?>/index/resetpw" class="ash5 right"><?php echo t('login_link_pwdrecover') ?></a>
        </div>
    </div>
</div>