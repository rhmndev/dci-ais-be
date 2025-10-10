<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\OutgoingGood;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TestSupplierPortalArchiveSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supplier-portal:test-archive-sync {outgoing_number?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test archive sync to Supplier Portal for a specific outgoing number or create a test case';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $outgoingNumber = $this->argument('outgoing_number');
        
        if ($outgoingNumber) {
            // Test with specific outgoing number
            $this->testSpecificOutgoing($outgoingNumber);
        } else {
            // Show menu
            $this->showMenu();
        }

        return 0;
    }

    private function showMenu()
    {
        $this->info('=== Supplier Portal Archive Sync Test ===');
        $this->line('');

        $choice = $this->choice('What would you like to do?', [
            'Test connection to Supplier Portal',
            'Test archive sync with existing outgoing',
            'Show failed syncs',
            'Exit'
        ]);

        switch ($choice) {
            case 'Test connection to Supplier Portal':
                $this->testConnection();
                break;
            case 'Test archive sync with existing outgoing':
                $this->testExistingOutgoing();
                break;
            case 'Show failed syncs':
                $this->showFailedSyncs();
                break;
            default:
                $this->info('Goodbye!');
                break;
        }
    }

    private function testConnection()
    {
        $this->info('Testing connection to Supplier Portal...');
        
        try {
            $supplierPortalUrl = env('SUPPLIER_PORTAL_URL', 'http://localhost:3001');
            $this->line("Portal URL: {$supplierPortalUrl}");
            
            $client = new Client(['timeout' => 10]);
            $response = $client->get($supplierPortalUrl . '/api/health');

            if ($response->getStatusCode() === 200) {
                $this->info('✅ Connection successful!');
                $responseBody = $response->getBody()->getContents();
                $this->line('Response: ' . $responseBody);
            } else {
                $this->error('❌ Connection failed!');
                $this->line("Status: {$response->getStatusCode()}");
                $this->line("Response: {$response->getBody()->getContents()}");
            }
        } catch (RequestException $e) {
            $this->error('❌ Connection error: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $this->line("Status: {$e->getResponse()->getStatusCode()}");
                $this->line("Response: {$e->getResponse()->getBody()->getContents()}");
            }
        } catch (\Exception $e) {
            $this->error('❌ Connection error: ' . $e->getMessage());
        }
    }

    private function testExistingOutgoing()
    {
        // Get some archived outgoing goods
        $outgoings = OutgoingGood::where('is_archived', true)
            ->orderBy('archived_at', 'desc')
            ->limit(10)
            ->get(['_id', 'number', 'archived_at', 'supplier_portal_sync_status']);

        if ($outgoings->isEmpty()) {
            $this->warn('No archived outgoing goods found.');
            return;
        }

        $this->table(['ID', 'Number', 'Archived At', 'Sync Status'], 
            $outgoings->map(function($og) {
                return [
                    substr($og->_id, -8),
                    $og->number,
                    $og->archived_at ? $og->archived_at->format('Y-m-d H:i:s') : 'N/A',
                    $og->supplier_portal_sync_status ?? 'not_synced'
                ];
            })->toArray()
        );

        $selectedNumber = $this->ask('Enter outgoing number to test sync');
        
        if ($selectedNumber) {
            $this->testSpecificOutgoing($selectedNumber);
        }
    }

    private function testSpecificOutgoing($outgoingNumber)
    {
        $this->info("Testing archive sync for outgoing: {$outgoingNumber}");

        $outgoing = OutgoingGood::where('number', $outgoingNumber)->first();
        
        if (!$outgoing) {
            $this->error('Outgoing good not found!');
            return;
        }

        if (!$outgoing->is_archived) {
            $this->warn('Outgoing good is not archived. Archiving now...');
            $outgoing->is_archived = true;
            $outgoing->archived_at = now();
            $outgoing->archived_by = 'test_command';
            $outgoing->archived_reason = 'Test archive from command';
            $outgoing->save();
        }

        $this->info('Outgoing details:');
        $this->line("- Number: {$outgoing->number}");
        $this->line("- Archived: " . ($outgoing->is_archived ? 'Yes' : 'No'));
        $this->line("- Archived At: {$outgoing->archived_at}");
        $this->line("- Sync Status: " . ($outgoing->supplier_portal_sync_status ?? 'not_synced'));

        if ($this->confirm('Proceed with sync test?')) {
            $result = $this->syncArchiveToSupplierPortal($outgoing);
            
            if ($result) {
                $this->info('✅ Archive sync successful!');
            } else {
                $this->error('❌ Archive sync failed!');
            }
        }
    }

    private function showFailedSyncs()
    {
        $failedSyncs = OutgoingGood::where('is_archived', true)
            ->where('supplier_portal_sync_status', 'failed')
            ->orderBy('archived_at', 'desc')
            ->limit(20)
            ->get(['_id', 'number', 'archived_at', 'supplier_portal_sync_error']);

        if ($failedSyncs->isEmpty()) {
            $this->info('No failed syncs found.');
            return;
        }

        $this->table(['ID', 'Number', 'Archived At', 'Error'], 
            $failedSyncs->map(function($og) {
                return [
                    substr($og->_id, -8),
                    $og->number,
                    $og->archived_at ? $og->archived_at->format('Y-m-d H:i:s') : 'N/A',
                    substr($og->supplier_portal_sync_error ?? '', 0, 50) . '...'
                ];
            })->toArray()
        );
    }

    private function syncArchiveToSupplierPortal($outgoing)
    {
        try {
            $supplierPortalUrl = env('SUPPLIER_PORTAL_URL', 'http://localhost:3001');
            
            $this->line('🔄 Syncing to: ' . $supplierPortalUrl . '/api/supplier/templates/archive-sync');

            $syncData = [
                'outgoing_no' => $outgoing->number,
                'po_number' => $outgoing->po_number ?? '',
                'supplier_code' => $outgoing->supplier_code ?? '',
                'supplier_id' => $outgoing->supplier_id ?? '',
                'good_receipt_number' => $outgoing->good_receipt_ref,
                'archived_by' => $outgoing->archived_by ?? 'test_command',
                'archived_reason' => $outgoing->archived_reason ?? 'Test sync from command',
                'archived_at' => $outgoing->archived_at ? $outgoing->archived_at->toISOString() : now()->toISOString(),
                'dcci_travel_document_id' => $outgoing->travel_document_id ?? null,
                'sj_number' => $outgoing->sj_number ?? null
            ];

            $this->line('Sync data: ' . json_encode($syncData, JSON_PRETTY_PRINT));

            $client = new Client(['timeout' => 30]);
            $response = $client->post($supplierPortalUrl . '/api/supplier/templates/archive-sync', [
                'json' => $syncData,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'DCCI-Portal-Test-Command/1.0'
                ]
            ]);

            $this->line("Response status: {$response->getStatusCode()}");
            $responseBody = $response->getBody()->getContents();
            $this->line("Response body: {$responseBody}");

            if ($response->getStatusCode() === 200) {
                $outgoing->supplier_portal_sync_status = 'synced';
                $outgoing->supplier_portal_sync_date = now();
                $outgoing->supplier_portal_sync_error = null;
                $outgoing->save();
                
                return true;
            } else {
                $outgoing->supplier_portal_sync_status = 'failed';
                $outgoing->supplier_portal_sync_error = $responseBody;
                $outgoing->supplier_portal_sync_date = now();
                $outgoing->save();
                
                return false;
            }
        } catch (RequestException $e) {
            $this->error('RequestException: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $this->line("Status: {$e->getResponse()->getStatusCode()}");
                $this->line("Response: {$e->getResponse()->getBody()->getContents()}");
            }
            
            $outgoing->supplier_portal_sync_status = 'failed';
            $outgoing->supplier_portal_sync_error = $e->getMessage();
            $outgoing->supplier_portal_sync_date = now();
            $outgoing->save();
            
            return false;
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            
            $outgoing->supplier_portal_sync_status = 'failed';
            $outgoing->supplier_portal_sync_error = $e->getMessage();
            $outgoing->supplier_portal_sync_date = now();
            $outgoing->save();
            
            return false;
        }
    }
}