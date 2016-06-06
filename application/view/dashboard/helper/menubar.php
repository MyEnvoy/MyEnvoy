<div id="dashboard_header_container">
    <div class="dashboard_realsize_container">
        <div id="dashboard_header_homelink" class="left">
            <a href="/<?php echo APPLICATION_LANG; ?>/dashboard/index" class="noa"><img src="/img/logo/logo128.png" alt="MyEnvoy Logo" width="32" height="32"></a>
        </div>
        <?php if (!empty($this->user)): ?>
            <?php if (!isset($this->disable_searchbar)) : ?>
                <div id="dashboard_header_searchbox" class="left">
                    <form>
                        <input accesskey="s" onfocus="window.location.href = '/<?php echo APPLICATION_LANG; ?>/dashboard/search'" type="text" class="small_input inline_input" placeholder="<?php echo t('dashboard_index_search'); ?>">
                    </form>
                </div>
            <?php endif; ?>
            <div id="dashboard_header_usermenu" class="right" data-toggle="tooltip" data-placement="bottom" title="<?php echo t('dashboard_profile_tooltip'); ?>">
                <img class="profile_pic" alt="Small Profile Picture" src="<?php echo $this->user->getPictureUrl(Currentuser::PIC_SMALL) ?>" width="<?php echo Currentuser::PIC_SMALL; ?>" height="<?php echo Currentuser::PIC_SMALL; ?>">
                <span class="dropdown_arrow"></span>
            </div>
            <div class="dropdown dropdown_usermenu">
                <ul class="dropdown_list">
                    <li><span class="greyspan"><?php echo t('dashboard_dropdown_title') ?> <b><?php echo $this->user->getName(); ?></b></span></li>
                    <hr>
                    <li><a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $this->user->getFullQualifiedName(); ?>"><span class="genericon genericon-user"></span><?php echo t('dashboard_dropdown_profile'); ?></a></li>
                    <li><a href="/<?php echo APPLICATION_LANG; ?>/settings"><span class="genericon genericon-cog"></span><?php echo t('dashboard_dropdown_settings'); ?></a></li>
                    <hr>
                    <li><a href="/<?php echo APPLICATION_LANG; ?>/dashboard/logout"><span class="genericon genericon-lock"></span><?php echo t('dashboard_dropdown_logout'); ?></a></li>
                </ul>
            </div>
            <div id="dashboard_header_notifications" class="right">
                <span class="genericon genericon-website"></span>
                <?php
                $count = $this->user->countNewNotifications();
                if ($count > 0):
                    ?>
                    <span id="notify_count"><?php echo $count; ?></span>
                <?php endif; ?>
            </div>
            <div class="dropdown dropdown_notify">
                <div id="notifications">
                    <?php foreach ($this->user->getNotifications() as $notification) : ?>
                        <div class="row" href="/de/notify/redir?id=<?php echo $notification->getId(); ?>">
                            <div class="col one"><span class="genericon <?php echo $notification->getGenericon(); ?>"></span></div>
                            <div class="col nine notify_explain">
                                <?php echo t($notification->getMsg()); ?>
                                <?php if ($notification->getRecStatus() === Notification::UNRECEIVED) : ?>
                                    <span class="notify_new"></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div id="dashboard_header_chat" class="right">
                <a href="/<?php echo APPLICATION_LANG; ?>/chat/index">
                    <span class="genericon genericon-comment"></span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>