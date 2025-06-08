<?php

function getDaysWordForm($days) {
    $lastDigit = $days % 10;
    $lastTwoDigits = $days % 100;

    if ($lastTwoDigits >= 11 && $lastTwoDigits <= 19) {
        return $days . ' дней';
    }

    switch ($lastDigit) {
        case 1:
            return $days . ' день';
        case 2:
        case 3:
        case 4:
            return $days . ' дня';
        default:
            return $days . ' дней';
    }
} 