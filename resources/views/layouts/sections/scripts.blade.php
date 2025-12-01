<!-- BEGIN: Vendor JS-->

@vite([
  'resources/assets/vendor/libs/jquery/jquery.js',
  'resources/assets/vendor/libs/popper/popper.js',
  'resources/assets/vendor/js/bootstrap.js',
  'resources/assets/vendor/libs/node-waves/node-waves.js',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
  'resources/assets/vendor/libs/hammer/hammer.js',
  'resources/assets/vendor/libs/typeahead-js/typeahead.js',
  'resources/assets/vendor/js/menu.js',
  'resources/assets/vendor/libs/toastr/toastr.js',
])
<script>
  document.addEventListener('DOMContentLoaded',function(e){
    toastr.options = {
      closeButton: true,
      debug: false,
      newestOnTop: true,
      progressBar: false,
      positionClass: 'toast-top-right',
      preventDuplicates: false,
      onclick: null,
      showDuration: 300,
      hideDuration: 1000,
      timeOut: 6000, // ← Increased from 1000 to 3000
      extendedTimeOut: 1000, // ← Increased for better visibility
      showEasing: 'swing',
      hideEasing: 'linear',
      showMethod: 'fadeIn',
      hideMethod: 'fadeOut'
    };
    @if (session('success'))
      toastr.success("{{session('success')}}", 'Success');
    @elseif(session('warning'))
      toastr.warning("{{session('warning')}}", 'Warning');
    @elseif(session('info'))
      toastr.info("{{session('info')}}", 'Info');
    @elseif(session('error'))
      toastr.error("{{session('error')}}", 'Error');
    @endif
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    @if (session('modal_success'))
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: "{{ session('modal_success') }}",
        customClass: {
          confirmButton: 'btn waves-effect waves-light btn-success'
        },
        buttonsStyling: false
      });
    @elseif (session('modal_warning'))
      Swal.fire({
        icon: 'warning',
        title: 'Warning!',
        text: "{{ session('modal_warning') }}",
        customClass: {
          confirmButton: 'btn waves-effect waves-light btn-warning'
        },
        buttonsStyling: false
      });
    @elseif (session('modal_info'))
      Swal.fire({
        icon: 'info',
        title: 'Information',
        text: "{{ session('modal_info') }}",
        customClass: {
          confirmButton: 'btn waves-effect waves-light btn-info'
        },
        buttonsStyling: false
      });
    @elseif (session('modal_error'))
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: "{{ session('modal_error') }}",
        customClass: {
          confirmButton: 'btn waves-effect waves-light btn-danger'
        },
        buttonsStyling: false
      });
    @endif
  });
</script>
@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])

<!-- END: Theme JS-->
<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->
