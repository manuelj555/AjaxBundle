AjaxFlashBundle
===============

This Bundle Allow the Process of Flashes in ajax request via Javascript. Require jQuery.

Installation
----

Add to composer.json:

```json
{
  "require": {
    "manuelj555/ajax-flash-bundle": "1.0.*@dev"
  }
}
```

Execute composer update.

Configuration
----

Register the bundle:

```php
<?php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Manuelj555\Bundle\AjaxFlashBundle\ManuelAjaxFlashBundle(),
        // ...
    );
}
```

In the config.yml (All config is Optional):

```yaml
manuel_ajax_flash:
    auto_assets:
        pnotify: ~
#        sticky: ~
    mapping:
#        success:
#            title: Información
#            icon: my-icon
#        info:
#            title: Información
```

auto_assets
____

Auto add the javascript and css in the html content. You have select the plugin to use, the available options are:

  * pnotify (http://sciactive.com/pnotify/)
  * sticky (http://danielraftery.com/read/Sticky-A-super-simple-notification-system-for-jQuery)

mapping
_____

Allow set the title, icon and type for use in javascript, for each setted mapping type.

Example:

```yaml
manuel_ajax_flash:
    mapping:
        success:
             type: success
             title: Información
             icon: my-icon
         info:
             type: info
             title: Información
         error:
             type: danger
             title: Error
```

Manual Assets Instalation
-----------

If you no enable the auto_assets config, you can use the twig view located in the bundle:

  * ManuelAjaxFlashBundle::pnotify.html.twig or
  * ManuelAjaxFlashBundle::sticky.html.twig
  
Example of use:

```jinja
{% use 'ManuelAjaxFlashBundle::pnotify.html.twig' %}
{#{% use 'ManuelAjaxFlashBundle::sticky.html.twig' %}#}
<!DOCTYPE html>
<html>
    <head>
        ...
        
        {{ block('ajax_flash_css') }}
    </head>
    <body>
        ...
        
        {{ block('ajax_flash_js') }}
        {{ block('ajax_flash_plugin') }}
    </body>
</html>
```

Javascript Plugin
-------

Usage:

```javascript
$.ajaxFlash('*', function (message, type, title, icon) {
    //call on all flash types. this function is called for each flash message
    //the message parameter is a string
});
$.ajaxFlash('success info', function (message, type, title, icon) {
    //call on success and info flash types. this function is called for each flash message
    //the message parameter is a string
});
$.ajaxFlash('error', function (message, type, title, icon) {
    //call on error flash type. this function is called for each flash message
    //the message parameter is a string
});

// Working with array messages:

$.ajaxFlash(function (messages, type, title, icon) {
    //call in all flash types, this function is called one time for each message type.
    //the messages parameter is an array.
});

$.ajaxFlash(function (messages, type, title, icon) {
    //call success and info flash types, this function is called one time for each message type.
    //the messages parameter is an array.
}, 'success info');
```
