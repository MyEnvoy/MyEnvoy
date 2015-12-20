<div id="dashboard_content">
    <div id="dashboard_header_container">
        <div class="dashboard_realsize_container">
            <div id="dashboard_header_homelink" class="left">
                <a href="/<?php echo APPLICATION_LANG; ?>/dashboard/index" class="noa"><img src="/img/logo32bw.png" alt="MyEnvoy Logo" width="32" height="32"></a>
            </div>
            <div id="dashboard_header_searchbox" class="left">
                <form>
                    <input type="text" class="small_input inline_input" name="search" placeholder="<?php echo t('dashboard_index_search'); ?>">
                </form>
            </div>
            <div id="dashboard_header_usermenu" class="right" data-toggle="tooltip" data-placement="bottom" title="<?php echo t('dashboard_profile_tooltip'); ?>">
                <img src="<?php echo $this->user->getPictureUrl(Currentuser::PIC_SMALL) ?>" width="<?php echo Currentuser::PIC_SMALL; ?>" height="<?php echo Currentuser::PIC_SMALL; ?>">
                <span class="dropdown_arrow"></span>
            </div>
            <div class="dropdown">
                <ul class="dropdown_list">
                    <li><span class="greyspan"><?php echo t('dashboard_dropdown_title') ?> <b><?php echo $this->user->getName(); ?></b></span></li>
                    <hr>
                    <li><a href="/<?php echo APPLICATION_LANG; ?>/dashboard/logout"><?php echo t('dashboard_dropdown_logout'); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div id="dashboard_central_container">
        <div class="dashboard_realsize_container">
            <div class="col three"><p>asd</p></div>
            <div class="col seven">asd</div>
        </div>
    </div>
</div>