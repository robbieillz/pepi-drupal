<?php

/**
 * Preprocess variables for html.tpl.php
 *
 * @see system_elements()
 * @see html.tpl.php
 */
function pepi_commerce_preprocess_html(&$variables) {
  // Add conditional stylesheets for IE
  drupal_add_css(path_to_theme() . '/css/commerce-kickstart-theme-ie-lte-8.css', array('group' => CSS_THEME, 'weight' => 23, 'browsers' => array('IE' => 'lte IE 8', '!IE' => FALSE), 'preprocess' => FALSE));
  drupal_add_css(path_to_theme() . '/css/commerce-kickstart-theme-ie-lte-7.css', array('group' => CSS_THEME, 'weight' => 24, 'browsers' => array('IE' => 'lte IE 7', '!IE' => FALSE), 'preprocess' => FALSE));

  // Add external libraries.
  drupal_add_library('pepi_commerce', 'selectnav');
}

/**
 * Implements hook_library().
 */
function pepi_commerce_library() {
  $libraries['selectnav'] = array(
    'title' => 'Selectnav',
    'version' => '',
    'js' => array(
      libraries_get_path('selectnav.js') . '/selectnav.min.js' => array(),
    ),
  );
  return $libraries;
}

/**
 * Override the submitted variable.
 */
function pepi_commerce_preprocess_node(&$variables) {
  $variables['submitted'] = $variables['date'] . ' - ' . $variables['name'];
  if ($variables['type'] == 'blog_post') {
    $variables['submitted'] = t('By') . ' ' . $variables['name'] . ', ' . $variables['date'];
  }
}
