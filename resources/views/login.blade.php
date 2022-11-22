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
      <script src="/bootstrap/js/bootstrap.min.js"></script>
  </head>
  <body>
    <section class="vh-100 login-container">
      <form class="container py-5 h-100" action="/login" method="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="row d-flex justify-content-center align-items-center h-100">
          <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="card shadow-2-strong" style="border-radius: 1rem;">
              <div class="card-body p-5 text-center">
                @if(isset($error))
                  <div class="alert alert-danger" role="alert">
                    {{ $error }}
                  </div>
                @endif

                <h3 class="mb-5">Sign in</h3>

                <div class="form-outline mb-4">
                  <input 
                    type="text" 
                    name="username" 
                    class="form-control form-control-lg" 
                    placeholder="Username" 
                    value="{{ isset($username) ? $username : '' }}"/>
                </div>

                <div class="form-outline mb-4">
                  <input type="password" name="password" id="typePasswordX-2" class="form-control form-control-lg" placeholder="Password"/>
                </div>

                <button class="btn btn-outline-success btn-lg btn-block" type="submit">Login</button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </section>
  </body>
</html>
