<?php

namespace App\Azure;

class AzureStorageLocal implements AzureStorageInterface
{
    // Returns a url that is is signed with a SAS
    // Based on using https://github.com/Azure/Azurite in local development
    public function generateURLForFileReading(string $filename, ?string $destinationPath): string
    {
        return "http://localhost:10000/devstoreaccount1/storage/$filename";
    }
}
