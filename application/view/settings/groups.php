<div id="dashboard_content">

    <?php require APPLICATION_PATH . 'view/dashboard/helper/menubar.php'; ?>

    <div id="dashboard_central_container">
        <div class="dashboard_realsize_container">

            <?php require APPLICATION_PATH . 'view/settings/helper/tabs.php'; ?>

            <div class="row margin2">
                <div class="col ten">
                    <?php foreach ($this->user->getGroupOverview() as $id => $name): ?>
                        <div class="row">
                            <div class="col ten">
                                <h3><?php echo Security::htmloutput($name); ?></h3>
                                <ul id="items">
                                    <?php foreach (Group::getMembers($id, $this->user) as $member) : ?>
                                        <li class="row settings_groups_userrow">1
                                            <img src="<?php echo $member->getPictureUrl(Currentuser::PIC_SMALL); ?>" alt="profile picture" width="<?php echo Currentuser::PIC_SMALL ?>">
                                            <a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $member->getFullQualifiedName(); ?>"><?php echo $member->getDisplayName(); ?></a>
                                        </li>
                                        <li class="row settings_groups_userrow">2
                                            <img src="<?php echo $member->getPictureUrl(Currentuser::PIC_SMALL); ?>" alt="profile picture" width="<?php echo Currentuser::PIC_SMALL ?>">
                                            <a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $member->getFullQualifiedName(); ?>"><?php echo $member->getDisplayName(); ?></a>
                                        </li>
                                        <li class="row settings_groups_userrow">3
                                            <img src="<?php echo $member->getPictureUrl(Currentuser::PIC_SMALL); ?>" alt="profile picture" width="<?php echo Currentuser::PIC_SMALL ?>">
                                            <a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $member->getFullQualifiedName(); ?>"><?php echo $member->getDisplayName(); ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <ul id="elements">
                                    <?php foreach (Group::getMembers($id, $this->user) as $member) : ?>
                                        <li class="row settings_groups_userrow">11
                                            <img src="<?php echo $member->getPictureUrl(Currentuser::PIC_SMALL); ?>" alt="profile picture" width="<?php echo Currentuser::PIC_SMALL ?>">
                                            <a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $member->getFullQualifiedName(); ?>"><?php echo $member->getDisplayName(); ?></a>
                                        </li>
                                        <li class="row settings_groups_userrow">22
                                            <img src="<?php echo $member->getPictureUrl(Currentuser::PIC_SMALL); ?>" alt="profile picture" width="<?php echo Currentuser::PIC_SMALL ?>">
                                            <a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $member->getFullQualifiedName(); ?>"><?php echo $member->getDisplayName(); ?></a>
                                        </li>
                                        <li class="row settings_groups_userrow">33
                                            <img src="<?php echo $member->getPictureUrl(Currentuser::PIC_SMALL); ?>" alt="profile picture" width="<?php echo Currentuser::PIC_SMALL ?>">
                                            <a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $member->getFullQualifiedName(); ?>"><?php echo $member->getDisplayName(); ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>