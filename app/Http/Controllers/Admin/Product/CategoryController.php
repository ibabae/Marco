<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreCategoryRequest;
use App\Models\Category;
use App\Models\CategoryLevel;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = 'دسته بندی ها';
        $categories = Category::get();
        return view('admin.products.category.list',compact(['title','categories']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $request->validated();
        $create = Category::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);
        if($request->parentId){
            CategoryLevel::create([
                'categoryId' => $create->id,
                'parentId' => $request->parentId
            ]);
        }
        $arrayData = [];
        foreach(Category::get() as $item){
            $arrayData[] = '
                <tr>
                    <th scope="row">'.$item->id.'</th>
                    <td>'.$item->title.'</td>
                    <td><span class="badge bg-primary">'.$item->Parent.'</span></td>
                    <td><span class="badge bg-info">'.$item->countProducts.'</span></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-primary btn-floating edit" href="'.route('admin.color.edit',$item->id).'"><i class="fa fa-edit text-light"></i></a>
                        <a class="btn btn-sm btn-danger btn-floating category-delete-warning" href="'.route('admin.color.destroy',$item->id).'"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            ';
        }
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'table' => implode($arrayData)
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => Category::findOrFail($id),
            'route' => route('admin.color.update',$id)
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => Category::findOrFail($id),
            'route' => route('admin.color.update',$id)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        $arrayData = [];
        foreach(Category::get() as $item){
            $arrayData[] = '
                <tr>
                    <th scope="row">'.$item->id.'</th>
                    <td>'.$item->title.'</td>
                    <td><span class="badge bg-primary">'.$item->Parent.'</span></td>
                    <td><span class="badge bg-info">'.$item->countProducts.'</span></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-primary btn-floating edit" href="'.route('admin.color.edit',$item->id).'"><i class="fa fa-edit text-light"></i></a>
                        <a class="btn btn-sm btn-danger btn-floating category-delete-warning" href="'.route('admin.color.destroy',$item->id).'"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            ';
        }
        return redirect()->back()->with([
            'success' => true,
            'message' => 'Category successfully removed',
            'table' => implode($arrayData),
        ]);
    }
}
