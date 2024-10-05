<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Front\Store;

use App\Http\Controllers\Controller;
use App\Models\Store\Group;
use App\Models\Store\Product;

class StoreController extends Controller
{
    public function index()
    {
        $groups = Group::getAvailable()->whereNull('parent_id')->orderBy('sort_order')->orderBy('pinned')->get();
        $subtitle = trans('store.subtitle');
        $title = trans('store.title');
        $products = collect();
        return view('front.store.index', compact('products', 'groups', 'title', 'subtitle'));
    }


    public function group(Group $group)
    {
        $this->checkGroup($group);
        $groups = Group::getAvailable()->orderBy('sort_order')->orderBy('pinned')->where('parent_id', $group->id)->get();
        $subtitle = $group->description;
        $title = $group->name;
        $products = $group->products()->orderBy('sort_order')->get();
        $products = collect($products)->filter(function (Product $product) {
            return $product->isValid();
        });
        if ($group->parent_id != null) {
            return redirect($group->route());
        }
        if  ($products->count() == 0 && $groups->count() == 0) {
            \Session::flash('info', __('store.product.noproduct'));
        }
        return view('front.store.index', compact('groups', 'title', 'subtitle', 'products'));
    }

    public function subgroup(Group $group, $subgroup)
    {
        $subgroup = Group::where('slug', $subgroup)->first();
        abort_if($subgroup == null, 404);
        $subtitle = $subgroup->description;
        $title = $subgroup->name;
        $this->checkGroup($subgroup);
        $this->checkGroup($group);
        $products = Product::getAvailable()->orderBy('sort_order')->orderBy('pinned')->where('group_id', $subgroup->id)->get();
        $groups = Group::getAvailable()->orderBy('sort_order')->orderBy('pinned')->where('parent_id', $subgroup->id)->get();
        if ($products->count() == 0 && $groups->count() == 0) {
            \Session::flash('info', __('store.product.noproduct'));
        }
        return view('front.store.group', compact('group', 'title', 'subtitle', 'products', 'groups'));
    }

    private function checkGroup(Group $group)
    {
        if ($group->status == 'hidden') {
            abort(404);
        }
        if ($group->status == 'unreferenced' && !auth('admin')->check()) {
            abort(404);
        }
    }
}
