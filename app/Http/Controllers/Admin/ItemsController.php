<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\Info;
use App\Models\Item;
use App\Models\Weblink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ItemsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Items/Index', [
            'items' => Item::query()->oldest()->limit(10)->get(),
        ]);
    }

    // Function to allow search of name and type on the items table
    /**
     * possible request params:
     * search - free text to search against an item name
     * type - enum to search against item content_type
     */
    public function getAll() {
        // request params
        $search = request()->input('search') != '' ? request()->input('search') : null;
        $type = request()->input('type') != '' ? request()->input('type') : null;

        // new empty item model instance
        $items = new Item();
        
        // search query against name by $search param - NOTE: the like SQL operator
        if ($search != null) {
            $items = $items->where('name', 'like', '%' . $search . '%');
        }
        // search query against type by $type param
        if ($type != null) {
            $items = $items->where('content_type', $type);
        }
        // return the results - type and search queries are appended to base query
        return $items->get();
    }

    public function create(): Response
    {
        return Inertia::render('Items/Create', []);
    }

    public function store()
    {
        $type = match(request()->input('content_type')) {
            'info' => new Info([
                    'header' => request('content.header'),
                    'content' => request('content.content'),
                ]),
            'download' => new Download([
                    'url =' => request('content.url'),
                ]),
            'WEBLINK' => new Weblink([
                    'url =' => request('content.url'),
                ]),
        };
        $type->save();

        $type->item()->save(new Item(request()->only(['name', 'description'])));

        return redirect()->route('admin.items.index')->with('message', 'Successfully Created Item');
    }

    public function edit(Item $item): Response
    {
        return Inertia::render('Items/Edit', [
            'item' => $item->load('content'),
        ]);
    }

    public function update(Item $item): RedirectResponse
    {
        $item->forceFill(request()->only(['name', 'description']));

        switch($item->content_type) {
            case 'info':
                $item->content->header = request('content.header');
                $item->content->content = request('content.content');
                break;
            case 'download':
            case 'WEBLINK':
                $item->content->url = request('content.url');
                break;
        }

        $item->push();

        return redirect()->route('admin.items.index')->with('message', 'Successfully Updated Item');
    }

    // delete an item from the DB
    /**
     * Request params:
     * id - the id of the item being deleted
     */
    public function destroy() {
        Item::find(request()->input('id'))->delete();
        return redirect()->back()->with('success', 'Item deleted successfully.');
    }
}
