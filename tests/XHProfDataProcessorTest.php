<?php

declare(strict_types = 1);

namespace PerformanceX\XHProfProcessor\Tests;

use PHPUnit\Framework\TestCase;
use PerformanceX\XHProfProcessor\XHProfDataProcessor;
use PerformanceX\XHProfProcessor\Tests\Fixtures\TestClass;

class XHProfDataProcessorTest extends TestCase {

  private XHProfDataProcessor $processor;

  protected function setUp(): void {
    $this->processor = new XHProfDataProcessor();
  }

  public function testMainFunctionStaysUnchanged(): void {
    $input = [
      'main()' => [
        'ct' => 1,
        'wt' => 100,
      ],
    ];

    $result = $this->processor->process($input);

    $this->assertArrayHasKey('main()', $result);
    $this->assertEquals($input['main()'], $result['main()']);
  }

  public function testProcessesSimpleFunctionCall(): void {
    $input = [
      'strlen==>count' => [
        'ct' => 1,
        'wt' => 100,
      ],
    ];

    $result = $this->processor->process($input);

    $keys = array_keys($result);
    $this->assertStringStartsWith('strlen(string $', $keys[0]);
    $this->assertStringEndsWith(')', $keys[0]);
    $this->assertEquals($input['strlen==>count'], $result[$keys[0]]);
  }

  public function testHandlesRecursion(): void {
    $input = [
      'recursive_function@1==>recursive_function@2' => [
        'ct' => 1,
        'wt' => 100,
      ],
    ];

    $result = $this->processor->process($input);
    
    $this->assertCount(1, $result);
    $keys = array_keys($result);
    $this->assertStringContainsString('@1==>recursive_function@2', $keys[0]);
  }

  public function testHandlesClassMethods(): void {
    $input = [
      'DateTime::createFromFormat==>strtotime' => [
        'ct' => 1,
        'wt' => 100,
      ],
    ];

    $result = $this->processor->process($input);

    $keys = array_keys($result);
    $this->assertStringStartsWith('\DateTime::createFromFormat', $keys[0]);
    $this->assertStringContainsString('strtotime', $keys[0]);
  }

  public function testPreservesSpecialTypes(): void {
    $input = [
      TestClass::class . '::methodWithSelf' => [
        'ct' => 1,
        'wt' => 100,
      ],
    ];

    $result = $this->processor->process($input);

    $keys = array_keys($result);
    $this->assertStringContainsString('self $param', $keys[0]);
  }

  public function testUsesShortClassNames(): void {
    $input = [
      TestClass::class . '::methodWithNamespacedParam' => [
        'ct' => 1,
        'wt' => 100,
      ],
    ];

    $result = $this->processor->process($input);

    $keys = array_keys($result);
    // Should show "User $user" instead of "\Namespace\User $user"
    $this->assertStringContainsString('User $user', $keys[0]);
    $this->assertStringNotContainsString('\Namespace\User $user', $keys[0]);
  }

  public function testHandlesNonExistentFunctions(): void {
    $input = [
      'non_existent_function==>also_not_real' => [
        'ct' => 1,
        'wt' => 100,
      ],
    ];

    $result = $this->processor->process($input);

    $this->assertArrayHasKey('non_existent_function==>also_not_real', $result);
    $this->assertEquals($input['non_existent_function==>also_not_real'], $result['non_existent_function==>also_not_real']);
  }

}
