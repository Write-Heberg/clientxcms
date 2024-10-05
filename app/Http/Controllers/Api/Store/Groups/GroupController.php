<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Api\Store\Groups;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreGroupRequest;
use App\Http\Requests\Store\StoreProductRequest;
use App\Http\Requests\Store\UpdateGroupRequest;
use App\Http\Requests\Store\UpdateProductRequest;
use App\Http\Resources\Store\GroupCollection;
use App\Http\Resources\Store\ProductCollection;
use App\Models\Store\Group;
use App\Models\Store\Product;
use Illuminate\Http\Request;

class GroupController extends Controller
{

    /**
     * @OA\Get(
     *      path="/application/groups",
     *      operationId="getGroupList",
     *      tags={"Groups"},
     *      summary="Get list of groups",
     *      description="Returns list of groups",
     *       @OA\Response(
     *          response=200,
     *          description="Show groups list",
     *       ),
     *
     *     @OA\Parameter(
     *         description="Search from names groups",
     *         in="query",
     *         name="q",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", summary="search from names groups")
     *     ),
     *     @OA\Parameter(
     *         description="Number of groups per page",
     *         in="query",
     *         name="per_page",
     *         @OA\Schema(type="number", format="int64", default=12),
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
            return new GroupCollection(Group::where('name', 'like', '%' . $request->get('q') . '%')->paginate($request->get('per_page', 12)));
        }
        return new GroupCollection(Group::with('metadata')->paginate($request->get('per_page', 12)));
    }

    /**
     * @OA\Post(
     *      path="/application/groups",
     *      operationId="getGroupStore",
     *      tags={"Groups"},
     *      summary="Create new group",
     *      description="Returns new group",
     *       @OA\Response(
     *          response=200,
     *          description="Show recently created group",
     *       ),
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     description="The id of the item",
     *     example="10"
     * ),
     * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="The name of the item",
     *     example="Sample Item"
     * ),
     * @OA\Property(
     *     property="slug",
     *     type="string",
     *     description="The URL-friendly slug for the item",
     *     example="sample-item"
     * ),
     * @OA\Property(
     *     property="status",
     *     type="string",
     *     description="The status of the item (e.g., active, inactive)",
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
     *     property="pinned",
     *     type="boolean",
     *     description="Whether the item is pinned or not",
     *     example=true
     * ),
     *
     * @OA\Property(
     *     property="group_id",
     *     type="integer",
     *     nullable=true,
     *     description="The id of the group to which the item belongs",
     *     example=1
     * ),
     * @OA\Property(
     *     property="image",
     *     type="string",
     *     description="The URL or path to the item's image",
     *     example="https://example.com/images/sample.jpg"
     * )
     *          )
     *     ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      )
     *     )
     */
    public function store(StoreGroupRequest $request)
    {
        $params = $request->validated();
        $item = Group::create($params);
        return response()->json($item, 201);
    }

    /**
     * @OA\Get(
     *      path="/application/groups/{id}",
     *      operationId="getGroupById",
     *      tags={"Groups"},
     *      summary="Get group information",
     *      description="Returns group data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Group id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/ShopGroup")
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="group not found"
     *      ),
     * )
     */
    public function show(string $id)
    {
        return Group::findOrFail($id);
    }


    /**
     * @OA\Post(
     *      path="/application/groups/{id}",
     *      operationId="updateGroupById",
     *      tags={"Groups"},
     *      summary="Update group information",
     *      description="Returns updated group data",
     *       @OA\Response(
     *          response=200,
     *          description="Show recently updated group",
     *       ),
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="id",
     *                  type="integer",
     *                  description="group id",
     *                  example="10"
     *              ),
     *
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     description="The id of the item",
     *     example="10"
     * ),
     * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="The name of the item",
     *     example="Sample Item"
     * ),
     *
     * @OA\Property(
     *     property="group_id",
     *     type="integer",
     *     nullable=true,
     *     description="The id of the group to which the item belongs",
     *     example=1
     * ),
     * @OA\Property(
     *     property="slug",
     *     type="string",
     *     description="The URL-friendly slug for the item",
     *     example="sample-item"
     * ),
     * @OA\Property(
     *     property="status",
     *     type="string",
     *     description="The status of the item (e.g., active, inactive)",
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
     *     property="pinned",
     *     type="boolean",
     *     description="Whether the item is pinned or not",
     *     example=true
     * ),
     * @OA\Property(
     *     property="image",
     *     type="string",
     *     description="The URL or path to the item's image",
     *     example="https://example.com/images/sample.jpg"
     * ),
     *          )
     *     ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      )
     *     )
     */
    public function update(UpdateGroupRequest $request, string $id)
    {
        $item = Group::findOrFail($id);
        $params = $request->validated();
        $item->update($params);
        return response()->json($item);
    }


    /**
     * @OA\Delete(
     *      path="/application/groups/{id}",
     *      operationId="deleteGroupById",
     *      tags={"Groups"},
     *      summary="Delete group information",
     *      description="Delete group data",
     *      @OA\Parameter(
     *          name="id",
     *          description="group id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/ShopGroup")
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="group not found"
     *      ),
     * )
     */
    public function destroy(string $id)
    {
        $item = Group::findOrFail($id);
        $item->delete();
        return response()->json($item);
    }
}
