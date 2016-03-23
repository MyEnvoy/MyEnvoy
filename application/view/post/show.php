<div id="dashboard_content">

    <?php require APPLICATION_PATH . 'view/dashboard/helper/menubar.php'; ?>

    <div id="dashboard_central_container">

        <?php if (!isset($this->error)): ?>
            <form id="dashboard_post_comment_form" method="post" action="/<?php echo APPLICATION_LANG; ?>/post/comment?redirectlocation=<?php echo urlencode('post/show?id=' . $this->post->getId()); ?>" style="display: none;">
                <input id="dashboard_post_comment_id" type="text" name="id" required>
                <textarea id="dashboard_post_comment_content" name="post" required></textarea>
            </form>
        <?php endif; ?>


        <div class="dashboard_realsize_container">

            <?php if (isset($this->error)): ?>
                <div class="col ten">
                    <div class="alert alert_danger"><b><?php echo t('post_show_postnotfound'); ?></b></div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col three"></div>
                    <div class="row">
                        <div class="col seven">
                            <div class="row">
                                <div class="col six">
                                    <div class="row">
                                        <div class="col ten">

                                            <?php Post::renderLikeWall($this->user, array(array('post' => $this->post, 'comments' => $this->post->getEntireComments()))); ?>

                                        </div>
                                    </div>
                                    <div class="col four"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    $('.onecomment').each(highlightPost);
    $('.dashboard_post_subcomment').each(highlightPost);

    function highlightPost() {
        if ($(this).attr('post-id') === '<?php echo $this->markPost; ?>') {
            $(this).addClass('glowing_post');
        }
    }
</script>
