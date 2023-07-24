<?php

/**
 * @file
 * Provides API documentation so other modules can interact with commerce_ups.
 */

/**
 * Allows modules to alter the UPS API endpoints.
 *
 * This is useful if a module needs to change the endpoint URL that an API
 * request is sent to.
 */
function hook_commerce_ups_api_endpoint_alter(&$enpoints, $method) {
  // No example provided.
}

/**
 * Allows modules to alter the UPS Access request.
 *
 * This is useful if a module wants to use different access credentials
 * based on the order data. For example, a store may want to use a separate
 * account for the rate request if a particular product is ordered.
 */
function hook_commerce_ups_build_access_request_alter(&$access_request, $order) {
  // No example provided.
}

/**
 * Allows modules to alter the UPS Rate request.
 *
 * This is useful if a module wants to alter anything about the rating request
 * that is sent to UPS. This includes ship from and to addresses, packaging
 * information, or anything else that can be included in a Rate request.
 */
function hook_commerce_ups_build_rate_request_alter(&$rating_request, $order) {
  // No example provided.
}
