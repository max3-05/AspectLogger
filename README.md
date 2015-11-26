# AspectLogger
AspectLogger gives developers ability to add logging functionality without needs to modify existing code. Ohh... Ok, with minimal needs.

### Prerequisites
Before you can use this library you need to install [php-aop module](http://aop-php.github.io/)

### Usage
To use this library you need to define what exactly you would like to log with xml configuration. Example of such config you may see at [config.xml.example](https://github.com/max3-05/AspectLogger/blob/master/docs/config.xml.example)
Also you need to load library and its configuration within your application like this

```php
require_once "./lib/AspectLogger/AspectLogger.php";

$logger = new AspectLogger();
$logger->init('<path to xml config file>');

/// Your application code here
```

That's all. Logging is on.

### Documentation
For more information please see [documentation](http://max3-05.github.io/AspectLogger/docs/)
