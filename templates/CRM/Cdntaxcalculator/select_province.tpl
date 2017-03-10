<div id="crm-cdntaxcalculator-province-popup" style="display: none;">
  <h4>{ts domain="biz.jmaconsulting.cdntaxcalculator"}Please select your billing province:{/ts}</h4>

  <form>
    <div class="crm-section">
      <div id="crm-cdntaxcalculator-province-label" class="label">{ts domain="biz.jmaconsulting.cdntaxcalculator"}Province:{/ts}</div>
      <div id="crm-cdntaxcalculator-province-value" class="content"></div>
    </div>
  </form>
</div>

{literal}
  <script>
    (function($, _, ts){

      CRM.cdntaxesShowPopup = function() {
        var province_id = $('#billing_state_province_id-5').val();
        var e = $('#billing_state_province_id-5').clone();

        // Cloning the select input makes it reset to its default value?
        // i.e. if the site default is Quebec, the popup would always show Quebec,
        // even if the previous selection was Ontario.
        e.val(province_id);

        e.appendTo('#crm-cdntaxcalculator-province-value');
        e.show();

        var dialog = $('#crm-cdntaxcalculator-province-popup').dialog({
          width: 600,
          minHeight: 200,
          modal: true,
          resizable: false,
          closeOnEscape: false,
          draggable: false,
          title: "{/literal}{ts escape="js" domain="biz.jmaconsulting.cdntaxcalculator"}Please select your billing province:{/ts}{literal}",
          buttons: {
            "Save": function() {
              var province_id = $('#billing_state_province_id-5', dialog).val();

              $('.ui-dialog-buttonset').append('<div class="crm-loading-element" style="float: right;"></div>');

              // Alter the URL params directly, then reload the page.
              // This used to do an ajax request instead, then store in the session,
              // but there were weird timing issues.
              // This is also practical since it leaves a papertrail in the server logs.
              var params = window.location.search;
              params = params.replace(/(\&|\?)cdntax_province_id=\d+/, '');

              params += (!params ? '?' : '&');
              params += 'cdntax_province_id=' + province_id;

              window.location.search = params;
            },
          }
        });

        $(".ui-dialog-titlebar").hide();
      };

      var province_id = CRM.cdntaxcalculator.province_id;
      var province_name = CRM.cdntaxcalculator.province_name;
      var has_address_based_taxes = CRM.cdntaxcalculator.has_address_based_taxes;

      if (has_address_based_taxes) {
        if (province_id) {
          // Read-only province field
          $('#crm-container #billing_state_province_id-5').val(province_id).trigger('change');
          var $parent = $('#crm-container #billing_state_province_id-5').parent();

          $parent.children().hide();
          $parent.append('<div>' + province_name + '</div>');

          // Read-only country field
          $('#crm-container #billing_country_id-5').val(1039); // safe assumption? get from CR..cdntaxcalculator.country_id ?
          var $parent = $('#crm-container #billing_country_id-5').parent();

          $parent.children().hide();
          $parent.append('<div>' + 'Canada' + '</div>'); // FIXME safe assumption, but still shouldn't be hardcoded?

          // This is shown in the priceset so that users can change it before
          // entering too much data in the form. Also has an impact on prices shown,
          // so it's good to show early.
          $('#priceset').append('{/literal}<div id="#crm-cdntaxcalculator-pricesetinfo"><p>{ts 1=$cdntaxcalculator_province_name escape="js" domain="biz.jmaconsulting.cdntaxcalculator"}Taxes are calculated based on your billing address (%1).{/ts} <a href="#" id="cdntaxcalculator-link-changeprovince">{ts escape="js" domain="biz.jmaconsulting.cdntaxcalculator"}Click here select another province.{/ts}</a></p></div>{literal}');

          $('#cdntaxcalculator-link-changeprovince').on('click', function(e) {
            CRM.cdntaxesShowPopup();
            e.preventDefault();
          });
        }
        else {
          CRM.cdntaxesShowPopup();
        }
      }
    })(CRM.$, CRM._, CRM.ts('cdntaxcalculator'));
  </script>
{/literal}
