<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
<div>
    @include("shared/textarea", ["name" => "bank_transfert_details", "rows" => 5, "value" => setting("bank_transfert_details"), "label" => __('client.invoices.banktransfer.setting_fielddescription')])
</div>
