<?php

namespace App\Exports;

use App\DeliveryOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DeliveryOrderExport implements FromCollection, WithHeadings
{
    /**
     * Return all delivery order data
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DeliveryOrder::select([
            'sold_to_pt',
            'name_1',
            'doc_date',
            'purchase_order_no',
            'external_delivery_id',
            'delivery',
            'customer_material_number',
            'description',
            'delivery_quantity',
            'net_price',
            'total',
            'pod_status',
            'gm',
            'bs',
            'plant',
            'currency',
            'su',
            'material',
            'shpt',
            'ac_gi_date',
            'time',
            'pod_date',
            'po_date',
            'ref_doc',
            'sorg',
            'curr',
            'dlvt',
            'ship_to',
            'name_1_ship_to',
            'dchl',
            'item',
            'sloc',
            'mat_frt_gp',
            'gi_indicator',
            'quantity_dn',
            'su_dn',
            'status_dn',
            'dn_customer',
            'zsogdo_cgrn',
            'nomor_kendaraan',
            'pod',
            'sales_text',
        ])->get();
    }

    /**
     * Optional headings row
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Sold To PT',
            'Name 1',
            'Document Date',
            'Purchase Order No',
            'External Delivery ID',
            'Delivery',
            'Customer Material Number',
            'Description',
            'Delivery Quantity',
            'Net Price',
            'Total',
            'POD Status',
            'GM',
            'BS',
            'Plant',
            'Currency',
            'SU',
            'Material',
            'SHPT',
            'AC GI Date',
            'Time',
            'POD Date',
            'PO Date',
            'Ref Doc',
            'SORG',
            'CURR',
            'DLVT',
            'Ship To',
            'Name 1 Ship To',
            'DCHL',
            'Item',
            'SLOC',
            'Mat Frt GP',
            'GI Indicator',
            'Quantity DN',
            'SU DN',
            'Status DN',
            'DN Customer',
            'Zsogdo CGRN',
            'Nomor Kendaraan',
            'POD',
            'Sales Text',
        ];
    }
}
