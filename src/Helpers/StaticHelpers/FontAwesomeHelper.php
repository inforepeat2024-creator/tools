<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class FontAwesomeHelper
{
    const BASIC_TEXT = 'card-text';
    const FIRST_NAME = 'far fa-user';
    const LAST_NAME = 'fas fa-user';
    const ADDRESS = 'fas fa-map-marker-alt';
    const CONTACT = 'telephone';
    const BANK = 'fas fa-university';
    const GLOBE = 'fas fa-globe';

    const PLUS = 'fas fa-plus';
    const TIMES = 'fas fa-times';


    const TAX = 'fas fa-business-time';
    const COMPANY_NAME = 'far fa-building';
    const COMPANY_TYPE = 'fas fa-building';
    const GENDER = 'fas fa-venus-mars';
    const ACADEMIC_TITLE = "fas fa-graduation-cap";
    const COUNTRY = "fas fa-globe-africa";
    const HOUSE = "house";
    const HOUSE_NUMBER = 'fas fa-hashtag';
    const ZIP_CODE = 'fas fa-map-signs';
    const CITY = 'fas fa-city';
    const NUMBERS = "123";
    const EMAIL = "fas fa-at";
    const MOBILE_PHONE = "fas fa-mobile-alt";
    const LANDLINE_PHONE = "fas fa-phone";
    const FAX = 'fas fa-fax';
    const WEB_PAGE = "fas fa-atlas";
    const INFO_SIGN = "info-circle";
    const QUESTION_SIGN = "question-circle";
    const ACCOUNT_HOLDER = 'fas fa-user-tie';
    const ACCOUNT_SUBHOLDER = 'far fa-user';
    const IBAN = 'fas fa-money-check-alt';
    const BIC = 'fas fa-money-check';
    const BANK_NAME = 'fa-light fa-building-columns';
    const TAX_REGISTER_NUMBER = "fas fa-hashtag";
    const TAX_OFFICE = "fas fa-chalkboard-teacher";
    const LOCAL_TAX_NUMBER = "fas fa-hashtag";
    const INTERNATIONAL_TAX_NUMBER = "fas fa-hashtag";
    const PASSWORD = "fas fa-key";
    const CREDENTIALS = "fas fa-key";
    const ROLES = "fas fa-user-tag";
    const ACTIVITIES = "fas fa-ellipsis-v";

    const CALENDAR = "fas fa-calendar";

    const ARROW_CIRCLE_LEFT = "fa-ragular fa-circle-left";


    public static function getIcon($type, $classes = "")
    {
        $whole_class = "$type $classes";

        return '<i class="'.$whole_class.'"></i>';

    }

}
