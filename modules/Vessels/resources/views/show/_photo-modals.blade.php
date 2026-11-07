<div class="modal fade gs-modal" id="gs-photo-viewer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-body p-0 text-center">
                <button type="button" class="btn-close gs-photo-close" data-bs-dismiss="modal" aria-label="{{ __('ui.close') }}"></button>
                <img src="" alt="" class="gs-photo-large">
                <p class="gs-photo-caption mb-0" data-gs-photo-caption></p>
            </div>
        </div>
    </div>
</div>

@can(App\Support\Authorization\Permission::VesselsManage->value)
    <div class="modal fade gs-modal" id="gs-photo-manage" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="gs-quick-head">
                        <span class="gs-modal-icon"><i class="feather icon-camera"></i></span>
                        <div>
                            <h5>{{ __('vessels::vessels.photos.title') }}</h5>
                            <small>{{ trans_choice('vessels::vessels.photos.count', $photos->count(), ['count' => $photos->count()]) }}</small>
                        </div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="{{ __('ui.close') }}"></button>
                    </div>
                    @include('vessels::show._photo-manager')
                </div>
            </div>
        </div>
    </div>
@endcan
