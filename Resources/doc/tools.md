TmsThemeBundle
==============

## Tools

### Twig
You will find below the documentation of the twig extensions/function created to simplify the theme creation.

#### templateParent()
Following the [symfony documentation](https://symfony.com/doc/current/templating.html#template-inheritance-and-layouts), you'll probably want to use the **block** tag. It's really a good idea, avoiding duplicate the html code. But you will be confronted to a problem using this bundle's theme inheritance. How to extends a template file from the parent theme ?

Using the **templateParent**  function will allow you to extends the same file from the parent theme avoiding some circular reference exception.
```twig
# app/Resources/themes/subtheme/views/index.html.twig
{% extends templateParent('index.html.twig') %}

{%- block myblock -%}
    {# ... #}
{%- endblock -%}
```

### themeAsset()
Your parent theme is using a javascript library and you don't want to duplicate it ? Just use the **themeAsset** function, you'll just need to specify the path from the theme's **public** folder and the bundle will take care of everything.

Supposing **myTheme** is a sub theme of **parentTheme**.
```bash
parentTheme
├── public
│   ├── javascripts
│   │   ├── jquery.min.js
│   │   ├── form.jquery.min.js
```
```bash
myTheme
├── public
│   ├── javascripts
│   │   ├── form.jquery.min.js
├── views
│   ├── index.html.twig
```
In this sample, you'll load the *jquery.min.js* asset from **parentTheme** and *form.jquery.min.js* asset from **myTheme**.
```twig
# app/Resources/themes/subtheme/views/index.html.twig
{%- block javascripts -%}
    <script src="{{ asset('javascripts/jquery.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('javascripts/form.jquery.min.js') }}" type="text/javascript"></script>
{%- endblock -%}
```
