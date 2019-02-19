<div id="recurringFieldsDisabled">
    <table class="form-layout-compressed">
        <tbody>
            <tr>
                <td><p>Recurring options are disabled because the selected a Price Set which contains a Price Field with a recurring frequency enabled.</p></td>
            </tr>
        </tbody>
    </table>
</div>
{literal}
    <script>
        cj("#recurringFieldsDisabled").insertAfter('#recurringFields');

        {/literal}
            {if $hideRecurringSection}
                cj("#recurringFieldsDisabled").show();
                cj("#recurringFields").hide();
                cj('#amountFields').hide();
            {else}
                cj("#recurringFieldsDisabled").hide();
                cj("#recurringFields").show();
            {/if}
        {literal}

        var priceSetIndividualContributions =[];
        {/literal}
        {foreach from=$priceSetIndividualContribution item=fieldId}
            priceSetIndividualContributions.push({$fieldId});
        {/foreach}
        {literal}

        function showHideRecurringBlockBasedOnPriceSet(priceSetId) {
            priceSetId = parseInt(priceSetId);
            if (priceSetIndividualContributions.indexOf(priceSetId) >= 0) {
                cj("#recurringFields").hide();
                cj("#recurringFieldsDisabled").show();
            }
            else{
                cj("#recurringFields").show();
                cj("#recurringFieldsDisabled").hide();
            }
        }
    </script>
{/literal}