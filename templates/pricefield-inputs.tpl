{literal}
    <style>
        .crm-container span.description {
            display: block;
        }
    </style>
{/literal}
<div class="crm-price-field-form-block-create-individual-contribution">
    <div class="label"><label for="weight">  Create Individual Contribution</label></div>
    <div>
        {$form.create_individual_contribution.html}
        <span class="description">
            If checked, separate contribution will be created for this price field.
        </span>
    </div>
</div>

<div class="crm-price-field-form-block-recurring-contribution-unit">
    <div class="label"><label for="weight">  Recurring Contribution Unit</label></div>
    <div>
        {$form.recurring_contribution_unit.html}
        <span class="description">
            Select individual recurring contribution unit.
        </span>
    </div>
</div>

<div class="crm-price-field-form-block-recurring-contribution-interval">
    <div class="label"><label for="weight">  Recurring Contribution Interval</label></div>
    <div>
        {$form.recurring_contribution_interval.html}
        <span class="description">
            Individual recurring contribution interval
        </span>
    </div>
</div>

<div class="price_field_extra_options" style="display: none;">
    {section name=rowLoop start=1 loop=16}
        {assign var=index value=$smarty.section.rowLoop.index}

        <div class="price_field_option_extra_{$index}">
            <div class="price_field_option_individual_contribution">
                {$form.option_create_individual_contribution.$index.html}
            </div>
            <div class="price_field_option_recur_contribution_unit">
                {$form.option_recurring_contribution_unit.$index.html}
            </div>
            <div class="price_field_option_recur_contribution_interval">
                {$form.option_recurring_contribution_interval.$index.html}
            </div>
        </div>
    {/section}
</div>

{literal}
<script type="text/javascript">

    if (cj('#optionField').size() > 0) {
        cj('#optionField tbody tr:first-child').append('<th>Create Individual Contribution?</th>');
        cj('#optionField tbody tr:first-child').append('<th>Recurring Contribution Unit</th>');
        cj('#optionField tbody tr:first-child').append('<th>Recurring Contribution Interval</th>');

        cj('#optionField tbody tr').each(function(index){

            var optionCell = cj('<td>');
            var unitCell = cj('<td>');
            var intervalCell = cj('<td>');

            optionCell.html(cj('.price_field_option_extra_' + index).find('.price_field_option_individual_contribution').html());
            unitCell.html(cj('.price_field_option_extra_' + index).find('.price_field_option_recur_contribution_unit').html());
            intervalCell.html(cj('.price_field_option_extra_' + index).find('.price_field_option_recur_contribution_interval').html());

            if (index != 0) {
                cj(this).append(optionCell);
                cj(this).append(unitCell);
                cj(this).append(intervalCell);
            }
        });
    }

    cj('.price_field_extra_options').remove();

    var classesToShift = [
        '.crm-price-field-form-block-create-individual-contribution',
        '.crm-price-field-form-block-recurring-contribution-unit',
        '.crm-price-field-form-block-recurring-contribution-interval'
    ];
    var insertAfterClass = '.crm-price-field-form-block-price';

    if (cj('.crm-price-option-form-block-amount').size() > 0) {
        insertAfterClass = '.crm-price-option-form-block-amount';
    }

    for (var i = 0; i < classesToShift.length; i++) {
        var inConClass = classesToShift[i];
        cj(inConClass).insertAfter(insertAfterClass);
        insertAfterClass = inConClass;
        var existingHTML = cj(inConClass).html();
        existingHTML = existingHTML.replace(/<div/g,"<td");
        existingHTML = existingHTML.replace(/<\/div>/g,"</td>");
        var classToAdd = inConClass.substr(1);
        cj(inConClass).replaceWith('<tr class="'+classToAdd+'">' + existingHTML + '</tr>');
    }

</script>
{/literal}