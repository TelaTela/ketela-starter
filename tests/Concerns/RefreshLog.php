<?php

namespace Tests\Concerns;

trait RefreshLog
{
    public function setUpRefreshLog(): void
    {
        file_put_contents(storage_path('logs/test.log'), '');
    }
}
