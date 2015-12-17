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
    <?php if (!empty($this->hint)): ?>
        <div class="row">
            <div class="col ten">
                <div class="alert alert_success">
                    <b><?php echo t('resetpw_hint'); ?></b>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col ten center_txt">
            <h2><?php echo t('resetpw_h2'); ?></h2>
        </div>
    </div>
    <div class="row">
        <form action="/<?php echo APPLICATION_LANG . '/index/resetpw.do' ?>" method="post">
            <div class="col ten">
                <div class="form_group">
                    <label for="name"><?php echo t('login_user_id'); ?></label>
                    <input id="input_user_id" type="text" id="name" name="name" placeholder="<?php echo t('login_user_id'); ?>" autofocus required pattern="[a-z0-9.]{3,40}">
                    <label id="input_info_overlay">@<?php echo Server::getMyHost(); ?></label>
                </div>
                <div class="form_group">
                    <label for="email"><?php echo t('register_user_email'); ?></label>
                    <input type="email" id="email" name="email" placeholder="<?php echo t('register_user_email'); ?>" required>
                </div>
                <div class="form_group">
                    <input class="btn btn_success right" type="submit" value="<?php echo t('resetpw_btn_reset'); ?>">
                </div>
            </div>
        </form>
    </div>
    <div class="row margin">
        <div class="col five">
            <a href="/<?php echo APPLICATION_LANG ?>" class="ash5"><?php echo t('register_link_login') ?></a>
        </div>
        <div class="col five">
            <a href="/<?php echo APPLICATION_LANG ?>/register" class="right ash5"><?php echo t('login_link_register') ?></a>
        </div>
    </div>
</div>