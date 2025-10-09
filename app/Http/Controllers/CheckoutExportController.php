<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;

class CheckoutExportController extends Controller
{
    public function exportCart(Request $request)
    {
        $user = Auth::user();
        $userId = $user->user_id;

        // Decode cart and get other details
        $cart = json_decode($request->cart, true) ?? [];
        $paymentMethod = $request->payment_method ?? '-';
        $shippingFee = $request->shipping_fee ?? 0;
        $totalAmount = $request->total_amount ?? 0;

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['Product', 'Qty', 'Unit Price', 'Subtotal'];
        $sheet->fromArray([$headers], null, 'A1');

        // Fill cart rows
        $row = 2;
        foreach ($cart as $item) {
            $sheet->fromArray([
                $item['name'],
                $item['qty'],
                $item['price'],
                $item['qty'] * $item['price']
            ], null, "A{$row}");
            $row++;
        }

        // Add summary rows
        $row++;
        $sheet->fromArray(['Payment Method', $paymentMethod], null, "A{$row}"); $row++;
        $sheet->fromArray(['Shipping Fee', $shippingFee], null, "A{$row}"); $row++;
        $sheet->fromArray(['Total', $totalAmount], null, "A{$row}");

        // Style headers
        $headerStyle = $sheet->getStyle('A1:D1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE2EFDA');
        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Auto widen columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Save locally to temp path
        $filename = 'cart_' . date('Ymd_His') . '.xlsx';
        $tempPath = storage_path('app/public/exports/' . $filename);

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        // Optional: cloud upload
        if ($request->has('cloud_sync')) {
            // Upload to Cloudinary
            $uploadResult = Cloudinary::uploadApi()->upload($tempPath, [
                'folder' => 'user_files/' . $userId,
                'resource_type' => 'raw',
            ]);

            $user->files()->create([
                'filename' => $filename,
                'file_url' => $uploadResult['secure_url'] ?? null,
                'file_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size' => filesize($tempPath),
            ]);
        }

        // Return download response and delete after send
        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
