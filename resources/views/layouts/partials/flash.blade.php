@if (session('status') && ! session('popup'))
    <div class="alert alert-success background-success alert-dismissible fade show" role="alert">
        <i class="feather icon-check-circle m-r-5"></i> {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.close') }}"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger background-danger alert-dismissible fade show" role="alert">
        <i class="feather icon-alert-circle m-r-5"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.close') }}"></button>
    </div>
@endif
