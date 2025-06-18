<div class="container">
    <div class="col-lg-8">
        <div class="table-responsive">          
          <table class="table">
            <tbody>
              <tr>
                <th>Order Id</th>
                <td>{{$order_id}}</td>
              </tr>
              <tr>
                <th>Customer Name</th>
                <td>{{$customer_name}}</td>
              </tr>
              <tr>
                <th>Customer Phone Number</th>
                <td>{{$phone_number}}</td>
              </tr>
              <tr>
                <th>Customer Address</th>
                <td>{{$address}}</td>
              </tr>
              <tr>
                <th>Restaurant Name</th>
                <td>{{$restaurant_name}}</td>
              </tr>
              <tr>
                <th>Restaurant Phone Number</th>
                <td>{{$restaurant_phone_number}}</td>
              </tr>
              <tr>
                <th>Delivered By</th>
                <td>{{$delivered_by}}</td>
              </tr>
              <tr>
                <th>Payment Mode</th>
                <td>{{$payment_mode}}</td>
              </tr>
              <tr>
                <th>Sub Total</th>
                <td>{{$sub_total}}</td>
              </tr>
              <tr>
                <th>Discount</th>
                <td>{{$discount}}</td>
              </tr>
              <tr>
                <th>Delivery Charge</th>
                <td>{{$delivery_charge}}</td>
              </tr>
              <tr>
                <th>Platform Fee</th>
                <td>{{$platform_fee}}</td>
              </tr>
              <tr>
                <th>Total</th>
                <td>{{$total}}</td>
              </tr>
              <tr>
                <th>Status</th>
                <td>{{$status}}</td>
              </tr>
            </tbody>
          </table>
        </div>
    </div>
    <div class="col-md-2 col-md-offset-2">
        <a href='/admin/orders' class='btn btn-info pull-right' style='margin-right:20px;'>Back</a>
    </div>
    <div class="col-lg-12">
        <h3>Items</h3>
        <table class="table table-hover">
            <thead>
              <tr>
                <th>S.No</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Price Per Item</th>
                <th>Total</th>
                <th>Options</th>
              </tr>
            </thead>
            <tbody>
            <?php $i=1; ?>
            @foreach($order_items as $value)
              <tr>
                <td>{{ $i }}</td>
                <td>{{ $value->item_name }}</td>
                <td>{{ $value->quantity }}</td>
                <td>{{ $value->price_per_item }}</td>
                <td>{{ $value->total }}</td>
                <td>
                    @foreach($value->options as $key => $options)
                        <p><b>Options : </b>{{$options->slug_value}} / <b>Qty : </b>{{$options->qty}}</p>
                    @endforeach
                </td>
              </tr>
              <?php $i++; ?>
            @endforeach
            </tbody>
        </table>
    </div>
</div>