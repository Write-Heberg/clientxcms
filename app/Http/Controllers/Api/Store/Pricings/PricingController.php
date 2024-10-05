<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Api\Store\Pricings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StorePricingRequest;
use App\Http\Requests\Store\StoreProductRequest;
use App\Http\Requests\Store\UpdateProductRequest;
use App\Http\Resources\Store\PricingCollection;
use App\Http\Resources\Store\ProductCollection;
use App\Models\Store\Pricing;
use App\Models\Store\Product;
use App\Services\Store\CurrencyService;
use Illuminate\Http\Request;

class PricingController extends Controller
{

    /**
     * @OA\Get(
     *      path="/application/pricings",
     *      operationId="getPriceList",
     *      tags={"Products"},
     *      summary="Get list of pricing",
     *      description="Returns list of pricing",
     *       @OA\Response(
     *          response=200,
     *          description="Show pricings list",
     *       ),
     *     @OA\Parameter(
     *         description="Number of pricings per page",
     *         in="query",
     *         name="per_page",
     *         @OA\Schema(type="number", format="int64", default=12),
     *     ),
     *
     *     @OA\Parameter(
     *         description="fetch only pricing for product with id",
     *         in="query",
     *         name="product",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="1", summary="fetch only pricings for product with id 1")
     *     ),
     *
     *     @OA\Parameter(
     *         description="fetch only pricing for product with currency",
     *         in="query",
     *         name="currency",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="EUR", summary="fetch only pricings for product with curency EUR")
     *     ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      )
     *     )
     */
    public function index(Request $request)
    {
        $currencies = app(CurrencyService::class)->getCurrencies()->keys()->implode(',');
        $this->validate($request, [
            'product' => 'nullable|integer|exists:products,id',
            'currency' => 'nullable|string|size:3|in:' . $currencies,
        ]);
        if ($request->has('currency') && $request->has('product')) {
            return new PricingCollection(Pricing::where('currency', $request->get('currency'))->where('related_id', $request->get('product'))->where('related_type', 'product')->paginate($request->get('per_page', 12)));
        }
        if ($request->has('currency')) {
            return new PricingCollection(Pricing::where('currency', $request->get('currency'))->paginate($request->get('per_page', 12)));
        }
        if ($request->has('product')) {
            return new PricingCollection(Pricing::where('related_id', $request->get('product'))->where('related_type', 'product')->paginate($request->get('per_page', 12)));
        }
        return new PricingCollection(Pricing::paginate($request->get('per_page', 12)));
    }

    /**
     * @OA\Post(
     *      path="/application/pricings",
     *      operationId="getPricingStore",
     *      tags={"Products"},
     *      summary="Create new pricing for product",
     *      description="Returns a new pricing",
     *       @OA\Response(
     *          response=200,
     *          description="Show recently created pricing",
     *       ),
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *
     * @OA\Property(
     *     property="product_id",
     *     type="integer",
     *     description="The ID of the associated product",
     *     example=123
     * ),
     * @OA\Property(
     *     property="currency",
     *     type="string",
     *     description="The currency for pricing",
     *     example="USD"
     * ),
     * @OA\Property(
     *     property="onetime",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="One-time payment amount",
     *     example=99.99
     * ),
     * @OA\Property(
     *     property="monthly",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Monthly payment amount",
     *     example=9.99
     * ),
     * @OA\Property(
     *     property="quarterly",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Quarterly payment amount",
     *     example=24.99
     * ),
     * @OA\Property(
     *     property="semiannually",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Semi-annual payment amount",
     *     example=49.99
     * ),
     * @OA\Property(
     *     property="annually",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Annual payment amount",
     *     example=99.99
     * ),
     * @OA\Property(
     *     property="biennially",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Biennial payment amount",
     *     example=199.99
     * ),
     * @OA\Property(
     *     property="triennially",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Triennial payment amount",
     *     example=299.99
     * ),
     * @OA\Property(
     *     property="setup_onetime",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="One-time setup fee amount",
     *     example=19.99
     * ),
     * @OA\Property(
     *     property="setup_monthly",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Monthly setup fee amount",
     *     example=4.99
     * ),
     * @OA\Property(
     *     property="setup_quarterly",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Quarterly setup fee amount",
     *     example=9.99
     * ),
     * @OA\Property(
     *     property="setup_semiannually",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Semi-annual setup fee amount",
     *     example=14.99
     * ),
     * @OA\Property(
     *     property="setup_annually",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Annual setup fee amount",
     *     example=29.99
     * ),
     * @OA\Property(
     *     property="setup_biennially",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Biennial setup fee amount",
     *     example=49.99
     * ),
     * @OA\Property(
     *     property="setup_triennially",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Triennial setup fee amount",
     *     example=69.99
     * ),
     *
     *          )
     *     ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      )
     *     )
     */
    public function store(StorePricingRequest $request)
    {
        $params = $request->validated();
        $item = Pricing::create($params);
        return response()->json($item, 201);
    }

    /**
     * @OA\Get(
     *      path="/application/pricings/{id}",
     *      operationId="getPricingById",
     *      tags={"Products"},
     *      summary="Get pricing information",
     *      description="Returns pricing data",
     *      @OA\Parameter(
     *          name="id",
     *          description="pricing id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/ShopPricing")
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="pricing not found"
     *      ),
     * )
     */
    public function show(string $id)
    {
        return Pricing::findOrFail($id);
    }


    /**
     * @OA\Post(
     *      path="/application/pricings/{id}",
     *      operationId="updatePricingById",
     *      tags={"Products"},
     *      summary="Update pricing",
     *      description="Returns updated pricing",
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
     *                  description="pricing id",
     *                  example="10"
     *              ),
     *     * @OA\Property(
     *     property="product_id",
     *     type="integer",
     *     description="The ID of the associated product",
     *     example=123
     * ),
     * @OA\Property(
     *     property="currency",
     *     type="string",
     *     description="The currency for pricing",
     *     example="USD"
     * ),
     * @OA\Property(
     *     property="onetime",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="One-time payment amount",
     *     example=99.99
     * ),
     * @OA\Property(
     *     property="monthly",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Monthly payment amount",
     *     example=9.99
     * ),
     * @OA\Property(
     *     property="quarterly",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Quarterly payment amount",
     *     example=24.99
     * ),
     * @OA\Property(
     *     property="semiannually",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Semi-annual payment amount",
     *     example=49.99
     * ),
     * @OA\Property(
     *     property="annually",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Annual payment amount",
     *     example=99.99
     * ),
     * @OA\Property(
     *     property="biennially",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Biennial payment amount",
     *     example=199.99
     * ),
     * @OA\Property(
     *     property="triennially",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Triennial payment amount",
     *     example=299.99
     * ),
     * @OA\Property(
     *     property="setup_onetime",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="One-time setup fee amount",
     *     example=19.99
     * ),
     * @OA\Property(
     *     property="setup_monthly",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Monthly setup fee amount",
     *     example=4.99
     * ),
     * @OA\Property(
     *     property="setup_quarterly",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Quarterly setup fee amount",
     *     example=9.99
     * ),
     * @OA\Property(
     *     property="setup_semiannually",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Semi-annual setup fee amount",
     *     example=14.99
     * ),
     * @OA\Property(
     *     property="setup_annually",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Annual setup fee amount",
     *     example=29.99
     * ),
     * @OA\Property(
     *     property="setup_biennially",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Biennial setup fee amount",
     *     example=49.99
     * ),
     * @OA\Property(
     *     property="setup_triennially",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Triennial setup fee amount",
     *     example=69.99
     * ),
     *          )
     *     ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      )
     *     )
     */
    public function update(StorePricingRequest $request, string $id)
    {
        $item = Pricing::findOrFail($id);
        $params = $request->validated();
        $item->update($params);
        return response()->json($item);
    }

    /**
     * @OA\Delete(
     *      path="/application/pricings/{id}",
     *      operationId="deletePricingById",
     *      tags={"Products"},
     *      summary="Delete pricing information",
     *      description="Delete pricing data",
     *      @OA\Parameter(
     *          name="id",
     *          description="pricing id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/ShopPricing")
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="pricing not found"
     *      ),
     * )
     */
    public function destroy(string $id)
    {
        $item = Pricing::findOrFail($id);
        $item->delete();
        return response()->json($item, 200);
    }

}
