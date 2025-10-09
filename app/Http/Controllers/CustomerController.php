<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CustomerController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cust_name'       => 'required|string|max:255',
            'cust_contact'    => 'nullable|string|max:50',
            'cust_address'    => 'nullable|string|max:255',
            'notes'           => 'nullable|string|max:255',
            'cust_image_path' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $branchId = session('current_branch_id');
        if (!$branchId) {
            return redirect()->back()->with('error', 'No branch selected.');
        }

        $validated['branch_id'] = $branchId;

        // Handle image upload
        if ($request->hasFile('cust_image_path')) {
            $imagePath = $request->file('cust_image_path')->store('customers', 'public');
            $validated['cust_image_path'] = $imagePath; // ✅ Save path into DB
        }

        Customer::create($validated);

        return redirect()->back()->with('success', 'Customer added successfully.');
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'cust_name' => 'required|string|max:255',
            'cust_contact' => 'nullable|string',
            'cust_address' => 'nullable|string',
            'cust_image_path' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('cust_image_path')) {
            $imagePath = $request->file('cust_image_path')->store('customers', 'public');
            $validated['cust_image_path'] = $imagePath;
        }

        $customer->update($validated);

        return redirect()->back()->with('success', 'Customer updated successfully!');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete(); // cascade handled by DB or events
        return redirect()->back()->with('success', 'Customer deleted successfully with all related records.');
    }

    public function credits(Customer $customer)
    {
        $creditSales = $customer->sales()->where('payment_type', 'Credit')->get();

        $credits = $creditSales->map(function($sale){
            return [
                'id' => $sale->sale_id,
                'due_date' => $sale->due_date->format('Y-m-d'),
                'sale_date' => $sale->sale_date->format('Y-m-d'),
                'amount' => '₱' . number_format($sale->total_amount, 2),
            ];
        });

        return response()->json([
            'customer_name' => $customer->cust_name,
            'credits' => $credits
        ]);
    }

    public function exportCustomers(Request $request)
    {
        $branchId = session('current_branch_id');
        if (!$branchId) {
            return redirect()->back()->with('error', 'No branch selected.');
        }

        // Fetch customers
        $customers = Customer::where('branch_id', $branchId)->get();

        // Fetch credits from sales
        $credits = $customers->map(function ($customer) {
            $creditSales = $customer->sales()->where('payment_type', 'Credit')->get();

            $totalCredit = $creditSales->sum('total_amount'); // total unpaid amount
            $nextDue = $creditSales->sortBy('due_date')->first()?->due_date;

            return (object)[
                'customer_id' => $customer->customer_id,
                'cust_name'   => $customer->cust_name,
                'total_credit'=> $totalCredit,
                'next_due_date'=> $nextDue,
            ];
        });

        // Create spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // === Sheet 1: Customer Credits ===
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Customer Credits');

        $headers = ['#', 'ID', 'Customer Name', 'Total Credit', 'Next Due Date'];
        $sheet1->fromArray([$headers], null, 'A1');

        $row = 2;
        foreach ($credits as $index => $credit) {
            $sheet1->fromArray([
                $index + 1,
                $credit->customer_id,
                $credit->cust_name,
                number_format($credit->total_credit, 2),
                $credit->next_due_date ? $credit->next_due_date->format('Y-m-d') : '-',
            ], null, "A{$row}");
            $row++;
        }

        // Style headers
        $headerStyle = $sheet1->getStyle('A1:E1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE2EFDA');
        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        foreach (range('A', 'E') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // === Sheet 2: Customer Details ===
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Customer Details');

        $headers = ['#', 'ID', 'Customer Name', 'Contact Number', 'Address'];
        $sheet2->fromArray([$headers], null, 'A1');

        $row = 2;
        foreach ($customers as $index => $customer) {
            $sheet2->fromArray([
                $index + 1,
                $customer->customer_id,
                $customer->cust_name,
                $customer->cust_contact ?? '-',
                $customer->cust_address ?? '-',
            ], null, "A{$row}");
            $row++;
        }

        // Style headers
        $headerStyle2 = $sheet2->getStyle('A1:E1');
        $headerStyle2->getFont()->setBold(true);
        $headerStyle2->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $headerStyle2->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE2EFDA');
        $headerStyle2->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        foreach (range('A', 'E') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // === Save to temp file ===
        $filename = 'customers_' . date('Ymd_His') . '.xlsx';
        $tempPath = storage_path('app/public/exports/' . $filename);
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        // === Optional: Sync to Cloud ===
        if ($request->has('cloud_sync')) {
            $user = auth()->user();

            try {
                $uploadResult = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::uploadApi()->upload($tempPath, [
                    'folder' => 'user_files/' . $user->user_id,
                    'resource_type' => 'raw',
                ]);

                $user->files()->create([
                    'filename' => $filename,
                    'file_url' => $uploadResult['secure_url'] ?? null,
                    'file_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'file_size' => filesize($tempPath),
                ]);
            } catch (\Exception $e) {
                \Log::error('Cloud upload failed: ' . $e->getMessage());
            }
        }

        // === Download response ===
        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    public function exportCredits(Request $request)
    {
        $user = auth()->user();
        $search = $request->query('search');
        $sortBy = $request->query('sort_by', 'sale_date'); // default sort
        $direction = $request->query('direction', 'desc');
        
        $userBranches = $user->branches ?? collect();
        $currentBranch = $userBranches->where('branch_id', session('current_branch_id'))->first()
                        ?? $userBranches->sortBy('branch_id')->first();
        $currentBranchId = $currentBranch?->branch_id;

        // Fetch all customers with **PAID credits** (i.e., payment_type = 'Cash')
        $customers = Customer::whereHas('sales', function ($q) use ($currentBranchId, $user, $userBranches) {
            // Role-based visibility
            switch (strtolower($user->role)) {
                case 'owner':
                    $q->whereIn('branch_id', $userBranches->pluck('branch_id'));
                    break;
                case 'admin':
                    $q->where('branch_id', $currentBranchId);
                    break;
                case 'cashier':
                    $q->where('created_by', $user->user_id)
                    ->where('branch_id', $currentBranchId);
                    break;
                default:
                    $q->where('branch_id', $currentBranchId);
                    break;
            }
            // Only include paid credits
            $q->where('payment_type', 'Cash');
        });

        // Apply search
        if ($search) {
            $customers->where('cust_name', 'like', "%{$search}%");
        }

        $customers = $customers->get();

        // Create Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Paid Credits');

        // Headers
        $headers = ['Customer ID', 'Customer Name', 'Total Paid Credits', 'Last Paid Date'];
        $sheet->fromArray([$headers], null, 'A1');

        $row = 2;
        foreach ($customers as $customer) {
            $paidSales = $customer->sales()
                ->where('payment_type', 'Cash')
                ->orderBy($sortBy, $direction)
                ->get();

            $totalPaid = $paidSales->sum('total_amount');
            $lastPaidDate = $paidSales->sortByDesc('sale_date')->first()?->sale_date?->format('Y-m-d');

            $sheet->fromArray([
                $customer->customer_id,
                $customer->cust_name,
                number_format($totalPaid, 2),
                $lastPaidDate ?? '-'
            ], null, "A{$row}");
            $row++;
        }

        // Style headers
        $headerStyle = $sheet->getStyle('A1:D1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2EFDA');
        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'paid_credits_' . now()->format('Ymd_His') . '.xlsx';

        // ================= Save locally =================
        $tempPath = storage_path('app/public/exports/' . $filename);

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer->save($tempPath);

        // ================= Optional Cloud Sync =================
        if ($request->has('cloud_sync')) {
            $uploadResult = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::uploadApi()->upload($tempPath, [
                'folder' => 'user_files/' . $user->user_id,
                'resource_type' => 'raw',
            ]);

            $user->files()->create([
                'filename' => $filename,
                'file_url' => $uploadResult['secure_url'] ?? null,
                'file_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size' => filesize($tempPath),
            ]);
        }

        // ================= Download Response =================
        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

}
