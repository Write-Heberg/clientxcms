<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;

trait ModelStatutTrait
{

    /**
     * @var array|string[] $statusList - Types of status
     * Active : visible and can be used for everyone
     * Hidden : visible but can't be used for everyone
     * Unreferenced : not visible and can be used for admin only
     */
    public array $statusList = [
        'active',
        'hidden',
        'unreferenced',
    ];
    public function isValid(bool $canUnreferenced = false)
    {
        if ($this->status == 'active') {
            return true;
        }
        if ($canUnreferenced && $this->status == 'unreferenced') {
            return true;
        }

        return false;
    }

    public function isNotValid(bool $canUnreferenced = false)
    {
        // Si il y n'a pas de stock et que le stock n'est pas désactivé ou rend le produit non valide
        if ($this->stock == 0 && $this->getMetadata('disabled_stock') == null) {
            return true;
        }
        if ($this->getMetadata('is_personalized_product') == 'true') {
            return true;
        }

        return !$this->isValid($canUnreferenced);
    }

    public function switchStatus(string $status)
    {
        $this->status = $status;
        $this->save();
    }

    public static function getAvailable(bool $inAdmin = false): Builder
    {
        if ($inAdmin){
            return self::query();
        }
        return self::where('status', 'active', 'or');
    }
}
