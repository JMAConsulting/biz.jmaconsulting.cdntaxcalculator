<div id="crm-cdntaxcalculator-province-popup">
  <h4>Please select your billing province:</h4>

  <form>
    <div class="crm-section">
      <div id="crm-cdntaxcalculator-province-label" class="label">Province:</div>
      <div id="crm-cdntaxcalculator-province-value" class="content"></div>
    </div>
  </form>
</div>

{literal}
  <script>
    (function($, _, ts){
      var province_id = {/literal}{$cdntaxcalculator_province_id}{literal};
      var province_name = "{/literal}{$cdntaxcalculator_province_name}{literal}";

      if (province_id) {
        var $parent = $('#crm-container #billing_state_province_id-5').parent();

        $parent.children().hide();
        $parent.append('<div>' + province_name + '</div>');
        // $parent.append('<input type="hidden" name="billing_state_province_id-5" value="' + province_id + '">');

        // This is shown in the priceset so that users can change it before
        // entering too much data in the form. Also has an impact on prices shown,
        // so it's good to show early.
        $('#priceset').append('<div id="#crm-cdntaxcalculator-pricesetinfo"><p>Taxes are calculated based on your billing address ({/literal}{$cdntaxcalculator_province_name}{literal}). <a href="#" id="cdntaxcalculator-link-changeprovince">Click here select another province.</a></p></div>');

        $('#cdntaxcalculator-link-changeprovince').on('click', function(e) {
          CRM.cdntaxesShowPopup();
          e.preventDefault();
        });
      }
      else {
        CRM.cdntaxesShowPopup();
      }

      CRM.cdntaxesShowPopup = function() {
        var e = $('#billing_state_province_id-5').clone();
        e.appendTo('#crm-cdntaxcalculator-province-value');
        e.show();

        var dialog = $('#crm-cdntaxcalculator-province-popup').dialog({
          width: 600,
          minHeight: 200,
          modal: true,
          resizable: false,
          closeOnEscape: false,
          draggable: false,
          title: "Please select your billing province:",
          buttons: {
            "Save": function() {
              var province_id = $('#billing_state_province_id-5', dialog).val();

              // Not necessary, since we are reloading the page.
              // $('#billing_state_province_id-5', '#crm-container').val(province_id).trigger('change');

              $('.ui-dialog-buttonset').append('<div class="crm-loading-element" style="float: right;"></div>');

              var url = CRM.url('civicrm/cdntaxcalculator/province', {
                state_province_id: province_id
              });

              $.ajax({
                "dataType": 'json',
                "type": "POST",
                "url": url,
                "success": function() {
                  // dialog.dialog('close');
                  location.reload();
                },
              });
            },
          }
        });

        $(".ui-dialog-titlebar").hide();
      };
    })(CRM.$, CRM._, CRM.ts('cdntaxcalculator'));
  </script>
{/literal}
