Ajax Flash Messages Bundle
===============

This Bundle Allow the Process Ajax Request in any action (Forms, Redirections, Close Modals, Distpatch a javascript event, handle errors and form errors, show flash messages, etc.). Require jQuery.

Installation
----

Add to composer.json:

```json
{
  "require": {
    "manuelj555/ajax-bundle": "1.0.*@dev"
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
    $this->get('ku_ajax.handler')
      ->formErros($form, true) //if form is submited and is invalid, this handler send the errors to ajax response;
      // the second parameter is a boolean (return errors as string or not).

    if ($form->isSubmitted() and $form->isValid()) {
        $this->get('fos_user.user_manager')
          ->updateUser($user);

        $this->addFlash('success', 'User Created!');

        $this->get('ku_ajax.handler')
          ->closeModal() // distpach a javascript event called "ajax.close_modal"
          ->stopRedirect(); // remove de header Location from response (only in ajax request)
        return $this->redirectToRoute('admin_company_users_list', array('companyId' => $company->getId()));
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
  $.post(this.action, $form.serialize(), function (html, status, request) {
    $form.replaceWith($(html).find('form'));
  }).onCloseModal(function () { //this method is called on ajax complete
    $("#myModal").modal('hide');
  }).onFormErrors(function (errors, isHtml) { //this method is called when form is invalid
    /** If the IsHtml argument is true, the errors variable is an object where the indexes is
     * the id of the form element and the value is an html with the errors.
     *
     * If the isHtml argument is false, the errors is and object where the indexes is
     * the id of the form element and the value is an array of errors.
     */
    $form.find('.ajax-form-error').remove();
    $form.find('.form-group').removeClass('has-error');
    $.each(errors, function (id, errors) {
        $("#" + id).after(errors).closest('.form-group').addClass('has-error');
    });
  });
});
```

Ajax Handler Methods:
--------

```php
$this->get('ku_ajax.handler')->redirect($success = true, $stopHttpRedirection = true);
$this->get('ku_ajax.handler')->stopRedirect();
$this->get('ku_ajax.handler')->errors($arrayOrStringErrors);
$this->get('ku_ajax.handler')->formErros($formObject, $isHtml = true, $statusCode = 400);
$this->get('ku_ajax.handler')->closeModal($success = true);
$this->get('ku_ajax.handler')->trigger($eventName, $data);
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
