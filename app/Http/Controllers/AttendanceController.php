<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use App\Models\UserBranch;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AttendanceController extends Controller
{
    public function mark(Request $request)
    {
        $today = Carbon::today();

        // === Branch ID logic ===
        $branchId = session('current_branch_id');

        if (!$branchId) {
            $branchId = auth()->user()->branches->sortBy('branch_id')->first()->branch_id ?? null;
            session(['current_branch_id' => $branchId]);
        }

        if (!$branchId) {
            return back()->with('error', 'No active branch selected.');
        }
        // ======================

        // Get all employees for this branch (so we can mark absent if not checked)
        $employees = Employee::where('branch_id', $branchId)->get();

        // IDs of employees marked present
        $presentIds = $request->input('present', []); // array of employee_ids

        foreach ($employees as $employee) {
            Attendance::updateOrCreate(
                [
                    'employee_id' => $employee->employee_id,
                    'att_date'    => $today,
                ],
                [
                    'status' => in_array($employee->employee_id, $presentIds) ? 'Present' : 'Absent',
                ]
            );
        }

        return back()->with('success', 'Attendance has been recorded!');
    }

    public function index(Request $request)
    {
        $today = Carbon::today();

        // === Branch ID logic ===
        $branchId = session('current_branch_id');

        if (!$branchId) {
            $branchId = auth()->user()->branches->sortBy('branch_id')->first()->branch_id ?? null;
            session(['current_branch_id' => $branchId]);
        }

        if (!$branchId) {
            return back()->with('error', 'No active branch selected.');
        }
        // ======================

        // Then use $branchId instead of session('current_branch_id')
        $employees = Employee::where('branch_id', $branchId)
            ->with('todayAttendance')
            ->paginate($request->per_page ?? 5);

        return view('attendance.index', compact('employees'));
    }

    public function exportAttendance()
    {   
        $user = auth()->user();
        $search = request('att_search');
        $sortBy = request('att_sort_by', 'att_date');
        $direction = request('direction', 'desc');

        $userBranches = $user->branches ?? collect();
        $currentBranch = $userBranches->where('branch_id', session('current_branch_id'))->first()
                            ?? $userBranches->sortBy('branch_id')->first();
        $currentBranchId = $currentBranch?->branch_id;

        $query = Attendance::with(['attendancebelongsToemployee.person', 'attendancebelongsToemployee.branch']);

        // Role-based visibility
        switch (strtolower($user->role)) {
            case 'owner':
                $branchIds = $userBranches->pluck('branch_id')->toArray();
                $query->whereHas('attendancebelongsToemployee', fn($q) => $q->whereIn('branch_id', $branchIds));
                break;
            case 'admin':
                $query->whereHas('attendancebelongsToemployee', fn($q) => $q->where('branch_id', $currentBranchId));
                break;
            case 'cashier':
                $personId = $user->person_id;
                $query->whereHas('attendancebelongsToemployee', fn($q) => $q->where('person_id', $personId));
                break;
            default:
                $query->whereHas('attendancebelongsToemployee', fn($q) => $q->where('branch_id', $currentBranchId));
                break;
        }

        // Search filter
        if ($search) {
            $query->whereHas('attendancebelongsToemployee.person', fn($q) =>
                $q->where('firstname', 'like', "%{$search}%")
                ->orWhere('lastname', 'like', "%{$search}%")
            );
        }

        // Sorting
        switch ($sortBy) {
            case 'employee_name':
                $query->join('employee', 'attendance.employee_id', '=', 'employee.employee_id')
                    ->join('person', 'employee.person_id', '=', 'person.person_id')
                    ->orderBy('person.firstname', $direction)
                    ->select('attendance.*');
                break;
            case 'branch_name':
                $query->join('employee', 'attendance.employee_id', '=', 'employee.employee_id')
                    ->join('branch', 'employee.branch_id', '=', 'branch.branch_id')
                    ->orderBy('branch.branch_name', $direction)
                    ->select('attendance.*');
                break;
            default:
                $query->orderBy($sortBy, $direction);
                break;
        }

        $attendances = $query->get();

        // Generate Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['Attendance ID', 'Employee Name', 'Branch', 'Date', 'Status'];
        $sheet->fromArray([$headers], NULL, 'A1');

        // Rows
        $row = 2;
        foreach ($attendances as $att) {
            $employeeName = ($att->attendancebelongsToemployee?->person?->firstname ?? '') . ' ' .
                            ($att->attendancebelongsToemployee?->person?->lastname ?? '');
            $branchName = $att->attendancebelongsToemployee?->branch?->branch_name ?? 'N/A';

            $sheet->fromArray([
                $att->attendance_id,
                $employeeName,
                $branchName,
                $att->att_date?->format('Y-m-d'),
                $att->status,
            ], NULL, "A{$row}");
            $row++;
        }

        // Style headers
        $headerStyle = $sheet->getStyle('A1:E1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE2EFDA');
        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Auto width
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'attendance.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }

}
