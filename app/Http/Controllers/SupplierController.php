<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\UserBranch;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Models\File;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    // Display suppliers list (paginated, branch-aware)
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->query('per_page', 5);
        $search = $request->query('search');

        $branchIds = $user->branches->pluck('branch_id');

        $query = Supplier::whereIn('branch_id', $branchIds);

        if ($search) {
            $query->where('supp_name', 'like', "%{$search}%")
                ->orWhere('supp_contact', 'like', "%{$search}%")
                ->orWhere('supp_address', 'like', "%{$search}%");
        }

        $suppliers = $query->paginate($perPage)->withQueryString();

        return view('modules.mySuppliers', compact('suppliers'));
    }

    // Store new supplier
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'supp_name' => 'required|string|max:255',
                'supp_contact' => 'nullable|string|max:20',
                'supp_address' => 'nullable|string|max:255',
                'supp_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);
                
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('supp_image')) {
                $imagePath = $request->file('supp_image')->store('suppliers', 'public');
            }

            $user = Auth::user();

            // Determine branch assignment
            $branchId = ($user->role === 'Owner' && $request->branch_id)
                        ? $request->branch_id       // Owner can select a branch
                        : $user->branches->first()->branch_id; // Admin/Cashier auto assigned

            Supplier::create([
                'supp_name' => $validated['supp_name'],
                'supp_contact' => $validated['supp_contact'] ?? null,
                'supp_address' => $validated['supp_address'] ?? null,
                'supp_image_path' => $imagePath,
                'branch_id' => $branchId,
            ]);

            return redirect()->back()->with('success', 'Supplier added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong, please try again.');
        }
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'supp_name' => 'required|string|max:255',
            'supp_contact' => 'nullable|string|max:20',
            'supp_email' => 'nullable|email|max:255',
            'supp_address' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('supp_image')) {
            $imagePath = $request->file('supp_image')->store('suppliers', 'public');
            $validated['supp_image_path'] = $imagePath;
        }

        $supplier->update($validated);

        return redirect()->back()->with('success', 'Supplier updated successfully!');
    }

    public function destroy(Supplier $supplier)
    {
        try {
            \DB::beginTransaction();

            // Delete related product_supplier records
            if ($supplier->supplierhasManyproduct_supplier()->exists()) {
                $supplier->supplierhasManyproduct_supplier()->delete();
            }

            // Now delete supplier
            $supplier->delete();

            \DB::commit();

            return redirect()->back()->with('success', 'Supplier deleted successfully!');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete supplier: ' . $e->getMessage());
        }
    }

    public function exportSuppliers(Request $request)
    {
        $user = Auth::user();
        $userId = $user->user_id;

        // Get all branch IDs for the authenticated user
        $branchIds = $user->branches->pluck('branch_id');

        // Get suppliers in those branches
        $suppliers = Supplier::whereIn('branch_id', $branchIds)->get();

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['ID', 'Supplier Name', 'Contact', 'Address'];
        $sheet->fromArray([$headers], null, 'A1');

        // Fill rows
        $row = 2;
        foreach ($suppliers as $supplier) {
            $sheet->fromArray([
                $supplier->supplier_id,
                $supplier->supp_name,
                $supplier->supp_contact ?? 'N/A',
                $supplier->supp_address ?? 'N/A',
            ], null, "A{$row}");
            $row++;
        }

        // Style headers
        $headerStyle = $sheet->getStyle('A1:D1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2EFDA');
        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Auto widen columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('D')->setWidth(40);

        // Save locally to temp path
        $filename = 'suppliers_' . date('Ymd_His') . '.xlsx';
        $tempPath = storage_path('app/public/exports/' . $filename);

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        // Optional: upload to cloud
        if ($request->has('cloud_sync')) {
            // The exported spreadsheet is a binary file (XLSX). Upload as a raw resource
            $uploadResult = Cloudinary::uploadApi()->upload($tempPath, [
                'folder' => 'user_files/' . $userId,
                'resource_type' => 'raw',
            ]);

            // Save record in DB — ApiResponse behaves like an array
            $user->files()->create([
                'filename' => $filename,
                'file_url' => $uploadResult['secure_url'] ?? null,
                'file_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size' => filesize($tempPath),
            ]);
        }

        // Return download response
        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

}
