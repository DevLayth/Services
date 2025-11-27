<?php

namespace App\Http\Controllers\SeviceAppControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class InvoicesController extends Controller
{


 public function show(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'status_code' => 422
            ], 422);
        }

        $customerId = $request->customer_id;
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $invoices = DB::table('paid_invoices')
            ->join('subscriptions', 'subscriptions.id', '=', 'paid_invoices.subscription_id')
            ->join('customers', 'customers.id', '=', 'subscriptions.customer_id')
            ->join('services', 'services.id', '=', 'subscriptions.service_id')
            ->join('currencies', 'currencies.id', '=', 'paid_invoices.currency_id')
            ->where('customers.id', $customerId)
            ->select(
                'paid_invoices.id',
                'paid_invoices.amount',
                'paid_invoices.months',
                'paid_invoices.paid_at',
                'paid_invoices.created_at',
                'customers.name as customer_name',
                'customers.phone as customer_phone',
                'services.name as service_name',
                'currencies.code as currency_code'
            )
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'data' => $invoices->items(),
            'pagination' => [
                'total'        => $invoices->total(),
                'per_page'     => $invoices->perPage(),
                'current_page' => $invoices->currentPage(),
            ],
            'isEmpty' => $invoices->isEmpty(),
            'status_code' => 200,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve invoices: ' . $e->getMessage(),
        ], 500);
    }
}


}
