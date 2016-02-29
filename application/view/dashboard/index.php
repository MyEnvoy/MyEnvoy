<div id="dashboard_content">

    <?php require APPLICATION_PATH . 'view/dashboard/helper/menubar.php'; ?>

    <div id="dashboard_central_container">

        <form id="dashboard_post_comment_form" method="post" action="/<?php echo APPLICATION_LANG; ?>/post/comment" style="display: none;">
            <input id="dashboard_post_comment_id" type="text" name="id" required>
            <textarea id="dashboard_post_comment_content" name="post" required></textarea>
        </form>

        <div class="dashboard_realsize_container">
            <div class="row">
                <div class="col three" id="dashboard_userinfo_container">
                    <div class="row">
                        <div class="col ten center_txt">
                            <a class="noa" href="/<?php echo APPLICATION_LANG; ?>/settings">
                                <img class="profile_pic" src="<?php echo $this->user->getPictureUrl(Currentuser::PIC_LARGE); ?>" width="<?php echo Currentuser::PIC_LARGE; ?>" height="<?php echo Currentuser::PIC_LARGE; ?>" alt="Big Profile Picture">
                            </a>
                        </div>
                    </div>
                    <div class="row" id="dashboard_username_container">
                        <div class="col ten">
                            <span><?php echo Security::wbrusername($this->user->getName(), TRUE); ?></span><span class="text_light"><wbr>@<?php echo Server::getMyHost(); ?></span>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col ten" id="dashboard_registered_since">
                            <span class="genericon genericon-time"></span><?php echo t('dashboard_registered_since') . ' ' . Dateutils::formatDateWithTextMonth($this->user->getAddDate()); ?>
                        </div>
                    </div>
                </div>
                <div class="col seven">
                    <div class="row">
                        <div id="dashboard_postit">
                            <form method="post" action="/<?php echo APPLICATION_LANG; ?>/post/add">
                                <div class="col ten">
                                    <div class="row">
                                        <div class="col ten">
                                            <div class="form_group">
                                                <textarea required name="post" data-toggle="tooltip" data-placement="top" pattern=".{1,<?php echo PostController::MAX_POST_SIZE; ?>}"
                                                          title="<?php echo t('dashboard_textarea_tooltip'); ?>" placeholder="<?php echo t('dashboard_textarea_placeholder'); ?>"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row margin">
                                        <div class="col ten">
                                            <input id="dashboard_postit_btn" type="submit" class="btn btn_primary right" value="<?php echo t('dashboard_post_btn_send'); ?>">
                                            <select id="dashboard_postit_group" name="group" class="right inline_margin_inverse">
                                                <?php foreach ($this->user->getGroupOverview() as $key => $value) : ?>
                                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label class="right inline_margin_inverse text_light" id="dashboard_postit_tolabel"><?php echo t('dashboard_post_recipient'); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col <?php echo (isset($this->weather_city) ? 'six' : 'ten'); ?>">

                            <?php
                            $posts = $this->user->getWall();
                            Post::renderLikeWall($this->user, $posts);
                            ?>

                        </div>
                        <?php if (isset($this->weather_city)) : ?>
                            <div class="col four inline_padding">
                                <div class="row dashboard_post_container" id="dashboard_weather_widget">
                                    <div id="dashboard_weather_settings">
                                        <a class="noa" href="/<?php echo APPLICATION_LANG; ?>/settings"><span class="genericon genericon-cog"></span></a>
                                    </div>
                                    <div class="col ten">
                                        <div class="row">
                                            <div class="col ten">
                                                <h3><?php echo t('dashboard_widget_weather_heading'); ?></h3>
                                                <span class="genericon genericon-location text_light weathericon"></span><span class="text_light"><?php echo Security::htmloutput($this->weather_city); ?></span>
                                            </div>
                                        </div>
                                        <div class="row" id="dashboard_weather_info">
                                            <div class="col six">
                                                <div class="center_txt" data-toggle="tooltip" data-placement="top" title="<?php echo Security::htmloutput($this->weather_desc); ?>">
                                                    <img src="<?php echo $this->weather_icon; ?>" alt="Weather icon">
                                                </div>
                                            </div>
                                            <div class="col four">
                                                <div id="dashboard_weather_temp" class="center">
                                                    <?php echo $this->weather_temp; ?> °C
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
