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

namespace App\Repository\Permission;

use App\Model\Enums\User\Type;
use App\Model\Permission\User;
use App\Repository\IRepository;
use Hyperf\Collection\Arr;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\ModelNotFoundException;

/**
 * Class UserRepository.
 * @extends IRepository<User>
 */
final class UserRepository extends IRepository
{
    public function __construct(protected readonly User $model) {}

    public function findByUnameType(string $username, Type $userType = Type::SYSTEM): User
    {
        // @phpstan-ignore-next-line
        return $this->model->newQuery()
            ->where('username', $username)
            ->where('user_type', $userType)
            ->firstOrFail();
    }

    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query
            ->when(Arr::get($params, 'unique_username'), static function (Builder $query, $uniqueUsername) {
                $query->where('username', $uniqueUsername);
            })
            ->when(Arr::get($params, 'username'), static function (Builder $query, $username) {
                $query->where('username', 'like', '%' . $username . '%');
            })
            ->when(Arr::get($params, 'phone'), static function (Builder $query, $phone) {
                $query->where('phone', $phone);
            })
            ->when(Arr::get($params, 'email'), static function (Builder $query, $email) {
                $query->where('email', $email);
            })
            ->when(Arr::exists($params, 'status'), static function (Builder $query) use ($params) {
                $query->where('status', Arr::get($params, 'status'));
            })
            ->when(Arr::exists($params, 'user_type'), static function (Builder $query) use ($params) {
                $query->where('user_type', Arr::get($params, 'user_type'));
            })
            ->when(Arr::exists($params, 'nickname'), static function (Builder $query) use ($params) {
                $query->where('nickname', 'like', '%' . Arr::get($params, 'nickname') . '%');
            })
            ->when(Arr::exists($params, 'created_at'), static function (Builder $query) use ($params) {
                $query->whereBetween('created_at', [
                    Arr::get($params, 'created_at')[0] . ' 00:00:00',
                    Arr::get($params, 'created_at')[1] . ' 23:59:59',
                ]);
            })
            ->when(Arr::get($params, 'user_ids'), static function (Builder $query, $userIds) {
                $query->whereIn('id', $userIds);
            })
            ->when(Arr::get($params, 'role_id'), static function (Builder $query, $roleId) {
                $query->whereHas('roles', static function (Builder $query) use ($roleId) {
                    $query->where('role_id', $roleId);
                });
            });
    }

    public function findOrCreateByIdCard(string $idCardName, string $idCardNumber): User
    {
        // 尝试查找用户
        try {
            return $this->getQuery()
                ->where('id_card_name', $idCardName)
                ->where('id_card_number', $idCardNumber)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $uniqid = uniqid();
            // 如果用户不存在，则创建新用户
            return User::create([
                'username' => $uniqid,
                'user_type' => Type::USER,
                'id_card_name' => $idCardName,
                'id_card_number' => $idCardNumber,
                'nickname' => '用户_' . $uniqid, // 默认昵称
            ]);
        }
    }


    public function findOrCreateByOpenid(string $openid): User
    {
        // 尝试查找用户
        try {
            return $this->model->newQuery()->where('openid', $openid)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $uniqid = uniqid();
            // 如果用户不存在，则创建新用户
            return User::create([
                'username'=>$uniqid,
                'user_type'=>Type::USER,
                'openid' => $openid,
                'nickname' => '用户_' .$uniqid, // 默认用户名
            ]);
        }
    }

    /**
     * 更新用户的手机号
     */
    public function updatePhoneNumber(int $userId, string $phoneNumber): void
    {
        $user = User::find($userId);
        if ($user) {
            $user->phone = $phoneNumber; // 假设有 phone_number 字段
            $user->save();
        } else {
            throw new ModelNotFoundException('用户不存在');
        }
    }
}
