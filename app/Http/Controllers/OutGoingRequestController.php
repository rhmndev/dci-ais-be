<?php

namespace App\Http\Controllers;

use App\OutgoingGood;
use App\OutgoingRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


class OutGoingRequestController extends Controller
{
    /**
     * Sync archive request to Portal Supplier
     */
    private function syncArchiveToSupplierPortal($outgoing, $goodReceiptRef = null, $archivedBy = null, $archivedReason = null)
    {
        try {
            // Portal Supplier URL - adjust based on your environment
            $supplierPortalUrl = env('SUPPLIER_PORTAL_URL', 'http://localhost:3001');
            
            Log::info('🔄 Syncing archive to Supplier Portal', [
                'outgoing_number' => $outgoing->number,
                'good_receipt_ref' => $goodReceiptRef,
                'supplier_portal_url' => $supplierPortalUrl
            ]);

            $syncData = [
                'outgoing_no' => $outgoing->number,
                'po_number' => $outgoing->po_number ?? '',
                'supplier_code' => $outgoing->supplier_code ?? '',
                'supplier_id' => $outgoing->supplier_id ?? '',
                'good_receipt_number' => $goodReceiptRef,
                'archived_by' => $archivedBy ?? 'portal_dcci_admin',
                'archived_reason' => $archivedReason ?? 'Auto-archived after admin scan and good receipt creation',
                'archived_at' => $outgoing->archived_at->toISOString(),
                'dcci_travel_document_id' => $outgoing->travel_document_id ?? null,
                'sj_number' => $outgoing->sj_number ?? null
            ];

            $client = new Client(['timeout' => 30]);
            $response = $client->post($supplierPortalUrl . '/api/supplier/templates/archive-sync', [
                'json' => $syncData,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'DCCI-Portal-Archive-Sync/1.0'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                Log::info('✅ Archive sync to Supplier Portal successful', [
                    'outgoing_number' => $outgoing->number,
                    'response' => $response->getBody()->getContents()
                ]);
                
                // Update sync status in outgoing record
                $outgoing->supplier_portal_sync_status = 'synced';
                $outgoing->supplier_portal_sync_date = now();
                $outgoing->save();
                
                return true;
            } else {
                Log::error('❌ Archive sync to Supplier Portal failed', [
                    'outgoing_number' => $outgoing->number,
                    'status' => $response->getStatusCode(),
                    'response' => $response->getBody()->getContents()
                ]);
                
                // Update sync status as failed
                $outgoing->supplier_portal_sync_status = 'failed';
                $outgoing->supplier_portal_sync_error = $response->getBody()->getContents();
                $outgoing->supplier_portal_sync_date = now();
                $outgoing->save();
                
                return false;
            }
        } catch (RequestException $e) {
            Log::error('❌ RequestException during archive sync to Supplier Portal', [
                'outgoing_number' => $outgoing->number,
                'error' => $e->getMessage()
            ]);
            
            // Update sync status as failed
            $outgoing->supplier_portal_sync_status = 'failed';
            $outgoing->supplier_portal_sync_error = $e->getMessage();
            $outgoing->supplier_portal_sync_date = now();
            $outgoing->save();
            
            return false;
        } catch (\Exception $e) {
            Log::error('❌ Exception during archive sync to Supplier Portal', [
                'outgoing_number' => $outgoing->number,
                'error' => $e->getMessage()
            ]);
            
            // Update sync status as failed
            $outgoing->supplier_portal_sync_status = 'failed';
            $outgoing->supplier_portal_sync_error = $e->getMessage();
            $outgoing->supplier_portal_sync_date = now();
            $outgoing->save();
            
            return false;
        }
    }

    public function archive(Request $request)
    {
        $request->validate([
            'outgoing_number' => 'required|string',
            'good_receipt_ref' => 'nullable|string',
            'archived_by' => 'nullable|string',
            'archived_reason' => 'nullable|string',
        ]);

        $outgoing = OutgoingGood::where('number', $request->outgoing_number)->first();
        if (!$outgoing) {
            return response()->json(['success' => false, 'message' => 'Outgoing request not found'], 404);
        }

        $outgoing->is_archived = true;
        $outgoing->archived_at = now();
        $outgoing->archived_by = $request->archived_by;
        $outgoing->archived_reason = $request->archived_reason;
        $outgoing->good_receipt_ref = $request->good_receipt_ref;
        $outgoing->save();

        // Sync archive status to Portal Supplier
        $this->syncArchiveToSupplierPortal(
            $outgoing, 
            $request->good_receipt_ref, 
            $request->archived_by, 
            $request->archived_reason
        );

        return response()->json(['success' => true, 'message' => 'Outgoing request archived', 'data' => $outgoing]);
    }
}
