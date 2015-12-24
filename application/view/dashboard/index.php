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
                <img alt="Small Profile Picture" src="<?php echo $this->user->getPictureUrl(Currentuser::PIC_SMALL) ?>" width="<?php echo Currentuser::PIC_SMALL; ?>" height="<?php echo Currentuser::PIC_SMALL; ?>">
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
            <div class="row">
                <div class="col three" id="dashboard_userinfo_container">
                    <div class="row">
                        <div class="col ten center_txt">
                            <img class="profile_pic" src="<?php echo $this->user->getPictureUrl(Currentuser::PIC_LARGE); ?>" width="<?php echo Currentuser::PIC_LARGE; ?>" height="<?php echo Currentuser::PIC_LARGE; ?>" alt="Big Profile Picture">
                        </div>
                    </div>
                    <div class="row" id="dashboard_username_container">
                        <div class="col ten">
                            <span><?php echo $this->user->getName(); ?></span><span class="text_light"><wbr>@<?php echo Server::getMyHost(); ?></span>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col ten" id="dashboard_registered_since">
                            <span class="genericon genericon-time"></span><?php echo t('dashboard_registered_since') . ' ' . Dateutils::formatDateWithTextMonth(new DateTime($this->user->getAddDate())); ?>
                        </div>
                    </div>
                </div>
                <div class="col seven">
                    <div class="row">
                        <div id="dashboard_postit">
                            <form method="post">
                                <div class="col ten">
                                    <div class="row">
                                        <div class="col ten">
                                            <div class="form_group">
                                                <textarea required name="post" data-toggle="tooltip" data-placement="top" 
                                                          title="<?php echo t('dashboard_textarea_tooltip'); ?>" placeholder="<?php echo t('dashboard_textarea_placeholder'); ?>"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row margin">
                                        <div class="col ten">
                                            <input type="submit" class="btn btn_primary right">
                                            <select id="dashboard_postit_group" name="group" class="right inline_margin_inverse">
                                                <?php foreach ($this->user->getGroupOverview() as $key => $value) : ?>
                                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label class="right inline_margin_inverse" id="dashboard_postit_tolabel"><?php echo t('dashboard_post_recipient'); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        - POSTS WILL COME HERE - 
                    </div>
                </div>
            </div>
        </div>
    </div>