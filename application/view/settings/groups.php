<div id="dashboard_content">

    <?php require APPLICATION_PATH . 'view/dashboard/helper/menubar.php'; ?>

    <div id="dashboard_central_container">
        <div class="dashboard_realsize_container">

            <?php require APPLICATION_PATH . 'view/settings/helper/tabs.php'; ?>

            <div id="settings_groups_trash" class="alert alert_danger"></div>

            <div class="row margin2">
                <div class="col ten">
                    <div class="row">
                        <?php
                        $groups = $this->user->getGroupOverview();
                        ksort($groups);
                        $count = 0;
                        foreach ($groups as $id => $name):
                            ?>
                            <form method="post" action="/<?php echo APPLICATION_LANG; ?>/settings/groups.do">
                                <input type="hidden" name="group_id" value="<?php echo $id; ?>" required>
                                <div class="col three dashboard_post_container group_margin">
                                    <h3><?php echo Security::htmloutput($name); ?></h3>
                                    <ul id="settings_groups_list_<?php echo $id; ?>" class="settings_groups_list">
                                        <?php foreach (Group::getMembers($id, $this->user) as $member) : ?>
                                            <li class="settings_groups_userrow">
                                                <img class="profile_pic" src="<?php echo $member->getPictureUrl(Currentuser::PIC_LARGE); ?>" alt="profile picture" width="50" height="50">
                                                <a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $member->getFullQualifiedName(); ?>"><?php echo $member->getDisplayName(); ?></a>
                                                <input type="hidden" name="users[]" value="<?php echo $member->getId(); ?>" required>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php if ($count > 0): ?>
                                        <input type="submit" class="btn btn_success" value="<?php echo t('settings_save_btn'); ?>">
                                    <?php endif; ?>
                                </div>
                            </form>
                            <?php
                            $count++;
                        endforeach;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>