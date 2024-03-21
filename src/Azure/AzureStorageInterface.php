<?php

namespace App\Azure;

interface AzureStorageInterface
{
    public function generateURLForFileReading(string $filename, ?string $destinationPath): string;
}
