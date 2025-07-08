<!-- filepath: resources/views/print-pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Print Letter</title>
    <style>
        html, body { height: 100%; margin: 0; }
        iframe { width: 100vw; height: 100vh; border: none; }
    </style>
</head>
<body>
    <iframe id="pdfFrame" src="{{ $pdfUrl }}"></iframe>
    <script>
        document.getElementById('pdfFrame').addEventListener('load', function() {
            setTimeout(function() {
                window.frames['pdfFrame'].focus();
                window.frames['pdfFrame'].print();
            }, 500);
        });
    </script>
</body>
</html>