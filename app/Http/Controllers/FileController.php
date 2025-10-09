<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\File;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class FileController extends Controller
{
    /**
     * Display the file upload form and list of user files
     */
    public function index()
    {
        // Get all files for the logged-in user
        $userFiles = Auth::user()->files()->latest()->get();

        return view('files.index', compact('userFiles'));
    }

    /**
     * Handle file upload to Cloudinary and save record in DB
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10 MB max
        ]);

        $uploadedFile = $request->file('file');

        // Upload file to Cloudinary using SDK's uploadApi
        $uploadResult = Cloudinary::uploadApi()->upload($uploadedFile->getRealPath(), [
            'folder' => 'user_files/' . Auth::id(), // organize by user ID
        ]);

        // ApiResponse is array-like; secure_url holds the https URL
        $uploadedFileUrl = $uploadResult['secure_url'] ?? ($uploadResult['secure_url'] ?? null);

        // Save file info in DB
        Auth::user()->files()->create([
            'filename' => $uploadedFile->getClientOriginalName(),
            'file_url' => $uploadedFileUrl,
            'file_type' => $uploadedFile->getMimeType(),
            'file_size' => $uploadedFile->getSize(),
        ]);

        return back()->with('success', 'File uploaded successfully!');
    }

    /**
     * Optional: Delete a file
     */
    public function destroy(File $file)
    {
        // Ensure user can only delete their own files
        if ($file->user_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Delete from Cloudinary
    // Use Upload API destroy method
    Cloudinary::uploadApi()->destroy($file->getPublicIdFromUrl($file->file_url));

        // Delete from database
        $file->delete();

        return back()->with('success', 'File deleted successfully.');
    }

    public function uploadToCloud(Request $request)
    {
        $request->validate([
            'local_file_path' => 'required|string',
        ]);

        $filePath = $request->input('local_file_path');

        if (!file_exists($filePath)) {
            return back()->with('error', 'File does not exist.');
        }

        $fileName = basename($filePath);

        // Upload to Cloudinary
        $uploadResult = Cloudinary::uploadApi()->upload($filePath, [
            'folder' => 'user_files/' . Auth::id(),
        ]);

        $uploadedFileUrl = $uploadResult['secure_url'] ?? null;

        // Save record in DB
        Auth::user()->files()->create([
            'filename' => $fileName,
            'file_url' => $uploadedFileUrl,
            'file_type' => mime_content_type($filePath),
            'file_size' => filesize($filePath),
        ]);

        return back()->with('success', "File uploaded to cloud successfully!");
    }

}
