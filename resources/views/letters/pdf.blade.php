<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Letter - {{ $letter->reference_number }}</title>
    <style>
        body {
            padding-top: 100px;
            font-family: Arial, sans-serif;
            margin: 10px;
            line-height: 1.3;
        }
        .header {
            text-align: right;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }
        .letter-info {
            margin-bottom: 20px;
        }
        .content {
            margin: 15px 0;
            text-align: justify;
        }
        .footer {
            position: fixed;
            bottom: 100px;
            right: 20px;
            text-align: right;
        }
        .qr-code {
            width: 100px;
            height: 100px;
        }
        .reference {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        
        <p class="reference">{{ $letter->ref_no }}</p>
        <p><strong>Date:</strong> {{ $letter->date }}</p>
    </div>

    {{-- @if(!empty($qr_code_path))
    <div style="text-align:center; margin-bottom: 10px;">
        <img src="{{ public_path('storage/' . $qr_code) }}" alt="QR Code" width="120">
        <div>Reference No: {{ $letter->ref_no }}</div>
    </div>
@endif --}}

    <div class="letter-info">
        <p><strong>To: {{ $letter->to }}</strong><br>
        {{ $letter->recipient_name }}<br>
        {{ $letter->recipient_address }}</p>
        
        <p><strong>Subject:</strong> {{ $letter->subject }}</p>
        <p><strong>         </strong> {{ $letter->body }}</p>
    </div>

    

    <p><strong>CC:</strong>
    <div class="content">
        {!! nl2br(e($letter->cc)) !!}
    </div>
    <div class="content">
        {!! nl2br(e($letter->approved_by)) !!}
    </div>
      <div class="content">
        {!! nl2br(e($letter->approved_position)) !!}
    </div>
   <div class="footer">  
    @if(!empty($letter->ref_no))
    <img src="{{ public_path('storage/qr-codes/' . $letter->ref_no . '.png') }}" alt="QR Code" width="120">
    @endif</div>

</body>
</html>
