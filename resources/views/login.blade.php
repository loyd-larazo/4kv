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
      <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
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
                <div id="customError" class="alert alert-danger text-center" role="alert"></div>
                <div id="customSuccess" class="alert alert-success text-center" role="alert"></div>

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
            <div class="reset-password">Reset password? Click <a href="#" data-bs-toggle="modal" data-bs-target="#resetModal">here</a></div>
          </div>
        </div>
      </form>

      <div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
          <div class="modal-content">
            <div>
              <input type="hidden" name="_token" value="{{ csrf_token() }}" />
              <div class="modal-header">
                <h5 class="modal-title" id="resetModalLabel">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                Please enter your email to reset your password:
                <input type="email" class="form-control" id="inputEmail">
              </div>
              <div class="modal-footer">
                <button type="button" id="resetBtn" class="btn btn-outline-secondary" disabled>Yes</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script>
        $(function() {
          $('#customSuccess').hide();
          $('#customError').hide();
          var email = "{{ $email }}";
          var admin = @json($admin);

          $('#inputEmail').keyup(function() {
            let val = $(this).val();
            if (val == email)
              $('#resetBtn').removeAttr('disabled').removeClass('btn-outline-secondary').addClass('btn-outline-warning');
            else
              $('#resetBtn').attr('disabled', 'disabled').removeClass('btn-outline-warning').addClass('btn-outline-secondary');
          });

          $('#resetBtn').click(function() {
            if ($('#inputEmail').val() == email) {
              var newPassword = generateRandomString(10);
              changePassword(newPassword);
            }
          });

          function generateRandomString(length) {
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
              result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
          }
  
          function sendEmailVerify(newPassword) {
            var adminName = admin.firstname;
            if (email && adminName && newPassword) {
              var templateParams = {
                to_email: email,
                to_name: adminName,
                new_password: newPassword
              };
    
              emailjs.init("Kl3kdiSrMNGEK0Tqt");
              emailjs.send('service_dwirg5s', 'template_8mqe2np', templateParams)
                .then(function(response) {
                  console.log('SUCCESS!', response.status, response.text);
                  $('#resetModal').modal('hide');
                  $('#customSuccess').html('Please check your registered email.').show();
                  // setTimeout(() => { $('#customSuccess').hide(); } , 2000);
                }, function(error) {
                  alertError(error);
                });
            }
          }

          $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() }
          });

          function changePassword(newPassword) {
            var adminId = admin.id;
            $.ajax({
              type: 'POST',
              dataType: 'json',
              url: `/reset-password`,
              data: {
                id: adminId,
                new_password: newPassword
              },
              success: (data) => {
                if (data.data) sendEmailVerify(newPassword);
                else alertError(`Password not change.`);
              }
            });
          }

          function alertError(error) {
            console.error(error);
            $('#resetModal').modal('hide');
            $('#customError').html('Something went wrong.').show();
          }
        });
      </script>
    </section>
  </body>
</html>
