<table id="shipping-rules-table" class="table" data-id-product="{$id_product}">
    <thead>
        <tr>
            <th>Country</th>
            <th>Start Rate</th>
            <th>Extra Rate</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$shippingRules item=rule}
            <tr data-id="{$rule.id_product}" data-country="{$rule.shipping_country}">
                <td>
                    <select class="form-control shipping-country" disabled>
                        {foreach from=$countries item=country}
                            <option value="{$country.iso_code}" {if $country.iso_code == $rule.shipping_country}selected{/if}>
                                {$country.name}
                            </option>
                        {/foreach}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control shipping-start-rate" value="{$rule.shipping_start_rate}">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control shipping-extra-rate" value="{$rule.shipping_extra_rate}">
                </td>
                <td>
                    <button class="btn btn-success update-row"><i class="icon-check"></i></button>
                    <button class="btn btn-danger delete-row"><i class="icon-trash"></i></button>
                </td>
            </tr>
        {/foreach}
    </tbody>
    <tfoot>
        <tr>
            <td>
                <select class="form-control new-shipping-country">
                    {foreach from=$countries item=country}
                        <option value="{$country.iso_code}">{$country.name}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <input type="number" step="0.01" class="form-control new-shipping-start-rate" placeholder="Enter Start Rate">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control new-shipping-extra-rate" placeholder="Enter Extra Rate">
            </td>
            <td>
                <button id="add-new-rule" class="btn btn-primary"><i class="icon-plus"></i> Addd</button>
            </td>
        </tr>
    </tfoot>
</table>
<script>
    var updateShippingRuleUrl = "{$updateShippingRuleUrl}";
    var deleteShippingRuleUrl = "{$deleteShippingRuleUrl}";
    var addShippingRuleUrl = "{$addShippingRuleUrl}";
</script>
<script src="{$js_file_url}"></script>

