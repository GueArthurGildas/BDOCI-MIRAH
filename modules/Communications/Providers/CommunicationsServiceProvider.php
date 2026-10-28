<?php

namespace Modules\Communications\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Communications\Channels\FakeWhatsAppGateway;
use Modules\Communications\Channels\WhatsAppGateway;
use Modules\Communications\Console\DispatchAutomaticMessages;

class CommunicationsServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WhatsAppGateway::class, fn () => match (config('communications.whatsapp_driver')) {
            'fake' => new FakeWhatsAppGateway,
        });
    }

    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('communications::communications.title', 'communications.messages.index', 'icon-mail', NavigationGroup::Surveillance, order: 50, permission: Permission::CommunicationsView->value)
            ->add('communications::ai_agent.title', 'communications.ai-agent.edit', 'icon-cpu', NavigationGroup::Administration, order: 55, permission: Permission::SettingsManage->value);
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(DispatchAutomaticMessages::class)->everyFifteenMinutes()->withoutOverlapping();
    }

    protected function consoleCommands(): array
    {
        return [DispatchAutomaticMessages::class];
    }
}
