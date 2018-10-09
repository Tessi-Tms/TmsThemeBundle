TmsThemeBundle
===========================

The TmsThemeBundle provides a simple way to handle multiple themes in a symfony application.
You will be able to manage, the templates files, the translations and all your assets for a theme.
With the 'parents' system, you can even extends your own themes in many  subthemes


Installation
------------

Add dependencies in your `composer.json` file:
```json
"repositories": [
    ...,
    {
        "type": "vcs",
        "url": "https://github.com/Tessi-Tms/TmsThemeBundle.git"
    }
],
"require": {
    ...,
    "tms/theme-bundle": "dev-master"
},
```

Install these new dependencies of your application:
```sh
$ php composer.phar update
```

Enable the bundle in your application kernel:
```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Tms\Bundle\ThemeBundle\TmsThemeBundle(),
    );
}
```
