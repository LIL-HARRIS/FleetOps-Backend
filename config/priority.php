<?php

/**
 * Priority Calculation Configuration
 * Defines weights for order priority scoring factors
 *
 * Fleet Manager can adjust these weights to control how priority is calculated:
 * - Perishable goods multiplier
 * - Promised delivery window urgency
 * - Customer priority level importance
 */

return [
    'weights' => [
        'order_type' => 0.35,                // 35% - Order type (Express, Normal, Low)
        'perishable_goods' => 0.35,          // 35% - Perishable items are time-sensitive
        'delivery_window' => 0.30,           // 30% - Promised delivery deadline urgency
    ],

    // Order type scoring
    'order_type' => [
        'enabled' => true,                   // Enable order type factor
        'types' => [
            'Express' => 95,                 // Express orders get highest priority
            'Normal' => 50,                  // Normal orders get medium priority
            'Low' => 25,                     // Low priority orders
        ],
    ],

    // Perishable goods scoring
    'perishable' => [
        'enabled' => true,                   // Enable perishable goods factor
        'base_score' => 80,                  // Base priority if perishable is true
    ],

    // Delivery window scoring
    'delivery_window' => [
        'enabled' => true,                   // Enable delivery window factor
        'hours_to_deadline' => [
            'within_2_hours' => 100,         // Critical urgency
            'within_4_hours' => 85,
            'within_8_hours' => 70,
            'within_24_hours' => 50,
            'beyond_24_hours' => 30,         // Lower priority for distant deadlines
        ],
    ],
];
