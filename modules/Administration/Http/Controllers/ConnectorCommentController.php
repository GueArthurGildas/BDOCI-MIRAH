<?php

namespace Modules\Administration\Http\Controllers;

use App\Enums\ConnectorCommentKind;
use App\Http\Controllers\Controller;
use App\Models\DataSource;
use App\Support\Authorization\Permission;
use App\Support\Documents\CommentService;
use App\Support\Notifications\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Administration\Notifications\ConnectorCommentAlert;

class ConnectorCommentController extends Controller
{
    private const MAX_LENGTH = 2000;

    public function store(Request $request, DataSource $dataSource, Notifier $notifier, CommentService $comments): RedirectResponse
    {
        $validated = $request->validate([
            'kind' => ['required', Rule::enum(ConnectorCommentKind::class)],
            'body' => ['required', 'string', 'max:'.self::MAX_LENGTH],
            'notify' => ['sometimes', 'boolean'],
        ], [], [
            'kind' => __('administration::administration.connectors.comment_kind'),
            'body' => __('administration::administration.connectors.comment_body'),
        ]);

        $comment = $comments->add($dataSource, $request->user(), $validated['body'], $validated['kind']);

        if ($request->boolean('notify')) {
            $notifier->notifyPermissionHolders(Permission::ConnectorsManage, new ConnectorCommentAlert($comment), cooldownHours: 1, except: $request->user());
        }

        return to_route('administration.connectors.show', [$dataSource, 'tab' => 'comments'])
            ->with('status', __('administration::administration.connectors.comment_added'));
    }
}
