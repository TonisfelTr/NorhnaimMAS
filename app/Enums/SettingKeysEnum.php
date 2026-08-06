<?php

namespace app\Enums;

enum SettingKeysEnum: string
{
    case Name = 'app.name';
    case URL = 'app.url';
    case RecaptchaSiteKey = 'recaptchav3.sitekey';
    case RecaptchaSecret = 'recaptchav3.secret';
    case MailHost = 'mail.host';
    case MailPort = 'mail.port';
    case MailUsername = 'mail.username';
    case MailPassword = 'mail.password';
    case MailEncryption = 'mail.encryption';
    case MailFromAddress = 'mail.from.address';
}
