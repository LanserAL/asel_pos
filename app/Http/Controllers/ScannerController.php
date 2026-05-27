<?php
 
 namespace App\Http\Controllers;
 
 use Illuminate\Http\Request;
 use App\Models\ScannerScan;
 use Illuminate\Support\Facades\Log;
 
 class ScannerController extends Controller
 {
     /**
      * Show the mobile camera scanner page.
      */
     public function showMobileScanner($token)
     {
         return view('scanner.mobile-scanner', [
             'token' => $token
         ]);
     }
 
     /**
      * Receive scan data from mobile phone.
      */
     public function receiveScan(Request $request)
     {
         $request->validate([
             'pairing_token' => 'required|string',
             'barcode' => 'required|string'
         ]);
 
         $scan = ScannerScan::create([
             'pairing_token' => $request->pairing_token,
             'barcode' => $request->barcode,
             'is_processed' => false
         ]);
 
         return response()->json([
             'success' => true,
             'scan_id' => $scan->id,
             'barcode' => $scan->barcode
         ]);
     }
 }
