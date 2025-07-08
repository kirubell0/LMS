// resources/js/Pages/Letters/Preview.jsx

import React, { useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Printer, Download, Edit } from 'lucide-react';

type PreviewProps = {
  task: any; // Replace 'any' with the actual type if available
  qrCodeBase64?: string;
};

export default function show({ task, qrCodeBase64 }: PreviewProps) {
  const printRef = useRef(null);

  const handlePrint = () => {
    // Open print dialog for the current page
    window.print();
  };

  const handlePrintPDF = () => {
    // Open the PDF in a new window for printing
    window.open(route('letters.print', task.id), '_blank');
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Head title={`Preview - ${task.ref_no}`} />
      
      {/* Print Styles */}
      <style>{`
        @media print {
          .no-print {
            display: none !important;
          }
          
          .print-container {
            margin: 0 !important;
            padding: 20px !important;
            background: white !important;
            box-shadow: none !important;
            border: none !important;
          }
          
          body {
            background: white !important;
          }
          
          .letter-content {
            font-size: 12pt !important;
            line-height: 1.6 !important;
            color: black !important;
          }
          
          .qr-footer {
            position: fixed;
            bottom: 20px;
            right: 20px;
          }
        }
      `}</style>
      
      <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header - Hidden when printing */}
        <div className="mb-8 no-print">
          <div className="flex items-center justify-between">
            <div>
              <Link
                href={route('letters.index')}
                className="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4"
              >
                <ArrowLeft className="h-4 w-4 mr-2" />
                Back to Letters
              </Link>
              <h1 className="text-3xl font-bold text-gray-900">Letter Preview</h1>
              <p className="mt-2 text-gray-600">Preview and print your letter</p>
            </div>
            
            {/* Action Buttons */}
            <div className="flex space-x-3">
              <Link
                href={route('letters.edit', task.id)}
                className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-white hover:bg-indigo-700"
              >
                <Edit className="h-4 w-4 mr-2" />
                Edit
              </Link>
              
              <button
                onClick={handlePrint}
                className="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700"
              >
                <Printer className="h-4 w-4 mr-2" />
                Print Page
              </button>
              
              <button
                onClick={handlePrintPDF}
                className="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg font-medium text-white hover:bg-purple-700"
              >
                <Printer className="h-4 w-4 mr-2" />
                Print PDF
              </button>
              
              <a
                href={route('letters.pdf', task.id)}
                className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700"
                target="_blank"
              >
                <Download className="h-4 w-4 mr-2" />
                Download PDF
              </a>
            </div>
          </div>
        </div>


        {/* Letter Preview */}
        <div className="print-container bg-white shadow-lg rounded-lg" ref={printRef}>
          <div className="letter-content p-12">
            {/* Letter Header */}
            <div className="text-center mb-8 pb-6 border-b-2 border-gray-800">
              <h1 className="text-2xl font-bold text-gray-900 mb-2">Official Letter</h1>
              <p className="text-lg font-semibold text-gray-600">
                Reference: {task.reference_number}
              </p>
            </div>

            {/* Letter Info */}
            <div className="mb-8 space-y-4">
              <div>
                <p className="text-sm font-semibold text-gray-700">Date:</p>
                <p className="text-gray-900">
                  {new Date(task.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                  })}
                </p>
              </div>
              
              <div>
                <p className="text-sm font-semibold text-gray-700">To:</p>
                <div className="text-gray-900">
                  <p className="font-medium">{task.recipient_name}</p>
                  {task.recipient_email && (
                    <p className="text-sm text-gray-600">{task.recipient_email}</p>
                  )}
                  <div className="mt-1 whitespace-pre-line">{task.recipient_address}</div>
                </div>
              </div>
              
              <div>
                <p className="text-sm font-semibold text-gray-700">Subject:</p>
                <p className="font-medium text-gray-900">{task.subject}</p>
              </div>
            </div>

            {/* Letter Content */}
            <div className="mb-12">
              <div className="text-gray-900 leading-relaxed whitespace-pre-line text-justify">
                {task.content}
              </div>
            </div>

            {/* Status Badge - Only show in preview, not print */}
            <div className="no-print mb-6">
              <span className={`inline-flex px-3 py-1 text-sm font-semibold rounded-full ${
                task.status === 'draft' ? 'bg-gray-100 text-gray-800' :
                task.status === 'sent' ? 'bg-blue-100 text-blue-800' :
                'bg-green-100 text-green-800'
              }`}>
                Status: {task.status.charAt(0).toUpperCase() + task.status.slice(1)}
              </span>
            </div>

            {/* Signature Area */}
            <div className="mt-16 mb-8">
              <div className="text-right">
                <div className="inline-block">
                  <div className="h-16 w-48 border-b border-gray-400 mb-2"></div>
                  <p className="text-sm text-gray-600">Authorized Signature</p>
                </div>
              </div>
            </div>
          </div>

          {/* QR Code Footer */}
          {qrCodeBase64 && (
            <div className="qr-footer absolute bottom-6 right-6 text-center">
              <img 
                src={qrCodeBase64} 
                alt="QR Code" 
                className="w-24 h-24 mx-auto mb-1"
              />
              <p className="text-xs text-gray-500">Scan for verification</p>
            </div>
          )}
        </div>


        {/* Preview Info - Hidden when printing */}
        <div className="mt-6 no-print">
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 className="text-sm font-medium text-blue-800 mb-2">Preview Information</h3>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
              <div>
                <span className="font-medium text-blue-700">Reference:</span>
                <span className="ml-2 text-blue-900">{task.reference_number}</span>
              </div>
              <div>
                <span className="font-medium text-blue-700">Status:</span>
                <span className="ml-2 text-blue-900">{task.status}</span>
              </div>
              <div>
                <span className="font-medium text-blue-700">Created:</span>
                <span className="ml-2 text-blue-900">
                  {new Date(task.created_at).toLocaleDateString()}
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Print Instructions - Hidden when printing */}
        <div className="mt-4 no-print">
          <div className="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 className="text-sm font-medium text-gray-800 mb-2">Print Options</h3>
            <ul className="text-sm text-gray-600 space-y-1">
              <li>• <strong>Print Page:</strong> Print this preview page directly from your browser</li>
              <li>• <strong>Print PDF:</strong> Open a formatted PDF version in a new window for printing</li>
              <li>• <strong>Download PDF:</strong> Download the PDF file to your computer</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  );
}
