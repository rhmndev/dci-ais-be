<?php

use App\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmailTemplate::truncate();
        EmailTemplate::create([
            'template_name' => 'Purchase Order Notification',
            'template_type' => 'purchase_order_to_vendor',
            'subject' => 'Purchase Order Notification {{orderNumber}}',
            'body' => '
            <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Notification</title>
    </head>
    <body>
                 <p>Dear {{supplierName}},</p>
                <p>This email confirms your purchase order (PO {{orderNumber}}) with a total amount of {{totalAmount}}.</p>
                <p>To view the complete details of your purchase order, please click on the following link:</p>
                <a href="{{purchaseOrderLink}}">View Purchase Order Details</a>
                <p>Thank you for your business!</p>

                <p>Sincerely,<br>
                The [Your Company Name] Team</p>
            </body>
</html>
                ',
            'variables' => ['supplierName', 'orderNumber', 'totalAmount', 'purchaseOrderLink'], // List of variables used
            'is_active' => true,
        ]);
        EmailTemplate::create([
            'template_name' => 'Purchase Order Notification Internal',
            'template_type' => 'purchase_order_approved_to_internal',
            'subject' => 'Purchase Order Notification {{orderNumber}} Internal',
            'body' => '
            <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Notification</title>
    </head>
    <body>
                 <p>Dear Internal PT DCCI,</p>
                <p>This email confirms purchase order (PO {{orderNumber}}) has been sent tosupplier {{supplierName}}.</p>
                <p>Order Date: {{orderDate}}</p>
                <p>Total Amount: {{totalAmount}}</p>
                <a href="{{purchaseOrderLinkInternal}}">View Purchase Order Details</a>

                <p>Sincerely,<br>
                PT DCCI</p>
            </body>
</html>
                ',
            'variables' => ['supplierName', 'orderNumber', 'totalAmount', 'purchaseOrderLinkInternal'], // List of variables used
            'is_active' => true,
        ]);
        EmailTemplate::create([
            'template_name' => 'Purchase Order Notification Internal',
            'template_type' => 'purchase_order_approved_to_internal',
            'subject' => 'Purchase Order Notification {{orderNumber}} Internal',
            'body' => '
            <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Notification</title>
    </head>
    <body>
                 <p>Dear Internal PT DCCI,</p>
                <p>This email confirms purchase order (PO {{orderNumber}}) has been sent tosupplier {{supplierName}}.</p>
                <p>Order Date: {{orderDate}}</p>
                <p>Total Amount: {{totalAmount}}</p>
                <a href="{{purchaseOrderLinkInternal}}">View Purchase Order Details</a>

                <p>Sincerely,<br>
                PT DCCI</p>
            </body>
</html>
                ',
            'variables' => ['supplierName', 'orderNumber', 'totalAmount', 'purchaseOrderLinkInternal'], // List of variables used
            'is_active' => true,
        ]);
    }
}
