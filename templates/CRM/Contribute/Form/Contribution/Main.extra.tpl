{if $renewButton}
{literal}
<script type="text/javascript">
  cj('#_qf_Main_upload-bottom').val('Renew Membership');
</script>
{/literal}
{/if}

{literal}
<script type="text/javascript">
  var icrmtax = {/literal}{$priceSet.fields.3.options.7.tax_amount}{literal};
  var inrmtax = {/literal}{$priceSet.fields.3.options.8.tax_amount}{literal};
  var smtax = {/literal}{$priceSet.fields.3.options.9.tax_amount}{literal};
  var cmtax = {/literal}{$priceSet.fields.3.options.10.tax_amount}{literal};
  var gmtax = {/literal}{$priceSet.fields.3.options.11.tax_amount}{literal};
cj('#state_province-Primary').change(function() {
  var icrm = {/literal}{$priceSet.fields.3.options.7.amount}{literal};
  var inrm = {/literal}{$priceSet.fields.3.options.8.amount}{literal};
  var sm = {/literal}{$priceSet.fields.3.options.9.amount}{literal};
  var cm = {/literal}{$priceSet.fields.3.options.10.amount}{literal};
  var gm = {/literal}{$priceSet.fields.3.options.11.amount}{literal};
  var taxes = '{/literal}{$totaltaxes}{literal}';
  var indtaxes = '{/literal}{$indtaxes}{literal}';

  taxes = cj.parseJSON(taxes);  
  indtaxes = cj.parseJSON(indtaxes);
  var state = cj(this).val();
  if (taxes[state]) {
    var newTax = parseFloat(icrm) * parseFloat(taxes[state]) / 100;
    var hst = parseFloat(icrm) * parseFloat(indtaxes[state]['HST_GST']) / 100;
    var pst = 0;
    if (indtaxes[state]['PST']) {
      var pst = parseFloat(icrm) * parseFloat(indtaxes[state]['PST']) / 100;
    }
    if (cj('label[for="CIVICRM_QFID_7_16"]').length) {
      var firstlabel = cj('label[for="CIVICRM_QFID_7_16"] > span:nth-child(2)').html();
    }
    else {
      var firstlabel = cj('label[for="CIVICRM_QFID_7_14"] > span:nth-child(2)').html();
    }
    if (firstlabel.indexOf('+') >= 0) {
      var firstlabel = firstlabel.substring(0, firstlabel.indexOf('+'));
    } 
    var firstlabel = firstlabel + ' + $' + hst.toFixed(2) + ' HST';
    if (pst) {
      var firstlabel = firstlabel + ' + $' + pst.toFixed(2) + ' PST';
    }			   
    cj('label[for="CIVICRM_QFID_7_16"] > span:nth-child(2)').html(firstlabel);	  
    cj('label[for="CIVICRM_QFID_7_14"] > span:nth-child(2)').html(firstlabel);	
    var total = parseFloat(icrm) + parseFloat(newTax);
    var st = '["price_3", "' + total.toFixed(2) + '||"]';
    cj('#CIVICRM_QFID_7_16').attr('price', st);
    cj('#CIVICRM_QFID_7_14').attr('price', st);

    var newTax = parseFloat(inrm) * parseFloat(taxes[state]) / 100;
    var hst = parseFloat(inrm) * parseFloat(indtaxes[state]['HST_GST']) / 100;
    var pst = 0;
    if (indtaxes[state]['PST']) {
      var pst = parseFloat(icrm) * parseFloat(indtaxes[state]['PST']) / 100;
    }
    if (cj('label[for="CIVICRM_QFID_8_18"]').length) {
      var firstlabel = cj('label[for="CIVICRM_QFID_8_18"] > span:nth-child(2)').html();
    }
    else {
      var firstlabel = cj('label[for="CIVICRM_QFID_8_16"] > span:nth-child(2)').html();
    }
    if (firstlabel.indexOf('+') >= 0) {
      var firstlabel = firstlabel.substring(0, firstlabel.indexOf('+'));
    } 
    var firstlabel = firstlabel + ' + $' + hst.toFixed(2) + ' HST';
    if (pst) {
      var firstlabel = firstlabel + ' + $' + pst.toFixed(2) + ' PST';
    }		   
    cj('label[for="CIVICRM_QFID_8_18"] > span:nth-child(2)').html(firstlabel);	 	 
    cj('label[for="CIVICRM_QFID_8_16"] > span:nth-child(2)').html(firstlabel);	
    var total = parseFloat(inrm) + parseFloat(newTax);
    var st = '["price_3","' + total.toFixed(2) + '||"]';
    cj('#CIVICRM_QFID_8_18').attr('price', st);
    cj('#CIVICRM_QFID_8_16').attr('price', st);

    var newTax = parseFloat(sm) * parseFloat(taxes[state]) / 100;
    var hst = parseFloat(sm) * parseFloat(indtaxes[state]['HST_GST']) / 100;
    var pst = 0;
    if (indtaxes[state]['PST']) {
      var pst = parseFloat(icrm) * parseFloat(indtaxes[state]['PST']) / 100;
    }
    if (cj('label[for="CIVICRM_QFID_9_20"]').length) {
      var firstlabel = cj('label[for="CIVICRM_QFID_9_20"] > span:nth-child(2)').html();
    }
    else {
      var firstlabel = cj('label[for="CIVICRM_QFID_8_16"] > span:nth-child(2)').html();
    }
    if (firstlabel.indexOf('+') >= 0) {
      var firstlabel = firstlabel.substring(0, firstlabel.indexOf('+'));
    } 
    var firstlabel = firstlabel + ' + $' + hst.toFixed(2) + ' HST';
    if (pst) {
      var firstlabel = firstlabel + ' + $' + pst.toFixed(2) + ' PST';
    }			   
    cj('label[for="CIVICRM_QFID_9_20"] > span:nth-child(2)').html(firstlabel);	
    cj('label[for="CIVICRM_QFID_9_18"] > span:nth-child(2)').html(firstlabel);	
    var total = parseFloat(sm) + parseFloat(newTax);
    var st = '["price_3","' + total.toFixed(2) + '||"]';
    cj('#CIVICRM_QFID_9_20').attr('price', st);
    cj('#CIVICRM_QFID_9_18').attr('price', st);

    var newTax = parseFloat(cm) * parseFloat(taxes[state]) / 100;
    var hst = parseFloat(cm) * parseFloat(indtaxes[state]['HST_GST']) / 100;
    var pst = 0;
    if (indtaxes[state]['PST']) {
      var pst = parseFloat(icrm) * parseFloat(indtaxes[state]['PST']) / 100;
    }
    if (cj('label[for="CIVICRM_QFID_10_22"]').length) {
      var firstlabel = cj('label[for="CIVICRM_QFID_10_22"] > span:nth-child(2)').html();
    }
    else {
      var firstlabel = cj('label[for="CIVICRM_QFID_10_20"] > span:nth-child(2)').html();
    }
    if (firstlabel.indexOf('+') >= 0) {
      var firstlabel = firstlabel.substring(0, firstlabel.indexOf('+'));
    } 	 
    var firstlabel = firstlabel + ' + $' + hst.toFixed(2) + ' HST';
    if (pst) {
      var firstlabel = firstlabel + ' + $' + pst.toFixed(2) + ' PST';
    }	   
    cj('label[for="CIVICRM_QFID_10_22"] > span:nth-child(2)').html(firstlabel);	   
    cj('label[for="CIVICRM_QFID_10_20"] > span:nth-child(2)').html(firstlabel);	
    var total = parseFloat(cm) + parseFloat(newTax);
    var st = '["price_3","' + total.toFixed(2) + '||"]';
    cj('#CIVICRM_QFID_10_22').attr('price', st);
    cj('#CIVICRM_QFID_10_20').attr('price', st);

    var newTax = parseFloat(gm) * parseFloat(taxes[state]) / 100;
    var hst = parseFloat(gm) * parseFloat(indtaxes[state]['HST_GST']) / 100;
    var pst = 0;
    if (indtaxes[state]['PST']) {
      var pst = parseFloat(icrm) * parseFloat(indtaxes[state]['PST']) / 100;
    }
    if (cj('label[for="CIVICRM_QFID_11_24"]').length) {
      var firstlabel = cj('label[for="CIVICRM_QFID_11_24"] > span:nth-child(2)').html();
    }
    else {
      var firstlabel = cj('label[for="CIVICRM_QFID_11_22"] > span:nth-child(2)').html();
    }
    if (firstlabel.indexOf('+') >= 0) {
      var firstlabel = firstlabel.substring(0, firstlabel.indexOf('+'));
    } 	
    var firstlabel = firstlabel + ' + $' + hst.toFixed(2) + ' HST';
    if (pst) {
      var firstlabel = firstlabel + ' + $' + pst.toFixed(2) + ' PST';
    }	   
    cj('label[for="CIVICRM_QFID_11_24"] > span:nth-child(2)').html(firstlabel);	 
    cj('label[for="CIVICRM_QFID_11_22"] > span:nth-child(2)').html(firstlabel);	
    var total = parseFloat(gm) + parseFloat(newTax);
    var st = '["price_3","' + total.toFixed(2) + '||"]';
    cj('#CIVICRM_QFID_11_24').attr('price', st);
    cj('#CIVICRM_QFID_11_22').attr('price', st);

  
    
  }
  else{
    var newTax = parseFloat(icrmtax);		   
    cj('label[for="CIVICRM_QFID_7_16"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));		   
    cj('label[for="CIVICRM_QFID_7_14"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));
    var total = parseFloat(icrm) + parseFloat(newTax);
    var st = '["price_3", "' + total.toFixed(2) + '||"]';
    cj('#CIVICRM_QFID_7_16').attr('price', st);
    cj('#CIVICRM_QFID_7_14').attr('price', st);

    var newTax = parseFloat(inrmtax);		   
    cj('label[for="CIVICRM_QFID_8_18"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));	   
    cj('label[for="CIVICRM_QFID_8_16"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));
    var total = parseFloat(inrm) + parseFloat(newTax);
    var st = '["price_3","' + total.toFixed(2) + '||"]';
    cj('#CIVICRM_QFID_8_18').attr('price', st);
    cj('#CIVICRM_QFID_8_16').attr('price', st);

    var newTax = parseFloat(smtax);		   
    cj('label[for="CIVICRM_QFID_9_20"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));		   
    cj('label[for="CIVICRM_QFID_9_18"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));
    var total = parseFloat(sm) + parseFloat(newTax);
    var st = '["price_3","' + total.toFixed(2) + '||"]';
    cj('#CIVICRM_QFID_9_20').attr('price', st);
    cj('#CIVICRM_QFID_9_18').attr('price', st);

    var newTax = parseFloat(cmtax);		   
    cj('label[for="CIVICRM_QFID_10_22"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));  
    cj('label[for="CIVICRM_QFID_10_20"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));
    var total = parseFloat(cm) + parseFloat(newTax);
    var st = '["price_3","' + total.toFixed(2) + '||"]';
    cj('#CIVICRM_QFID_10_22').attr('price', st);
    cj('#CIVICRM_QFID_10_20').attr('price', st);

    var newTax = parseFloat(gmtax);		   
    cj('label[for="CIVICRM_QFID_11_24"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));	   
    cj('label[for="CIVICRM_QFID_11_22"] > span:nth-child(3)').html(' + $' + newTax.toFixed(2));
    var total = parseFloat(gm) + parseFloat(newTax);
    var st = '["price_3","' + total.toFixed(2) + '||"]';
    cj('#CIVICRM_QFID_11_24').attr('price', st);
    cj('#CIVICRM_QFID_11_22').attr('price', st);

  }
    var optionSep      = '|';
    cj("#priceset input:radio").each(function () {
    //default calcution of element.
    eval( 'var option = ' + cj(this).attr('price') );
    ele        = option[0];
    optionPart = option[1].split(optionSep);
    addprice   = parseFloat( optionPart[0] );
    if ( ! price[ele] ) {
      price[ele] = 0;
    }
      if( cj(this).prop('checked') ) {
      totalfee   = parseFloat(totalfee) + addprice - parseFloat(price[ele]);
      price[ele] = addprice;
    }

    //event driven calculation of element.
    cj(this).click( function(){
    eval( 'var option = ' + cj(this).attr('price') );
    ele        = option[0];
    optionPart = option[1].split(optionSep);
    addprice   = parseFloat( optionPart[0] );
      display( addprice );
    });
     display( totalfee );				
    });
});

function display( totalfee ) {
    totalfee = Math.round(totalfee*100)/100;
    var totalEventFee  = formatMoney( totalfee, 2, seperator, thousandMarker);
    document.getElementById('pricevalue').innerHTML = "<b>"+symbol+"</b> "+totalEventFee;
    scriptfee   = totalfee;
    scriptarray = price;
    cj('#total_amount').val( totalfee );
    cj('#pricevalue').data('raw-total', totalfee).trigger('change');

    ( totalfee < 0 ) ? cj('table#pricelabel').addClass('disabled') : cj('table#pricelabel').removeClass('disabled');
    if (typeof skipPaymentMethod == 'function') {
      skipPaymentMethod();
    }
}

function formatMoney (amount, c, d, t) {
var n = amount,
    c = isNaN(c = Math.abs(c)) ? 2 : c,
    d = d == undefined ? "," : d,
    t = t == undefined ? "." : t, s = n < 0 ? "-" : "",
    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
    j = (j = i.length) > 3 ? j % 3 : 0;
  return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}


</script>


{/literal}