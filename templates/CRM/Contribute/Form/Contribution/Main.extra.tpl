{literal}
<script type="text/javascript">

  var dom = cj('#price_2').html();
  var domattr = cj('#price_2').attr('price');
cj('#state_province-1').change(function() {
  var icrm = {/literal}{$priceSet.fields.3.options.12.amount}{literal};
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
    if (cj('label[for="price_3"]').length) {
      var firstlabel = cj('label[for="price_3"]').html();
    }
    if (firstlabel.indexOf('+') >= 0) {
      var firstlabel = firstlabel.substring(0, firstlabel.indexOf('+'));
    } 
    var firstlabel = firstlabel + ' + $' + hst.toFixed(2) + ' HST';
    if (pst) {
      var firstlabel = firstlabel + ' + $' + pst.toFixed(2) + ' PST';
    }			   
    cj('label[for="price_3"]').html(firstlabel);	
    var total = parseFloat(icrm) + parseFloat(newTax);
    var st = '["price_3", "' + total.toFixed(2) + '||"]';
    cj('#price_3').attr('price', st);

    var amts = [];
    cj('#price_2 option').each( function() {
      if (cj(this).val() != '') {
        var firstlabel = cj(this).text();
        if (firstlabel.indexOf('-') >= 0) {
          var firstpartlabel = firstlabel.substring(0, firstlabel.indexOf('-'));
          var firstlabel = firstlabel.substring(firstlabel.indexOf('-') + 1); 
          if (firstlabel.indexOf('-') >= 0) {
            var firstlabel = firstlabel.substring(firstlabel.indexOf('-') + 1);
          }
          if (firstlabel.indexOf('+') >= 0) {
            var firstlabel = firstlabel.substring(0, firstlabel.indexOf('+'));
          }
	  var baseamount = firstlabel.replace(/[^\d.-]/g,'');
          var hst = parseFloat(baseamount) * parseFloat(indtaxes[state]['HST_GST']) / 100;
          var pst = 0;
          if (indtaxes[state]['PST']) {
            var pst = parseFloat(baseamount) * parseFloat(indtaxes[state]['PST']) / 100;
          }
        }
	if (!(firstlabel.indexOf('-') >= 0)) {
          var firstlabel = firstpartlabel + ' - ' + firstlabel + ' + $' + hst.toFixed(2) + ' HST';
          if (pst) {
            var firstlabel = firstlabel + ' + $' + pst.toFixed(2) + ' PST';
          }
        }
	else {
          var firstlabel = firstlabel + ' + $' + hst.toFixed(2) + ' HST';
          if (pst) {
            var firstlabel = firstlabel + ' + $' + pst.toFixed(2) + ' PST';
          }
        }	
        cj(this).text(firstlabel);
	var total = parseFloat(baseamount) + parseFloat(hst) + parseFloat(pst);
	var val = cj(this).val();
	var texts = '"' + val + '":"' + total + '||"';
	amts.push(texts);
        cj('#price_2').attr('price', '{' + amts + '}');
      } 
    });
  }
  else {
    if (cj('label[for="price_3"]').length) {
      var firstlabel = cj('label[for="price_3"]').html();
    }
    if (firstlabel.indexOf('+') >= 0) {
      var firstlabel = firstlabel.substring(0, firstlabel.indexOf('+'));
    } 		   
    cj('label[for="price_3"]').html(firstlabel);	
    var total = parseFloat(icrm);
    var st = '["price_3", "' + total.toFixed(2) + '||"]';
    cj('#price_3').attr('price', st);

    var sel = cj('#price_2 option:selected').val();
    cj('#price_2').html(dom);
    cj('#price_2').attr('price', domattr);  
    cj('#price_2').val(sel).change(); 
  }
    var optionSep      = '|';
    cj("#priceset input").each(function () {
    
     var eleType =  cj(this).attr('type');
     if ( this.tagName == 'SELECT' ) {
     eleType = 'select-one';
     }
     switch( eleType ) {

       case 'text':

         //default calcution of element.
         calculateText( this );

        //event driven calculation of element.
        cj(this).bind( 'keyup', function() { calculateText( this );
          }).bind( 'blur' , function() { calculateText( this );
        });

      break;

      case 'select-one':

        //default calcution of element.
        var ele = cj(this).attr('id');
        if ( ! price[ele] ) {
          price[ele] = 0;
        }
        eval( 'var selectedText = ' + cj(this).attr('price') );
        var addprice = 0;
        if ( cj(this).val( ) ) {
          optionPart = selectedText[cj(this).val( )].split(optionSep);
          addprice   = parseFloat( optionPart[0] );
        }

        if ( addprice ) {
          totalfee   = parseFloat(totalfee) + addprice - parseFloat(price[ele]);
          price[ele] = addprice;
        }

        //event driven calculation of element.
        cj(this).change( function() {
          var ele = cj(this).attr('id');
          if ( ! price[ele] ) {
            price[ele] = 0;
          }
          eval( 'var selectedText = ' + cj(this).attr('price') );

          var addprice = 0;
          if ( cj(this).val( ) ) {
            optionPart = selectedText[cj(this).val( )].split(optionSep);
            addprice   = parseFloat( optionPart[0] );
          }

          if ( addprice ) {
            totalfee   = parseFloat(totalfee) + addprice - parseFloat(price[ele]);
            price[ele] = addprice;
          } else {
            totalfee   = parseFloat(totalfee) - parseFloat(price[ele]);
            price[ele] = parseFloat('0');
          }
          display( totalfee );
      });
      display( totalfee );
      break;
     }
     display( totalfee );				
    });
});

//calculation for text box.
function calculateText( object ) {
   var textval = parseFloat( cj(object).val() );

   eval( 'var option = '+ cj(object).attr('price') );
   ele         = option[0];
   if ( ! price[ele] ) {
       price[ele] = 0;
   }
   optionPart = option[1].split(optionSep);
   addprice   = parseFloat( optionPart[0] );
   var curval  = textval * addprice;
   if ( textval >= 0 ) {
       totalfee   = parseFloat(totalfee) + curval - parseFloat(price[ele]);
       price[ele] = curval;
   }
   else {
       totalfee   = parseFloat(totalfee) - parseFloat(price[ele]);
       price[ele] = parseFloat('0');
   }
   if(!isNaN(curval) && cj(object).attr('name') == 'price_3') {
     cj('.price-field-amount').text('$ ' + curval.toFixed(2));
   }
   else { 
     cj('.price-field-amount').text('$ 17.00');
   }
   display( totalfee );
}

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