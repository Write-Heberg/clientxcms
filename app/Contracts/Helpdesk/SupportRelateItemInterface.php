<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Contracts\Helpdesk;

interface SupportRelateItemInterface
{
    public function relatedName(): string;

    public function relatedLink(): string;

    public function relatedIcon(): string;

    public function relatedType(): string;

    public function relatedId(): int;
}
