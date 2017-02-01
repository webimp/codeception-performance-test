# PerformanceTest

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/5910781de43943cfa756382e133ff130)](https://www.codacy.com/app/KhairulA/codeception-performance-test?utm_source=github.com&utm_medium=referral&utm_content=webimp/codeception-performance-test&utm_campaign=badger)

Codeception Extension to list out slow steps during the test. Compatible with [Codeception 2.2.7](http://codeception.com).

After running your tests you will see a Performance Report of your slow tests:
```bash
Slow Steps (more than 2.5s) ----------------------
I click button 6s
```

## Installation
Add PerformanceTest to your `composer.json`:

```yaml
  "require-dev": {
    ...
    "webimp/codeception-performance-test": "1.0.*",
```

## Usage
Add this to your extensions line at the bottom of your `codeception.yml`:

```yaml
actor: Tester
paths:
    tests: tests
    log: tests/_output
    data: tests/_data
    helpers: tests/_support
settings:
    bootstrap: _bootstrap.php
    colors: true
    memory_limit: 1024M
extensions:
    enabled:
        - Codeception\Extension\PerformanceTest:
            benchmark: 2.5 # min seconds for step to be marked as "slow"
```

Include the file into your `_bootstrap.php`:
```php
include('./vendor/webimp/codeception-performance-test/src/PerformanceTest.php');
```
