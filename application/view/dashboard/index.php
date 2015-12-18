<div id="dashboard_content">
    <div id="dashboard_header_container">
        <div id="dashboard_realsize_container">
            <div id="dashboard_header_homelink" class="left">
                <a href="/<?php echo APPLICATION_LANG; ?>/dashboard/index" class="noa"><img src="/img/logo32bw.png" alt="MyEnvoy Logo" width="32" height="32"></a>
            </div>
            <div id="dashboard_header_searchbox" class="left">
                <form>
                    <input type="text" class="small_input inline_input" name="search" placeholder="<?php echo t('dashboard_index_search'); ?>">
                </form>
            </div>
            <div id="dashboard_header_usermenu" class="right">
                <img src="<?php echo $this->user->getPictureUrl(Currentuser::PIC_SMALL) ?>" width="<?php echo Currentuser::PIC_SMALL; ?>" height="<?php echo Currentuser::PIC_SMALL; ?>">
                <span class="dropdown_arrow"></span>
                <div style="position: absolute;background-color: white;padding: 10px;margin-left: -10px;">
                    sad
                </div>
            </div>
        </div>
    </div>
</div>