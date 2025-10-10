<?php

namespace App\Http\Controllers;

use App\OutgoingGood;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SupplierPortalSyncController extends Controller
{
    /**
     * Manually retry sync archive to Portal Supplier for failed syncs
     */
    public function retrySyncArchive(Request $request)
    {
        $request->validate([
            'outgoing_good_ids' => 'required|array',
            'outgoing_good_ids.*' => 'exists:outgoing_goods,_id'
        ]);

        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($request->outgoing_good_ids as $id) {
            $outgoing = OutgoingGood::find($id);
            
            if (!$outgoing || !$outgoing->is_archived) {
                $results[] = [
                    'id' => $id,
                    'status' => 'skipped',
                    'message' => 'Outgoing good not found or not archived'
                ];
                continue;
            }

            $syncResult = $this->syncArchiveToSupplierPortal($outgoing);
            
            if ($syncResult) {
                $successCount++;
                $results[] = [
                    'id' => $id,
                    'outgoing_number' => $outgoing->number,
                    'status' => 'success',
                    'message' => 'Archive sync successful'
                ];
            } else {
                $failedCount++;
                $results[] = [
                    'id' => $id,
                    'outgoing_number' => $outgoing->number,
                    'status' => 'failed',
                    'message' => 'Archive sync failed'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sync completed. Success: {$successCount}, Failed: {$failedCount}",
            'summary' => [
                'total' => count($request->outgoing_good_ids),
                'success' => $successCount,
                'failed' => $failedCount
            ],
            'results' => $results
        ]);
    }

    /**
     * Get list of outgoing goods with failed sync status
     */
    public function getFailedSyncs(Request $request)
    {
        $query = OutgoingGood::where('is_archived', true)
            ->where(function($q) {
                $q->where('supplier_portal_sync_status', 'failed')
                  ->orWhereNull('supplier_portal_sync_status');
            });

        if ($request->has('limit')) {
            $query->limit($request->limit);
        }

        $failedSyncs = $query->orderBy('archived_at', 'desc')->get([
            '_id', 'number', 'archived_at', 'archived_by', 'supplier_portal_sync_status', 
            'supplier_portal_sync_date', 'supplier_portal_sync_error'
        ]);

        return response()->json([
            'success' => true,
            'data' => $failedSyncs,
            'count' => $failedSyncs->count()
        ]);
    }

    /**
     * Test Portal Supplier connectivity
     */
    public function testSupplierPortalConnection()
    {
        try {
            $supplierPortalUrl = env('SUPPLIER_PORTAL_URL', 'http://localhost:3001');
            
            $client = new Client(['timeout' => 10]);
            $response = $client->get($supplierPortalUrl . '/api/health');

            if ($response->getStatusCode() === 200) {
                return response()->json([
                    'success' => true,
                    'message' => 'Portal Supplier connection successful',
                    'url' => $supplierPortalUrl,
                    'response' => json_decode($response->getBody()->getContents(), true)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Portal Supplier connection failed',
                    'url' => $supplierPortalUrl,
                    'status' => $response->getStatusCode()
                ], 500);
            }
        } catch (RequestException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Portal Supplier connection error',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Portal Supplier connection error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync archive request to Portal Supplier
     */
    private function syncArchiveToSupplierPortal($outgoing, $goodReceiptRef = null, $archivedBy = null, $archivedReason = null)
    {
        try {
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
                'good_receipt_number' => $goodReceiptRef ?? $outgoing->good_receipt_ref,
                'archived_by' => $archivedBy ?? $outgoing->archived_by ?? 'portal_dcci_admin',
                'archived_reason' => $archivedReason ?? $outgoing->archived_reason ?? 'Auto-archived after admin scan and completion',
                'archived_at' => $outgoing->archived_at ? $outgoing->archived_at->toISOString() : now()->toISOString(),
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
                
                $outgoing->supplier_portal_sync_status = 'synced';
                $outgoing->supplier_portal_sync_date = now();
                $outgoing->supplier_portal_sync_error = null;
                $outgoing->save();
                
                return true;
            } else {
                Log::error('❌ Archive sync to Supplier Portal failed', [
                    'outgoing_number' => $outgoing->number,
                    'status' => $response->getStatusCode(),
                    'response' => $response->getBody()->getContents()
                ]);
                
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
            
            $outgoing->supplier_portal_sync_status = 'failed';
            $outgoing->supplier_portal_sync_error = $e->getMessage();
            $outgoing->supplier_portal_sync_date = now();
            $outgoing->save();
            
            return false;
        }
    }
}