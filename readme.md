# SilverStripe Postmark Mailer

This module lets you send SilverStripe emails through the [official Postmark PHP library](https://github.com/wildbit/postmark-php), falling back to PHP's built-in `sendmail()` if Postmark is unreachable.

## Requirements
 * PHP 5.5+
 * SilverStripe ^3.1
 * [Postmark-PHP](https://github.com/wildbit/postmark-php)

*Note: an alternative Postmark Mailer class that works with older versions of SilverStripe and PHP is available [here](https://github.com/fullscreeninteractive/silverstripe-postmarkmailer).*

## Installation
Install with Composer. [Learn how](https://docs.silverstripe.org/en/getting_started/composer/#adding-modules-to-your-project)

```
composer require "jonom/silverstripe-postmark-mailer:^1.0"
```

## Documentation

You will need to provide a PostmarkAPP API key and at least one verified email address (Sender Signature) that you have set up in your [Postmark account](https://postmarkapp.com/).
If you try to send an email from a non-verified address, the From address will be changed to the first verified address you provided and a Reply-To field will be set with the original From address.

## Example configuration

In your project's `_config.php` file:

```php
Email::set_mailer(new PostmarkMailer());
```

or:

```php
// Send email through Postmark in live environment only
if (Director::isLive()) {
	Email::set_mailer(new PostmarkMailer());
}
```

In your project's `_config/config.yml` file:

```yaml
PostmarkMailer:
  api_key: 'your-key-goes-here'
  sender_signatures:
    - 'example@yourwebsite.com'
    - 'example2@yourwebsite.com'
```

## Maintainer contact

[jonathonmenz.com](http://jonathonmenz.com)