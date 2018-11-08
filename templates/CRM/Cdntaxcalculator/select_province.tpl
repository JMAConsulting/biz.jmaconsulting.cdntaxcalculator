{crmScope extensionKey='biz.jmaconsulting.cdntaxcalculator'}
<div id="crm-cdntaxcalculator-province-popup" style="display: none;">
  <h4>{$cdntaxcalculator_settings.setting_text_select_location}</h4>

  <form>
    <div class="crm-section crm-cdntaxcalculator-country-row">
      <div id="crm-cdntaxcalculator-country-label" class="label">{ts}Country:{/ts}</div>
      <div id="crm-cdntaxcalculator-country-value" class="content"></div>
    </div>
    <div class="crm-section crm-cdntaxcalculator-province-row">
      <div id="crm-cdntaxcalculator-province-label" class="label">{ts}Province or State:{/ts}</div>
      <div id="crm-cdntaxcalculator-province-value" class="content"></div>
    </div>
  </form>
</div>

{literal}
  <script>
    (function($, _, ts){

      CRM.cdntaxesShowPopup = function() {
        // We can move the widgets because we are going to reload the page anyway.
        $('#billing_state_province_id-5').appendTo('#crm-cdntaxcalculator-province-value');
        $('#s2id_billing_state_province_id-5').show();
        $('#s2id_billing_state_province_id-5').appendTo('#crm-cdntaxcalculator-province-value');

        $('#billing_country_id-5').appendTo('#crm-cdntaxcalculator-country-value');
        $('#s2id_billing_country_id-5').show();
        $('#s2id_billing_country_id-5').appendTo('#crm-cdntaxcalculator-country-value');

        // Disabling reminder that they will lose data
        // it's confusing and they don't have a choice anyway.
        window.onbeforeunload = null;

        var dialog = $('#crm-cdntaxcalculator-province-popup').dialog({
          width: 600,
          minHeight: 200,
          modal: true,
          resizable: false,
          closeOnEscape: false,
          draggable: false,
          title: "{/literal}{ts escape="js"}Please select your billing province:{/ts}{literal}",
          buttons: {
            "Save": function() {
              var province_id = $('#billing_state_province_id-5').val();
              var country_id = $('#billing_country_id-5').val();

              // FIXME: Hardcoded country ID
              if (country_id == 1039 && !province_id) {
                $('#crm-cdntaxcalculator-province-label').addClass('error');
                $('#crm-cdntaxcalculator-province-value').append('<span class="error">' + ts('Please select a province.') + '</span>');
                return false;
              }

              $('.ui-dialog-buttonset').append('<div class="crm-loading-element" style="float: right;"></div>');

              // Alter the URL params directly, then reload the page.
              // This used to do an ajax request instead, then store in the session,
              // but there were weird timing issues.
              // This is also practical since it leaves a papertrail in the server logs.
              var params = window.location.search;
              params = params.replace(/(\&|\?)cdntax_province_id=\d+/, '');
              params = params.replace(/(\&|\?)cdntax_country_id=\d+/, '');

              params += (!params ? '?' : '&');
              params += 'cdntax_province_id=' + province_id;
              params += '&cdntax_country_id=' + country_id;

              window.location.search = params;
            },
          }
        });

        $(".ui-dialog-titlebar").hide();
      };

      var country_id = CRM.cdntaxcalculator.country_id;
      var country_name = CRM.cdntaxcalculator.country_name;
      var province_id = CRM.cdntaxcalculator.province_id;
      var province_name = CRM.cdntaxcalculator.province_name;
      var has_address_based_taxes = CRM.cdntaxcalculator.has_address_based_taxes;

      if (has_address_based_taxes) {
        if (province_id || (country_id && country_id != 1039)) {
          // Read-only country/province billing fields
          // FIXME: how to handle if using 'primary' address?
          if (CRM.cdntaxcalculator.setting_address_type == 1) {
            // $('#crm-container #billing_country_id-5').val(country_id).trigger('change');
            var $parent = $('#crm-container #billing_country_id-5').parent();

            $parent.children().hide();
            $parent.append('<div>' + country_name + '</div>');

            // Read-only province field
            if (province_id) {
              // $('#crm-container #billing_state_province_id-5').val(province_id).trigger('change');
              var $parent = $('#crm-container #billing_state_province_id-5').parent();

              $parent.children().hide();
              $parent.append('<div>' + province_name + '</div>');
            }
          }

          // This is shown in the priceset so that users can change it before
          // entering too much data in the form. Also has an impact on prices shown,
          // so it's good to show early.
          $('#priceset').append('{/literal}<div id="crm-cdntaxcalculator-pricesetinfo"><p>' + CRM.cdntaxcalculator.setting_text_current_location + ' <a href="#" id="cdntaxcalculator-link-changeprovince">' + CRM.cdntaxcalculator.setting_text_change_location + '</a>' + '<span id="crm-cdntaxcalculator-pricesetinfo-help">' + CRM.cdntaxcalculator.setting_text_help + '</span></p></div>{literal}');

          $('#cdntaxcalculator-link-changeprovince').on('click', function(e) {
            CRM.cdntaxesShowPopup();
            e.preventDefault();
          });
        }
        else {
          CRM.cdntaxesShowPopup();
        }
      }

      /**
       * If the user selects a different Payment Processor
       * CiviCRM will reset the billing-block to its initial state.
       * Therefore we have to set the country/province back to the
       * value selected in the popup.
       */
      $(document).on('crmLoad', function(e) {
        if (e.target.id != 'billing-payment-block') {
          return;
        }

        if (CRM.cdntaxcalculator.country_id) {
          $('#billing_country_id-5').val(CRM.cdntaxcalculator.country_id).trigger('change');
          $('#billing_country_id-5').hide();
          $('#s2id_billing_country_id-5').hide(); // select2 element
          $('#billing_country_id-5').parent().append('<span>' + CRM.cdntaxcalculator.country_name + '</span>');
        }
        if (CRM.cdntaxcalculator.province_id) {
          $('#billing_state_province_id-5').val(CRM.cdntaxcalculator.province_id).trigger('change');
          $('#billing_state_province_id-5').hide();
          $('#s2id_billing_state_province_id-5').hide(); // select2 element
          $('#billing_state_province_id-5').parent().append('<span>' + CRM.cdntaxcalculator.province_name + '</span>');
        }
      });

    })(CRM.$, CRM._, CRM.ts('cdntaxcalculator'));
  </script>
{/literal}
{/crmScope}
