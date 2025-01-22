<?php

declare(strict_types = 1);

namespace PerformanceX\XHProfProcessor;

use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionClass;

/**
 * Processes XHProf data to provide fully qualified function names.
 */
class XHProfDataProcessor {

  /**
   * Processes raw XHProf data.
   *
   * @param array $xhprof_data
   *   Raw XHProf data to process.
   *
   * @return array
   *   Processed XHProf data with fully qualified function names.
   */
  public function process(array $xhprof_data): array {
    $processed_data = [];
    foreach ($xhprof_data as $key => $metrics) {
      // Split into caller and callee parts.
      $parts = explode('==>', $key, 2);
      $processed_caller = $this->processFunctionWithSuffix($parts[0]);

      // Only add the separator and callee if there actually is a callee.
      $new_key = count($parts) > 1
        ? $processed_caller . '==>' . $this->processFunctionWithSuffix($parts[1])
        : $processed_caller;

      $processed_data[$new_key] = $metrics;
    }

    return $processed_data;
  }

  /**
   * Processes a function name that may include a recursion suffix.
   *
   * @param string $function_str
   *   The function string to process.
   *
   * @return string
   *   The processed function string with suffix preserved.
   */
  protected function processFunctionWithSuffix(string $function_str): string {
    // Handle recursion suffix (e.g., "@1").
    if (preg_match('/^(.*?)(@\d+)$/', $function_str, $matches)) {
      $base = $matches[1];
      $suffix = $matches[2];
      return $this->processSingleFunction($base) . $suffix;
    }
    return $this->processSingleFunction($function_str);
  }

  /**
   * Processes a single function name to include full qualification.
   *
   * @param string $function
   *   The function name to process.
   *
   * @return string
   *   The fully qualified function name with parameters.
   */
  protected function processSingleFunction(string $function): string {
    // Preserve main() special case.
    if ($function === 'main()') {
      return $function;
    }

    try {
      // Handle class methods.
      if (strpos($function, '::') !== FALSE) {
        [$class, $method] = explode('::', $function, 2);
        $reflection = new ReflectionMethod($class, $method);
      }
      // Handle regular functions.
      else {
        $reflection = new ReflectionFunction($function);
      }
    }
    catch (ReflectionException $e) {
      return $function;
    }

    // Get parameters with FQ types.
    $params = [];
    foreach ($reflection->getParameters() as $param) {
      $type = $param->getType();
      $type_str = '';
      if ($type instanceof ReflectionNamedType) {
        $type_name = $type->getName();
        if (!$type->isBuiltin() && !in_array($type_name, ['self', 'static', 'parent'])) {
          // Use short class name instead of FQ name.
          $type_name = (new ReflectionClass($type_name))->getShortName();
        }
        $type_str = $type_name . ' ';
      }
      $params[] = $type_str . '$' . $param->getName();
    }

    // Build qualified name.
    $name = $reflection instanceof ReflectionMethod
      ? '\\' . ltrim($reflection->getDeclaringClass()->getName(), '\\') . '::' . $reflection->getName()
      : $reflection->getName();

    return $name . '(' . implode(', ', $params) . ')';
  }

}
