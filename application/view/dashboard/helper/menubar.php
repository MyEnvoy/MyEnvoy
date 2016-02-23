<div id="dashboard_header_container">
    <div class="dashboard_realsize_container">
        <div id="dashboard_header_homelink" class="left">
            <a href="/<?php echo APPLICATION_LANG; ?>/dashboard/index" class="noa"><img src="/img/logo/logo128.png" alt="MyEnvoy Logo" width="32" height="32"></a>
        </div>
        <?php if (!isset($this->disable_searchbar)) : ?>
            <div id="dashboard_header_searchbox" class="left">
                <form>
                    <input accesskey="s" onfocus="window.location.href = '/<?php echo APPLICATION_LANG; ?>/dashboard/search'" type="text" class="small_input inline_input" placeholder="<?php echo t('dashboard_index_search'); ?>">
                </form>
            </div>
        <?php endif; ?>
        <div id="dashboard_header_usermenu" class="right" data-toggle="tooltip" data-placement="bottom" title="<?php echo t('dashboard_profile_tooltip'); ?>">
            <img alt="Small Profile Picture" src="<?php echo $this->user->getPictureUrl(Currentuser::PIC_SMALL) ?>" width="<?php echo Currentuser::PIC_SMALL; ?>" height="<?php echo Currentuser::PIC_SMALL; ?>">
            <span class="dropdown_arrow"></span>
        </div>
        <div class="dropdown">
            <ul class="dropdown_list">
                <li><span class="greyspan"><?php echo t('dashboard_dropdown_title') ?> <b><?php echo $this->user->getName(); ?></b></span></li>
                <hr>
                <li><a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $this->user->getFullQualifiedName(); ?>"><span class="genericon genericon-user"></span><?php echo t('dashboard_dropdown_profile'); ?></a></li>
                <li><a href="/<?php echo APPLICATION_LANG; ?>/settings"><span class="genericon genericon-cog"></span><?php echo t('dashboard_dropdown_settings'); ?></a></li>
                <hr>
                <li><a href="/<?php echo APPLICATION_LANG; ?>/dashboard/logout"><span class="genericon genericon-lock"></span><?php echo t('dashboard_dropdown_logout'); ?></a></li>
            </ul>
        </div>
    </div>
</div>