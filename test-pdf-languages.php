<?php
// Quick test to see what languages are causing issues
require_once 'vendor/autoload.php';

use Barryvdh\DomPDF\Facade\Pdf;

// Test different languages
$testContent = [
    'English' => 'Hello World',
    'Arabic' => 'مرحبا بالعالم',
    'Chinese' => '你好世界',
    'Japanese' => 'こんにちは世界',
    'Korean' => '안녕하세요 세계',
    'Russian' => 'Привет мир',
    'French' => 'Bonjour le monde',
    'German' => 'Hallo Welt',
    'Spanish' => 'Hola Mundo',
    'Hindi' => 'नमस्ते दुनिया',
];

$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { 
            font-family: "DejaVu Sans", sans-serif; 
            font-size: 14pt;
            line-height: 1.5;
        }
        .test-item {
            margin: 10px 0;
            padding: 5px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <h1>Multi-Language PDF Test</h1>';

foreach ($testContent as $language => $text) {
    $html .= "<div class='test-item'><strong>{$language}:</strong> {$text}</div>";
}

$html .= '</body></html>';

try {
    $pdf = PDF::loadHTML($html);
    $pdf->setPaper('A4', 'portrait');
    $pdf->setOptions([
        'defaultFont' => 'DejaVu Sans',
        'isHtml5ParserEnabled' => true,
        'isPhpEnabled' => false,
        'fontSubsetting' => true,
        'enable_font_subsetting' => true,
    ]);
    
    $pdf->save('language-test.pdf');
    echo "PDF generated successfully! Check language-test.pdf\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>