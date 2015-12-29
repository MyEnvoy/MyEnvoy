<div id="dashboard_content">

    <?php require APPLICATION_PATH . 'view/dashboard/helper/menubar.php'; ?>

    <div id="loading"><img src="/img/loading.gif" alt="Loading..."></div>

    <div id="dashboard_central_container">
        <div class="dashboard_realsize_container">
            <div class="row">
                <div class="col ten">
                    <input id="search_box" type="text" class="large_input" placeholder="<?php echo t('dashboard_index_search'); ?>" autofocus>
                </div>
            </div>
            <div id="results" class="row margin2">
                
            </div>
        </div>
    </div>
</div>