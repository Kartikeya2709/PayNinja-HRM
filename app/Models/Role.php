<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Slug;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'permissions', 'company_id', 'is_default', 'is_active'];

    public static function rolePermissions() {
        $logedIn_user = Auth::user();
        $role_permissions = self::find($logedIn_user->role_id)->permissions;
        if(!Session()->has('active_menu')) {
            Session()->put('active_menu', 'backend');
        }
        $slug = str_replace(url('')."/", '', url()->current());
        $getslug = Slug::where('slug', $slug)->first();
        if($getslug) {
            Session()->put('active_menu', $getslug->slug);
        }
        return array_keys(json_decode($role_permissions, true));
    }
    public static function getMenus() {
        $parent_menu = Slug::whereNull('parent_id')->where('is_visible', true)->get();
        foreach($parent_menu as $level1) {
            $menu = array();
            $menu = array_merge($menu, $level1->toArray());
            //check its child element
            $sub_menus = Slug::where('parent_id', $level1->id)->where('is_visible', true)->get();
            $sun_menu['child'] = array();
            if($sub_menus->isNotEmpty()) {
                foreach($sub_menus as $level2) {
                    $sun_menu['child'][$level2->id] = $level2->toArray();
                    //check its child element
                    $sub_menu2 = Slug::where('parent_id', $level2->id)->where('is_visible', true)->get();
                    if($sub_menu2->isNotEmpty()) {
                        foreach($sub_menu2 as $level3) {
                            $sun_menu['child'][$level2->id]['child'][$level3->id] = $level3->toArray();
                        }
                    }
                }
                $menu = array_merge($menu, $sun_menu);
            }
            $menus[$level1->id] = $menu;
        }
        return $menus;
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
