<?php

class Dateutils {

    public static function formatDateWithTextMonth(DateTime $date) {
        return $date->format('d') . '. ' . t('dateutils_monthabbr_' . $date->format('n')) . ' ' . $date->format('Y');
    }

}
