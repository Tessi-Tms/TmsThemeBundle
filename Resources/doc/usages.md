# TmsThemeBundle

## Configuration
For a simple use, the TmsThemeBundle is quite easy to configure. All you need to do is define your theme in the symfony configuration file.
```yaml
#app/Resources/config.yml
tms_theme:
    themes:
        mytheme: 'The theme name'
        anothertheme: 'Another name'
```

If you want to use the theme inheritance system, you just have to specify the parent theme.
```yaml
#app/Resources/config.yml
tms_theme:
    themes:
        mytheme: 'The theme name'
        anothertheme:
            name: 'Another name'
            parent: mytheme
```

## Development
Nothing extraordinary here. If you alredy read the symfony documentation for [*templating*](https://symfony.com/doc/3.4/templating.html), [*assets*](https://symfony.com/doc/3.4/best_practices/web-assets.html) and [*translation*](https://symfony.com/doc/3.4/translation.html), **you are ready!**

The only things you need to know, is the way a theme is structured and where to store it.

### Themes structure
All your themes will have the same structure.

Just like a symfony bundle, your templates will be stored in the **views** folder, the translations in the **translations** folder and your assets in the **public** folder.

```bash
mytheme
├── public
│   ├── css
│   ├── images
│   ├── ...
├── translations
├── views
```

### Themes location
Your theme files will be stored under the **Resources/themes** folder.
* `app/Resources/themes` for a symfony application
* `vendor/path/to/some/bundle/Resources/themes` for a third party bundle

### Global inheritance
With the theme inheritance system, you can modify **the files you want** only!
If a template, an asset or a translation is not found in your theme, the TmsThemeBundle will look up in the parent theme or in the symfony default location.

### Setting the theme
In order to load a theme, the only thing to do is calling the php method **setCurrentTheme** from the **ThemeManager** service.
```php
<?php

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MyController extends Controller
{
    //...
    public function myAction() {
        $this->get('tms_theme.manager')->setCurrentTheme('myTheme');
    }
}
```
### Tools
In order to simplify the creation of a theme, you can find some useful tools [here](tools.md).


## Production
Before using the bundle in a production environment, you need to dump all your theme assets with the following command.
```sh
$ php bin/console tms:themes:install
```

All your assets will be dumped in the web directory using this arborescence
```bash
web
├── themes
│   ├── mytheme
│   │   ├── mybundle
│   │   │   ├── css
│   │   │   ├── images
│   │   │   ├── ...
│   │   ├── css
│   │   ├── images
│   │   ├── ...
│   ├── anothertheme
│   │   ├── mybundle
│   │   │   ├── css
│   │   │   ├── images
│   │   │   ├── ...
│   │   ├── anotherbundle
│   │   │   ├── css
│   │   │   ├── images
│   │   │   ├── ...
│   │   ├── css
│   │   ├── images
│   │   ├── ...
```
