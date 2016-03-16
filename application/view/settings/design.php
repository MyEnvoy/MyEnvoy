<div id="dashboard_content">

    <?php require APPLICATION_PATH . 'view/dashboard/helper/menubar.php'; ?>

    <div id="dashboard_central_container">
        <div class="dashboard_realsize_container">

            <?php require APPLICATION_PATH . 'view/settings/helper/tabs.php'; ?>

            <div class="row margin2">
                <h4><?php echo t('settings_design_commands_title'); ?></h4>
                <p><?php echo t('settings_design_commands_intro'); ?></p>
                <div class="col three">
                    <table class="padding">
                        <tr>
                            <td><?php echo t('settings_design_codeexplain_bgcolor'); ?></td>
                            <td><pre>background-color: #000000;</pre></td>
                        </tr>
                        <tr>
                            <td><?php echo t('settings_design_codeexplain_color'); ?></td>
                            <td><pre>color: #000000;</pre></td>
                        </tr>
                        <tr>
                            <td><?php echo t('settings_design_codeexplain_width'); ?></td>
                            <td><pre>width: 20px;</pre></td>
                        </tr>
                    </table>
                </div>
                <div class="col three">
                    <table class="padding">
                        <tr>
                            <td><?php echo t('settings_design_codeexplain_border'); ?></td>
                            <td><pre>border-color: #000000;</pre></td>
                        </tr>
                        <tr>
                            <td><?php echo t('settings_design_codeexplain_more'); ?></td>
                            <td><a href="http://www.w3schools.com/css/default.asp" target="_blank">w3schools.org</a></td>
                        </tr>
                    </table>
                </div>
                <div class="col three padding">
                    <input type="text" class="minicolors input-lg" placeholder="<?php echo t('settings_design_color_picker'); ?>" />
                </div>
            </div>
            <form method="post" action="/<?php echo APPLICATION_LANG; ?>/settings/design.do">
                <div class="row margin2">
                    <div class="col five">
                        <?php
                        $elsPerCol = count(Customdesign::$_selectors) / 2;
                        $count = 0;
                        foreach (Customdesign::$_selectors as $selector):
                            $langSelector = Customdesign::makeLangSelector($selector);
                            if ($count >= $elsPerCol):
                                ?>
                            </div>
                            <div class="col five">
                            <?php endif; ?>
                            <div class="row form_group horizontal_form">
                                <label class="col three right_txt"><?php echo t('settings_design_el' . $langSelector); ?></label>
                                <input class="col six" name="design[<?php echo $selector; ?>]" maxlength="2000"
                                       placeholder="<?php echo ''; ?>" 
                                       <?php if (isset($this->user->getSettings()->getCustomCss()->$selector)) : ?>
                                           value="<?php echo Security::htmloutput($this->user->getSettings()->getCustomCss()->$selector); ?>"
                                       <?php endif; ?>>
                            </div>
                            <?php
                            $count++;
                        endforeach;
                        ?>
                    </div>
                </div>
                <div class="row margin2">
                    <div class="col ten">
                        <input class="btn btn_success right" type="submit" value="<?php echo t('settings_save_btn'); ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('input.minicolors').minicolors();
    });
</script>