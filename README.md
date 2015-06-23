Ajax Flash Messages Bundle
===============

This Bundle Allow the Process Ajax Request in any action (Forms, Redirections, Close Modals, Distpatch a javascript event, handle errors and form errors, show flash messages, etc.). Require jQuery.

Installation
----

Add to composer.json:

```json
{
  "require": {
    "manuelj555/ajax-bundle": "2.0.*@dev"
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
        new Ku\AjaxBundle\KuAjaxBundle(),
        // ...
    );
}
```

Ajax Handler
===============

Example of Usage:

A Controller
--------

```php

public function createAction(Request $request)
{
    $user = new User();
    $form = $this->createForm(new UserType(), $user, array(
        'action' => $request->getRequestUri(),
    ));
    $form->handleRequest($request);

    if ($form->isSubmitted() and $form->isValid()) {
        $this->get('fos_user.user_manager')
          ->updateUser($user);

        $this->addFlash('success', 'User Created!');

        $this->get('ku_ajax.handler')->success();
        // Calling to succes method on ajax Handler, stop the redirection on ajax request 
        // and send a status code 200
        return $this->redirectToRoute('admin_company_users_list', array('companyId' => $company->getId()));
    }elseif($form->isSubmitted()){
      // invalid form
      $this->get('ku_ajax.handler')->badRequest();
      // this send a status code 400
    }

    return $this->render('user/create.html.twig', array(
        'form' => $form->createView(),
    ));
}
```

The View
-------

```javascript
$('#user-form').on('submit', 'form', function (e) {
  e.preventDefault();
  var $form = $(this);
  $.post(this.action, $form.serialize(), function () {
    // this callback is called on success response
    $("#myModal").modal('hide');
  }).fail(function (xhr) { //this method is called on ajax error
    if(xhr.status == 400){
     // invalid form data
      $form.replaceWith($(html).find('form'));
    }
  });
});
```

Ajax Handler Methods:
--------

```php
$this->get('ku_ajax.handler')->success($statusCode = 200);
$this->get('ku_ajax.handler')->error($message, $statusCode = 400)
$this->get('ku_ajax.handler')->badRequest($statusCode = 400)
$this->get('ku_ajax.handler')->redirect($isOk = true, $statusCode = 278)
```

Flash Messages
===============

This Bundle Allow the Process of Flashes in ajax request via Javascript. Require jQuery.

In the config.yml (All config is Optional):

```yaml
ku_ajax:
    handler: ~
    flash_messages:
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

flash_messages: auto_assets
____

Auto add the javascript and css in the html content. You have select the plugin to use, the available options are:

  * pnotify (http://sciactive.com/pnotify/)
  * sticky (http://danielraftery.com/read/Sticky-A-super-simple-notification-system-for-jQuery)

flash_messages: mapping
_____

Allow set the title, icon and type for use in javascript, for each setted mapping type.

Example:

```yaml
ku_ajax:
    flash_messages:
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

  * KuAjaxBundle:flash:pnotify.html.twig or
  * KuAjaxBundle:flash:sticky.html.twig
  
Example of use:

```jinja
{% use 'KuAjaxBundle:flash:pnotify.html.twig' %}
{#{% use 'KuAjaxBundle:flash:sticky.html.twig' %}#}
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
