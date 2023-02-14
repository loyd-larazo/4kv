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
      <script>
        $(function() {
          sessionStorage.setItem('login', true);

          $('#typePasswordX-2').on('change paste keyup focus', function() {
            if ($(this).val()) {
              $(this).attr('type', 'password');
            } else {
              $(this).attr('type', 'text');
            }
          });
        });
      </script>
  </head>
  <body>
    <section class="vh-100 login-container">
      <form class="container py-5 h-100" action="/login" method="POST" autocomplete="new-password">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="row d-flex justify-content-center align-items-center h-100">
          <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="card shadow-2-strong" style="border-radius: 1rem;">
              <div class="card-body p-5 text-center">
                @if(isset($error))
                  <div class="alert alert-danger text-center" role="alert">
                    {{ $error }}
                  </div>
                @endif

                <h3 class="mb-5">Sign in</h3>
                <input style="display: none" type="text" name="fakeusernameremembered" />
                <input style="display: none" type="password" name="fakepasswordremembered" autocomplete="new-password" />

                <div class="form-outline mb-4">
                  <input 
                    type="text" 
                    name="username" 
                    class="form-control form-control-lg" 
                    placeholder="Username" 
                    autocomplete="off"
                    value="{{ isset($username) ? $username : '' }}"/>
                </div>

                <div class="form-outline mb-4">
                  <input 
                    type="text" 
                    autocomplete="new-password"
                    name="password" 
                    id="typePasswordX-2" 
                    class="form-control form-control-lg" 
                    placeholder="Password" />
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
