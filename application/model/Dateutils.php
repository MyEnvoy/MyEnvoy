<?php

class Dateutils {

    public static function formatDateWithTextMonth(DateTime $date) {
        return $date->format('d') . '. ' . t('dateutils_monthabbr_' . $date->format('n')) . ' ' . $date->format('Y');
    }

    public static function formatDateTime(DateTime $date) {
        return $date->format('d.m.Y H:i');
    }

}
