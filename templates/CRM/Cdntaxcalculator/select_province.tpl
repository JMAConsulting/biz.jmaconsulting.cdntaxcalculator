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
    CRM.$(function($) {
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
            $('#billing_state_province_id-5', '#crm-container').val(province_id).trigger('change');

            $('.ui-dialog-buttonset').append('<div class="crm-loading-element" style="float: right;"></div>');

            var url = CRM.url('civicrm/cdntaxcalculator/province', {
              state_province_id: province_id
            });

            $.ajax({
              "dataType": 'json',
              "type": "POST",
              "url": url,
              "success": function() {
                dialog.dialog('close');
                location.reload();
              },
            });
          },
        },
        close: function() {
          console.log('CLOSE');
        }
      });

      $(".ui-dialog-titlebar").hide();
    });
  </script>
{/literal}
