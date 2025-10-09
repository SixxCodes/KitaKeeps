<?php

namespace App\Http\Controllers;

use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\UserBranch;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Storage;

class BranchController extends Controller
{
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'Owner') {
            abort(403, 'Unauthorized'); // only owners allowed
        }

        $request->validate([
            'branch_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        $branch = Branch::create([
            'branch_name' => $request->branch_name,
            'location' => $request->location,
        ]);

        UserBranch::create([
            'user_id' => auth()->user()->user_id,
            'branch_id' => $branch->branch_id,
        ]);

        return back()->with('success', 'Branch added successfully!');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Determine number of entries per page
        $perPage = $request->input('per_page', 5); // default 5

        // Search query
        $search = $request->input('search');

        // Fetch branches for this owner
        $branches = $user->branches()
            ->when($search, function($query, $search) {
                $query->where('branch_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            })
            ->orderBy('branch_id', 'asc')
            ->paginate($perPage)
            ->withQueryString(); // keep query parameters for pagination links

        return view('modules.myHardware', compact('branches', 'perPage', 'search'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'branch_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            // 'is_active' => 'required|boolean',
        ]);

        $branch->update([
            'branch_name' => $request->branch_name,
            'location' => $request->location,
            // 'is_active' => $request->is_active,
        ]);

        return back()->with('success', 'Branch updated successfully!');
    }

    public function destroy(Branch $branch)
    {
        try {
            // --- 1. Delete related BranchProducts and all their dependents ---
            foreach ($branch->branchproducts as $branchProduct) {
                // Delete forecasts
                $branchProduct->forecasts()->delete();

                // Delete purchase items
                $branchProduct->branch_producthasManypurchase_item()->delete();

                // Delete stock movements
                $branchProduct->branch_producthasManystock_movement()->delete();

                // Delete sale items
                $branchProduct->branch_producthasManysale_item()->delete();

                // Finally delete the branch product itself
                $branchProduct->delete();
            }

            // --- 2. Delete Purchases and their children ---
            foreach ($branch->purchases as $purchase) {
                // Delete purchase items
                $purchase->purchasehasManypurchase_item()->delete();

                // Delete stock movements for this purchase
                $purchase->purchasehasManystock_movement()->delete();

                $purchase->delete();
            }

            // --- 3. Delete Sales and their children ---
            foreach ($branch->sales as $sale) {
                // Delete sale items
                $sale->sale_items()->delete();

                // Delete payment_sale
                $sale->salehasManypayment_sale()->delete();

                // Delete stock movements
                $sale->salehasManystock_movement()->delete();

                $sale->delete();
            }

            // --- 4. Delete Customers ---
            foreach ($branch->customers as $customer) {
                // Sales already deleted above, so just delete customer
                $customer->delete();
            }

            // --- 5. Delete Employees and their related data ---
            foreach ($branch->employees as $employee) {
                // Delete attendance
                $employee->attendance()->delete();

                // Delete payroll
                \App\Models\Payroll::where('employee_id', $employee->employee_id)->delete();

                // Delete employee
                $employee->delete();
            }

            // --- 6. Delete Suppliers ---
            foreach (\App\Models\Supplier::where('branch_id', $branch->branch_id)->get() as $supplier) {
                // Delete product_supplier relations
                $supplier->supplierhasManyproduct_supplier()->delete();

                // Delete purchases (should already be gone, but just in case)
                $supplier->supplierhasManypurchase()->delete();

                $supplier->delete();
            }

            // --- 7. Detach Users from pivot table ---
            $branch->users()->detach();

            // --- 8. Delete the branch itself ---
            $branch->delete();

            return redirect()->back()->with('success', 'Branch and all related records deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete branch: ' . $e->getMessage());
        }
    }

    public function switch(Request $request, $branchId)
    {
        $user = Auth::user(); // now works

        if ($user->branches->contains('branch_id', $branchId)) {
            session(['current_branch_id' => $branchId]);
            return back()->with('success', 'Branch switched successfully.');
        }

        return back()->with('error', 'Unauthorized to switch to this branch.');
    }
    
    public function export(Request $request)
    {
        $user = Auth::user();
        $userId = $user->user_id;

        // Get all branch IDs for the authenticated user
        $branchIds = \App\Models\UserBranch::where('user_id', $userId)->pluck('branch_id');

        // Fetch branches
        $branches = Branch::whereIn('branch_id', $branchIds)
            ->select('branch_id', 'branch_name', 'location')
            ->get();

        // Create spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['Branch ID', 'Branch Name', 'Location'];
        $sheet->fromArray([$headers], null, 'A1');

        // Fill rows
        $row = 2;
        foreach ($branches as $branch) {
            $sheet->fromArray([
                $branch->branch_id,
                $branch->branch_name,
                $branch->location,
            ], null, "A{$row}");
            $row++;
        }

        // Style headers
        $headerStyle = $sheet->getStyle('A1:C1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2EFDA');
        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Auto widen columns
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('C')->setWidth(40);

        // Save locally to temp path
        $filename = 'branches_' . date('Ymd_His') . '.xlsx';
        $tempPath = storage_path('app/public/exports/' . $filename);

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        // Optional: upload to cloud
        if ($request->has('cloud_sync')) {
            $uploadResult = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::uploadApi()->upload($tempPath, [
                'folder' => 'user_files/' . $userId,
                'resource_type' => 'raw',
            ]);

            // Save record in DB
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
