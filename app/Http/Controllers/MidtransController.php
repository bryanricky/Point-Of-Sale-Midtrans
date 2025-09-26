<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Transaction;

class MidtransController extends Controller
{
    public function __construct()
    {
        // Set your Midtrans Config
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$clientKey = env('MIDTRANS_CLIENT_KEY');
        Config::$isProduction = false;  // Set to true in production
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createSnapToken(Request $request)
    {
        // Get the transaction details from the request
        $transactionDetails = [
            'order_id' => 'PB-' . $request->id_pembelian . '-' . time(),
            'gross_amount' => (int) $request->bayar, // Ensure total is in integer format (no commas)
        ];

        // Create the transaction data array
        $itemDetails = [
            [
                'id' => 'ITEM1',
                'price' => (int) $request->bayar, // Price in IDR
                'quantity' => 1,
                'name' => 'Pembelian Barang', // Modify this as needed
            ]
        ];

        // Prepare the transaction request
        $transactionData = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => 'Customer Name', // Replace with real customer name
                'email' => 'customer@example.com', // Replace with real email
            ],
        ];

        try {
            // Request a Snap token
            $snapToken = Snap::getSnapToken($transactionData);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create snap token'], 500);
        }
    }

    public function success()
    {
        return view('pembelian_detail.success');
    }
}

