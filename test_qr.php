<?php
require_once 'vendor/autoload.php';

try {
    echo "Attempting to create QR code builder...<br>";
    $builder = \Endroid\QrCode\Builder\Builder::create();
    echo "Builder created successfully!<br>";
    
    $result = $builder
        ->writer(new \Endroid\QrCode\Writer\PngWriter())
        ->data('Test')
        ->build();
        
    echo "QR Code generated successfully!";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage();
}
