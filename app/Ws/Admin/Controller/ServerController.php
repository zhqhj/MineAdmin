<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Ws\Admin\Controller;

use App\Service\DataCenter\QueueMessageService;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\WebSocketServer\Context;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

use function App\Kernel\container;

/**
 * Class ServerController.
 */
class ServerController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * 成功连接到 ws 回调.
     * @param Response|Server $server
     * @param Request $request
     */
    public function onOpen($server, $request): void
    {
        $uid = user()->getUserInfo(
            container()->get(ServerRequestInterface::class)->getQueryParams()['token']
        )['id'];
        Context::set('uid', $uid);

        console()->info(
            "WebSocket [ user connection to message server: id > {$uid}, " .
            "fd > {$request->fd}, time > " . date('Y-m-d H:i:s') . ' ]'
        );
    }

    /**
     * 消息回调.
     * @param Response|Server $server
     * @param Frame $frame
     */
    public function onMessage($server, $frame): void
    {
        $data = json_decode($frame->data, true);
        switch ($data['event']) {
            case 'get_unread_message':
                $service = container()->get(QueueMessageService::class);
                $server->push($frame->fd, json_encode([
                    'event' => 'ev_new_message',
                    'message' => 'success',
                    'data' => $service->getUnreadMessage(Context::get('uid'))['items'],
                ]));
                break;
        }
    }

    /**
     * 关闭 ws 连接回调.
     * @param Response|\Swoole\Server $server
     */
    public function onClose($server, int $fd, int $reactorId): void
    {
        console()->info(
            'WebSocket [ user close connect for message server: id > ' . Context::get('uid') . ', ' .
            "fd > {$fd}, time > " . date('Y-m-d H:i:s') . ' ]'
        );
    }
}
