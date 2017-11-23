<?php
declare(strict_types=1);

namespace Pac\Download\Security;

interface AuthorizationCheckInterface
{
    public function allowed($file): bool;
}
