<?php

namespace Modules\ImportRectorate\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

class VerifyImportDeletionCsrfToken extends ValidateCsrfToken
{
    protected function inExceptArray($request)
    {
        // The legacy /backoffice/* exception must not exempt import deletion.
        return false;
    }
}
