<div id="recurringFieldsDisabled" class="help">
    Recurring options are disabled because the selected a {if $isMembershipPriceSetSelected}Membership{/if} Price Set which contains a Price Field with a recurring frequency enabled.
</div>
{literal}
    <script>
        cj('.crm-block.crm-form-block.crm-contribution-contributionpage-amount-form-block').prepend(cj("#recurringFieldsDisabled"));

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