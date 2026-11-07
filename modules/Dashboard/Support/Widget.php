<?php

namespace Modules\Dashboard\Support;

use App\Support\Authorization\Permission;

final readonly class Widget
{
    public const KPI = 'kpi';

    public const CHART = 'chart';

    public const TABLE = 'table';

    public function __construct(
        public string $key,
        public string $kind,
        public string $icon,
        public string $color,
        public Permission $permission,
        public ?string $detailRoute = null,
        public ?string $indicator = null,
    ) {}

    public function title(): string
    {
        return $this->indicator
            ? __('indicators::indicators.names.'.$this->indicator)
            : __('dashboard::widgets.'.$this->key.'.title');
    }

    public function explanation(): string
    {
        return $this->indicator
            ? __('indicators::indicators.descriptions.'.$this->indicator)
            : __('dashboard::widgets.'.$this->key.'.explanation');
    }

    public function kindLabel(): string
    {
        return __('dashboard::widgets.kinds.'.$this->kind);
    }
}
