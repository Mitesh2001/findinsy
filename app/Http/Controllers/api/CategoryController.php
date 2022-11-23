<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Category;
use DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{

            $categories = auth()->user()->categories;

            return response()->json(['categories' => $categories, 'message' => 'All Categories fetched successfully !', 'success' => false], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            $errors['success'] = false;
            $errors['message'] = "Something went wrong !";
            return response()->json($errors, 401);
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:categories',
            ]);

            if($validator->fails()){

                $errorString = implode(",", $validator->messages()->all());
                return response()->json([
                    'message' => $errorString,
                    'success' => false
                ]);

            }

            $category = auth()->user()->categories()->create([
                'name' => $request->name,
            ]);

            return response()->json(['category' => $category, 'message' => 'New category has been created successfully !', 'success' => false], 200);

        } catch (\Throwable $th) {
            $errors['success'] = false;
            $errors['message'] = "Something went wrong !";
            return response()->json($errors, 401);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            if (auth()->user()->categories->contains($id)) {
                $category = Category::find($id);
                $category->delete();
                return response()->json(['message' => 'Category has been deleted successfully !', 'success' => true], 200);
            } else {
                return response()->json(['message' => 'Category does not exists !', 'success' => false], 200);
            }

        } catch (\Throwable $th) {
            $errors['success'] = false;
            $errors['message'] = "Something went wrong !";
            return response()->json($errors, 401);
        }
    }
}
