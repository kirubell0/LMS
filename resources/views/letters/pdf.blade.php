<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Letter - {{ $letter->ref_no }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Arial Unicode MS', sans-serif;
            margin: 40px;
            line-height: 1.6;
            font-size: 12pt;
            color: #333;
        }

        .header {
            text-align: right;
            margin-bottom: 40px;
            font-size: 10pt;
        }

        .reference {
            font-weight: normal;
            margin-bottom: 5px;
        }

        .date {
            margin-bottom: 20px;
        }

        .recipient {
            margin-bottom: 30px;
            font-weight: bold;
        }

        .recipient-line {
            margin-bottom: 2px;
        }

        .subject {
            margin-bottom: 25px;
            font-weight: bold;
        }

        .subject-underline {
            text-decoration: underline;
        }

        .content {
            margin-bottom: 30px;
            text-align: justify;
            word-wrap: break-word;
            overflow-wrap: break-word;
            line-height: 1.8;
        }

        .closing {
            margin-bottom: 60px;
        }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 40px;
        }

        .signature-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }

        .signature-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-left: 20px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            margin-bottom: 5px;
            height: 20px;
        }

        .signature-title {
            font-size: 10pt;
            margin-bottom: 15px;
        }

        .footer {
            position: fixed;
            bottom: 30px;
            right: 30px;
            text-align: right;
        }

        .qr-code {
            width: 80px;
            height: 80px;
        }

        /* Better support for RTL languages */
        .rtl {
            direction: rtl;
            text-align: right;
        }

        /* Ensure proper Unicode rendering */
        * {
            unicode-bidi: embed;
        }

        /* Amharic/Ethiopian script support */
        .amharic,
        [lang="am"],
        [lang="amh"] {
            font-family: 'Noto Sans Ethiopic', 'Abyssinica SIL', 'DejaVu Sans', 'Arial Unicode MS', sans-serif;
            font-size: 12pt;
            line-height: 1.8;
        }

        /* Ensure proper text rendering for complex scripts */
        .content {
            text-rendering: optimizeLegibility;
            -webkit-font-feature-settings: "liga", "kern";
            font-feature-settings: "liga", "kern";
        }

        .cc-section {
            margin-top: 20px;
            font-size: 10pt;
        }

        /* Lexical Editor HTML Styles */
        .content p {
            margin: 0 0 10px 0;
        }

        .content strong {
            font-weight: bold;
        }

        .content em {
            font-style: italic;
        }

        .content u {
            text-decoration: underline;
        }

        .content h1,
        .content h2,
        .content h3 {
            font-weight: bold;
            margin: 15px 0 10px 0;
        }

        .content h1 {
            font-size: 14pt;
        }

        .content h2 {
            font-size: 12pt;
        }

        .content h3 {
            font-size: 11pt;
        }

        .content ul,
        .content ol {
            margin: 10px 0;
            padding-left: 20px;
        }

        .content li {
            margin: 3px 0;
        }

        .content blockquote {
            border-left: 3px solid #ccc;
            padding-left: 15px;
            margin: 10px 0;
            font-style: italic;
        }
    </style>
</head>

<body>
    <!-- Header with Reference and Date -->
    <div class="header">
        <div class="reference">{{ $letter->ref_no }}</div>
        <div class="date">Date: {{ $letter->date }}</div>
    </div>

    <!-- Recipient Information -->
    <div class="recipient">
        @php
            $toLines = explode("\n", $letter->to);
        @endphp
        @foreach ($toLines as $line)
            <div class="recipient-line">{{ trim($line) }}</div>
        @endforeach
    </div>

    <!-- Subject -->
    <div class="subject">
        Subject: - <span class="subject-underline">{{ $letter->subject }}</span>
    </div>

    <!-- Letter Body -->
    <div class="content">
        {!! $letter->body !!}
    </div>

    <!-- Signatures Section -->
    <div class="signatures">
        <div class="signature-left">
            @if (!empty($letter->approved_by))
                <div class="signature-line"></div>
                <div class="signature-title">{{ $letter->approved_by }}</div>
                @if (!empty($letter->approved_position))
                    <div class="signature-title">{{ $letter->approved_position }}</div>
                @endif
            @endif
        </div>
        <div class="signature-right">
            @if (!empty($letter->approved_by_optional))
                <div class="signature-line"></div>
                <div class="signature-title">{{ $letter->approved_by_optional }}</div>
                @if (!empty($letter->approved_position_optional))
                    <div class="signature-title">{{ $letter->approved_position_optional }}</div>
                @endif
            @endif
        </div>
    </div>

    <!-- CC Section -->
    @if (!empty($letter->cc) || !empty($letter->cc_position))
        <div class="cc-section">
            @if (!empty($letter->cc))
                <div><strong>CC:</strong></div>
                <div>{{ $letter->cc }}</div>
            @endif
            @if (!empty($letter->cc_position))
                <div><strong>CC (Optional):</strong></div>
                <div>{{ $letter->cc_position }}</div>
            @endif
        </div>
    @endif

    <!-- QR Code Footer -->
    <div class="footer">
        @if (!empty($qrCodeBase64))
            <img src="{{ $qrCodeBase64 }}" alt="QR Code" class="qr-code">
        @endif
    </div>

</body>

</html>
