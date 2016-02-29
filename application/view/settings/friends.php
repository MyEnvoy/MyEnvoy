<div id="dashboard_content">

    <?php require APPLICATION_PATH . 'view/dashboard/helper/menubar.php'; ?>

    <div id="dashboard_central_container">
        <div class="dashboard_realsize_container">

            <?php require APPLICATION_PATH . 'view/settings/helper/tabs.php'; ?>


            <div class="row margin2">
                <?php
                foreach ($this->user->getMyFriends() as $friend) :
                    $conType = $this->user->getConnectionWith($friend);
                    $pic = Picture::getConnectionPicture($conType);
                    ?>
                    <div class="col dashboard_search_resultcontainer">
                        <div class="dashboard_search_resultwrapper">
                            <div class="row">
                                <div class="col one">
                                    <img class="profile_pic" src="<?php echo $friend->getPictureUrl(Currentuser::PIC_SMALL); ?>" alt="Profile picture" width="<?php echo Currentuser::PIC_SMALL; ?>" height="<?php echo Currentuser::PIC_SMALL; ?>">
                                </div>
                                <div class="col nine dashboard_search_username">
                                    <div class="row">
                                        <div class="col eight">
                                            <a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $friend->getFullQualifiedName(); ?>" class="text_bold"><?php echo Security::htmloutput($friend->getDisplayName()); ?></a><br>
                                            <span class="text_light">@<?php echo Security::htmloutput(($friend->getHost() === NULL ? Server::getMyHost() : $friend->getHost()->getDomain())); ?></span>
                                        </div>
                                        <div class="col two">
                                            <img <?php echo $pic; ?> height="30" alt="Connection type" data-toggle="tooltip" data-placement="top" title="<?php echo t('dashboard_connectionstatus_' . $conType); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>