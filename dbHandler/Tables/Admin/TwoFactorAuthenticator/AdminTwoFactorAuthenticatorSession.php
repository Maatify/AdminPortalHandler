<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2024-10-17
 * Time: 2:35 PM
 * https://www.Maatify.dev
 */

namespace Maatify\Portal\Admin\TwoFactorAuthenticator;

use App\Assist\Jwt\JWTAdminTwoFactorAuthenticatorSession;

class AdminTwoFactorAuthenticatorSession extends AdminTwoFactorAuthenticator
{
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function generate(): void
    {
        JWTAdminTwoFactorAuthenticatorSession::obj()->Jwt2FaTokenHash();
    }

    public function validate(): bool
    {
        return JWTAdminTwoFactorAuthenticatorSession::obj()->Jwt2FaValidation();
    }
}