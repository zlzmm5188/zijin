<?php
/**
 * 系统配置保存API
 * POST /api/admin/config-save
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方式错误', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

// 支持批量保存
$configs = $data['configs'] ?? [];

// 单个保存
if (empty($configs) && !empty($data['key'])) {
    $configs = [$data];
}

if (empty($configs)) {
    Response::error('配置数据不能为空');
}

$db = Database::getInstance();

try {
    $db->beginTransaction();

    foreach ($configs as $config) {
        $group = trim($config['group'] ?? '');
        $key = trim($config['key'] ?? '');
        $value = $config['value'] ?? '';
        $type = trim($config['type'] ?? 'string');
        $description = trim($config['description'] ?? '');
        $isPublic = (int)($config['is_public'] ?? 0);
        $sortOrder = (int)($config['sort_order'] ?? 0);

        if (empty($group) || empty($key)) {
            continue;
        }

        // 处理值
        if ($type === 'boolean') {
            $value = $value ? 'true' : 'false';
        } elseif ($type === 'json' && is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        // 检查是否存在
        $existSql = "SELECT id FROM " . $db->getPrefix() . "system_configs 
                     WHERE `group` = :group AND `key` = :key";
        $exist = $db->fetchOne($existSql, ['group' => $group, 'key' => $key]);

        if ($exist) {
            // 更新
            $db->update('system_configs', [
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'is_public' => $isPublic,
                'sort_order' => $sortOrder
            ], 'id = :id', ['id' => $exist['id']]);
        } else {
            // 新增
            $db->insert('system_configs', [
                'group' => $group,
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'is_public' => $isPublic,
                'sort_order' => $sortOrder
            ]);
        }
    }

    $db->commit();

    Response::success([], '保存成功');

} catch (Exception $e) {
    $db->rollBack();
    error_log("配置保存失败: " . $e->getMessage());
    Response::error('保存失败');
}
