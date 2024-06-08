[![Current version](https://img.shields.io/packagist/v/maatify/admin-portal-handler)][pkg]
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/maatify/admin-portal-handler)][pkg]
[![Monthly Downloads](https://img.shields.io/packagist/dm/maatify/admin-portal-handler)][pkg-stats]
[![Total Downloads](https://img.shields.io/packagist/dt/maatify/admin-portal-handler)][pkg-stats]
[![Stars](https://img.shields.io/packagist/stars/maatify/admin-portal-handler)](https://github.com/maatify/AdminPortalHandler/stargazers)

[pkg]: <https://packagist.org/packages/maatify/admin-portal-handler>
[pkg-stats]: <https://packagist.org/packages/maatify/admin-portal-handler/stats>

# PostValidatorJsonCode

maatify.dev Admin Portal Handler, known by our team


# Installation

```shell
composer require maatify/admin-portal-handler
```
    
## Important
Don't forget to use \App\DB\DBS\DbConnector;

Don't forget to use \App\DB\DBS\DbLogger;

Don't forget to use \App\DB\DBS\DbPortalHandler;

Don't forget to use \App\DB\DBS\DbProjectHandler;

Don't forget to use \App\Assist\AppFunctions

Don't forget to use \App\Assist\Encryptions

Don't forget to use \App\Assist\Jwt

Don't forget to use \App\Assist\OpensslEncryption

Don't forget to use \App\DB\Tables\Language\LanguagePortalRecord

Don't forget to use \App\Assist\DefaultPassword

```php
namespace App\Assist;

use Maatify\Functions\GeneralPasswordGenerator;

class DefaultPassword
{
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function GenerateAdminDefaultPassword(): string
    {
        $password = GeneralPasswordGenerator::passwordGenerator(16, GeneralPasswordGenerator::allCharacters());
        return $password;
    }

    public static function GenerateCustomerDefaultPassword(): string
    {
        $password = GeneralPasswordGenerator::passwordGenerator(13, GeneralPasswordGenerator::allCharacters());
        return $password;
    }
}
```

