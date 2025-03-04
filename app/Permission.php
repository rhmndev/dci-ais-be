<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Permission extends Model
{
    protected $dates = [
        'deleted_at'
    ];

    protected $fillable = [
        'permission_id',
        'slug',
        'allow',
        'name',

        'icon',
        'parent_id',
        'order_number',
        'subdropdownMenu'
    ];

    public function children()
    {
        return $this->hasMany('App\Permission', 'parent_id', '_id')->orderBy('order_number');
    }

    public static function buildMenu(array $menuItems, $parentId = null)
    {
        $menu = [];

        foreach ($menuItems as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = self::buildMenu($menuItems, $item['_id']);

                if ($children) {
                    $item['children'] = $children;
                }

                $menu[] = $item;
            }
        }

        return $menu;
    }
}
