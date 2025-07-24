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
    // Generate QR code
    $qrCodePath = 'qr-codes/' . $task->ref_no . '.png';
    $pdfUrl = url('/tasks/' . $task->id . '/print');
        // Ensure directory exists
        if (!Storage::disk('public')->exists('qr-codes')) {
            Storage::disk('public')->makeDirectory('qr-codes');
        }

    // Generate QR code
    $qrCode = QrCode::format('png')
            ->size(200)
            ->generate($pdfUrl);

        // Store the QR code
    Storage::disk('public')->put($qrCodePath, $qrCode);
        
        // Update task with QR code path
        $task->update(['qr_code_path' => $qrCodePath]);

        return $qrCodePath;
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
        $qrCodePath = 'qr-codes/' . $task->ref_no . '.png';
        $qrCodeBase64 = null;
        
        if (Storage::disk('public')->exists($qrCodePath)) {
            $qrCodeContent = Storage::disk('public')->get($qrCodePath);
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrCodeContent);
        }

        $pdf = PDF::loadView('letters.pdf', [
            'letter' => $task,
            'qrCodeBase64' => $qrCodeBase64
        ]);
        
        // Set PDF options for better compatibility
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
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
        if ($task->qr_code_path && Storage::exists($task->qr_code_path)) {
            $qrCodeContent = Storage::get($task->qr_code_path);
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrCodeContent);
        }

        return Inertia::render('tasks/show', [
            'task' => $task,
            'qrCodeBase64' => $qrCodeBase64
        ]);
    }

}