<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class IconsHelper
{

    const BASIC_TEXT = 'card-text';
    const USER_BASIC_INFO = 'person-lines-fill';
    const ADDRESS = 'geo-alt';
    const CONTACT = 'telephone';
    const BANK = 'bank';
    const TAX = 'briefcase';
    const COMPANY = 'building';
    const GENDER = 'gender-ambiguous';
    const BOOK = "book";
    const COUNTRY = "globe";
    const HOUSE = "house";
    const NUMBERS = "123";
    const EMAIL = "envelope";
    const MOBILE_PHONE = "phone";
    const LINK = "link";
    const INFO_SIGN = "info-circle";
    const QUESTION_SIGN = "question-circle";

    public static function getIcon($type, $classes = "")
    {
        $whole_class = "bi bi-$type $classes";

        return '<i class="'.$whole_class.'"></i>';

    }

}
