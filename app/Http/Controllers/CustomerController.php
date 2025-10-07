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

    public function exportCustomers()
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

        // Export
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'customers.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }

}
