<?php

declare(strict_types = 1);

namespace PerformanceX\XHProfProcessor\Tests\Fixtures;

class TestClass {
  public function methodWithSelf(self $param): void {
  }

  public function methodWithNamespacedParam(User $user): void {
  }
}

class User {
  public function __construct(public string $name) {
  }
}
