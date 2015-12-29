<?php

class Dateutils {

    public static function formatDateWithTextMonth(DateTime $date) {
        return $date->format('d') . '. ' . t('dateutils_monthabbr_' . $date->format('n')) . ' ' . $date->format('Y');
    }

    public static function formatDateTime(DateTime $date) {
        return $date->format('d.m.Y H:i');
    }

    public static function getPostDiff(DateTime $date) {
        $now = new DateTime();
        $diff = $date->diff($now, TRUE);

        if ($diff->format('%a') < 1) {
            $hr = $diff->format('%h');
            if (intval($hr) === 0) {
                return $diff->format('%i min');
            }
            return $hr . ' hr';
        }

        return self::formatDateTime($date);
    }

}
