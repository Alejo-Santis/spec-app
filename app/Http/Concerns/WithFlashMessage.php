<?php

namespace App\Http\Concerns;

trait WithFlashMessage
{
    protected function success(string $message): array
    {
        return ['message', ['success' => true, 'message' => $message]];
    }

    protected function error(string $message): array
    {
        return ['message', ['success' => false, 'message' => $message]];
    }
}
