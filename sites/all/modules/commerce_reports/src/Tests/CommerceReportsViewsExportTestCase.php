<?php

namespace Drupal\commerce_reports\Tests;

/**
 * Class CommerceReportsViewsExportTestCase
 */
class CommerceReportsViewsExportTestCase extends CommerceReportsBaseTestCase {

  public static function getInfo() {
    return array(
      'name' => 'Reports views data exports',
      'description' => 'Test the views data export capabilities.',
      'group' => 'Commerce Reports',
    );
  }

  function setUp() {
    $this->resetAll();
    $this->additional_modules[] = 'views_data_export';
    parent::setUp();
  }

  protected function getRenderedExport($name, $id = 'views_data_export_1') {
    $report = $this->getView($name, $id);
    return array_filter(explode(PHP_EOL, $report->render()));
  }

  protected function assertCsvHeaders($header, $output) {
    $this->assertTrue(trim($output) == $header, t('@header matches', array('@header' => trim($output))));
  }

  function testCustomerViewsDataExport() {
    $this->createCustomers(3);
    $this->createProducts(2);
    $this->createOrders(10);
    $customers = $this->createdCustomersData();

    $rendered = $this->getRenderedExport('commerce_reports_customers');
    $this->assertCsvHeaders('"Customer","Orders","Total","Average"', $rendered[0]);

    array_shift($rendered);
    $this->assertEqual(count($rendered), count($customers), t('The amount of customers (%reported) that is reported (%generated) upon is correct.', array('%reported' => count($rendered), '%generated' => count($customers))));
  }

  function testProductViewsDataExport() {
    $this->createProducts(5);
    $this->createOrders(5);
    $products = $this->createdProductsData();

    $rendered = $this->getRenderedExport('commerce_reports_products');
    $this->assertCsvHeaders('"Type","Product","Title","Sold","Revenue"', $rendered[0]);

    array_shift($rendered);
    $this->assertEqual(count($rendered), count($products), t('The amount of products (%reported) that is reported (%generated) upon is correct.', array('%reported' => count($rendered), '%generated' => count($products))));
  }

  function testPaymentMethodViewsDataExport() {
    $this->createOrders(10, TRUE);

    $transactions = 0;
    $revenue = 0;

    foreach ($this->orders as $order) {
      $transactions ++;
      $revenue += $order['commerce_transaction']->amount;
    }

    $rendered = $this->getRenderedExport('commerce_reports_payment_methods');
    $this->assertCsvHeaders('"Payment method","Transactions","Revenue"', $rendered[0]);

    array_shift($rendered);
    $this->assertEqual(count($rendered), 1, t('The amount of payment methods (%reported) that is reported (%generated) upon is correct.', array('%reported' => count($rendered), '%generated' => 1)));

    // Verify data
    $data = reset($rendered);
    $data = str_getcsv($data);
    $this->assertEqual("Example payment", $data[0]);
    $this->assertEqual($transactions, $data[1]);
    $this->assertEqual(commerce_currency_format($revenue, commerce_default_currency()), $data[2]);
  }

  function testSalesViewsDataExport() {
    $this->createCustomers(5);
    $this->createOrders(20, FALSE, $this->sampleDates());

    // Monthly
    $rendered = $this->getRenderedExport('commerce_reports_sales');

    $this->assertCsvHeaders('"Created date","Number of Orders","Total Revenue","Average Order"', $rendered[0]);

    $months = $this->ordersGroupedByTime('F Y');

    array_shift($rendered);
    $this->assertEqual(count($rendered), count($months), t('The amount of months (%reported) that is reported (%generated) upon is correct.', array('%reported' => count($rendered), '%generated' => count($months))));

    // Weekly
    $rendered = $this->getRenderedExport('commerce_reports_sales', 'views_data_export_3');
    $this->assertCsvHeaders('"Created date","Number of Orders","Total Revenue","Average Order"', $rendered[0]);

    $months = $this->ordersGroupedByTime('\\w\\e\\e\\k W \\o\\f Y');
    array_shift($rendered);
    $this->assertEqual(count($rendered), count($months), t('The amount of weeks (%reported) that is reported (%generated) upon is correct.', array('%reported' => count($rendered), '%generated' => count($months))));

    // Daily
    $rendered = $this->getRenderedExport('commerce_reports_sales', 'views_data_export_4');
    $this->assertCsvHeaders('"Created date","Number of Orders","Total Revenue","Average Order"', $rendered[0]);

    $months = $this->ordersGroupedByTime('j F Y');
    array_shift($rendered);
    $this->assertEqual(count($rendered), count($months), t('The amount of days (%reported) that is reported (%generated) upon is correct.', array('%reported' => count($rendered), '%generated' => count($months))));

    // Yearly
    $rendered = $this->getRenderedExport('commerce_reports_sales', 'views_data_export_2');
    $this->assertCsvHeaders('"Created date","Number of Orders","Total Revenue","Average Order"', $rendered[0]);

    $months = $this->ordersGroupedByTime('Y');
    array_shift($rendered);
    $this->assertEqual(count($rendered), count($months), t('The amount of years (%reported) that is reported (%generated) upon is correct.', array('%reported' => count($rendered), '%generated' => count($months))));
  }

  public function testExportRespectsFilters() {
    // Generate test data
    $dates = $this->sampleDates();
    $this->createCustomers(5);
    $this->createOrders(20, FALSE, $dates);
    $months = $this->ordersGroupedByTime('F Y');

    // Date info
    $start = new \DateTime();
    $start->setTimestamp($dates[0]);
    $end = clone $start;
    $end = $end->add(new \DateInterval('P1M'));

    // Setup our view
    $name = 'commerce_reports_sales';
    $id = 'page';
    $view = views_get_view($name, TRUE);
    $view->set_arguments(array());
    $view->set_display(array($id));

    // Set filters.
    $this->verbose(t('Filtering on @start and @end', array(
      '@start' => $start->format('F Y'),
      '@end' => $end->format('F Y'),
    )));
    $filters = $view->display_handler->get_option('filters');
    $filters['date_filter']['default_date'] = $start->format('F Y');
    $filters['date_filter']['default_to_date'] = $end->format('F Y');
    $view->display_handler->set_option('filters', $filters);

    $view->pre_execute();
    $view->execute('views_data_export_1');

    $rendered = array_filter(explode(PHP_EOL, $view->render()));
    array_shift($rendered);
    $data = reset($rendered);
    $data = str_getcsv($data);

    $this->assertEqual($data[1], $months[$start->format('F Y')], t('The amount of orders (%reported) that is reported (%generated) upon is correct for @month.', array('%reported' => $data[1], '%generated' => $months[$start->format('F Y')], '@month' => $start->format('F Y'))));

  }

  protected function ordersGroupedByTime($format) {
    $time = array();
    foreach ($this->orders as $order) {
      $date_string = date($format, $order['commerce_order']->created);
      if (!isset($time[$date_string])) {
        $time[$date_string] = 0;
      }
      $time[$date_string]++;
    }
    return $time;
  }

  protected function sampleDates() {
    $sample = array();
    $date = new \DateTime();
    $max_date = $date->getTimestamp();
    $min_date = $date->sub(new \DateInterval("P12M"))->getTimestamp();

    for ($i = 0; $i < 20; $i++) {
      $random_date = rand($max_date, $min_date);
      $sample[] = $random_date;
    }
    return $sample;
  }

}
