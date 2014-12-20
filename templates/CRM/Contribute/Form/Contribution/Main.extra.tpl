{literal}
<script type="text/javascript">
cj('#state_province-Primary').change(function() {
  var icrm = {/literal}{$priceSet.fields.3.options.7.amount}{literal};
  var inrm = {/literal}{$priceSet.fields.3.options.8.amount}{literal};
  var sm = {/literal}{$priceSet.fields.3.options.9.amount}{literal};
  var cm = {/literal}{$priceSet.fields.3.options.10.amount}{literal};
  var gm = {/literal}{$priceSet.fields.3.options.11.amount}{literal};
  var taxes = '{/literal}{$totaltaxes}{literal}';
  taxes = cj.parseJSON(taxes);
  var state = cj(this).val();
  if (taxes[state]) {
    var newTax = parseFloat(icrm) * parseFloat(taxes[state]) / 100;		   
    cj('label[for="CIVICRM_QFID_7_16"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));
    var newTax = parseFloat(inrm) * parseFloat(taxes[state]) / 100;		   
    cj('label[for="CIVICRM_QFID_8_18"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));
    var newTax = parseFloat(sm) * parseFloat(taxes[state]) / 100;		   
    cj('label[for="CIVICRM_QFID_9_20"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));
    var newTax = parseFloat(cm) * parseFloat(taxes[state]) / 100;		   
    cj('label[for="CIVICRM_QFID_10_22"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));
    var newTax = parseFloat(gm) * parseFloat(taxes[state]) / 100;		   
    cj('label[for="CIVICRM_QFID_11_24"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));
    

  // TBD Later
  //  cj.each(price, function(key, val){
  //  	var newTax = val.amount * taxes[state] / 100;		   
  //   	cj('label[for="CIVICRM_QFID_7_16"] > span:nth-child(3)').html('+ $' + newTax);
  //  });
  }
});

</script>


{/literal}