<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
//carbon for organize Dates
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return all collumn of table products
        //return Product::all();
        //Return only columns (id,title,description,image) ,e.g. created_at and updated_at you won't be able to access them.
        return Product::select('id','title','description','image')->get();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'=>'required',
            'description'=>'required',
            'image'=>'required|image'
        ]);

        $imageName=Str::random(10) . '_' . time().'.'.$request->image->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('product/image/',$request->image,$imageName);
        Product::create($request->post()+['image'=>$imageName]);
        return response()->json([
            'message'=>'Item added successfully'
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        
        return response()->json([
            'product'=>$product
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image' => 'nullable'
        ]);
    
        // Fill the product model with new data from the request
        $product->fill($request->post());
        $product->save();  // Save the changes to the database
    
        if ($request->hasFile('image')) {
            if ($product->image) {
                // Check if the image exists in the storage
                $exist = Storage::disk('public')->exists('product/image/' . $product->image);
                if ($exist) {
                    // Delete the old image if it exists
                    Storage::disk('public')->delete('product/image/' . $product->image);
                }
            }
            // Generate a new image name and store it
            $imageName = Str::random(10) . '_' . time() . '.' . $request->image->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('product/image/', $request->image, $imageName);
            
            // Update the product image name and save again
            $product->image = $imageName;
            $product->save();
        }
    
        return response()->json([
            'message' => 'Item updated successfully'
        ]);
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->image) {
            
            $exist=Storage::disk('public')->exists("product/image/{$product->image}");
            if ($exist) {
           
                Storage::disk('public')->delete("product/image/{$product->image}");
            }
        }
        $product->delete();
        return response()->json([
            'message'=>'Item deleted'
        ]);
    }
}
