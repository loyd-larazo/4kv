<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">

      <title>4KV Hardware and Construction Supply</title>

      <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
      <link href="/fontawesome/css/all.min.css" rel="stylesheet">
      <link href="/css/app.css" rel="stylesheet">
      <link href="/jquery-ui/jquery-ui.min.css" rel="stylesheet">
      
      <script src="/js/jquery-3.6.1.min.js"></script>
      <script src="/js/jquery.canvasjs.min.js"></script>
      <script src="/jquery-ui/jquery-ui.min.js"></script>
      <script src="/bootstrap/js/bootstrap.bundle.min.js"></script>
      <script>
        const isLoggedin = sessionStorage.getItem('login');
        if (!isLoggedin) location.href = "/logout";
      </script>
  </head>
  <body>

    <?php 
      $user = Session::get('user');
    ?>

    <!--Main Navigation-->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <div class="container-fluid">
        <a href="/">
          <img class="logo" src="/images/logo.png"/>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            @if(in_array($user->type, ["admin", "cashier"]))
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {{ in_array(request()->route()->getName(), ['sales','cashier', 'dailySales', 'returnItems']) ? 'text-primary' : '' }}" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-chart-area fa-fw me-3"></i><span>POS</span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <li>
                    <a href="/cashier" class="dropdown-item {{ in_array(request()->route()->getName(), ['cashier']) ? 'text-primary' : '' }}">
                      <i class="fa-solid fa-cash-register me-3"></i><span>Cashier</span>
                    </a>
                  </li>
                  <li>
                    <a href="/sales" class="dropdown-item {{ in_array(request()->route()->getName(), ['sales']) ? 'text-primary' : '' }}">
                      <i class="fa-solid fa-file-invoice me-3"></i><span>Sales</span>
                    </a>
                  </li>
                  <li>
                    <a href="/daily-sales" class="dropdown-item {{ in_array(request()->route()->getName(), ['dailySales']) ? 'text-primary' : '' }}">
                      <i class="fa-regular fa-calendar-check me-3"></i><span>Daily Sales</span>
                    </a>
                  </li>
                  <li>
                    <a href="/return-items" class="dropdown-item {{ in_array(request()->route()->getName(), ['returnItems']) ? 'text-primary' : '' }}">
                      <i class="fa-solid fa-right-left me-3"></i><span>Refund Items</span>
                    </a>
                  </li>
                </ul>
              </li>
            @endif
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle {{ in_array(request()->route()->getName(), ['items','categories','transactions', 'addTransactionPage','suppliers'])  ? 'text-primary' : '' }}" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-warehouse me-3"></i><span>Inventory</span>
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <li>
                  <a href="/items" class="dropdown-item {{ in_array(request()->route()->getName(), ['items'])  ? 'text-primary' : '' }}">
                    <i class="fa-solid fa-boxes-packing me-3"></i><span>Items</span>
                  </a>
                </li>
                <li>
                  <a href="/categories" class="dropdown-item {{ in_array(request()->route()->getName(), ['categories'])  ? 'text-primary' : '' }}">
                    <i class="fa-solid fa-table-cells-large me-3"></i><span>Categories</span>
                  </a>
                </li>
                @if(in_array($user->type, ["admin", "stock man"]))
                  <li>
                    <a href="/transactions" class="dropdown-item {{ in_array(request()->route()->getName(), ['transactions', 'addTransactionPage'])  ? 'text-primary' : '' }}">
                      <i class="fa-solid fa-cart-flatbed me-3"></i><span>Transactions</span>
                    </a>
                  </li>
                  <li>
                    <a href="/suppliers" class="dropdown-item {{ in_array(request()->route()->getName(), ['suppliers'])  ? 'text-primary' : '' }}">
                      <i class="fa-solid fa-truck-field me-3"></i><span>Suppliers</span>
                    </a>
                  </li>
                @endif
              </ul>
            </li>
            @if(in_array($user->type, ["admin"]))
              <li>
                <a class="nav-link {{ in_array(request()->route()->getName(), ['users'])  ? 'text-primary' : '' }}" href="/users">
                  <i class="fa-solid fa-users me-3"></i><span>Users</span>
                </a>
              </li>
            @endif
          </ul>

          <ul class="navbar-nav mb-2 mb-lg-0">
            <li class="nav-item dropdown">
              <span class="nav-link">Welcome {{ $user->firstname ." ". $user->lastname }}</span>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link" href="/logout">
                <i class="fa-solid fa-right-from-bracket me-2"></i><span>Logout</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main>
      <div class="container-fluid pt-4">
        <div class="card">
          <div class="card-body">
            @yield('content')
          </div>
        </div>
      </div>
    </main>
  </body>
</html>
