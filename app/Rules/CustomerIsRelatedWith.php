<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CustomerIsRelatedWith implements Rule
{
    protected ?string $relatedType = null;
    protected ?string $relatedId = null;

    public function __construct(?string $relatedType=null, ?string $relatedId=null)
    {
        $this->relatedType = $relatedType;
        $this->relatedId = $relatedId;
    }

    public function passes($attribute, $value)
    {
        if (auth('admin')->check()) {
            return true;
        }
        if ($this->relatedType === null || $this->relatedId === null) {
            return true;
        }
        if ($this->relatedType === 'service') {
            if (auth('web')->user()->services()->where('id', $this->relatedId)->where('customer_id', auth('web')->id())->exists()) {
                return true;
            }
        }
        if ($this->relatedType === 'invoice') {
            if (auth('web')->user()->invoices()->where('id', $this->relatedId)->where('customer_id', auth('web')->id())->exists()) {
                return true;
            }
        }
        return false;
    }

    public function message(): string
    {
        return 'The selected :attribute is invalid.';
    }
}
