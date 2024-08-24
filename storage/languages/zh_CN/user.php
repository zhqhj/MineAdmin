<?php
return [
    'id' => '用户ID，主键',
    'username' => '用户名',
    'user_type' => '用户类型：(100系统用户)',
    'nickname' => '用户昵称',
    'phone' => '手机',
    'email' => '用户邮箱',
    'avatar' => '用户头像',
    'signed' => '个人签名',
    'dashboard' => '后台首页类型',
    'status' => '状态 (1正常 2停用)',
    'login_ip' => '最后登陆IP',
    'login_time' => '最后登陆时间',
    'backend_setting' => '后台设置数据',
    'created_by' => '创建者',
    'updated_by' => '更新者',
    'created_at' => '创建时间',
    'updated_at' => '更新时间',
    'deleted_at' => '删除时间',
    'remark' => '备注',
    'enums' =>  [
        'type'  =>  [
            100 => '系统用户',
            200 => '普通用户'
        ],
        'status'    =>  [
            1   =>  '正常',
            2   =>  '停用'
        ]
    ]
];