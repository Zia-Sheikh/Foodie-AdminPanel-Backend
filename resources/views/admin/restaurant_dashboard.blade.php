<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.css">

<div class="row">
  
  <!-- ./col -->
  <div class="col-lg-3 col-xs-6">
    <!-- small box -->
    <div class="small-box bg-olive">
      <div class="inner">
          
        <a href="/admin/orders">
        <h3 style="color:#FFFFFF;">{{$total_orders}}
        
        </h3>

        <p style="color:#FFFFFF;">Total Orders</p>
        </a>
      </div>
      <div class="icon">
        <i class="fa fa-bar-chart"></i>
      </div>
    </div>
  </div>
  <!-- ./col -->
  <div class="col-lg-3 col-xs-6">
    <!-- small box -->
    <div class="small-box bg-maroon">
      <div class="inner">
          <a href="/admin/orders">
            <h3 style="color:#FFFFFF;">{{$completed_orders}}</h3>
    
            <p style="color:#FFFFFF;">Completed Orders</p>
          </a>
      </div>
      <div class="icon">
        <i class="fa fa-bookmark"></i>
      </div>
    </div>
  </div>
  <!-- ./col -->
  
  <div class="col-lg-3 col-xs-6">
    <!-- small box -->
    <div class="small-box bg-navy">
      <div class="inner">
          <a href="/admin/orders">
            <h3 style="color:#FFFFFF;">{{$pending_orders}}</h3>
    
            <p style="color:#FFFFFF;">Pending orders</p>
            </a>
      </div>
      <div class="icon">
        <i class="fa fa-bookmark"></i>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-xs-6">
    <!-- small box -->
    <div class="small-box bg-orange">
      <div class="inner">
          <a href="/admin/restaurant-earnings">
            <h3 style="color:#FFFFFF;">{{$earnings}}</h3>
    
            <p style="color:#FFFFFF;">Total Earnings</p>
            </a>
      </div>
      <div class="icon">
        <i class="fa fa-money"></i>
      </div>
    </div>
  </div>
  
  <!-- ./col -->
</div>
<div class="col-lg-6">
  <canvas id="orders" width="400"></canvas>
</div>
<div class="col-lg-6">
  <canvas id="earnings" width="400"></canvas>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>

<script>
var ctx_orders = document.getElementById('orders').getContext('2d');
var orders = new Chart(ctx_orders, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: '# of Orders',
            data: [{{ $orders_chart }}],
            backgroundColor: [
                'rgba(54, 162, 235, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(54, 162, 235, 0.2)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(54, 162, 235, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    callback: function(value) {if (value % 1 === 0) {return value;}}
                }
            }]
        }
    }
});

var ctx_earnings = document.getElementById('earnings').getContext('2d');
var earnings = new Chart(ctx_earnings, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: '# of Earnings',
            data: [{{ $earnings_chart }}],
            backgroundColor: [
                'rgba(153, 102, 255, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(153, 102, 255, 0.2)'
            ],
            borderColor: [
                'rgba(153, 102, 255, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(153, 102, 255, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    callback: function(value) {if (value % 1 === 0) {return value;}}
                }
            }]
        }
    }
});
</script>
