<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyDocumentController extends Controller
{
    /**
     * Display a listing of company documents.
     */
    public function index(Company $company)
    {
        $documents = $company->documents()->with('uploadedBy')->get();
        $documentTypes = CompanyDocument::getDocumentTypes();
        
        return view('superadmin.companies.documents.index', compact('company', 'documents', 'documentTypes'));
    }

    /**
     * Show the document details.
     */
    public function show(Company $company, CompanyDocument $document)
    {
        $documentTypes = CompanyDocument::getDocumentTypes();
        return view('superadmin.companies.documents.show', compact('company', 'document', 'documentTypes'));
    }

    /**
     * Upload a new document.
     */
    public function upload(Request $request, Company $company)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'document_type' => 'required|string|in:' . implode(',', array_keys(CompanyDocument::getDocumentTypes())),
            'notes' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('document');
        $originalFilename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        
        // Generate a unique filename
        $filename = Str::slug($company->name) . '-' . 
                   Str::slug(CompanyDocument::getDocumentTypes()[$request->document_type]) . '-' . 
                   time() . '.' . $extension;

        // Store the file
        $path = $file->storeAs(
            'company-documents/' . Str::slug($company->name), $filename, 'public'
        );

        // Create document record
        $document = $company->documents()->create([
            'document_type' => $request->document_type,
            'file_path' => $path,
            'original_filename' => $originalFilename,
            'uploaded_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        return redirect()
            ->route('superadmin.companies.documents.index', $company)
            ->with('success', 'Document uploaded successfully');
    }

    /**
     * Verify a document.
     */
    public function verify(Request $request, Company $company, CompanyDocument $document)
    {
        $document->update([
            'status' => CompanyDocument::STATUS_VERIFIED,
            'verified_at' => now(),
            'notes' => $request->notes ?? $document->notes,
        ]);

        return redirect()
            ->route('superadmin.companies.documents.index', $company)
            ->with('success', 'Document verified successfully');
    }

    /**
     * Reject a document.
     */
    public function reject(Request $request, Company $company, CompanyDocument $document)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $document->update([
            'status' => CompanyDocument::STATUS_REJECTED,
            'notes' => $request->notes,
        ]);

        return redirect()
            ->route('superadmin.companies.documents.index', $company)
            ->with('success', 'Document rejected successfully');
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Company $company, CompanyDocument $document)
    {
        // Delete the file from storage
        Storage::disk('public')->delete($document->file_path);
        
        // Delete the database record
        $document->delete();

        return redirect()
            ->route('superadmin.companies.documents.index', $company)
            ->with('success', 'Document deleted successfully');
    }
}
