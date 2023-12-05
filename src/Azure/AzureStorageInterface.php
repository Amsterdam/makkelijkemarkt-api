<?php

namespace App\Azure;

interface AzureStorageInterface
{
    public function generateURLForImageReading(string $blob): string;
}
