<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print PDF</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        .pdf-container {
            text-align: center;
            margin: 20px 0;
        }
        .pdf-embed {
            width: 100%;
            height: 600px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .print-controls {
            text-align: center;
            margin: 20px 0;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        @media print {
            body { margin: 0; padding: 0; background: white; }
            .container { box-shadow: none; margin: 0; padding: 0; }
            .header, .print-controls { display: none; }
            .pdf-embed { height: auto; border: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PDF Print Preview</h1>
            @if(isset($task))
                <h2>{{ $task->title ?? 'Task Document' }}</h2>
            @endif
            @if(isset($fileName))
                <p><strong>File:</strong> {{ $fileName }}</p>
            @endif
            <p>Review the document below and use the print button to print</p>
        </div>

        <div class="print-controls">
            <button onclick="window.print()" class="btn">Print PDF</button>
            <a href="{{ $pdfUrl }}" target="_blank" class="btn btn-secondary">Open in New Tab</a>
            <button onclick="window.close()" class="btn btn-secondary">Close</button>
        </div>

        <div class="pdf-container">
            <embed src="{{ $pdfUrl }}" type="application/pdf" class="pdf-embed">
        </div>

        <div class="print-controls">
            <button onclick="window.print()" class="btn">Print PDF</button>
            <a href="{{ $pdfUrl }}" target="_blank" class="btn btn-secondary">Open in New Tab</a>
        </div>
    </div>

    <script>
        // Auto-focus for better UX
        window.addEventListener('load', function() {
            // Optional: Auto-open print dialog after a short delay
            // setTimeout(() => window.print(), 1000);
        });

        // Handle keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>