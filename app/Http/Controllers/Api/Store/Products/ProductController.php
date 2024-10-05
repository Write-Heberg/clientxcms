<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Api\Store\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreProductRequest;
use App\Http\Requests\Store\UpdateProductRequest;
use App\Http\Resources\Store\ProductCollection;
use App\Models\Store\Pricing;
use App\Models\Store\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    /**
     * @OA\Get(
     *      path="/application/products",
     *      operationId="getproductsList",
     *      tags={"Products"},
     *      summary="Get list of products",
     *      description="Returns list of products",
     *       @OA\Response(
     *          response=200,
     *          description="Show products list",
     *       ),
     *
     *     @OA\Parameter(
     *         description="Search from names products",
     *         in="query",
     *         name="q",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", summary="search from names products")
     *     ),
     *     @OA\Parameter(
     *         description="Number of products per page",
     *         in="query",
     *         name="per_page",
     *         @OA\Schema(type="number", format="int64", default=12),
     *     ),
     *
     *     @OA\Parameter(
     *         description="Sort by group_id",
     *         in="query",
     *         name="group_id",
     *         @OA\Schema(type="number", format="int64"),
     *     ),
     *
     *     @OA\Parameter(
     *         description="Add pricing details to the response",
     *         in="query",
     *         name="pricing",
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         description="Sort by type",
     *         in="query",
     *         name="type",
     *         @OA\Schema(type="string"),
     *     ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      )
     *     )
     */
    public function index(Request $request)
    {
        if ($request->has('q')) {
            if ($request->has('pricing')){
                return new ProductCollection(Product::where('name', 'like', '%' . $request->get('q') . '%')->with('pricing')->paginate($request->get('per_page', 12)));
            }
            return new ProductCollection(Product::where('name', 'like', '%' . $request->get('q') . '%')->paginate($request->get('per_page', 12)));
        }
        if ($request->has('group_id')) {
            if ($request->has('pricing')){
                return new ProductCollection(Product::where('group_id', $request->get('group_id'))->with('metadata')->with('pricing')->paginate($request->get('per_page', 12)));
            }
            return new ProductCollection(Product::where('group_id', $request->get('group_id'))->paginate($request->get('per_page', 12)));
        }
        if ($request->has('type')) {
            if ($request->has('pricing')){
                return new ProductCollection(Product::where('type', $request->get('type'))->with('metadata')->with('pricing')->paginate($request->get('per_page', 12)));
            }
            return new ProductCollection(Product::where('type', $request->get('type'))->paginate($request->get('per_page', 12)));
        }
        if ($request->has('pricing')){
            return new ProductCollection(Product::with('pricing')->paginate($request->get('per_page', 12)));
        }
        return new ProductCollection(Product::paginate($request->get('per_page', 12)));
    }

    /**
     * @OA\Post(
     *      path="/application/products",
     *      operationId="getProductStore",
     *      tags={"Products"},
     *      summary="Create new product",
     *      description="Returns new product",
     *       @OA\Response(
     *          response=200,
     *          description="Show recently created product",
     *       ),
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *
     * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="The name of the item",
     *     example="Sample Item"
     * ),
     * @OA\Property(
     *     property="status",
     *     type="string",
     *     description="The status of the item (e.g., Active, Hidden, Unreferenced)",
     *     example="active"
     * ),
     * @OA\Property(
     *     property="description",
     *     type="string",
     *     description="A description or details about the item",
     *     example="This is a sample item description."
     * ),
     * @OA\Property(
     *     property="sort_order",
     *     type="integer",
     *     description="The order in which the item should be sorted",
     *     example=1
     * ),
     * @OA\Property(
     *     property="group_id",
     *     type="integer",
     *     description="The ID of the group to which the item belongs",
     *     example=123
     * ),
     * @OA\Property(
     *     property="stock",
     *     type="integer",
     *     description="The stock quantity of the item",
     *     example=50
     * ),
     * @OA\Property(
     *     property="type",
     *     type="string",
     *     description="The type of the product",
     *     example="pterodactyl"
     * ),
     * @OA\Property(
     *     property="pinned",
     *     type="boolean",
     *     description="Whether the item is pinned or not",
     *     example=true
     * ),
     *          )
     *     ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      )
     *     )
     */
    public function store(StoreProductRequest $request)
    {
        $params = $request->validated();
        $item = Product::create($params);
        return response()->json($item, 201);
    }

    /**
     * @OA\Get(
     *      path="/application/products/{id}",
     *      operationId="getProductById",
     *      tags={"Products"},
     *      summary="Get product information",
     *      description="Returns product data",
     *      @OA\Parameter(
     *          name="id",
     *          description="product id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/ShopProduct")
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="product not found"
     *      ),
     * )
     */
    public function show(string $id)
    {
        return Product::findOrFail($id);
    }


    /**
     * @OA\Post(
     *      path="/application/products/{id}",
     *      operationId="updateProductById",
     *      tags={"Products"},
     *      summary="Update product",
     *      description="Returns updated product",
     *       @OA\Response(
     *          response=200,
     *          description="Show recently updated product",
     *       ),
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="id",
     *                  type="integer",
     *                  description="product id",
     *                  example="10"
     *              ),
     *
     *  * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="The name of the item",
     *     example="Sample Item"
     * ),
     * @OA\Property(
     *     property="status",
     *     type="string",
     *     description="The status of the item (e.g., Active, Hidden, Unreferenced)",
     *     example="active"
     * ),
     * @OA\Property(
     *     property="description",
     *     type="string",
     *     description="A description or details about the item",
     *     example="This is a sample item description."
     * ),
     * @OA\Property(
     *     property="sort_order",
     *     type="integer",
     *     description="The order in which the item should be sorted",
     *     example=1
     * ),
     * @OA\Property(
     *     property="group_id",
     *     type="integer",
     *     description="The ID of the group to which the item belongs",
     *     example=123
     * ),
     * @OA\Property(
     *     property="stock",
     *     type="integer",
     *     description="The stock quantity of the item",
     *     example=50
     * ),
     * @OA\Property(
     *     property="type",
     *     type="string",
     *     description="The type of the product",
     *     example="pterodactyl"
     * ),
     * @OA\Property(
     *     property="pinned",
     *     type="boolean",
     *     description="Whether the item is pinned or not",
     *     example=true
     * ),
     *          )
     *     ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      )
     *     )
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $item = Product::findOrFail($id);
        $params = $request->validated();
        $item->update($params);
        return response()->json($item);
    }


    /**
     * @OA\Delete(
     *      path="/application/products/{id}",
     *      operationId="deleteProductById",
     *      tags={"Products"},
     *      summary="Delete product information",
     *      description="Delete product data",
     *      @OA\Parameter(
     *          name="id",
     *          description="product id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/ShopProduct")
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="product not found"
     *      ),
     * )
     */
    public function destroy(string $id)
    {
        $item = Product::findOrFail($id);
        $item->delete();
        return response()->json($item, 200);
    }
}
