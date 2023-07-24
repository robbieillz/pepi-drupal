<?php

/**
 * Field handler to present a card data's operations links.
 */
class commerce_cardonfile_handler_field_card_operations extends views_handler_field {
  function construct() {
    parent::construct();

    $this->additional_fields['card_id'] = 'card_id';
    $this->additional_fields['uid'] = 'uid';
  }

  function option_definition() {
    $options = parent::option_definition();

    $options['add_destination'] = TRUE;

    return $options;
  }

  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['add_destination'] = array(
      '#type' => 'checkbox',
      '#title' => t('Add a destination parameter to operation links so users return to this View on form submission.'),
      '#default_value' => $this->options['add_destination'],
    );
  }

  function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  function render($values) {
    $card_id = $this->get_value($values, 'card_id');
    $card = commerce_cardonfile_load($card_id);
    $uid = $this->get_value($values, 'uid');

    // Get the operations links.
    // Hardcoded because the Edit and Delete links are not under a common
    // parent, to allow methods to provide delete functionality without
    // providing edit functionality.
    $links = array(
      'commerce-cardonfile-edit' => array(
        'title' => t('Edit'),
        'href' => 'user/' . $uid . '/cards/' . $card_id . '/edit',
        'access' => commerce_cardonfile_access('update', $card),
      ),
      'commerce-cardonfile-delete' => array(
        'title' => t('Delete'),
        'href' => 'user/' . $uid . '/cards/' . $card_id . '/delete',
        'access' => commerce_cardonfile_access('delete', $card),
      ),
    );
    foreach ($links as $id => &$link) {
      if (!$link['access']) {
        unset($links[$id]);
      }
      elseif ($this->options['add_destination']) {
        $link['query'] = drupal_get_destination();
      }
    }

    if (!empty($links)) {
      drupal_add_css(drupal_get_path('module', 'commerce_cardonfile') . '/theme/commerce_cardonfile.admin.css');
      return theme('links', array('links' => $links, 'attributes' => array('class' => array('links', 'inline', 'operations'))));
    }
  }
}
