<div id="social_icons">
    <div class="row">
        <div class="col ten">
            <a href="https://github.com/LaCodon/MyEnvoy" target="_blank" class="noa">
                <img src="/img/github-square-social-media.png" width="48" height="48" alt="MyEnvoy on GitHub">
            </a>
        </div>
    </div>
</div>
<div id="center_content" class="center">
    <?php if (!empty($this->success)): ?>
        <div class="row">
            <div class="col ten">
                <div class="alert alert_success"><b><?php echo t('login_register_success'); ?></b></div>
            </div>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col ten center_txt">
            <img width="256" height="256" src="/img/profile256.png" alt="MyEnvoy Profile">
        </div>
    </div>
    <div class="row">
        <form>
            <div class="col ten">
                <div class="form_group">
                    <label for="name"><?php echo t('login_user_id'); ?></label>
                    <input id="input_user_id" type="text" id="name" name="name" placeholder="<?php echo t('login_user_id'); ?>" autofocus>
                    <label id="input_info_overlay">@<?php echo Server::getMyHost(); ?></label>
                </div>
                <div class="form_group">
                    <label for="pwd"><?php echo t('login_user_pwd'); ?></label>
                    <input type="password" id="pwd" name="pwd" placeholder="<?php echo t('login_user_pwd'); ?>">
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
            <a href="#" class="right ash5"><?php echo t('login_link_pwdrecover') ?></a>
        </div>
    </div>
</div>