<?php

/**
 * @file
 * Hooks provided by the Card on File module.
 */

/**
 * Allows you to act when a card is created.
 *
 * @param \CommerceCardOnFile $card
 *   The card entity being created.
 */
function hook_commerce_cardonfile_insert(CommerceCardOnFile $card) {
  // No example.
}

/**
 * Allows you to act when a card is updated.
 *
 * @param \CommerceCardOnFile $card
 *   The card entity being updated.
 */
function hook_commerce_cardonfile_update(CommerceCardOnFile $card) {
  // No example.
}

/**
 * Allows you to act when a card is being deleted.
 *
 * @param \CommerceCardOnFile $card
 *   The card entity being deleted.
 */
function hook_commerce_cardonfile_delete(CommerceCardOnFile $card) {
  // No example.
}

/**
 * Allows other modules to alter Card on File's checkout pane alter.
 *
 * @param array $payment_details
 *   The payment details form array.
 * @param $form
 *   The entire form array.
 * @param $form
 *   The entire form state array.
 */
function hook_commerce_cardonfile_checkout_pane_form_alter(&$payment_details, $form, $form_state) {
  // No example.
}

/**
* Implements hook_commerce_cardonfile_payment_terminal_form_alter().
 *
 * Allows alteration of admin payment terminal form elements after
 * they've been processed by commerce_cardonfile.
 *
 * @param $form
 *   The entire form array.
 * @param $form
 *   The entire form state array.
 */
function commerce_cardonfile_commerce_cardonfile_payment_terminal_form_alter(&$form, &$form_state) {
  // No example provided.
}

