<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\Image\Png;
use BaconQrCode\Writer;

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
        
            $qrCodePath = 'qr-codes/' . $task->ref_no . '.png';
            $pdfUrl = asset('storage/pdfs/' . $task->ref_no . '.pdf');

            Storage::disk('public')->put($qrCodePath, QrCode::format('png')->size(200)->generate($pdfUrl));
            $task->update(['qr_code' => $qrCodePath]);

        // Generate PDF
        // $this->generateQRCode($task);
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


      public function generatePDF(Task $task)
    {
        $pdf = PDF::loadView('letters.pdf', ['letter' => $task]);
        $pdfPath = 'pdfs/' . $task->ref_no . '.pdf';

        Storage::disk('public')->put($pdfPath, $pdf->output());

        $task->update(['pdf_path' => $pdfPath]);

        return $pdf;
    }


public function printPDF(Task $task)
{
    if (!$task->pdf_path || !Storage::disk('public')->exists($task->pdf_path)) {

        $this->generatePDF($task);
        $task->refresh();
    }
    $pdfPath = storage_path('app/public/' . $task->pdf_path);

    return response()->file($pdfPath, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="letter-' . $task->ref_no . '.pdf"',
    ]);
}




public function printDialog(Task $task)
{
    // Ensure the PDF exists
    if (!$task->pdf_path || !Storage::disk('public')->exists($task->pdf_path)) {
        $task->refresh();
    }

    // Use asset() to get a public URL for the PDF
    $pdfUrl = asset('storage/' . $task->pdf_path);

    return view('print-pdf', compact('pdfUrl'));
}


// private function generateQRCode(Task $task)
// {
//     logger()->info('Generating QR code for task:', ['id' => $task->id, 'ref_no' => $task->ref_no]);

//     $qrCodeData = route('tasks.index', $task->id);
//     $qrCodePath = 'qr-codes/' . $task->ref_no . '.svg';

//     try {
//         $renderer = new ImageRenderer(
//             new RendererStyle(200),
//             new SvgImageBackEnd()
//         );

//         $writer = new Writer($renderer);
//         $qrImage = $writer->writeString($qrCodeData);

//         Storage::disk('public')->put($qrCodePath, $qrImage);

//         $task->update(['qr_code_path' => 'public/' . $qrCodePath]);

//         logger()->info('QR code generated and saved.', ['path' => $qrCodePath]);
//     } catch (\Exception $e) {
//         logger()->error('QR code generation failed.', ['message' => $e->getMessage()]);
//     }
// }

    // private function generateQRCode(Task $task)
    // {
    //     $qrCodeData = route('tasks.index', $task->id);
    //     $qrCodePath = 'qr-codes/' . $task->ref_no . '.png';
        
    //     // Ensure directory exists
    //     Storage::makeDirectory('public/qr-codes');
        
    //     QrCode::format('png')
    //         ->size(200)
    //         ->generate($qrCodeData, storage_path('app/public/' . $qrCodePath));
        
    //     $letter->update(['qr_code' => 'public/' . $qrCodePath]);
    
    // }

    // private function generateQRCode(Task $task)
    // {
    //     $qrCodeData = route('tasks.index', $task->id);
    //     $qrCodePath = 'qr-codes/' . $task->ref_no . '.png';
        
    //     QrCode::format('png')
    //         ->size(200)
    //         ->generate($qrCodeData, storage_path('app/public/' . $qrCodePath));
        
    //     $task->update(['qr_code_path' => 'public/' . $qrCodePath]);
    // }



    // public function generatePDF(Task $task)
    // {
    //     $pdf = PDF::loadView('letters.pdf', ['letter' => $task]);
    //     $pdfPath = 'pdfs/' . $task->ref_no . '.pdf';

    //     Storage::disk('public')->put($pdfPath, $pdf->output());

    //     $task->update(['pdf_path' => $pdfPath]);

    //     return $pdf;
    // }

    // public function downloadPDF(Task $task)
    // {
    //     $pdf = $this->generatePDF($task);
    //     return $pdf->download($task->ref_no . '.pdf');
    // }

    // public function printPDF(Task $task)
    // {
    //     $pdf = $this->generatePDF($task);
    //     return $pdf->stream($task->ref_no . '.pdf');
    // }
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