<?php

return [
'pdf' => [
    'enabled' => true,
    'binary'  => '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"', // Use quotes for Windows
    'timeout' => false,
    'options' => [
        'enable-local-file-access' => true, // Add if needed for local files
        'lowquality' => true,              // Keep if you need low quality
    ],
    'env'     => [],
],

'image' => [
    'enabled' => true,
    'binary'  => '"' . env('WKHTML_IMG_BINARY','"C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe') . '"',
    'timeout' => false,
    'options' => [],
    'env'     => [],
],

];