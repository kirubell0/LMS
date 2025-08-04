<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use \SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;


class TaskController extends Controller
{ 
   
    public function index()
    {
        $query = Task::with('list')
            ->whereHas('list', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->orderBy('created_at', 'desc');

        // Handle search
        if (request()->has('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('ref_no', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        // Handle completion filter
        if (request()->has('filter') && request('filter') !== 'all') {
            $query->where('is_completed', request('filter') === 'completed');
        }

        $tasks = $query->paginate(10);

        $lists = TaskList::where('user_id', auth()->id())->get();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'lists' => $lists,
            'filters' => [
                'search' => request('search', ''),
                'filter' => request('filter', 'all'),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ]
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'ref_no' => 'required|string|max:500',
            'to' => 'required|string| max:500',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'pdf_path' => 'string',
            'date' => 'required|string',
            'qr_code_path' => 'string',
            'approved_by' => 'string ',
            'approved_position' => 'string',
            'is_completed' => 'boolean',
            'cc' => 'required | string',
            'list_id' => 'required|exists:lists,id',
            'cc_position' => 'nullable|string',
            'approved_by_optional' => 'nullable|string',
            'approved_position_optional' => 'nullable|string',
        ]);

        $task = Task::create($validated);
        
        // Generate PDF
        $this->generatePDF($task);
    

        return redirect()->route('tasks.index')->with('success', 'Letter created successfully!');
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'ref_no' => 'required|string|max:500',
            'to' => 'required|string| max:500',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'pdf_path' => 'string',
            'date' => 'required|string',
            'approved_by' => 'string ',
            'approved_position' => 'string',
            'is_completed' => 'boolean',
            'cc' => 'required | string',
            'list_id' => 'required|exists:lists,id',
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Letter updated successfully!');
    }


public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Letter deleted successfully!');
    }
public function generateQRCode(Task $task)
{
    try {
        // Validate task data
        if (!$task || !$task->id || !$task->ref_no) {
            throw new \InvalidArgumentException('Invalid task data provided');
        }

        // Sanitize reference number for file naming
        $sanitizedRefNo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $task->ref_no);
        if (empty($sanitizedRefNo)) {
            throw new \InvalidArgumentException('Invalid reference number for file naming');
        }

        $qrCodePath = 'qr-codes/' . $sanitizedRefNo . '.png';
        $pdfUrl = url('/tasks/' . $task->id . '/print');

        // Validate URL generation
        if (empty($pdfUrl) || !filter_var($pdfUrl, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('Failed to generate valid PDF URL');
        }

        // Ensure directory exists with proper error handling
        try {
            if (!Storage::disk('public')->exists('qr-codes')) {
                if (!Storage::disk('public')->makeDirectory('qr-codes')) {
                    throw new \RuntimeException('Failed to create qr-codes directory');
                }
            }
        } catch (\Exception $e) {
            \Log::error('QR Code directory creation failed: ' . $e->getMessage());
            throw new \RuntimeException('Unable to create storage directory: ' . $e->getMessage());
        }

        // Generate QR code with multiple fallback options
        $qrCode = null;
        $finalPath = $qrCodePath;

        // First attempt: PNG format
        try {
            $qrCode = QrCode::format('png')
                    ->size(200)
                    ->backgroundColor(255, 255, 255)
                    ->color(0, 0, 0)
                    ->errorCorrection('M')
                    ->generate($pdfUrl);
            
            if (empty($qrCode)) {
                throw new \RuntimeException('QR code generation returned empty result');
            }
        } catch (\Exception $e) {
            \Log::warning('PNG QR code generation failed: ' . $e->getMessage());
            
            // Second attempt: SVG format
            try {
                $qrCode = QrCode::format('svg')
                        ->size(200)
                        ->errorCorrection('M')
                        ->generate($pdfUrl);
                $finalPath = 'qr-codes/' . $sanitizedRefNo . '.svg';
                
                if (empty($qrCode)) {
                    throw new \RuntimeException('SVG QR code generation returned empty result');
                }
            } catch (\Exception $svgException) {
                \Log::error('SVG QR code generation also failed: ' . $svgException->getMessage());
                
                // Third attempt: Basic format without specific backend
                try {
                    $qrCode = QrCode::size(200)->generate($pdfUrl);
                    $finalPath = 'qr-codes/' . $sanitizedRefNo . '.txt';
                    
                    if (empty($qrCode)) {
                        throw new \RuntimeException('Basic QR code generation returned empty result');
                    }
                } catch (\Exception $basicException) {
                    \Log::error('All QR code generation methods failed', [
                        'task_id' => $task->id,
                        'ref_no' => $task->ref_no,
                        'png_error' => $e->getMessage(),
                        'svg_error' => $svgException->getMessage(),
                        'basic_error' => $basicException->getMessage()
                    ]);
                    throw new \RuntimeException('Failed to generate QR code in any format: ' . $basicException->getMessage());
                }
            }
        }

        // Store the QR code with error handling
        try {
            $stored = Storage::disk('public')->put($finalPath, $qrCode);
            if (!$stored) {
                throw new \RuntimeException('Failed to store QR code file');
            }
        } catch (\Exception $e) {
            \Log::error('QR Code storage failed: ' . $e->getMessage());
            throw new \RuntimeException('Unable to save QR code: ' . $e->getMessage());
        }

        // Verify the file was actually created
        if (!Storage::disk('public')->exists($finalPath)) {
            throw new \RuntimeException('QR code file was not created successfully');
        }

        // Update task with QR code path
        try {
            $updated = $task->update(['qr_code_path' => $finalPath]);
            if (!$updated) {
                \Log::warning('Failed to update task with QR code path', [
                    'task_id' => $task->id,
                    'qr_path' => $finalPath
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to update task with QR code path: ' . $e->getMessage());
            // Don't throw here as the QR code was generated successfully
        }

        \Log::info('QR code generated successfully', [
            'task_id' => $task->id,
            'ref_no' => $task->ref_no,
            'path' => $finalPath
        ]);

        return $finalPath;

    } catch (\InvalidArgumentException $e) {
        \Log::error('QR Code Generation - Invalid Argument: ' . $e->getMessage());
        throw $e;
    } catch (\RuntimeException $e) {
        \Log::error('QR Code Generation - Runtime Error: ' . $e->getMessage());
        throw $e;
    } catch (\Exception $e) {
        \Log::error('QR Code Generation - Unexpected Error: ' . $e->getMessage(), [
            'task_id' => $task->id ?? 'unknown',
            'exception' => get_class($e),
            'trace' => $e->getTraceAsString()
        ]);
        throw new \RuntimeException('Unexpected error during QR code generation: ' . $e->getMessage());
    }
}
public function generatePDF(Task $task)
{
    try {
        // Ensure QR code exists before generating PDF
        $this->generateQRCode($task);

        // Ensure PDFs directory exists
        if (!Storage::disk('public')->exists('pdfs')) {
            Storage::disk('public')->makeDirectory('pdfs');
        }

        // Get QR code as base64 for the PDF view
        $qrCodeBase64 = null;
        
        // Try different QR code formats in order of preference
        $qrCodeFormats = [
            ['path' => 'qr-codes/' . $task->ref_no . '.png', 'mime' => 'image/png'],
            ['path' => 'qr-codes/' . $task->ref_no . '.svg', 'mime' => 'image/svg+xml'],
            ['path' => 'qr-codes/' . $task->ref_no . '.txt', 'mime' => 'image/svg+xml']
        ];
        
        foreach ($qrCodeFormats as $format) {
            if (Storage::disk('public')->exists($format['path'])) {
                $qrCodeContent = Storage::disk('public')->get($format['path']);
                $qrCodeBase64 = 'data:' . $format['mime'] . ';base64,' . base64_encode($qrCodeContent);
                break;
            }
        }

        // Ensure proper UTF-8 encoding for all text fields
        $taskData = $task->toArray();
        foreach ($taskData as $key => $value) {
            if (is_string($value)) {
                // Ensure proper UTF-8 encoding and handle Amharic characters
                $taskData[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                // Additional encoding check for complex scripts
                if (!mb_check_encoding($taskData[$key], 'UTF-8')) {
                    $taskData[$key] = utf8_encode($value);
                }
            }
        }
        
        $pdf = PDF::loadView('letters.pdf', [
            'letter' => (object) $taskData,
            'qrCodeBase64' => $qrCodeBase64
        ]);
        
        // Set PDF options for better compatibility and language support
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
            'fontSubsetting' => true,
            'debugKeepTemp' => false,
            'enable_font_subsetting' => true,
            'defaultMediaType' => 'print',
            'defaultPaperSize' => 'a4',
            'defaultPaperOrientation' => 'portrait',
            'isFontSubsettingEnabled' => true,
            'isUnicode' => true,
            'enable_unicode' => true,
        ]);
        
        $pdfPath = 'pdfs/' . $task->ref_no . '.pdf';
        $pdfContent = $pdf->output();
        
        Storage::disk('public')->put($pdfPath, $pdfContent);
        $task->update(['pdf_path' => $pdfPath]);

        return $pdf;
    } catch (\Exception $e) {
        \Log::error('PDF Generation Error: ' . $e->getMessage());
        throw new \Exception('Failed to generate PDF: ' . $e->getMessage());
    }
}


public function printPDF(Task $task)
{
    try {
        // Generate PDF if it doesn't exist
        if (!$task->pdf_path || !Storage::disk('public')->exists($task->pdf_path)) {
            $this->generatePDF($task);
            $task->refresh();
        }
        
        $pdfPath = storage_path('app/public/' . $task->pdf_path);
        
        // Check if file exists and is readable
        if (!file_exists($pdfPath) || !is_readable($pdfPath)) {
            // Regenerate PDF if file is missing or corrupted
            $this->generatePDF($task);
            $task->refresh();
            $pdfPath = storage_path('app/public/' . $task->pdf_path);
        }

        return response()->file($pdfPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="letter-' . $task->ref_no . '.pdf"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    } catch (\Exception $e) {
        \Log::error('PDF Print Error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to generate PDF'], 500);
    }
}




public function printDialog(Task $task)
{
    try {
        // Ensure the PDF exists
        if (!$task->pdf_path || !Storage::disk('public')->exists($task->pdf_path)) {
            $this->generatePDF($task);
            $task->refresh();
        }

        // Use asset() to get a public URL for the PDF
        $pdfUrl = asset('storage/' . $task->pdf_path);

        return view('print-pdf', compact('pdfUrl'));
    } catch (\Exception $e) {
        \Log::error('PDF Dialog Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to generate PDF for printing');
    }
}

public function downloadPDF(Task $task)
{
    try {
        // Generate PDF if it doesn't exist
        if (!$task->pdf_path || !Storage::disk('public')->exists($task->pdf_path)) {
            $this->generatePDF($task);
            $task->refresh();
        }
        
        $pdfPath = storage_path('app/public/' . $task->pdf_path);
        
        // Check if file exists
        if (!file_exists($pdfPath)) {
            throw new \Exception('PDF file not found');
        }

        return response()->download($pdfPath, 'letter-' . $task->ref_no . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    } catch (\Exception $e) {
        \Log::error('PDF Download Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to download PDF');
    }
}

    public function preview(Task $task)
    {
        // Ensure QR code exists
        if (!$task->qr_code_path) {
            $task->refresh();
        }

        // Get QR code as base64 for preview
        $qrCodeBase64 = null;
        
        // Try different QR code formats in order of preference
        $qrCodeFormats = [
            ['path' => 'qr-codes/' . $task->ref_no . '.png', 'mime' => 'image/png'],
            ['path' => 'qr-codes/' . $task->ref_no . '.svg', 'mime' => 'image/svg+xml'],
            ['path' => 'qr-codes/' . $task->ref_no . '.txt', 'mime' => 'image/svg+xml']
        ];
        
        foreach ($qrCodeFormats as $format) {
            if (Storage::disk('public')->exists($format['path'])) {
                $qrCodeContent = Storage::disk('public')->get($format['path']);
                $qrCodeBase64 = 'data:' . $format['mime'] . ';base64,' . base64_encode($qrCodeContent);
                break;
            }
        }

        return Inertia::render('tasks/show', [
            'task' => $task,
            'qrCodeBase64' => $qrCodeBase64
        ]);
    }

}