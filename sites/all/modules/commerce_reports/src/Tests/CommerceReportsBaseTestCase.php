<?php

namespace Drupal\commerce_reports\Tests;

/**
 * Class CommerceReportsBaseTestCase
 */
class CommerceReportsBaseTestCase extends \CommerceBaseTestCase {
  /**
   * Don't need most of default core modules.
   */
  protected $profile = 'minimal';

  protected $products;
  protected $customers;
  protected $orders;

  protected $additional_modules = array();
  protected $store_admin;

  /**
   * Overrides CommerceBaseTestCase::permissionBuilder().
   */
  protected function permissionBuilder($set) {
    $permissions = parent::permissionBuilder($set);

    switch ($set) {
      case 'store admin':
      case 'site admin':
        $permissions[] = 'access commerce reports';
        break;
    }

    return $permissions;
  }

  /**
   * Overrides DrupalWebTestCase::setUp().
   */
  function setUp() {
    $modules = parent::setUpHelper('all');
    $modules[] = 'commerce_reports';
    foreach ($this->additional_modules as $module) {
      $modules[] = $module;
    }
    parent::setUp($modules);

    $this->products = array();
    $this->customers = array();
    $this->orders = array();
    $this->store_admin = $this->createStoreAdmin();

    if (!$this->store_admin) {
      $this->fail(t('Failed to create Store Admin user for test.'));
    }

    // Set the default country to US.
    variable_set('site_default_country', 'US');
  }

  /**
   * Helper function creating multiple dummy products with a variable price.
   */
  protected function createProducts($amount = 1) {
    for ($i = 0; $i < $amount; $i ++) {
      $product = $this->createDummyProduct($this->randomName(), '', rand(1, 1000));
      $this->products[$product->product_id] = $product;
    }
  }

  /**
   * Returns information about created products for tests.
   *
   * @return array
   */
  protected function createdProductsData() {
    $products = array();
    foreach ($this->orders as $order) {
      foreach ($order['products'] as $product_id => $quantity) {
        $sku = $this->products[$product_id]->sku;

        if (empty($products[$sku])) {
          $products[$sku] = array(
            'quantity' => 0,
            'revenue' => 0,
          );
        }

        $products[$sku]['quantity'] += $quantity;
        $products[$sku]['revenue'] += $quantity * $this->products[$product_id]->commerce_price[LANGUAGE_NONE][0]['amount'];
      }
    }
    return $products;
  }

  /**
   * Helper function creating multiple dummy customers.
   */
  protected function createCustomers($amount = 1) {
    for ($i = 0; $i < $amount; $i ++) {
      $customer = $this->createStoreCustomer();
      $this->customers[$customer->uid] = $customer;
    }
  }

  /**
   * Returns information about created customers for tests.
   *
   * @return array
   */
  protected function createdCustomersData() {
    $customers = array();

    foreach ($this->orders as $order) {
      $uid = $order['commerce_order']->uid;

      if (empty($customers[$uid])) {
        $customers[$uid] = array(
          'orders' => 0,
          'products' => 0,
          'revenue' => 0,
        );
      }

      $customers[$uid]['orders']++;

      foreach ($order['products'] as $product_id => $quantity) {
        $customers[$uid]['products'] += $quantity;
        $customers[$uid]['revenue'] += $quantity * $this->products[$product_id]->commerce_price[LANGUAGE_NONE][0]['amount'];
      }
    }

    return $customers;
  }

  /**
   * Helper function creating multiple dummy orders.
   * If no customers or products exist, then one of each get created.
   */
  protected function createOrders($amount = 1, $createTransactions = FALSE, $possibleDates = array()) {
    if (empty($this->products)) {
      $this->createProducts();
    }

    if (empty($this->customers)) {
      $this->createCustomers();
    }

    $order_status = 'completed';
    if ($createTransactions) {
      $order_status = 'pending';
    }

    for ($i = 0; $i < $amount; $i ++) {
      $totalProducts = rand(1, count($this->products));
      $products = array();

      for ($x = 0; $x < $totalProducts; $x ++) {
        $product = $this->products[array_rand($this->products)];
        $products[$product->product_id] = rand(1, 10);
      }

      if (!($customer = next($this->customers))) {
        $customer = reset($this->customers);
      }

      $order = $this->createDummyOrder($customer->uid, $products, $order_status);

      if (!empty($possibleDates)) {
        $date = $possibleDates[array_rand($possibleDates)];
        $order->created = $date;

        commerce_order_save($order);
      }

      $transaction = NULL;
      if ($createTransactions) {
        $transaction = commerce_payment_transaction_new('commerce_payment_example', $order->order_id);
        $transaction->amount = $order->commerce_order_total[LANGUAGE_NONE][0]['amount'];
        $transaction->status = 'success';

        commerce_payment_transaction_save($transaction);
      }

      $this->orders[] = array(
        'commerce_transaction' => $transaction,
        'commerce_order' => $order,
        'products' => $products,
      );
    }
  }

  protected function getTotal() {
    $total = 0;

    foreach ($this->orders as $order) {
      foreach ($order['products'] as $product_id => $quantity) {
        $total += $quantity * $this->products[$product_id]->commerce_price[LANGUAGE_NONE][0]['amount'];
      }
    }

    return $total;
  }

  /**
   * Return a an executed View.
   *
   * @param $name
   * @param $id
   * @return \view
   */
  protected function getView($name, $id) {
    $view = views_get_view($name, TRUE);
    $view->set_arguments(array());
    $view->set_display(array($id));
    $view->pre_execute();
    $view->execute($id);
    return $view;
  }

}
