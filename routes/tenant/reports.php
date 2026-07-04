<?php

use Illuminate\Support\Facades\Route;

/**
 * Reporting & Analytics Routes
 * Protected by: reports features
 */

Route::group(['middleware' => ['tenant.feature:reports.basic']], function() {
    // Basic reports

});

Route::group(['middleware' => ['tenant.feature:reports.advanced_analytics']], function() {
    // Advanced analytics (future features)
    // Add advanced analytics endpoints here
});

Route::group(['middleware' => ['tenant.feature:reports.export']], function() {
    // Export functionality
    // Add export endpoints here
});
