<?php
declare(strict_types=1);

namespace Pac\Download\Security;

class PermissiveAuthorizationCheck implements AuthorizationCheckInterface
{
    public function allowed($file): bool
    {
        return true;
    }
}
