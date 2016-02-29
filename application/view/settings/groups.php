<div id="dashboard_content">

    <?php require APPLICATION_PATH . 'view/dashboard/helper/menubar.php'; ?>

    <div id="dashboard_central_container">
        <div class="dashboard_realsize_container">

            <?php require APPLICATION_PATH . 'view/settings/helper/tabs.php'; ?>

            <div id="settings_groups_trash" class="alert alert_danger"></div>

            <div class="row margin2">
                <div class="col ten">
                    <div class="row">
                        <div class="col two" id="settings_groups_add_new_col">
                            <button class="btn btn_file" id="settings_groups_add_new"><?php echo t('settings_groups_btn_add'); ?></button>
                        </div>
                        <form method="post" action="/<?php echo APPLICATION_LANG; ?>/settings/group.add" id="settings_groups_add_form">
                            <div class="col nine">
                                <input type="text" id="settings_groups_add_input" maxlength="<?php echo Group::MAX_NAME_LENGTH; ?>" required name="name" placeholder="<?php echo t('settings_groups_input_add'); ?>">
                            </div>
                            <div class="col one right_txt">
                                <input type="submit" class="btn btn_success" value="<?php echo t('settings_groups_btn_add_sumbit'); ?>">
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row margin2">
                <div class="col ten">
                    <div class="row">
                        <form method="post" action="/<?php echo APPLICATION_LANG; ?>/settings/groups.do" id="settings_groups_savechanges_form">
                            <?php
                            $groups = $this->user->getGroupOverview();
                            ksort($groups);
                            $count = 0;
                            foreach ($groups as $id => $name):
                                ?>
                                <div class="col three dashboard_post_container group_margin">
                                    <div class="row settings_groups_list_title">
                                        <div class="col nine">
                                            <h3><?php echo Security::htmloutput($name); ?></h3>
                                        </div>
                                        <?php if ($count > 0): ?>
                                            <div class="col one">
                                                <a class="noa" onclick="jsconfirm('/<?php echo APPLICATION_LANG; ?>/settings/group.remove?id=<?php echo $id; ?>', '<?php echo t('settings_jsconfirm'); ?>')">
                                                    <span class="genericon genericon-trash"></span>
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                    <ul id="settings_groups_list_<?php echo $id; ?>" class="settings_groups_list" group-id="<?php echo $id; ?>">
                                        <?php foreach (Group::getMembers($id, $this->user) as $member) : ?>
                                            <li class="settings_groups_userrow">
                                                <img class="profile_pic" src="<?php echo $member->getPictureUrl(Currentuser::PIC_LARGE); ?>" alt="profile picture" width="50" height="50">
                                                <a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $member->getFullQualifiedName(); ?>"><?php echo $member->getDisplayName(); ?></a>
                                                <input type="hidden" name="users[<?php echo $id; ?>][]" value="<?php echo $member->getId(); ?>" required>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php if ($count > 0): ?>
                                        <a class="btn btn_success noa fixform"><?php echo t('settings_save_btn'); ?></a>
                                    <?php endif; ?>
                                </div>
                                <?php
                                $count++;
                            endforeach;
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>