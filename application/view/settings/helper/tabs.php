<?php if (isset($this->error)) : ?>
    <div class="row">
        <div class="col ten">
            <div class="alert alert_danger">
                <b><?php echo ($this->error == RegisterController::ERR_BAD_PASSWORD ? t('register_errorhint_' . RegisterController::ERR_BAD_PASSWORD) : t('settings_error')); ?></b>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col ten">
        <ul class="tab_list">
            <li><a href="/<?php echo APPLICATION_LANG ?>/settings" class="<?php echo ($this->activeTab === SettingsController::GENERAL_TAB ? 'active' : ''); ?>"><?php echo t('settings_general_tab_title'); ?></a></li>
            <li><a href="/<?php echo APPLICATION_LANG ?>/settings/groups" class="<?php echo ($this->activeTab === SettingsController::GROUPS_TAB ? 'active' : ''); ?>"
                   data-toggle="tooltip" data-placement="top" title="<?php echo t('settings_tab_groups_tooltip'); ?>"><?php echo t('settings_groups_tab_title'); ?></a></li>
            <li><a href="/<?php echo APPLICATION_LANG ?>/settings/friends" class="<?php echo ($this->activeTab === SettingsController::FRIENDS_TAB ? 'active' : ''); ?>"
                   data-toggle="tooltip" data-placement="top" title="<?php echo t('settings_tab_friends_tooltip'); ?>">Freunde</a></li>
            <li><a href="/<?php echo APPLICATION_LANG ?>/settings/design" class="<?php echo ($this->activeTab === SettingsController::DESIGN_TAB ? 'active' : ''); ?>"><?php echo t('settings_design_tab_title'); ?></a></li>
            <li><a href="/<?php echo APPLICATION_LANG ?>/settings/log" class="<?php echo ($this->activeTab === SettingsController::LOG_TAB ? 'active' : ''); ?>"><?php echo t('settings_log_tab_title'); ?></a></li>
        </ul>
    </div>
</div>