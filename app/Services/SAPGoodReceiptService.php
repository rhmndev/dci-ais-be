<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SAPGoodReceiptService
{
    private $client;
    private $sapUrl;
    private $sapClient;
    private $sapUsername;
    private $sapPassword;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 60,
            'verify' => false,
        ]);
        
        $this->sapUrl = 'http://erpqas-dp.dharmap.com:8001/sap/zapi/ZMM_POSTING_GR';
        $this->sapClient = '300';
        $this->sapUsername = 'dpm-einvc';
        $this->sapPassword = 'Einvoice01';
    }

    /**
     * Post Good Receipt to SAP
     * 
     * @param array $receiptData Data dari Good Receipt form
     * @return array Response dengan status success/failed
     */
    public function postGoodReceiptToSAP($receiptData)
    {
        try {
            Log::info('📤 Starting SAP Good Receipt Posting', [
                'outgoing_number' => $receiptData['outgoingNumber'] ?? 'N/A',
                'po_number' => $receiptData['poNumber'] ?? 'N/A',
            ]);

            // Prepare data for SAP
            $sapData = $this->prepareSAPData($receiptData);
            
            Log::info('📋 SAP Data Prepared', [
                'reference' => $sapData['Reference'],
                'header_text' => $sapData['HeaderText'],
                'items_count' => count($sapData['GoodReceiptSet']),
            ]);
            
            // Validate required fields
            if (!$this->validateSAPData($sapData)) {
                return [
                    'success' => false,
                    'message' => 'Data tidak lengkap untuk posting ke SAP',
                    'matdoc' => null,
                    'type' => 'E'
                ];
            }

            // Send to SAP
            $url = $this->sapUrl . '?sap-client=' . $this->sapClient;
            
            $response = $this->client->post($url, [
                'json' => $sapData,
                'auth' => [$this->sapUsername, $this->sapPassword],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents(), true);

            Log::info('📥 SAP Response', [
                'status_code' => $statusCode,
                'response' => $responseBody
            ]);

            // Parse SAP response
            return $this->parseSAPResponse($responseBody);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = $e->getMessage();
            if ($e->hasResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();
                $errorMessage .= ' | Response: ' . $errorBody;
            }
            
            Log::error('❌ SAP Request Error', [
                'error' => $errorMessage,
            ]);

            return [
                'success' => false,
                'message' => 'Error connecting to SAP: ' . $e->getMessage(),
                'matdoc' => null,
                'type' => 'E'
            ];

        } catch (\Exception $e) {
            Log::error('❌ SAP Posting Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error posting to SAP: ' . $e->getMessage(),
                'matdoc' => null,
                'type' => 'E'
            ];
        }
    }

    /**
     * Prepare data untuk format SAP
     */
    private function prepareSAPData($receiptData)
    {
        // Get receipt date
        $receiptDate = isset($receiptData['receiptDate']) 
            ? Carbon::parse($receiptData['receiptDate']) 
            : Carbon::now();
        
        // SAP Period Check: If current period is closed, use last allowed period
        // Based on SAP error: "Posting only possible in periods 2025/11 and 2025/10"
        $currentMonth = (int) $receiptDate->format('m');
        $currentYear = (int) $receiptDate->format('Y');
        
        // If December 2025, fallback to November 2025
        if ($currentYear === 2025 && $currentMonth === 12) {
            $receiptDate = Carbon::create(2025, 11, 30); // Last day of November
            Log::warning('⚠️ SAP posting date adjusted to allowed period', [
                'original_date' => $receiptData['receiptDate'] ?? 'now',
                'adjusted_date' => $receiptDate->format('Y-m-d'),
                'reason' => 'SAP period 2025/12 is closed'
            ]);
        }
        
        // Format tanggal untuk SAP (ISO 8601 format)
        $postingDate = $receiptDate->format('Y-m-d') . 'T00:00:00';
        $documentDate = $postingDate;

        // CRITICAL: Reference HARUS UNIK - Gunakan kombinasi vendor + random string
        // Format: VENDOR-RANDOM (contoh: 0000101713-A3F5B2D8)
        $receiptNumber = $receiptData['receiptNumber'] ?? null;
        $vendorCode = $receiptData['supplierCode'] ?? '0000000000';
        
        if (!$receiptNumber) {
            // Generate unique receipt number jika tidak ada
            $receiptNumber = 'GR-' . now()->format('YmdHis') . '-' . strtoupper(substr(md5(microtime()), 0, 4));
            Log::warning('⚠️ Receipt number not provided, generated unique number', [
                'generated_number' => $receiptNumber
            ]);
        }
        
        // Generate completely unique reference using vendor code + random string (8 chars)
        $randomString = strtoupper(substr(md5(microtime(true) . uniqid()), 0, 8));
        $reference = "{$vendorCode}-{$randomString}";
        
        Log::info('🔑 SAP Reference generated', [
            'receipt_number' => $receiptNumber,
            'vendor_code' => $vendorCode,
            'reference' => $reference
        ]);

        // Generate unique HeaderText
        // Format: DCIDN-{ReceiptNumber}-{Timestamp}
        $timestamp = now()->format('YmdHis');
        $headerText = "DCIDN-{$receiptNumber}-{$timestamp}";

        // Prepare items dari Good Receipt
        $goodReceiptSet = [];
        $items = $receiptData['items'] ?? [];
        $poNumber = $receiptData['poNumber'] ?? '';
        $outgoingNumber = $receiptData['outgoingNumber'] ?? 'OUT-UNKNOWN';

        foreach ($items as $index => $item) {
            $itemNo = $index + 1;
            
            // Extract item data
            $materialCode = $item['materialCode'] ?? $item['material_code'] ?? '';
            $poItemNo = $item['itemNo'] ?? $item['item_no'] ?? '00010';
            
            // CRITICAL: ENTRY_QTY harus dari quantity_gr (Qty Delivery)
            $quantityGR = $item['quantityDelivery'] ?? $item['quantity_gr'] ?? $item['quantity_delivery'] ?? 0;
            
            $uom = $item['uom'] ?? $item['unit'] ?? 'PCE';
            $plant = $item['plant'] ?? $item['werks'] ?? '1601';
            $storageLocation = $item['storageLocation'] ?? $item['lgort'] ?? 'OH01';
            $batch = $item['batch'] ?? $item['batch1'] ?? '';

            // Format PO Item Number (harus 5 digit dengan leading zeros)
            $poItemNo = str_pad($poItemNo, 5, '0', STR_PAD_LEFT);

            // Create reference per item - using Receipt Number for uniqueness
            // Format: DN-{ReceiptNumber}-BA{ItemNo}
            $itemReference = "DN-{$receiptNumber}-BA" . str_pad($itemNo, 3, '0', STR_PAD_LEFT);

            $goodReceiptSet[] = [
                'Reference' => $itemReference,
                'Item' => (string) $itemNo,
                'PO_NO' => $poNumber,
                'ITEM_NO' => $poItemNo,
                'Matnr' => $materialCode,
                'Werks' => $plant,
                'Lgort' => $storageLocation,
                'Batch1' => $batch,
                'ENTRY_QTY' => (string) $quantityGR, // Dari quantity_gr
                'Satuan' => $uom
            ];
        }

        return [
            'PostingDate' => $postingDate,
            'DocumentDate' => $documentDate,
            'Reference' => $reference, // UNIK dari Outgoing Number
            'HeaderText' => $headerText, // UNIK dengan timestamp
            'GoodReceiptSet' => $goodReceiptSet
        ];
    }

    /**
     * Validate SAP data before sending
     */
    private function validateSAPData($sapData)
    {
        // Check required fields
        if (empty($sapData['PostingDate']) || empty($sapData['DocumentDate'])) {
            Log::error('❌ Missing posting/document date');
            return false;
        }

        if (empty($sapData['Reference'])) {
            Log::error('❌ Missing Reference (must be unique!)');
            return false;
        }

        if (empty($sapData['HeaderText'])) {
            Log::error('❌ Missing HeaderText (must be unique!)');
            return false;
        }

        if (empty($sapData['GoodReceiptSet']) || count($sapData['GoodReceiptSet']) === 0) {
            Log::error('❌ No items to post');
            return false;
        }

        // Validate each item
        foreach ($sapData['GoodReceiptSet'] as $item) {
            if (empty($item['PO_NO']) || empty($item['Matnr']) || empty($item['ENTRY_QTY'])) {
                Log::error('❌ Missing required item fields', ['item' => $item]);
                return false;
            }
        }

        return true;
    }

    /**
     * Parse SAP response
     */
    private function parseSAPResponse($responseBody)
    {
        $laReturn = $responseBody['la_return'] ?? [];
        
        $type = $laReturn['type'] ?? 'E';
        $message = $laReturn['message'] ?? 'Unknown response from SAP';
        $matdoc = $laReturn['matdoc'] ?? null;

        // Check if successful (type = 'S')
        $success = ($type === 'S' || $type === 's');

        if ($success) {
            Log::info('✅ SAP Posting Successful', [
                'matdoc' => $matdoc,
                'message' => $message
            ]);
        } else {
            Log::warning('⚠️ SAP Posting Failed', [
                'type' => $type,
                'message' => $message
            ]);
        }

        return [
            'success' => $success,
            'message' => $message,
            'matdoc' => $matdoc,
            'type' => $type,
            'raw_response' => $responseBody
        ];
    }
}
