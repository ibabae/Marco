<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    public function Main(){
        return $this->hasOne(Category::class, 'id','main');
    }

    public function getParentAttribute(){
        $parent = CategoryLevel::where('categoryId',$this->id);
        if($parent->exists()){
            $theParent = $parent->first();
            $category = Category::find($theParent->parentId);
            $parentName = [
                'id' => $category->id,
                'title' => $category->title,
            ];
        } else {
            $parentName = [
                'id' => 0,
                'title' => 'بدون والد'
            ];
        }
        return $parentName;
    }

    public function Parent(){
        return $this->belongsTo(CategoryLevel::class, 'id','categoryId')->with('Parent');
    }

    public function getCountProductsAttribute(){
        // return Product::where('categoryId',$this->id)->count();
        return 0;
    }

    // protected $appends = ['countProducts'];
    protected $appends = ['Parent','countProducts'];
}
