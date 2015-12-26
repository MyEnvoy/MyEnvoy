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
                            <form method="post" action="/<?php echo APPLICATION_LANG; ?>/post/add">
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
                        <div class="col six">

                            <?php
                            $posts = $this->user->getWall();

                            foreach ($posts as $post) :
                                ?>
                                <div class="row dashboard_post_container" post-id="<?php echo $post['post']->getId(); ?>">
                                    <div class="col ten">
                                        <?php $post['post']->render($this->user); ?>

                                        <div class="row dashboard_post_comments">
                                            <div class="col ten">

                                                <?php foreach ($post['comments'] as $comment): ?>
                                                    <div class="onecomment" post-id="<?php echo $comment['comment']->getId(); ?>">
                                                        <?php $comment['comment']->render($this->user); ?>
                                                        <?php
                                                        foreach ($comment['subcomments'] as $subcomment) {
                                                            $subcomment->render($this->user);
                                                        }
                                                        ?>
                                                        <div class="row dashboard_post_comments_newsub" style="display: none;">
                                                            <div class="col one">
                                                                <img src="<?php echo $this->user->getPictureUrl(Currentuser::PIC_SMALL); ?>" width="20" height="20" alt="Posting user picture">
                                                            </div>
                                                            <div class="col nine">
                                                                <div class="row">
                                                                    <div class="col ten">
                                                                        <input type="text" class="newsubcomment small_input" placeholder="<?php echo t('dashboard_post_comment_placeholder'); ?>">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>

                                                <div class="row dashboard_post_comments_new">
                                                    <div class="col one">
                                                        <img src="<?php echo $this->user->getPictureUrl(Currentuser::PIC_SMALL); ?>" width="<?php echo Currentuser::PIC_SMALL; ?>" height="<?php echo Currentuser::PIC_SMALL; ?>" alt="Posting user picture">
                                                    </div>
                                                    <div class="col nine">
                                                        <div class="row dashboard_post_user">
                                                            <div class="col ten">
                                                                <input type="text" class="newcomment small_input" placeholder="<?php echo t('dashboard_post_comment_placeholder'); ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div>
                        <div class="col four inline_padding">
                            Some widgets
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>