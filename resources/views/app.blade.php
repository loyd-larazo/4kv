<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">

      <title>4KV Hardware and Construction Supply</title>

      <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
      <link href="/fontawesome/css/all.min.css" rel="stylesheet">
      <link href="/css/app.css" rel="stylesheet">

      <script src="/js/jquery-3.6.1.min.js"></script>
      <script src="/js/jquery.canvasjs.min.js"></script>
      <script src="/bootstrap/js/bootstrap.min.js"></script>
  </head>
  <body>
    <div>
      <a href="/">
        <img class="logo" src="/images/logo.png"/>
      </a>
    </div>

    <!-- Sidebar -->
    <nav id="sidebarMenu" class="d-lg-block sidebar bg-white">
      <div class="position-sticky">
        <div class="list-group list-group-flush mx-3 mt-4">
          <a href="/" class="list-group-item list-group-item-action ripple {{ request()->route()->getName() == 'dashboard' ? 'active' : ''  }}" aria-current="true">
            <i class="fas fa-tachometer-alt fa-fw me-3"></i><span>Dashboard</span>
          </a>

          <span class="list-group-item ripple">
            <i class="fas fa-chart-area fa-fw me-3"></i><span>POS</span>
          </span>
          <a href="/sell" class="list-group-item list-group-item-action ripple ps-5 {{ request()->route()->getName() == 'sell' ? 'active' : ''  }}">
            <i class="fa-solid fa-cash-register me-3"></i><span>Sell</span>
          </a>
          <a href="/sales" class="list-group-item list-group-item-action ripple ps-5 {{ request()->route()->getName() == 'sales' ? 'active' : ''  }}">
            <i class="fa-solid fa-file-invoice me-3"></i><span>Sales</span>
          </a>

          <span class="list-group-item ripple">
            <i class="fa-solid fa-warehouse me-3"></i><span>Inventory</span>
          </span>
          <a href="/items" class="list-group-item list-group-item-action ripple ps-5 {{ request()->route()->getName() == 'items' ? 'active' : ''  }}">
            <i class="fa-solid fa-boxes-packing me-3"></i><span>Items</span>
          </a>
          <a href="/categories" class="list-group-item list-group-item-action ripple ps-5 {{ request()->route()->getName() == 'categories' ? 'active' : ''  }}">
            <i class="fa-solid fa-table-cells-large me-3"></i><span>Categories</span>
          </a>
          <a href="/transactions" class="list-group-item list-group-item-action ripple ps-5 {{ request()->route()->getName() == 'transactions' ? 'active' : ''  }}">
            <i class="fa-solid fa-cart-flatbed me-3"></i><span>Transactions</span>
          </a>
          <a href="/suppliers" class="list-group-item list-group-item-action ripple ps-5 {{ request()->route()->getName() == 'suppliers' ? 'active' : ''  }}">
            <i class="fa-solid fa-truck-field me-3"></i><span>Suppliers</span>
          </a>
          <a href="/laborers" class="list-group-item list-group-item-action ripple ps-5 {{ request()->route()->getName() == 'laborers' ? 'active' : ''  }}">
            <i class="fa-solid fa-people-carry-box me-3"></i><span>Laborers</span>
          </a>

          <a href="/settings" class="list-group-item list-group-item-action ripple {{ request()->route()->getName() == 'settings' ? 'active' : ''  }}">
            <i class="fa-solid fa-gears me-3"></i><span>Settings</span>
          </a>
          
        </div>
      </div>
    </nav>
      <!-- Sidebar -->
    
    <!--Main Navigation-->

    <!--Main layout-->
    <main class="main-content px-4">
      <div class="pt-4">
        <div class="card">
          <div class="card-body">
            @yield('content')
          </div>
        </div>
      </div>
    </main>
    <!--Main layout-->
    
  </body>
</html>
