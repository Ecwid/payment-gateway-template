# Payment Gateway Integration Template

This is a sample code for creating a native payment integration for Ecwid. 

It has the following features: 

- automated read/save of user's settings
- up-to-date elements for user interface
- basic initialization and interaction with Ecwid JS SDK

## Files' descriptions

- `payment-request.php` – payment URL for the integration. Customers will be directed to that URL at the checkout to make the payment for order. It will also be used to update order status when callback is received from payment

- `index.html`, `functions.js`, `CssFw.css` – files for merchant settings in Ecwid Control Panel

## How to use automated functions to read/save user's settings: 

1) Assign `data-*` elements to user input elements

These functions support various user inputs: 

- inputs: radio, checkbox, text
- select
- textarea

Start by marking user inputs with `data-*` elements with these names: 

Name | Type | Description 
---- | ---- | -----------
data-visibility | string | Field visibility - where to save the value: to private or public application storage
data-name | string | Field key to be used to save to application storage


Possible values of `data-visibility`: `"public"`, `"private"`

Possible values of `data-name`: any value, except for `"public"`

2) Set default values for new users

In `functions.js` find the `initialConfig` object. It contains default user settings – map the keys and values with the ones you've set in the HTML for user inputs. 

-------

After that, use functions: `setValuesForPage()` and `readValuesFromPage()` to work with the page when you need to save or set values for the page.
