<?php

namespace App\Http\Controllers\SeviceAppControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class CustomerController extends Controller
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
            $customer = DB::table('customers')
                ->where('id', $customerId)
                ->select(
                    'customers.id as customer_id',
                    'customers.name as customer_name',
                    'customers.phone as customer_phone',
                    'customers.created_at as customer_created_at',
                    'customers.updated_at as customer_updated_at'
                )
                ->first();
            return response()->json([

                'status' => 'success',
                'data' => $customer,
                'status_code' => 200,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve customer: ' . $e->getMessage(),
            ], 500);
        }
    }


    // Show specific customer subscribed services
    public function showServices(Request $request)
    {
        try {
            $request->validate([
                'customer_id' => 'required|integer|exists:customers,id',
            ]);
            $customerId = $request->customer_id;
            $services = DB::table('subscriptions')
                ->join('services', 'services.id', '=', 'subscriptions.service_id')
                ->where('subscriptions.customer_id', $customerId)
                ->select(
                    'services.*',
                    'subscriptions.*',
                )
                ->get()
                ->toArray();

            $isEmpty = empty($services);
            return response()->json([

                'status' => 'success',
                'data' => $services,
                'isEmpty' => $isEmpty,
                'status_code' => 200,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve services: ' . $e->getMessage(),
            ], 500);
        }
    }
}
