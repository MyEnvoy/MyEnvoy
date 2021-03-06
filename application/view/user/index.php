<style>
    div#page_title_myenvoy {position: absolute;}
</style>

<div id="dashboard_content">

    <?php require APPLICATION_PATH . 'view/dashboard/helper/menubar.php'; ?>

    <div id="dashboard_central_container">

        <?php if ((!isset($this->error) || $this->error === FALSE ) && !empty($this->user)): ?>
            <form id="dashboard_post_comment_form" method="post" action="/<?php echo APPLICATION_LANG; ?>/post/comment?redirectlocation=user/<?php echo $this->otheruser->getFullQualifiedName(); ?>" style="display: none;">
                <input id="dashboard_post_comment_id" type="text" name="id" required>
                <textarea id="dashboard_post_comment_content" name="post" required></textarea>
            </form>
        <?php endif; ?>

        <div class="dashboard_realsize_container">
            <div class="row">
                <?php if (isset($this->error) && $this->error === TRUE): ?>
                    <div class="col ten">
                        <div class="alert alert_danger"><b><?php echo t('user_index_usernotfound'); ?></b></div>
                    </div>
                <?php else: ?>
                    <?php if (!empty($this->user) && $this->user->getId() === $this->otheruser->getId()) : ?>
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
                        <?php if (!empty($this->user) && $this->user->getId() !== $this->otheruser->getId()) : ?>
                            <div class="row">
                                <?php $pic = Picture::getConnectionPicture($this->connectionType); ?>
                                <div class="col three right_txt">
                                    <img class="profile_pic" src="<?php echo $this->user->getPictureUrl(Currentuser::PIC_SMALL); ?>" width="32" height="32" alt="My profile picture">
                                </div>
                                <div class="col four center_txt">
                                    <img <?php echo $pic; ?> height="30" alt="Connection type" data-toggle="tooltip" data-placement="top" title="<?php echo t('dashboard_connectionstatus_' . $this->connectionType); ?>">
                                </div>
                                <div class="col three">
                                    <img class="profile_pic" src="<?php echo $this->otheruser->getPictureUrl(Currentuser::PIC_SMALL); ?>" width="32" height="32" alt="Other user's profile picture">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col ten padding">
                                    <?php if ($this->connectionType & Currentuser::I_AM_FOLLOWING === Currentuser::I_AM_FOLLOWING) : ?>
                                        <a class="noa btn btn_danger fullsize btn_follow" href="/<?php echo APPLICATION_LANG . '/user/unfollow?id=' . $this->otheruser->getId(); ?>"><?php echo t('usercontroller_unfollow'); ?></a>
                                    <?php else: ?>
                                        <a class="noa btn btn_success fullsize btn_follow" href="/<?php echo APPLICATION_LANG . '/user/follow?id=' . $this->otheruser->getId(); ?>"><?php echo t('usercontroller_follow'); ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col seven">
                        <div class="row">
                            <div class="col six">
                                <?php
                                if ($this->connectionType & Currentuser::I_AM_FOLLOWING === Currentuser::I_AM_FOLLOWING) {
                                    Post::renderLikeWall($this->user, $this->posts, TRUE);
                                } else {
                                    foreach ($this->posts as $post):
                                        ?>
                                        <div class="row dashboard_post_container">
                                            <div class="col ten">
                                                <?php
                                                if ($this->connectionType & Currentuser::I_AM_FOLLOWING === Currentuser::I_AM_FOLLOWING) {
                                                    $post->render($this->user);
                                                } else {
                                                    $post->renderPublic($this->user);
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <?php
                                    endforeach;
                                }
                                ?>
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
                                                    <?php echo Security::htmloutput($this->status, FALSE); ?>
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