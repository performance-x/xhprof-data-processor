# XHProf Data Processor

Enhances XHProf profiling data by adding detailed function signatures including parameter types while keeping class names short and readable.

## Installation

```bash
composer require performance-x/xhprof-data-processor
```

## Usage

```php
use PerformanceX\XHProfProcessor\XHProfDataProcessor;

$processor = new XHProfDataProcessor();
$processed_data = $processor->process($xhprof_data);
```

### Example

Input:
```php
[
    "main()" => [
        "ct" => 1,
        "wt" => 100
    ],
    "SomeClass::method==>strlen" => [
        "ct" => 1,
        "wt" => 50
    ]
]
```

Output:
```php
[
    "main()" => [
        "ct" => 1,
        "wt" => 100
    ],
    "SomeClass::method(User $user, array $data)==>strlen(string $string)" => [
        "ct" => 1,
        "wt" => 50
    ]
]
```

## Features

- Adds parameter type information to function calls
- Uses short class names for better readability
- Preserves XHProf recursion markers
- Maintains all original metrics
- Handles special types (self, static, parent)

## Requirements

- PHP 8.1 or higher
- Reflection extension enabled
