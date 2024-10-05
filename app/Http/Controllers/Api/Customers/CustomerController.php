<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Api\Customers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerCollection;
use App\Models\Account\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *      path="/application/customers",
     *      operationId="getCustomersList",
     *      tags={"Customers"},
     *      summary="Get list of customers",
     *      description="Returns list of customers",
     *       @OA\Response(
     *          response=200,
     *          description="Show customers list",
     *       ),
     *
     *     @OA\Parameter(
     *         description="Search from emails customers",
     *         in="query",
     *         name="q",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="admin@clientxcms.com", summary="search from emails customers")
     *     ),
     *     @OA\Parameter(
     *         description="Number of customers per page",
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
            return new CustomerCollection(Customer::where('email', 'like', '%' . $request->get('q') . '%')->paginate($request->get('per_page', 12)));
        }
        return new CustomerCollection(Customer::paginate($request->get('per_page', 12)));
    }

    /**
     * @OA\Post(
     *      path="/application/customers",
     *      operationId="getCustomersStore",
     *      tags={"Customers"},
     *      summary="Create new customer",
     *      description="Returns new customer",
     *       @OA\Response(
     *          response=200,
     *          description="Show recently created customer",
     *       ),
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="email",
     *                  type="string",
     *                  description="Customer email",
     *                  example="john.doe@example.com"
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  type="string",
     *                  description="Customer password",
     *                  example="StrongPassword123"
     *              ),
     *              @OA\Property(
     *                  property="firstname",
     *                  type="string",
     *                  description="Customer firstname",
     *                  example="John"
     *              ),
     *              @OA\Property(
     *                  property="lastname",
     *                  type="string",
     *                  description="Customer lastname",
     *                  example="Doe"
     *              ),
     *              @OA\Property(
     *                  property="address",
     *                  type="string",
     *                  description="Customer address",
     *                  example="123 Main St"
     *              ),
     *              @OA\Property(
     *                  property="address2",
     *                  type="string",
     *                  description="Customer address line 2",
     *                  example="Apt 456"
     *              ),
     *              @OA\Property(
     *                  property="city",
     *                  type="string",
     *                  description="Customer city",
     *                  example="New York"
     *              ),
     *              @OA\Property(
     *                  property="zipcode",
     *                  type="string",
     *                  description="Customer zipcode",
     *                  example="10001"
     *              ),
     *              @OA\Property(
     *                  property="phone",
     *                  type="string",
     *                  description="Customer phone",
     *                  example="+1234567890"
     *              ),
     *              @OA\Property(
     *                  property="region",
     *                  type="string",
     *                  description="Customer region",
     *                  example="NY"
     *              ),
     *              @OA\Property(
     *                  property="verified",
     *                  type="boolean",
     *                  description="set verified customer to true",
     *                  example=true
     *              ),
     *              @OA\Property(
     *                  property="balance",
     *                  type="number",
     *                  format="float",
     *                  description="Customer balance",
     *                  example=100.50
     *              ),
     *              @OA\Property(
     *                  property="country",
     *                  type="string",
     *                  description="Customer country",
     *                  example="USA"
     *              )
     *          )
     *     ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      )
     *     )
     */
    public function store(StoreCustomerRequest $request)
    {
        $params = $request->validated();
        $verified = false;
        if (!isset($params['password'])) {
            $params['password'] = \Illuminate\Support\Str::random(8);
        }
        if (isset($params['verified']) && $params['verified']) {
            unset($params['verified']);
            $verified = true;
        }
        $item = Customer::create($params);
        if ($verified){
            $item->markEmailAsVerified();
        }
        if (!isset($params['password'])) {
            Password::broker('users')->sendResetLink($request->only('email'));
        }
        return response()->json($item, 201);
    }

    /**
     * @OA\Get(
     *      path="/application/customers/{id}",
     *      operationId="getCustomerById",
     *      tags={"Customers"},
     *      summary="Get customer information",
     *      description="Returns customer data",
     *      @OA\Parameter(
     *          name="id",
     *          description="customer id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Customer")
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="customer not found"
     *      ),
     * )
     */
    public function show(string $id)
    {
        return Customer::findOrFail($id);
    }


    /**
     * @OA\Post(
     *      path="/application/customers/{id}",
     *      operationId="updateCustomerById",
     *      tags={"Customers"},
     *      summary="Create new customer",
     *      description="Returns new customer",
     *       @OA\Response(
     *          response=200,
     *          description="Show recently updated customer",
     *       ),
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="id",
     *                  type="integer",
     *                  description="Customer id",
     *                  example="10"
     *              ),
     *              @OA\Property(
     *                  property="email",
     *                  type="string",
     *                  description="Customer email",
     *                  example="john.doe@example.com"
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  type="string",
     *                  description="Customer password",
     *                  example="StrongPassword123"
     *              ),
     *              @OA\Property(
     *                  property="firstname",
     *                  type="string",
     *                  description="Customer firstname",
     *                  example="John"
     *              ),
     *              @OA\Property(
     *                  property="lastname",
     *                  type="string",
     *                  description="Customer lastname",
     *                  example="Doe"
     *              ),
     *              @OA\Property(
     *                  property="address",
     *                  type="string",
     *                  description="Customer address",
     *                  example="123 Main St"
     *              ),
     *              @OA\Property(
     *                  property="address2",
     *                  type="string",
     *                  description="Customer address line 2",
     *                  example="Apt 456"
     *              ),
     *              @OA\Property(
     *                  property="city",
     *                  type="string",
     *                  description="Customer city",
     *                  example="New York"
     *              ),
     *              @OA\Property(
     *                  property="zipcode",
     *                  type="string",
     *                  description="Customer zipcode",
     *                  example="10001"
     *              ),
     *              @OA\Property(
     *                  property="phone",
     *                  type="string",
     *                  description="Customer phone",
     *                  example="+1234567890"
     *              ),
     *              @OA\Property(
     *                  property="region",
     *                  type="string",
     *                  description="Customer region",
     *                  example="NY"
     *              ),
     *              @OA\Property(
     *                  property="verified",
     *                  type="boolean",
     *                  description="set verified customer to true",
     *                  example=true
     *              ),
     *              @OA\Property(
     *                  property="balance",
     *                  type="number",
     *                  format="float",
     *                  description="Customer balance",
     *                  example=100.50
     *              ),
     *              @OA\Property(
     *                  property="country",
     *                  type="string",
     *                  description="Customer country",
     *                  example="USA"
     *              )
     *          )
     *     ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      )
     *     )
     */
    public function update(UpdateCustomerRequest $request, string $id)
    {
        $item = Customer::findOrFail($id);
        $params = $request->validated();
        $verified = false;
        if (isset($params['verified'])) {
            unset($params['verified']);
            $verified = true;
        }
        $item->update($params);
        if ($verified){
            $item->markEmailAsVerified();
        }
        return response()->json($item);
    }


    /**
     * @OA\Delete(
     *      path="/application/customers/{id}",
     *      operationId="deleteCustomerById",
     *      tags={"Customers"},
     *      summary="Delete customer information",
     *      description="Delete customer data",
     *      @OA\Parameter(
     *          name="id",
     *          description="customer id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Customer")
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Key is invalid"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="customer not found"
     *      ),
     * )
     */
    public function destroy(string $id)
    {
        $item = Customer::findOrFail($id);
        $item->delete();
        return response()->json($item, 200);
    }
}
