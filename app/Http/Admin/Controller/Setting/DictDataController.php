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

namespace App\Http\Admin\Controller\Setting;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\CurrentUser;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\DictDataRequest;
use App\Http\Common\Middleware\AuthMiddleware;
use App\Http\Common\Result;
use App\Kernel\Annotation\Permission;
use App\Kernel\Swagger\Attributes\ResultResponse;
use App\Service\Setting\DictDataService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Delete;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\Swagger\Annotation\Put;
use Hyperf\Swagger\Annotation\RequestBody;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AuthMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
final class DictDataController extends AbstractController
{
    public function __construct(
        private readonly CurrentUser $user,
        private readonly DictDataService $service
    ) {}

    #[Get(
        path: '/admin/dictData/list',
        operationId: 'dictDataList',
        summary: '字典数据列表',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['字典数据']
    )]
    #[ResultResponse(instance: new Result())]
    #[Permission('dictData:list')]
    public function pageList(): Result
    {
        return $this->success($this->service->page(
            $this->getRequest()->all(),
            $this->getCurrentPage(),
            $this->getPageSize()
        ));
    }

    #[Post(
        path: '/admin/dictData',
        operationId: 'dictDataCreate',
        summary: '字典数据创建',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['字典数据']
    )]
    #[ResultResponse(instance: new Result())]
    #[Permission('dictData:create')]
    #[RequestBody(content: new JsonContent(ref: DictDataRequest::class))]
    public function create(DictDataRequest $request): Result
    {
        $this->service->create(array_merge(
            $request->validated(),
            ['created_by' => $this->user->id()]
        ));
        return $this->success();
    }

    #[Put(
        path: '/admin/dictData/{id}',
        operationId: 'dictDataSave',
        summary: '字典数据保存',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['字典数据']
    )]
    #[ResultResponse(instance: new Result())]
    #[Permission('dictData:save')]
    #[RequestBody(content: new JsonContent(ref: DictDataRequest::class))]
    public function save(int $id, DictDataRequest $request): Result
    {
        $this->service->updateById($id, array_merge($request->validated(), [
            'updated_by' => $this->user->id(),
        ]));
        return $this->success();
    }

    #[Delete(
        path: '/admin/dictData/{id}',
        operationId: 'dictDataDelete',
        summary: '字典数据删除',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['字典类型']
    )]
    #[ResultResponse(instance: new Result())]
    #[Permission('dictData:delete')]
    public function delete(int $id): Result
    {
        $this->service->deleteById($id, false);
        return $this->success();
    }
}
