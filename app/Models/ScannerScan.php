<?php
 
 namespace App\Models;
 
 use Illuminate\Database\Eloquent\Factories\HasFactory;
 use Illuminate\Database\Eloquent\Model;
 
 class ScannerScan extends Model
 {
     use HasFactory;
 
     protected $fillable = [
         'pairing_token', 'barcode', 'is_processed'
     ];
 
     protected $casts = [
         'is_processed' => 'boolean',
     ];
 }
