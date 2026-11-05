<?php

/*
| Storefront policy values (system-overview §7, data-model.md). Each is a PO decision with an
| open question and has NO default: unset or invalid values fail closed (US-059, research R-01).
| Local placeholders live in .env.example and are labelled as such.
*/

return [
    'reservation_minutes' => env('STOREFRONT_RESERVATION_MINUTES'),                                   // Q-25, Q-32
    'reservation_timer_mode' => env('STOREFRONT_RESERVATION_TIMER_MODE'),                             // Q-32: per_item | shared
    'max_quantity_per_article' => env('STOREFRONT_MAX_QUANTITY_PER_ARTICLE'),                         // Q-46
    'low_stock_threshold' => env('STOREFRONT_LOW_STOCK_THRESHOLD'),                                   // Q-26
    'return_period_days' => env('STOREFRONT_RETURN_PERIOD_DAYS'),                                     // Q-31
    'packaging_fee_cents' => env('STOREFRONT_PACKAGING_FEE_CENTS'),                                   // Q-30
    'display_timezone' => env('STOREFRONT_DISPLAY_TIMEZONE'),                                         // Q-10: IANA name
    'upcoming_relative_threshold_minutes' => env('STOREFRONT_UPCOMING_RELATIVE_THRESHOLD_MINUTES'),   // Q-35
    'upcoming_horizon_hours' => env('STOREFRONT_UPCOMING_HORIZON_HOURS'),                             // Q-74
    'last_hours_badge_threshold_hours' => env('STOREFRONT_LAST_HOURS_BADGE_THRESHOLD_HOURS'),         // Q-36
    'listing_page_size' => env('STOREFRONT_LISTING_PAGE_SIZE', 60),                                   // CONFIRMED 60 (BR-06-07)
    'catalog_access' => env('STOREFRONT_CATALOG_ACCESS'),                                             // Q-28, Q-71: members | public

    // HYPOTHESIS behaviours, off until the PO records a validation decision (Principle VII).
    'features' => [
        'hide_add_button_after_add' => (bool) env('FEATURE_HIDE_ADD_BUTTON_AFTER_ADD', false),        // FR-PDP-04
        'last_hours_badge' => (bool) env('FEATURE_LAST_HOURS_BADGE', false),                          // FR-STK-03
    ],
];
