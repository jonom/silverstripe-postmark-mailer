## Notice for SilverStripe 4 users

This module is for SilverStripe v3 only. SilverStripe 4 supports many email services through configuration only by providing a wrapper for SwiftMailer. Postmark takes some extra work to set up though, because SS4 uses SwiftMailer v5, and the official Postmark adapter for that version is incomplete. Here are a couple of different approaches for working around the issue:

* [Use patched fork through composer](https://forum.silverstripe.org/t/using-postmark-with-swift-mailer-on-ss4-2/873)
* [Create custom transport in project](https://gist.github.com/wilr/d32a0e83af3489538603f1b2f18dc73a)

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
