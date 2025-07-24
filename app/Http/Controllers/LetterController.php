<?php

namespace App\Http\Controllers;
use App\Models\letter;
use Illuminate\Http\Request;

class LetterController extends Controller
{
    
    public function index()
    {
        // $letters = Letter::latest()->paginate(10);
        // return Inertia::render('Letters/Index', [
        //     'letters' => $letters
        // ]);
         $query = letter::with('list')
            ->whereHas('list', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->orderBy('created_at', 'desc');

        // Handle search
        if (request()->has('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Handle completion filter
        if (request()->has('filter') && request('filter') !== 'all') {
            $query->where('is_completed', request('filter') === 'completed');
        }
            $letter = $query->paginate(10);

        $letter_type = TaskList::where('user_id', auth()->id())->get();

        return Inertia::render('letters/index', [
            'letter' => $letter,
            'letter_type' => $letter_type,
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

    // public function create()
    // {
    //     return Inertia::render('Letters/Create');
    // }

    public function store(Request $request)
    {
       $validated =  $request->validate([
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

        $letter = letter::create($validated);
        // Generate QR Code

        $qrCodeContent = route('letters.show', $letter->id);
        $qrCodePath = 'qr-codes/' . Str::uuid() . '.png';
        
        Storage::disk('public')->put($qrCodePath, QrCode::format('png')->size(200)->generate($qrCodeContent));
        $letter->update(['qr_code' => $qrCodePath]);

        // Generate PDF
        $this->generatePDF($letter);
        return redirect()->route('letters.index')->with('success', 'Letter created successfully!');
    }

    public function show(letter $letter)
    {
        return Inertia::render('Letters/Show', [
            'letter' => $letter
        ]);
    }

    public function generatePDF(letter $letter)
    {
        $pdf = PDF::loadView('letters.pdf', compact('letter'));
        $pdfPath = 'pdfs/' . $letter->letter_number . '.pdf';
        
        Storage::disk('public')->put($pdfPath, $pdf->output());
        
        $letter->update(['pdf_path' => $pdfPath]);
        
        return $pdf;
    }

    public function downloadPDF(letter $letter)
    {
        $pdf = $this->generatePDF($letter);
        return $pdf->download($letter->letter_number . '.pdf');
    }

    public function printPDF(letter $letter)
    {
        $pdf = $this->generatePDF($letter);
        return $pdf->stream($letter->letter_number . '.pdf');
    }
}
