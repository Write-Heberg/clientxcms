<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Abstracts;

trait SupportRelateItemTrait
{

    public function relatedName(): string
    {
        return "Related Name";
    }

    public function relatedId(): int
    {
        return $this->id;
    }

    public function relatedLink(): string
    {
        return route('admin.' . $this->relatedType() . 's.show', $this->relatedId());
    }

    public function relatedIcon(): string
    {
        switch ($this->relatedType()) {
            case 'invoice':
                return 'bi bi-file-earmark-text';
            case 'service':
                return 'bi bi-cube';
            default:
                return 'bi bi-question-circle';
        }
    }

    public function relatedType(): string {
        return strtolower(class_basename($this));
    }
}
