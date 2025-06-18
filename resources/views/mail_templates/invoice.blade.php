<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<style type="text/css">
	/* FONTS */
    @media screen {
		@font-face {
		  font-family: 'Lato';
		  font-style: normal;
		  font-weight: 400;
		  src: local('Lato Regular'), local('Lato-Regular'), url(https://fonts.gstatic.com/s/lato/v11/qIIYRU-oROkIk8vfvxw6QvesZW2xOQ-xsNqO47m55DA.woff) format('woff');
		}
		
		@font-face {
		  font-family: 'Lato';
		  font-style: normal;
		  font-weight: 700;
		  src: local('Lato Bold'), local('Lato-Bold'), url(https://fonts.gstatic.com/s/lato/v11/qdgUG4U09HnJwhYI-uK18wLUuEpTyoUstqEm5AMlJo4.woff) format('woff');
		}
		
		@font-face {
		  font-family: 'Lato';
		  font-style: italic;
		  font-weight: 400;
		  src: local('Lato Italic'), local('Lato-Italic'), url(https://fonts.gstatic.com/s/lato/v11/RYyZNoeFgb0l7W3Vu1aSWOvvDin1pK8aKteLpeZ5c0A.woff) format('woff');
		}
		
		@font-face {
		  font-family: 'Lato';
		  font-style: italic;
		  font-weight: 700;
		  src: local('Lato Bold Italic'), local('Lato-BoldItalic'), url(https://fonts.gstatic.com/s/lato/v11/HkF_qI1x_noxlxhrhMQYELO3LdcAZYWl9Si6vvxL-qU.woff) format('woff');
		}
    }
    
    /* CLIENT-SPECIFIC STYLES */
    body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img { -ms-interpolation-mode: bicubic; }

    /* RESET STYLES */
    img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
    table { border-collapse: collapse !important; }
    body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }

    /* iOS BLUE LINKS */
    a[x-apple-data-detectors] {
        color: inherit !important;
        text-decoration: none !important;
        font-size: inherit !important;
        font-family: inherit !important;
        font-weight: inherit !important;
        line-height: inherit !important;
    }

    /* ANDROID CENTER FIX */
    div[style*="margin: 16px 0;"] { margin: 0 !important; }
    
    .column_70 {
      float: left;
      width: 70%;
    }
    
    .column_30 {
      float: left;
      width: 30%;
    }
    
    .rows:after {
      content: "";
      display: table;
      clear: both;
    }
    
    /* Clear floats after the columns */
   

</style>
</head>
<body style="background-color: #FFFFFF; margin: 0 !important; padding: 0 !important;">
    <div style="width:100%;background-color:#FFFFFF;margin-top:20px;" >
        <table>
            <tr>
                <td>
                    <div style="float:left;width:70%">
                      <img src="{{ env('APP_URL') }}/uploads/{{ $data['logo'] }}" style="height:150px;width:150px;" />
                    </div>
                    <div style="float:left;">
                        <div style="padding:10px">
                            <h2 style="color:black;">{{ env('APP_NAME') }}</h2>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
       <hr/>
       <div>
           <h2 style="color:black;">Hi {{ $data['name'] }},</h2>
           <p style="padding: 10px;color:#000;"><b>Congratulations! We have received your order.This is your order confirmation, We will notify you as soon as your items ship.</b></p>
           <p style="padding: 10px;color:#000;"><b>ORDER ID :</b> #{{$data['order_id']}}</p>
           <p style="padding: 10px;color:#000;"><b>Address :</b> {{$data['address']}}</p>
           <p style="padding: 10px;color:#000;"><b>Payment Mode :</b> {{$data['payment_mode']}}</p>
           <p style="padding: 10px;color:#000;"><b>Order items</b></p>
           <table border="0" cellpadding="0" cellspacing="0" width="600" >
                  @foreach($data['items'] as $value)
                  <tr>
                    <td style="padding: 10px;width:20px;">
                      <p style="margin: 0;color:#000;">{{$value['quantity']}}X</p>
                    </td>
                    <td style="padding: 10px;">
                      <p style="margin: 0;color:#000;">{{$value['item_name']}}</p>
                    </td>
                    <td style="padding: 10px;">
                      <p style="margin: 0;color:#000;">₹{{$value['price_per_item']}}</p>
                    </td>
                    <td style="padding: 10px;">
                      <p style="margin: 0;color:#000;">₹{{$value['total']}}</p>
                    </td>
                  </tr>
                  @endforeach
                  <tr>
                    <td style="padding: 10px;">
                      <p style="margin: 0;"></p>
                    </td>
                    <td style="padding: 10px;">
                      <p style="margin: 0;color:#000;"><b>Subtotal</b></p>
                    </td>
                    <td style="padding: 10px;">
                      <p style="margin: 0;color:#000;">₹{{$data['sub_total']}}</p>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding: 10px;">
                      <p style="margin: 0;"></p>
                    </td>
                    <td style="padding: 10px;">
                      <p style="margin: 0;color:#000;"><b>Discount</b></p>
                    </td>
                    <td style="padding: 10px;">
                      <p style="margin: 0;color:#000;">₹{{$data['discount']}}</p>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding: 10px;">
                      <p style="margin: 0;"></p>
                    </td>
                    <td style="padding: 10px;">
                      <p style="margin: 0;color:#000;"><b>Delivery Charge</b></p>
                    </td>
                    <td style="padding: 10px;">
                      <p style="margin: 0;color:#000;">₹{{$data['delivery_charge']}}</p>
                    </td>
                  </tr>
                   <tr>
                    <td style="padding: 10px;">
                      <p style="margin: 0;"></p>
                    </td>
                    <td style="padding: 10px;">
                      <p style="margin: 0;color:#000;"><b>Tax</b></p>
                    </td>
                    <td style="padding: 10px;">
                      <p style="margin: 0;color:#000;">₹{{$data['tax']}}</p>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding: 10px;">
                      <p style="margin: 0;"></p>
                    </td>
                    <td style="padding: 10px;">
                      <p style="margin: 0;color:#000;"><b>Total</b></p>
                    </td>
                    <td style="padding: 10px;">
                      <p style="margin: 0;color:#000;">₹{{$data['total']}}</p>
                    </td>
                  </tr>
                </td>
              </tr>
            </table>
       </div>
       <div style="background-color:#006ac0;padding:5px;">
           <center>
            <h4 style="color:#FFFFFF">{{ env('APP_NAME') }}</h4>
       </center>
       </div>
    </div>
</body>
</html>