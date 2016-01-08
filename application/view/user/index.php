<div id="dashboard_content">

    <?php require APPLICATION_PATH . 'view/dashboard/helper/menubar.php'; ?>

    <div id="dashboard_central_container">

        <div class="dashboard_realsize_container">
            <div class="row">
                <?php if (isset($this->error)): ?>
                    <div class="col ten">
                        <div class="alert alert_danger"><b><?php echo t('user_index_usernotfound'); ?></b></div>
                    </div>
                <?php else: ?>
                    <?php if ($this->user->getId() === $this->otheruser->getId()) : ?>
                        <div class="col ten">
                            <div class="alert alert_success"><b><?php echo t('user_index_youlikepublic'); ?></b></div>
                        </div>
                    <?php endif; ?>
                    <div class="col three" id="dashboard_userinfo_container">
                        <div class="row">
                            <div class="col ten center_txt">
                                <img class="profile_pic" src="<?php echo $this->otheruser->getPictureUrl(Currentuser::PIC_LARGE); ?>" width="<?php echo Currentuser::PIC_LARGE; ?>" height="<?php echo Currentuser::PIC_LARGE; ?>" alt="Big Profile Picture">
                            </div>
                        </div>
                        <div class="row" id="dashboard_username_container">
                            <div class="col ten">
                                <span><?php echo Security::wbrusername($this->otheruser->getName(), TRUE); ?></span><span class="text_light"><wbr>@<?php echo Server::getMyHost(); ?></span>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col five center_txt colwithborderright">
                                <div class="row">
                                    <div class="col ten user_info_count">
                                        <?php echo $this->otheruser->countFollowers(); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col ten">
                                        <span class="text_light">Follower</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col five center_txt">
                                <div class="row">
                                    <div class="col ten user_info_count">
                                        <?php echo $this->otheruser->countPosts(); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col ten">
                                        <span class="text_light">Posts</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            FREUNDSCHAFTSSTATUS MIT DIESEM NUTZER
                        </div>
                    </div>
                    <div class="col seven">
                        <div class="row">
                            <div class="col six">
                                <?php foreach ($this->posts as $post): ?>
                                    <div class="row dashboard_post_container">
                                        <div class="col ten">
                                            <?php $post->renderPublic($this->user); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="col four inline_padding">
                                <div class="row dashboard_post_container" id="dashboard_weather_widget">
                                    <div class="col ten">
                                        <div class="row">
                                            <div class="col ten">
                                                <h3><?php echo t('user_index_status'); ?></h3>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col ten">
                                                <p id="user_status">
                                                    <?php echo $this->status; ?>
                                                </p>
                                            </div>
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