<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Payment;
use App\Models\PaymentSale;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\BranchProduct;
use App\Jobs\GenerateForecast;

class SalesController extends Controller
{
    public function store(Request $request)
    {
        // ✅ Validate input
        $validated = $request->validate([
            'customer_id'     => 'required|exists:customer,customer_id',
            'cart'            => 'required|json', // cart will be JSON from your hidden input
            'payment_method'  => 'required|in:Cash,Credit,Other',
            'branch_id'       => 'required|exists:branch,branch_id',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // ✅ Fallback branch_id
            $branchId = $validated['branch_id']
                ?? session('current_branch_id')
                ?? (auth()->user()->branches->first()->branch_id ?? null);

            if (!$branchId) {
                return back()->withErrors(['branch_id' => 'No branch selected.']);
            }

            // ✅ Decode cart JSON into array
            $items = json_decode($request->cart, true) ?? [];

            if (empty($items)) {
                return back()->withErrors(['cart' => 'No items in cart.']);
            }
            
            // 1. Create Sale
            $totalItems = collect($items)->sum(fn($i) => $i['qty'] * $i['price']);
            $shippingFee = $request->input('shipping_fee', 0);
            $totalAmount = $totalItems + $shippingFee;

            // Calculate due date only if payment type is Credit
            $dueDate = $validated['payment_method'] === 'Credit' 
                ? now()->addWeek() 
                : null;

            $sale = Sale::create([
                'branch_id'    => $branchId,
                'customer_id'  => $validated['customer_id'],
                'total_amount' => $totalAmount,
                'payment_type' => $validated['payment_method'],
                'sale_date'    => now(),
                'due_date'     => $dueDate,
                'created_by'   => auth()->id(),
            ]);

            // 2. Sale Items + Stock Movements
            foreach ($items as $item) {
                $sale->sale_items()->create([
                    'branch_product_id' => $item['branch_product_id'],
                    'quantity'          => $item['qty'],
                    'unit_price'        => $item['price'],
                    'subtotal'          => $item['qty'] * $item['price'],
                ]);

                StockMovement::create([
                    'branch_product_id' => $item['branch_product_id'],
                    'change_qty'        => -$item['qty'],
                    'movement_type'     => 'Sale',
                    'reference_id'      => $sale->sale_id,
                    'movement_date'     => now(),
                    'created_by'        => auth()->id(),
                ]);

                $branchProduct = BranchProduct::find($item['branch_product_id']);
                if ($branchProduct) {
                    $branchProduct->stock_qty -= $item['qty'];
                    if ($branchProduct->stock_qty < 0) {
                        $branchProduct->stock_qty = 0; // safety check
                    }
                    $branchProduct->save();
                }

                // 2.5. Update or create Forecast record for this product
                \App\Models\Forecast::updateOrCreate(
                    [
                        'branch_product_id' => $item['branch_product_id'],
                        // Define current forecast period (e.g., this week)
                        'period_start' => now()->startOfWeek(),
                        'period_end' => now()->endOfWeek(),
                    ],
                    [
                        // Adjust forecast quantity by sales made
                        'forecast_qty' => $item['qty'], // This is a placeholder. You might want to calculate this based on historical data.
                        'method' => 'auto',
                        'notes' => 'Updated by SalesController after sale #' . $sale->sale_id,
                    ]
                );

            }

            // \App\Jobs\GenerateForecast::dispatch($branchId);

            // 3. Handle Payment if Cash
            if ($validated['payment_method'] === 'Cash') {
                $payment = Payment::create([
                    'payment_date'   => now(),
                    'payment_method' => 'Cash',
                    'payment_status' => 'Completed',
                    'created_by'     => auth()->id(),
                ]);

                PaymentSale::create([
                    'payment_id' => $payment->payment_id,
                    'sale_id'    => $sale->sale_id,
                    'amount'     => $sale->total_amount,
                ]);
            }

            // 4. Audit Log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => 'Created Sale',
                'details' => 'Sale ID: ' . $sale->sale_id . ' for Customer ' . $sale->customer_id,
            ]);

            return redirect()->back()->with('success', 'Sale recorded successfully!');
        });
    }

    public function pay(Sale $sale)
    {
        // 1. Check if the sale is still a credit
        if ($sale->payment_type !== 'Credit') {
            return back()->with('error', 'This sale is already paid.');
        }

        // 2. Create a Payment record
        $payment = \App\Models\Payment::create([
            'payment_date'   => now(),
            'payment_method' => 'Cash', // or whatever method
            'payment_status' => 'Completed',
            'created_by'     => auth()->id(),
            'notes'          => 'Payment of credit sale #' . $sale->sale_id,
        ]);

        // 3. Link PaymentSale
        \App\Models\PaymentSale::create([
            'payment_id' => $payment->payment_id,
            'sale_id'    => $sale->sale_id,
            'amount'     => $sale->total_amount,
        ]);

        // 4. Update sale as paid
        $sale->update(['payment_type' => 'Cash']);

        // 5. Add an AuditLog
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action'  => 'Pay Credit',
            'details' => 'Paid credit sale #' . $sale->sale_id . ' for customer #' . $sale->customer_id,
        ]);

        return back()->with('success', 'Credit paid successfully.');
    }

    public function destroy(Sale $sale)
    {
        // Prevent deletion if already paid (optional)
        if ($sale->payment_type === 'Cash') {
            return back()->with('error', 'Cannot delete a sale that has already been paid.');
        }

        $customerId = $sale->customer_id;
        $saleId = $sale->sale_id;

        // 1. Delete related sale_items
        $sale->sale_items()->delete();

        // 2. Delete related PaymentSale (if any)
        $sale->salehasManypayment_sale()->delete();

        // 3. Delete the sale itself
        $sale->delete();

        // 4. Log in AuditLog
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action'  => 'Delete Credit',
            'details' => "Deleted credit sale #{$saleId} for customer #{$customerId}",
        ]);

        return back()->with('success', 'Credit deleted successfully.');
    }

    public function payAll(\App\Models\Customer $customer)
    {
        // Get all unpaid credit sales for this customer
        $creditSales = $customer->sales()->where('payment_type', 'Credit')->get();

        if ($creditSales->isEmpty()) {
            return back()->with('error', 'No unpaid credits found for this customer.');
        }

        foreach ($creditSales as $sale) {
            // Create Payment
            $payment = \App\Models\Payment::create([
                'payment_date'   => now(),
                'payment_method' => 'Cash', // or any default method
                'payment_status' => 'Completed',
                'created_by'     => auth()->id(),
                'notes'          => 'Payment of credit sale #' . $sale->sale_id,
            ]);

            // Link PaymentSale
            \App\Models\PaymentSale::create([
                'payment_id' => $payment->payment_id,
                'sale_id'    => $sale->sale_id,
                'amount'     => $sale->total_amount,
            ]);

            // Update sale as paid
            $sale->update(['payment_type' => 'Cash']);

            // Log Audit
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => 'Pay Credit',
                'details' => "Paid credit sale #{$sale->sale_id} for customer #{$customer->customer_id}",
            ]);
        }

        return back()->with('success', 'All credits for this customer have been paid.');
    }

    public function destroyAll(\App\Models\Customer $customer)
    {
        // Get all unpaid credit sales for this customer
        $creditSales = $customer->sales()->where('payment_type', 'Credit')->get();

        if ($creditSales->isEmpty()) {
            return back()->with('error', 'No unpaid credits found for this customer.');
        }

        foreach ($creditSales as $sale) {
            $saleId = $sale->sale_id;

            // 1. Delete related sale_items
            $sale->sale_items()->delete();

            // 2. Delete associated PaymentSale if any
            $sale->salehasManypayment_sale()->delete();

            // 3. Delete the sale
            $sale->delete();

            // 4. Log in AuditLog
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => 'Delete Credit',
                'details' => "Deleted credit sale #{$saleId} for customer #{$customer->customer_id}",
            ]);
        }

        return back()->with('success', 'All unpaid credits for this customer have been deleted.');
    }

}
