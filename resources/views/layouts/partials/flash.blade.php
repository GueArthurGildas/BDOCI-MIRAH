@if (session('status'))
    <div class="alert alert-success background-success alert-dismissible fade show" role="alert">
        <i class="feather icon-check-circle m-r-5"></i> {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.close') }}"></button>
    </div>
@endif
