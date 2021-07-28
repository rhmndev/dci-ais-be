<?php

use Illuminate\Database\Seeder;
use App\Permission;

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
						"name" => "Vendor",
						"description" => null,
						"url" => "master-vendor",
						"icon" => null,
						"order_number" => 2,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
					[
						"name" => "Material",
						"description" => null,
						"url" => "master-material",
						"icon" => null,
						"order_number" => 3,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					]
				]
			],
			[
				"name" => "Transaction",
				"description" => null,
				"url" => "transaction",
				"icon" => "exchange-alt",
				"order_number" => 3,
				"created_by" => "seeder",
				"changed_by" => "seeder",
				"children" => [
					[
						"name" => "Receiving Materia",
						"description" => null,
						"url" => "receiving",
						"icon" => null,
						"order_number" => 1,
						"created_by" => "seeder",
						"changed_by" => "seeder",
					],
				]
			],
			[
				"name" => "Settings",
				"description" => null,
				"url" => "settings",
				"icon" => "cogs",
				"order_number" => 5,
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
			$data = new Permission;
			$data->name = $permission['name'];
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
					$data2 = new Permission;
					$data2->name = $child['name'];
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
