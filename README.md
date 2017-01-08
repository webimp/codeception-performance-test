# PerformanceTest
Codeception Extension to measure the performance of your tests. Compatible with Codeception 2.2.7.

After running your tests you will see a Performance Report of your slow tests:
```bash
Slow Steps (more than 3s) ----------------------
I click button 6s
```

## Installation
Add PerformanceTest to your composer.json

```yaml
  "require": {
    ...
    "webimp/codeception-performance-test": "dev-master",
```

## Usage
Add this to your extensions line at the bottom of your codeception.yml:

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
        - Codeception\Extension\PerformanceTest
```
