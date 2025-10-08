<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    public function paySalary(Employee $employee)
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek   = now()->endOfWeek();

        // Get the total salary for this week
        $attendances = $employee->attendance()
            ->whereBetween('att_date', [$startOfWeek, $endOfWeek])
            ->where('status', 'Present')
            ->get();

        $totalSalary = $attendances->sum('daily_rate');

        // Optionally, save to payroll table
        \App\Models\Payroll::create([
            'employee_id' => $employee->employee_id,
            'period_start' => $startOfWeek,
            'period_end'   => $endOfWeek,
            'gross_pay'    => $totalSalary,
            'deductions'   => 0, // add logic if needed
            'net_pay'      => $totalSalary,
        ]);

        // Delete or reset attendance for this week
        Attendance::where('employee_id', $employee->employee_id)
            ->whereBetween('att_date', [$startOfWeek, $endOfWeek])
            ->delete();

        return back()->with('success', 'Salary has been paid!');
    }

    public function exportPayroll()
    {
        $user = auth()->user();
        $search = request('payroll_search');
        $sortBy = request('payroll_sort_by', 'period_end');
        $direction = request('payroll_direction', 'desc');

        $userBranches = $user->branches ?? collect();
        $currentBranch = $userBranches->where('branch_id', session('current_branch_id'))->first()
                            ?? $userBranches->sortBy('branch_id')->first();
        $currentBranchId = $currentBranch?->branch_id;

        $query = Payroll::with(['payrollbelongsToemployee.person', 'payrollbelongsToemployee.branch']);

        // Role-based visibility
        switch (strtolower($user->role)) {
            case 'owner':
                $branchIds = $userBranches->pluck('branch_id')->toArray();
                $query->whereHas('payrollbelongsToemployee', fn($q) => $q->whereIn('branch_id', $branchIds));
                break;
            case 'admin':
                $query->whereHas('payrollbelongsToemployee', fn($q) => $q->where('branch_id', $currentBranchId));
                break;
            case 'cashier':
                $personId = $user->person_id;
                $query->whereHas('payrollbelongsToemployee', fn($q) => $q->where('person_id', $personId));
                break;
            default:
                $query->whereHas('payrollbelongsToemployee', fn($q) => $q->where('branch_id', $currentBranchId));
                break;
        }

        // Search filter
        if ($search) {
            $query->whereHas('payrollbelongsToemployee.person', fn($q) =>
                $q->where('firstname', 'like', "%{$search}%")
                ->orWhere('lastname', 'like', "%{$search}%")
            );
        }

        // Sorting
        switch ($sortBy) {
            case 'employee_name':
                $query->join('employee', 'payroll.employee_id', '=', 'employee.employee_id')
                    ->join('person', 'employee.person_id', '=', 'person.person_id')
                    ->orderBy('person.firstname', $direction)
                    ->select('payroll.*');
                break;
            case 'branch_name':
                $query->join('employee', 'payroll.employee_id', '=', 'employee.employee_id')
                    ->join('branch', 'employee.branch_id', '=', 'branch.branch_id')
                    ->orderBy('branch.branch_name', $direction)
                    ->select('payroll.*');
                break;
            default:
                $query->orderBy($sortBy, $direction);
                break;
        }

        $payrolls = $query->get();

        // Generate Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['Employee Name', 'Branch', 'Period Start', 'Period End', 'Gross Pay', 'Deductions', 'Net Pay'];
        $sheet->fromArray([$headers], NULL, 'A1');

        // Rows
        $row = 2;
        foreach ($payrolls as $payroll) {
            $employeeName = ($payroll->payrollbelongsToemployee?->person?->firstname ?? '') . ' ' .
                            ($payroll->payrollbelongsToemployee?->person?->lastname ?? '');
            $branchName = $payroll->payrollbelongsToemployee?->branch?->branch_name ?? 'N/A';

            $sheet->fromArray([
                $employeeName,
                $branchName,
                $payroll->period_start?->format('Y-m-d'),
                $payroll->period_end?->format('Y-m-d'),
                number_format($payroll->gross_pay, 2),
                number_format($payroll->deductions, 2),
                number_format($payroll->net_pay, 2),
            ], NULL, "A{$row}");
            $row++;
        }

        // Style headers
        $headerStyle = $sheet->getStyle('A1:G1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE2EFDA');
        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Auto width
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'payroll.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }

}
