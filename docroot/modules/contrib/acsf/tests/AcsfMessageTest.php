<?php

/**
 * @file
 * Provides PHPUnit tests for AcsfMessage.
 */

use PHPUnit\Framework\TestCase;

/**
 * AcsfMessageTest.
 */
class AcsfMessageTest extends TestCase {

  /**
   * Setup.
   */
  public function setUp() {
    $files = [
      __DIR__ . '/../vendor/autoload.php',
      __DIR__ . '/AcsfConfigUnitTest.inc',
      __DIR__ . '/AcsfConfigUnitTestMissingPassword.inc',
      __DIR__ . '/AcsfConfigUnitTestMissingUrl.inc',
      __DIR__ . '/AcsfConfigUnitTestMissingUsername.inc',
      __DIR__ . '/AcsfConfigUnitTestIncompatible.inc',
      __DIR__ . '/AcsfMessageUnitTestSuccess.inc',
      __DIR__ . '/AcsfMessageUnitTestFailure.inc',
      __DIR__ . '/AcsfMessageUnitTestFailureException.inc',
      __DIR__ . '/AcsfMessageUnitTestMissingEndpoint.inc',
      __DIR__ . '/AcsfMessageUnitTestMissingResponse.inc',
      __DIR__ . '/AcsfMessageResponseUnitTest.inc',
    ];
    foreach ($files as $file) {
      require_once $file;
    }
  }

  /**
   * Tests the AcsfMessage constructor parameters.
   */
  public function testAcsfMessageConstructor() {
    $config = new AcsfConfigUnitTest('unit_test_site', 'unit_test_env');
    $message = new AcsfMessageUnitTestSuccess('TEST', 'unit_test_endpoint', [], $config);
    $this->assertSame($message->method, 'TEST');
  }

  /**
   * Tests that the config object must be an instance of AcsfConfig.
   */
  public function testAcsfMessageConfigCompatible() {
    // Provide a compatible config object to check that no error is generated.
    // This isn't very precise since any error would make this test fail.
    $config = new AcsfConfigUnitTest('unit_test_site', 'unit_test_env');
    $this->assertTrue(is_subclass_of($config, '\Drupal\acsf\AcsfConfig'));
    $message = new AcsfMessageUnitTestSuccess('TEST', 'unit_test_endpoint', [], $config);
  }

  /**
   * Tests message sending and response works as expected.
   */
  public function testAcsfMessageResponse() {
    $config = new AcsfConfigUnitTest('unit_test_site', 'unit_test_env');
    $message = new AcsfMessageUnitTestSuccess('TEST', 'unit_test_endpoint', [], $config);
    $message->send();
    $response = $message->getResponseBody();
    $expected_response = [
      'url' => 'http://gardener.unit.test',
      'method' => 'TEST',
      'endpoint' => 'unit_test_endpoint',
      'parameters' => [],
      'username' => 'gardener_unit_test',
      'password' => 'Un1tT35t',
    ];
    $this->assertSame($response, json_encode($expected_response));
  }

  /**
   * Tests that an exception is throw when endpoint is missing.
   *
   * @expectedException \Drupal\acsf\AcsfMessageMalformedResponseException
   */
  public function testAcsfMessageResponseMissingEndpoint() {
    $config = new AcsfConfigUnitTest('unit_test_site', 'unit_test_env');
    $message = new AcsfMessageUnitTestMissingEndpoint('TEST', 'unit_test_endpoint', [], $config);
    $message->send();
  }

  /**
   * Tests that an exception is throw when response is missing.
   *
   * @expectedException \Drupal\acsf\AcsfMessageMalformedResponseException
   */
  public function testAcsfMessageResponseMissingResponse() {
    $config = new AcsfConfigUnitTest('unit_test_site', 'unit_test_env');
    $message = new AcsfMessageUnitTestMissingResponse('TEST', 'unit_test_endpoint', [], $config);
    $message->send();
  }

  /**
   * Tests that a response code greater than 0 triggers the correct exception.
   */
  public function testAcsfMessageResponseFailure() {
    $config = new AcsfConfigUnitTest('unit_test_site', 'unit_test_env');
    $message = new AcsfMessageUnitTestFailure('TEST', 'unit_test_endpoint', [], $config);
    try {
      $caught = FALSE;
      $message->send();
    }
    catch (\Drupal\acsf\AcsfMessageFailedResponseException $e) {
      $caught = TRUE;
    }
    $this->assertTrue($caught);
    $this->assertSame($message->getResponseCode(), 1);
  }

  /**
   * Tests that the AcsfMessageFailureException exception is thrown.
   *
   * @expectedException \Drupal\acsf\AcsfMessageFailureException
   */
  public function testAcsfMessageResponseFailureException() {
    $config = new AcsfConfigUnitTest('unit_test_site', 'unit_test_env');
    $message = new AcsfMessageUnitTestFailureException('TEST', 'unit_test_endpoint', [], $config);
    $message->send();
  }

}
