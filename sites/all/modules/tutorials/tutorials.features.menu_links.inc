<?php
/**
 * @file
 * tutorials.features.menu_links.inc
 */

/**
 * Implements hook_menu_default_menu_links().
 */
function tutorials_menu_default_menu_links() {
  $menu_links = array();

  // Exported menu link: menu-footer-navigation_puzzle-ring-tutorials:tutorials.
  $menu_links['menu-footer-navigation_puzzle-ring-tutorials:tutorials'] = array(
    'menu_name' => 'menu-footer-navigation',
    'link_path' => 'tutorials',
    'router_path' => 'tutorials',
    'link_title' => 'Puzzle Ring Tutorials',
    'options' => array(
      'identifier' => 'menu-footer-navigation_puzzle-ring-tutorials:tutorials',
    ),
    'module' => 'system',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 1,
    'parent_identifier' => 'menu-footer-navigation_service--support:<nolink>',
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Puzzle Ring Tutorials');

  return $menu_links;
}
