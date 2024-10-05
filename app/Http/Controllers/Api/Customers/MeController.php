<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Api\Customers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeController extends Controller
{

    /**
     * @OA\Get(
     *      path="/customer/me",
     *      operationId="customerMe",
     *      summary="Display the current customer details",
     *      @OA\Response(
     *          response=403,
     *          description="Invalid token",
     *      ),
     *      tags={"Client API"},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Display the current customer details",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Customer")
     *       ),
     *     )
     */
    public function me(Request $request)
    {
        return $request->user();
    }
}
