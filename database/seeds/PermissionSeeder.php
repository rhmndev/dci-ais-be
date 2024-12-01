<?php

use Illuminate\Database\Seeder;
use App\Permission;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Permission::truncate();

		$permissions = [
			[
				"name" => "Dashboard",
				"description" => null,
				"url" => "dashboard",
				"icon" => "chart-line",
				"order_number" => 1,
				"created_by" => "seeder",
				"changed_by" => "seeder",
			],
			[
				"name" => "Master",
				"description" => null,
				"url" => "master",
				"icon" => "database",
				"order_number" => 2,
				"created_by" => "seeder",
				"changed_by" => "seeder",
				"children" => [
					[
						"name" => "User",
						"description" => null,
						"url" => "user",
						"icon" => null,
						"order_number" => 1,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Customer",
						"description" => null,
						"url" => "customer",
						"icon" => null,
						"order_number" => 2,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Vendor",
						"description" => null,
						"url" => "vendor",
						"icon" => null,
						"order_number" => 3,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Material",
						"description" => null,
						"url" => "material",
						"icon" => null,
						"order_number" => 4,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "News",
						"description" => null,
						"url" => "news",
						"icon" => null,
						"order_number" => 5,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Part Component",
						"description" => null,
						"url" => "part_component",
						"icon" => null,
						"order_number" => 6,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Supplier",
						"description" => null,
						"url" => "supplier",
						"icon" => null,
						"order_number" => 7,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
				]
			],
			[
				"name" => "Scan",
				"description" => null,
				"url" => "scanmaterial",
				"icon" => "search",
				"order_number" => 3,
				"created_by" => "seeder",
				"changed_by" => "seeder",
			],
			[
				"name" => "Transaction",
				"description" => null,
				"url" => "transaction",
				"icon" => "exchange-alt",
				"order_number" => 5,
				"created_by" => "seeder",
				"changed_by" => "seeder",
				"children" => [
					// [
					// 	"name" => "Prepreration Delivery Vendor",
					// 	"description" => null,
					// 	"url" => "receiving-vendor",
					// 	"icon" => null,
					// 	"order_number" => 1,
					// 	"created_by" => "seeder",
					// 	"changed_by" => "seeder",
					// ],
					// [
					// 	"name" => "Receiving Material",
					// 	"description" => null,
					// 	"url" => "receiving",
					// 	"icon" => null,
					// 	"order_number" => 2,
					// 	"created_by" => "seeder",
					// 	"changed_by" => "seeder",
					// ],
					[
						"name" => "Receiving Checkpoint",
						"description" => null,
						"url" => "receiving-checkpoint",
						"icon" => null,
						"order_number" => 3,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Reporting Inspection",
						"description" => null,
						"url" => "inspeksilaporan",
						"icon" => null,
						"order_number" => 4,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					]
				]
			],
			[
				"name" => "Purchase Order",
				"description" => null,
				"url" => "purchase-order",
				"icon" => "shopping-cart",
				"order_number" => 6,
				"created_by" => "seeder",
				"changed_by" => "seeder",
				"children" => [
					[
						"name" => "Purchase Order",
						"description" => null,
						"url" => "purchase-order",
						"icon" => null,
						"order_number" => 1,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Order Approvals",
						"description" => null,
						"url" => "OrderApproval",
						"icon" => null,
						"order_number" => 2,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Approvals",
						"description" => null,
						"url" => "Approval",
						"icon" => null,
						"order_number" => 3,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Monitoring Purchase Order",
						"description" => null,
						"url" => "MonitoringPO",
						"icon" => null,
						"order_number" => 4,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Delivery Schedule",
						"description" => null,
						"url" => "delivery-schedule",
						"icon" => null,
						"order_number" => 5,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Settings PO",
						"description" => null,
						"url" => "purchase-order-settings",
						"icon" => null,
						"order_number" => 6,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					]
				]
			],
			[
				"name" => "PO Order Delivery",
				"description" => null,
				"url" => "supplier-area",
				"icon" => "shopping-cart",
				"order_number" => 3,
				"created_by" => "seeder",
				"changed_by" => "seeder",
			],
			[
				"name" => "Warehouse Area",
				"description" => null,
				"url" => "warehouse-area",
				"icon" => "home",
				"order_number" => 3,
				"created_by" => "seeder",
				"changed_by" => "seeder",
			],
			[
				"name" => "Reminders",
				"description" => null,
				"url" => "reminder",
				"icon" => "home",
				"order_number" => 6,
				"created_by" => "seeder",
				"changed_by" => "seeder",
			],
			[
				"name" => "Settings",
				"description" => null,
				"url" => "settings",
				"icon" => "cogs",
				"order_number" => 7,
				"created_by" => "seeder",
				"changed_by" => "seeder",
				"children" => [
					[
						"name" => "Configuration",
						"description" => null,
						"url" => "setting",
						"icon" => null,
						"order_number" => 1,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Permission",
						"description" => null,
						"url" => "permission",
						"icon" => null,
						"order_number" => 2,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Role",
						"description" => null,
						"url" => "role",
						"icon" => null,
						"order_number" => 3,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					]
				]
			],
		];

		foreach (collect($permissions) as $permission) {
			$slug = Str::slug($permission['name']);
			$counter = 1;
			while (Permission::where('slug', $slug)->exists()) {
				$slug = Str::slug($permission['name']) . '-' . $counter;
				$counter++;
			}

			$data = new Permission;
			$data->name = $permission['name'];
			$data->slug = $slug;
			$data->description = $permission['description'];
			$data->url = $permission['url'];
			$data->icon = $permission['icon'];
			$data->parent_id = null;
			$data->parent_name = null;
			$data->order_number = $permission['order_number'];
			$data->created_by = $permission['created_by'];
			$data->changed_by = $permission['changed_by'];
			$data->save();

			if (!empty($permission['children'])) {
				foreach ($permission['children'] as $child) {
					$childSlug = Str::slug($child['name']);
					$childCounter = 1;

					while (Permission::where('slug', $childSlug)->exists()) {
						$childSlug = Str::slug($child['name']) . '-' . $childCounter;
						$childCounter++;
					}

					$data2 = new Permission;
					$data2->name = $child['name'];
					$data2->slug = $childSlug;
					$data2->description = $child['description'];
					$data2->url = $child['url'];
					$data2->icon = $child['icon'];
					$data2->parent_id = $data->id;
					$data2->parent_name = $data->name;
					$data2->order_number = $data->order_number;
					$data2->created_by = $data->created_by;
					$data2->changed_by = $data->changed_by;
					$data2->save();
				}
			}
		}
	}
}
